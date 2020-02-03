<?php

use \Slim\Slim;
use \Zware\Model\Chaves;
use \Zware\Model\Empresas;
use \Zware\Pagina;
use \Zware\PaginaInicial;
use \Zware\Model\User;
use \Zware\Model\Tickets;
use \Zware\Model\Rotinas;
use \Zware\Model\Files;
use \Zware\Model\Funcoes;
use \Zware\DB\MySql;
use \Zware\Model\ConfigHandler;



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

			$dadosUsuario["divisoes"] = Empresas::RetornaDivisoes($nomeEmpresa);

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

?>
