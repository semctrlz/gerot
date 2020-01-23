<?php
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use Zware\Model\Chaves;
use Zware\Model\Empresas;
use \Zware\Pagina;
use \Zware\PaginaInicial;
use \Zware\Model\User;
use \Zware\Model\Tickets;
use \Zware\Model\Rotinas;
use \Zware\Model\Files;
use \Zware\Model\Funcoes;

$app = new Slim();

$app->config('debug', true);

require_once("functions.php");

$app->get('/', function () {

	$user = new User();
	$user->loadCookie();
	$dadosUsuario = User::retornaDadosDaSession();

	$alerta = "";
	if(isset($_GET['redefinir']))
	{
		$alerta = "E-mail para redefinição de senha enviado com sucesso!";
	}
	$dadosUsuario['alerta'] = $alerta;
	if(isset($_GET['perfil'])){
		$dadosUsuario['deshabiliadades_arr'] = Funcoes::separaTexto($dadosUsuario['deshabiliadades']);
		$dadosUsuario['deseducacao_arr'] = Funcoes::separaTexto($dadosUsuario['deseducacao']);
		User::verificaAcesso("perfil", $dadosUsuario, "Perfil");
		exit;
	}
	User::verificaAcesso("index", $dadosUsuario, "Home");
	exit;
});

$app->post('/', function(){

	if(isset($_POST)){
		if(isset($_GET['perfil'])){
			if(isset($_GET['EditFoto'])){
				if(isset($_FILES)){
					$idUsuario = isset($_POST["idusuario"])?$_POST["idusuario"]:0;
					$foto = isset($_FILES["foto"])?$_FILES["foto"]:"";

					if($idUsuario > 0){

						$usuario = new User();
						try{
							$usuario->salvaFotoPerfil($foto);
							User::atualizaSession();
						}catch(Exception $e){
							echo($e->getMessage());
							exit;
						}

						header('Location: ?perfil');
						exit;
					}
				}
			}
		}
	}else{
		header("Location: /");
	}

});

$app->get('/invitation(/)',function(){

	if(isset($_GET["code"]) && isset($_GET["resposta"])){
		$codigo = $_GET["code"];
		$resposta = $_GET["resposta"];

		$dados = array();
		$dados["erro"] = "";

		$idconvite = User::decodeBase64($codigo);

		$dados["convite"] = User::dadosConvite($idconvite);


		if(count($dados["convite"])==0)
		{	$dados["tipo"] = "outro";
			$dados["erro"] = "Código não encontrado. Por favor, clique no link enviado para o seu e-mail.";
		}
		else
		{
			if($resposta == "negativo")
			{
				$dados["tipo"] = "negacao";
				//Função que nega o pedido de Participação
				if(!User::ConviteNegado($idconvite))
				{
					$dados["erro"] = Empresas::getError();
				}
			}
			else if($resposta == "positivo")
			{
				$dados["tipo"] = "confirmacao";
				//Funcção que autoriza a participação

				if(!User::ConviteAceito($idconvite))
				{
					$dados["erro"] = Empresas::getError();
				}
			}
		}
	}

	$page = new PaginaInicial($dados, "convite");
	$page->setTpl("feedbackConvite");
	exit;
});

$app->get('/uploads(/)', function () {

	$user = new User;

	header("Location: /");

	exit;
});

$app->get('/login(/)', function () {

	if (User::verifyLogin()) {
		header("Location: /");
		exit;
	} else {
		$page = new PaginaInicial([], "Login");

		$page->setTpl("login", [
			'error' => User::getError(),
			'message' => ''
		]);
	}
});

$app->post('/login(/)', function () {


	if (isset($_POST["remember"]) && $_POST["remember"] != "") {
		$conexaoAutomatica = TRUE;
	} else {
		$conexaoAutomatica = FALSE;
	}

	try {
		User::login($_POST["desemail"], $_POST["dessenha"], $conexaoAutomatica);
	} catch (Exception $e) {
		User::setErro($e->getMessage());
		header("Location: /login");
		exit;
	}

	header("Location: /");
	exit;
});

$app->get('/logout(/)', function () {
	User::logout();
	header("Location: /");
	exit;
});

$app->get('/cadastro(/)', function () {
	if (User::verifyLogin()) {
		header("Location: /");
		exit;
	} else {

		$valores = User::Cadastro(null);

		$page = new PaginaInicial($valores, "Cadastro");

		$page->setTpl("cadastro");
	}
});

