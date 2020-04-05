<?php

use \Slim\Slim;
use \Zware\Model\Chaves;
use \Zware\Model\Empresas;
use \Zware\Pagina;
use \Zware\PaginaInicial;
use \Zware\Model\User;
use \Zware\Model\Clientes;
use \Zware\Model\Tickets;
use \Zware\Model\Rotinas;
use \Zware\Model\Files;
use \Zware\Model\Funcoes;
use \Zware\DB\MySql;
use \Zware\Model\ConfigHandler;
use \Zware\Mailer;



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

		//Verificar se o cadastro vem de um convite de empresa.

		$valores = User::Cadastro(null);
		$valores['mail'] = "";

		if($_GET && $_GET["convite"] != '')
		{
			//Verifica se o código do convite é válido
			$convite = $_GET["convite"];
			$id = User::decodeBase64($convite);
			$dados = json_decode(User::DadosFuncionario($id, false), true);

			$valores['mail'] = $dados['email'];
		}

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

$app->get('/notificacoes(/)', function(){
	$user = new User();
	$user->loadCookie();
	$dadosUsuario = User::retornaDadosDaSession();
	$tipo = "";

	if($_GET && isset($_GET['t'])){
		$tipo = $_GET['t'];
	}

	$lida = false;

	if($_GET && isset($_GET['nr']) && $_GET['nr']=='v'){
		$lida = true;
	}

	$dadosUsuario['notificacoes'] = Tickets::RecuperaNotificacoes($dadosUsuario['idusuario'],$tipo, $lida);

	$dadosUsuario['quantNotifications'] = count($dadosUsuario['notificacoes']);

	User::verificaAcesso("notifications", $dadosUsuario, "Notificacoes");
	exit;
});



?>
