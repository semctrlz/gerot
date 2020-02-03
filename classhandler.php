<?php
session_start();
require_once('vendor/autoload.php');
use \Zware\Model\User;
use \Zware\Model\Empresas;

use \Zware\Model\Files;
use \Zware\Model\Funcoes;


$resposta = array();

if(isset($_POST))
{
    if(isset($_POST['poperacao']))
    {
				if($_POST['poperacao'] == "notificacoes")
				{
					$resposta = array(
						"status"=>"",
						"retorno"=>""
					);

				$not = User::Notificacoes();

				$resposta['status'] = "success";
				$resposta['retorno'] = json_encode($not);
				}

        if($_POST['poperacao'] == "verificarAPelido"){

            $apelido = $_POST['apelido'];
            $idUsuario = $_POST['idusuario'];

            $resp = User::verificaApelido($idUsuario, $apelido);
            $resposta['status'] = $resp;
            echo(json_encode($resposta));
            exit;
        }

        if($_POST['poperacao'] == 'alteraUsuario'){

            $resposta = array(
                "status"=>"",
                "retorno"=>""
            );


            $nome = $_POST['pnome'];
            $sobrenome = $_POST['psobrenome'];
            $apelido = $_POST['papelido'];
            $educacao = $_POST['peducacao'];
            $habilidades = $_POST['phabilidades'];
            $bio = $_POST['psobre'];
            $usuario = $_POST['pidusuario'];
            $dtnascimento = $_POST['nascimento'] == ""? null: $_POST['nascimento'];

            $nascimento = null;
            if($dtnascimento != null){
                $nascimento = date('Y/m/d',strtotime(str_replace("/","-",$dtnascimento)));
            }

            $resp = User::AlteraDados($nome, $sobrenome, $apelido, $educacao, $habilidades,$bio,$usuario,$nascimento) ;

            $resposta['status'] = 'sucesso';
            $resposta['retorno'] = $resp;
            echo json_encode($resposta);
            exit;
        }

        if($_POST['poperacao'] == 'alteraSenha'){

            $resposta = array(
                "status"=>"",
                "retorno"=>""
            );

            $id = $_POST['pid'];
            $nome = $_POST['pnome'];
            $email = $_POST['pemail'];

            $ret = User::recuperacaoSenha($email);

            $resposta['status'] = 'sucesso';
            $resposta['retorno'] = $ret;

            echo json_encode($resposta);
            exit;
        }

        if($_POST['poperacao'] == 'verificarEmailDivisao'){
            $resposta = array(
                "status"=>"",
                "retorno"=>"",
                "dados"=>""
            );




            $email = $_POST['pemail'];
            $divisao = $_POST['pdivisao'];

            $resposta["status"] = "sucesso";
            $resposta["retorno"] = "E-mail encontrado";

            $ret = Empresas::VerificaEmailDivisao($email, $divisao);

            if(count($ret) == 0)
            {
                $resposta["status"] = "erro";
                $resposta["retorno"] = "E-mail inesistente para esta divisão.";
            }
            else
            {
                $resposta["status"] = "sucesso";
                $resposta["retorno"] = "E-mail encontrado";
                $resposta["dados"] = $ret;
            }

            echo json_encode($resposta);
            exit;
        }

        if($_POST['poperacao'] == 'insereSetor'){
            $resposta = array(
                "status"=>"",
                "retorno"=>""
            );

            $nome = $_POST['desnome'];
            $descricao = $_POST['desdescricao'];
            $divisao = $_POST['divisao'];
            $usuario = $_POST['usuario'];

            $resposta['retorno'] = Empresas::adicionaSetor($nome, $descricao, $divisao, $usuario);

            $resposta['status'] = "sucesso";
        }

        if($_POST['poperacao'] == 'addGerente'){

            $divisao = $_POST["pdivisao"];
            $emails = $_POST["pemails"];
            $usuario = $_POST["pusuario"];

						$emailsArr = explode(";",$emails);

            $resposta = array(
                "status"=>"",
                "retorno"=>""
            );

            $empresa = new Empresas();
            $empresa->DefineGerente($emailsArr, $divisao,$usuario);

            $resposta['status'] = 'sucesso';
        }

        if($_POST['poperacao'] == 'addSupervisor'){

            $setor = $_POST["pidsetor"];
            $emails = $_POST["pemails"];
            $usuario = $_POST["pusuario"];
            $divisao = $_POST["piddivisao"];

						$emailsArr = explode(";",$emails);

            $resposta = array(
                "status"=>"",
                "retorno"=>""
            );

            $empresa = new Empresas();
            $empresa->DefineSupervisor($emailsArr, $setor, $divisao, $usuario);

            $resposta['status'] = 'sucesso';
        }

        if($_POST['poperacao'] == 'addFuncionario'){

            $setor = $_POST["pidsetor"];
            $emails = $_POST["pemails"];
            $usuario = $_POST["pusuario"];
            $divisao = $_POST["piddivisao"];

						$emailsArr = explode(";",$emails);

            $resposta = array(
                "status"=>"",
                "retorno"=>""
            );

            $empresa = new Empresas();
            $empresa->DefineFuncionario($emailsArr, $setor, $divisao, $usuario);

            $resposta['status'] = 'sucesso';
				}

				if($_POST['poperacao'] == 'AddColaborador'){

					$resposta = array(
						"status"=>"",
						"retorno"=>""
					);

					$usuarioCriacao = $_POST['pusuarioCriacao'];
					$email = $_POST['pemail'];
					$nome = $_POST['pnome'];
					$sobrenome = $_POST['psobrenome'];
					$cargo = $_POST['pcargo'];
					$fechamento = $_POST['pfechamento'];
					$unidadesChamados = $_POST['punidadesChamados'];
					$divisao = $_POST['pdivisao'];

					//Inserir colaborador.
					$idconvite = User::convidarUsuarioDivisao($email, $nome, $sobrenome, $cargo, $divisao, $unidadesChamados, $fechamento, $usuarioCriacao);

					//obtem dados do usuario inserido
					$dadosRetorno = User::ObtemUsuarioConvidado($idconvite);

					//Retornar dados do colaborador para exibição.

					$resposta["retorno"] = json_encode($dadosRetorno);

					$resposta["status"] = "success";
			}

			if($_POST['poperacao'] == 'consultaColaborador')
			{
				$id = $_POST["pusuario"];
				$tipo = $_POST["ptipo"];

				$resposta = array(
					"status"=>"",
					"retorno"=>""
				);

				$ret = User::DadosFuncionario($id, $tipo);




				$resposta['status'] = "success";
				$resposta['retorno'] = $ret;

			}


    }
}
else
{



    $resposta['status'] = 'fracasso';
}

// $objeto = User::retornaDadosDaSession();
// var_dump($objeto);
echo json_encode($resposta);
exit;

?>