$app->post('/cadastro(/)', function () {

	if (isset($_POST)) {
		$valores = User::cadastro($_POST);
	} else {
		$valores = User::cadastro(null);
	}
	$page = new PaginaInicial($valores, "Cadastro");

	$page->setTpl("cadastro");
});

$app->get('/confirmacao_cadastro', function () {

	$dados = User::getInfo();

	if (isset($dados) && $dados) {
		$page = new PaginaInicial($dados, "Confirmacao");
		$page->setTpl("confirmacaoCadastro");
	} else {
		header("Location: /");
		exit;
	}
});

$app->get('/recovery(/)', function () {

	if (User::verifyLogin()) {
		header("Location: /");
		exit;
	} else {
		$page = new PaginaInicial([
			"erro" => "",
			"email" => ""
		], "Recuperar senha");

		$page->setTpl("recuperarSenha");
	}
});

$app->post('/recovery(/)', function () {

	$tipo = isset($_POST["tipo"])?$_POST["tipo"]:"";

	if (User::verifyLogin() && $tipo == "") {
		header("Location: /");
		exit;
	} else {
		$email = $_POST["email"];

		$valores = [
			"erro" => "",
			"email" => $email
		];


		try {

			User::recuperacaoSenha($email);

			if($tipo=="")
			{
				$page = new PaginaInicial([], "Recuperar senha");
				$page->setTpl("feedbackRecuperacaoSenha");
				exit;
			}
			header("Location: /?perfil&redefinir=true");
			exit;
		} catch (Exception $e) {
			$valores["erro"] = $e->getMessage();
			$page = new PaginaInicial($valores, "Recuperar senha");
			$page->setTpl("recuperarSenha");
			exit;
		}
	}
});

$app->get('/reset(/)', function () {

	if (isset($_GET['code']) && $_GET['code']) {
		$info = array(
			"error" => "",
			"nome" => "",
			"idUsuario" => ""
		);

		try {
			//Exibe a tela para alteração de senha.
			$dados = User::verificarRecuperacao($_GET['code']);
			$info["nome"] = trim($dados["desnome"]);
			$info["idUsuario"] = $dados["idusuario"];
		} catch (Exception $e) {
			//Exibe uma tela com erro informando que não foi possível recuperar a senha
			$info["error"] = $e->getMessage();
		}

		$page = new PaginaInicial($info, "Redefinir senha");
		$page->setTpl("redefinirSenha");
		exit;
	} else {
		header("Location: /");
		exit;
	}
});

$app->post('/reset(/)', function () {
	if (isset($_POST) && $_POST) {
		try {
			$retorno = User::alterarSenha($_POST);
			$page = new PaginaInicial([], "Login");

			$page->setTpl("login", $retorno);
			exit;
		} catch (Exception $e) { }
	} else {
		header("Location: /");
		exit;
	}
});

$app->post('/verificacaoEmail', function () {

	if (isset($_POST) && $_POST) {
		$usuario = $_POST["idusuario"];
		$nome = $_POST["desnome"];
		$email = $_POST["desemail"];

		try {
			User::enviaVerificacaoEmail($usuario, $email, $nome);
			$dados = array(
				"status" => "ok",
				"msg" => "",
				"name" => $nome,
				"email" => $email
			);

			$page = new PaginaInicial([], "Ativação E-mail");
			$page->setTpl("feedbackEmailAtivacao", $dados);
			exit;
		} catch (Exception $e) {
			$dados = array(
				"status" => "erro",
				"msg" => $e->getMessage(),
				"name" => $nome,
				"email" > $email
			);

			$page = new PaginaInicial([], "Ativação E-mail");
			$page->setTpl("feedbackEmailAtivacao", $dados);
			exit;
		}
	} else {
		$dados = array(
			"status" => "erro",
			"msg" => "",
			"name" => $nome,
			"email" > $email
		);
		$page = new PaginaInicial([], "Ativação E-mail");
		$page->setTpl("feedbackEmailAtivacao", $dados);
		exit;
	}
});

$app->get('/verificacaoEmail', function () {

	if (isset($_GET["codigo"]) && $_GET["codigo"]) {
		if (User::AtivaEmail($_GET["codigo"])) {
			$usuario = User::carregaDadosLogado();

			$dadosU = $usuario->getdata();

			$dados = array(
				"status" => "recuperado",
				"msg" => "",
				"name" => $dadosU["desnome"],
				"email" => $dadosU["desemail"]
			);

			$page = new PaginaInicial([], "Ativação E-mail");
			$page->setTpl("feedbackEmailAtivacao", $dados);
			exit;
		} else {
			$dados = User::carregaDadosLogado();

			$dados = array(
				"status" => "erro",
				"msg" => "",
				"name" => "",
				"email" > ""
			);


			$page = new PaginaInicial([], "Ativação E-mail");
			$page->setTpl("feedbackEmailAtivacao", $dados);
			exit;
		}
	} else {
		$dadosUsuario = User::retornaDadosDaSession();
		User::verificaAcesso("index", $dadosUsuario, "Home");
		exit;
	}
});

$app->get('/adicionar_empresa', function () {

	$user = new User();
	$user->loadCookie();
	$dadosUsuario = User::retornaDadosDaSession();

	User::verificaAcesso("adicionar_empresa", $dadosUsuario, "adicionarEmpresa");
	exit;
});

$app->post('/adicionar_empresa', function () {

	$user = new User();
	$user = $user->carregaDadosLogado();
	$data = $user->getdata();
	$idusuario = $data["idusuario"];

	if (isset($_POST) && $_POST) {
		$empresa = new Empresas();
		//Verifica campos obrigatórios
		$nome = $_POST["desnome"];
		$apelido = $_POST["desapelido"];
		$imagem = "";
		$textoDesc = isset($_POST["textoDesc"]) ? $_POST["textoDesc"] : "";

		$empresa->criarEmpresa($nome, $apelido, $imagem, $textoDesc, $idusuario);
		header("Location: /");
		exit;
	}
});

//Rotas nomeadas devem vir aqui

$app->get('/tickets(/)', function(){
	User::verifyLogin("Location: /");

	$user = new User();
	$user->loadCookie();
	$dadosUsuario = User::retornaDadosDaSession();
	$tickets = Tickets::GeraTickets($dadosUsuario['idusuario']);

	$dadosUsuario['tickets'] = $tickets;

	User::verificaAcesso("tickets", $dadosUsuario, "Tickets");
	exit;
});



$app->get('/:NOME(/)', function ($nomeEmpresa) {

	User::verifyLogin("Location: /");
	$user = new User();
	$user->loadCookie();

	User::verificaAdminEmpresa($nomeEmpresa, "");

	$dadosUsuario = User::retornaDadosDaSession();

	$empresas = new Empresas();

	$empresa = $empresas->obtemDadosEmpresa($nomeEmpresa, $dadosUsuario["idusuario"]);
	$dadosUsuario['empresaLogada'] = $empresa;

	//Criar caminhos para configurações da empresa, divisões e etc.

	if (isset($_GET["adddivisao"])) {
		if ($_GET["adddivisao"] == "true") {

			User::verificaAcesso("adicionar_divisao", $dadosUsuario, $nomeEmpresa);
			exit;
		}
	} else if (isset($_GET["config"])) {
		if ($_GET["config"] == "true") {

			$dadosempresa = Empresas::retornaEmpresa($nomeEmpresa);
			$dadosUsuario['empresa'] = $dadosempresa;
			$dadosUsuario['erro'] = Empresas::getError();

			//Recolher dados necessarios
			//Cargos cadastrados
			$cargos = Empresas::retornaCargosCadastrados($dadosempresa["idempresa"]);
			$dadosUsuario['cargos'] = $cargos;

			User::verificaAcesso("empresa_config", $dadosUsuario, $nomeEmpresa);
			exit;
		}
	}

	$dadosempresa = Empresas::retornaEmpresa($nomeEmpresa);
	$dadosUsuario['empresa'] = $dadosempresa;


	User::verificaAcesso("empresa", $dadosUsuario, $nomeEmpresa);
	exit;
});

$app->post('/:NOME(/)', function ($nomeEmpresa) {

	if (isset($_POST)) {

		$getConfig = isset($_GET["config"]) ? $_GET["config"] : "";

		if ($getConfig == "true") {

			//verifica subnomes
			if(isset($_GET["alteraIcone"]))
			{
				$idEmpresa = isset($_POST["idempresa"])?$_POST["idempresa"]:0;
				$foto = isset($_FILES["foto"])?$_FILES["foto"]:"";

				if($idEmpresa > 0){

					$empresa = new Empresas();
					$empresa->salvarFotoEmpresa($foto, $idEmpresa);
					try{

					}catch(Exception $e){
						header('Location: /$nomeEmpresa?adddivisao=true');
						exit;
					}
				}
			}
			//Tenta editar o cargo. Não conseguindo por algum motivo retorna um erro!
			$idEmpresa = isset($_POST["idempresa"]) ? $_POST["idempresa"] : "";
			$nomeA = isset($_POST["nomeA"]) ? $_POST["nomeA"] : "";
			$nomeN = isset($_POST["nomeN"]) ? $_POST["nomeN"] : "";
			$obs = isset($_POST["obs"]) ? $_POST["obs"] : "";
			$idUsuario = isset($_POST["idusuario"]) ? $_POST["idusuario"] : "";

			Empresas::alteraCargo($idEmpresa, $nomeA, $nomeN, $obs, $idUsuario);

			header("Location: /$nomeEmpresa?config=true");
			exit;
		}

		$empresa = new Empresas();
		if ($empresa->adicionarDivisao($_POST)) {
			header("Location: /$nomeEmpresa");
			exit;
		} else {
			header('Location: /$nomeEmpresa?adddivisao=true');
			exit;
		}
	}
});



$app->get('/:EMPRESA/:DIVISAO(/)', function ($nomeEmpresa, $nomedivisao) {

	// $emailsArr = explode(";","vagner.lenon@dadobier.com.br;vagner.lenon@gmail.com");

	// $resposta = array(
	// 	"status"=>"",
	// 	"retorno"=>""
	// );

	// Empresas::DefineGerente($emailsArr, 38);
	// exit;

	User::verifyLogin("Location: /");

	$user = new User();
	$user->loadCookie();
	$dadosUsuario = User::retornaDadosDaSession();

	//Função que verifica se o usuário tem direitos de administrar a empresa
	User::verificaAdminEmpresa($nomeEmpresa, "");

	//Verificar se existe a divisão na empresa. Se não existir, retornar a empresa
	Empresas::verificaDivisao($nomeEmpresa, $nomedivisao);

	//Recuperar dados da divisão

	$divisao = Empresas::RecuperarDadosDivisao($nomeEmpresa, $nomedivisao);

	$dadosUsuario['divisao'] = $divisao;


	$dadosempresa = Empresas::retornaEmpresa($nomeEmpresa);
	$dadosUsuario['empresa'] = $dadosempresa;

	$dadosUsuario["erro"] = "";
	//Caso queira editar o quadro de funcionários
	$dadosUsuario['funcionarios'] = Empresas::retornaFuncionariosDivisao($dadosUsuario['divisao']["iddivisao"]);

	if (isset($_GET["editcolab"])) {
		if ($_GET["editcolab"] == "true") {

			$dadosUsuario["erro"] = User::getError();


			$dadosUsuario['cargos'] = Empresas::retornaCargos($dadosUsuario['divisao']["idempresa"]);


			User::verificaAcesso("quadro_funcionarios", $dadosUsuario, $nomeEmpresa);
			exit;
		}
	}

	else if(isset($_GET["editsetores"])){
		if ($_GET["editsetores"] == "true") {

			$dadosUsuario["erro"] = User::getError();
			$dadosUsuario['funcionarios'] = Empresas::retornaFuncionariosDivisao($dadosUsuario['divisao']["iddivisao"]);
			$dadosUsuario['setores'] = Empresas::retornaSetores($nomedivisao);


			User::verificaAcesso("setores", $dadosUsuario, $nomeEmpresa);
			exit;
		}
	}

	else if(isset($_GET['colabset'])){

		$setor = $_GET['colabset'];
		$dadosUsuario["erro"] = User::getError();

		$dadosUsuario['funcionarios'] = Empresas::retornaFuncionariosSetor($setor);
		$dadosUsuario['setores'] = Empresas::retornaSetores($nomedivisao);

		User::verificaAcesso("funcionarios", $dadosUsuario, $nomeEmpresa);
		exit;

	}

	User::verificaAcesso("divisao", $dadosUsuario, $nomeEmpresa);
	exit;
});

$app->post('/:EMPRESA/:DIVISAO(/)', function ($nomeEmpresa, $nomedivisao){

	User::verifyLogin("Location: /");

	$user = new User();
	$user->loadCookie();
	$dadosUsuario = User::retornaDadosDaSession();

	//Função que verifica se o usuário tem direitos de administrar a empresa
	if (!Empresas::verificaAdminEmpresa($nomeEmpresa, $dadosUsuario["idusuario"])) {
		header("Location: /");
		exit;
	}

	if(isset($_POST)){

		if(isset($_GET["alteraIcone"])){
			$iddivisao = isset($_POST["iddivisao"])?$_POST["iddivisao"]:0;
			$foto = isset($_FILES["foto"])?$_FILES["foto"]:"";

			if($iddivisao > 0){

				$empresa = new Empresas();

				try{
					$empresa->salvarFotoDivisao($foto, $iddivisao);
					header("Location: /$nomeEmpresa/$nomedivisao");
					exit;

				}catch(Exception $e){
					var_dump($e->getMessage());
					exit;
					Empresas::setErro($e->getMessage());
					header('Location: /$nomeEmpresa/$nomedivisao');
					exit;
				}
			}
		}

		if(isset($_GET["delete"])){
			if ($_GET["delete"] == "true") {

				$iddivisao = $_POST["idDivisao"];

				Empresas::DeletaDivisao($iddivisao);

				header("Location: /".$nomeEmpresa);
				exit;
			}
		}

		if(isset($_GET["addFuncionario"])){

			$email = isset($_POST['desemail'])?$_POST['desemail']:"";
			$nome = isset($_POST['desnome'])?$_POST['desnome']:"";
			$sobrenome = isset($_POST['dessobrenome'])?$_POST['dessobrenome']:"";
			$cargo = isset($_POST['cargo'])?$_POST['cargo']:"";
			$divisao = isset($_POST['iddivisao'])?$_POST['iddivisao']:"";

			User::convidarUsuarioDivisao($email, $nome, $sobrenome, $cargo, $divisao);
			header("Location: /".$nomeEmpresa."/".$nomedivisao."?editcolab=true");
			exit;

		}

		if(isset($_GET["editcolab"])){

			//Verifica se o colaborador é efetivo ou convidado
			if(isset($_GET['excluirColab']))
			{
				//Se efetivo, o remove da lista de colaboradores
				User::RemoveColaborador($_POST["email"], $_POST["divisao"], $_POST["tipo"]=="efetivo");
				header("Location: /".$nomeEmpresa."/".$nomedivisao."?editcolab=true");
				exit;
			}




		}

		if(isset($_GET["addGerente"])){

			$emails = $_POST["emails"];
			$emailsArr = explode(";",$emails);

			for($i = 0;$i<$emailsArr;$i++)
			{
				echo($emailsArr[$i]."<br>");
			}

			exit;

			$email = $_POST["desemail"];

			$divisao = $_POST["idDivisao"];

			$usuario = User::obtemIdUsuarioSession();

			Empresas::DefineGerente($email, $divisao, $usuario);

			header("Location: /".$nomeEmpresa."/".$nomedivisao);
			exit;
		}

		if(isset($_GET["removeGerente"])){

			$id = $_POST["idgerente"];

			Empresas::RemoveGerente($id);

			header("Location: /".$nomeEmpresa."/".$nomedivisao);
			exit;
		}

		if(isset($_GET["removeSupervisor"])){

			$id = $_POST["idsupervisor"];

			Empresas::RemoveSupervisor($id);

			header("Location: /".$nomeEmpresa."/".$nomedivisao);
			exit;
		}

		if(isset($_GET["excluirSetor"])){

			$id = $_POST["setor"];

			Empresas::DeletaSetor($id);

			header("Location: /".$nomeEmpresa."/".$nomedivisao."?editsetores=true");
			exit;
		}

		if(isset($_GET["editarSetor"])){

			$id = $_POST["setor"];
			$nome = $_POST["nomeSetor"];
			$descricao = $_POST["setorDesc"];
			$ativo = FALSE;

			if (isset($_POST["SetorAtivo"]) && $_POST["SetorAtivo"] != "")
			{
				$ativo = TRUE;
			}

			Empresas::AlteraSetor($id, $nome, $descricao, $ativo);

			header("Location: /".$nomeEmpresa."/".$nomedivisao."?editsetores=true");
			exit;
		}




	}

});

$app->run();
