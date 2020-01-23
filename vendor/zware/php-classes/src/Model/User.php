<?php

namespace Zware\Model;

use DateTime;

use Rain\Tpl;
use Rain;
use \Zware\DB\MySql;
use \Zware\Mailer;
use \Zware\Model;

use \Zware\Model\Files;
use \Zware\Model\Empresas;
use \Zware\Pagina;
use \Zware\PaginaInicial;

class User extends Model
{
    const ERROR = "ErroNoSistemaUsuario";

    const ERROR_REGISTER = "UserErrorRegister";

    public static function login($email, $senha, $conexaoAutomatica = false)
    {
				$sql = new MySql();



        $results = $sql->select("select p.desemail, u.idusuario, u.dessenha from tb_usuarios u
        left join tb_pessoas p on p.idpessoa = u.idpessoa
        where boolcadastroativo = true and p.desemail = :EMAIL", array(":EMAIL" => $email));

        if (count($results) === 0) {
            throw new \Exception("Email inexistente ou senha inválida.");
        }

        $data = $results[0];

        if (password_verify($senha, $data["dessenha"])) {
            $user = new User();

            $user = User::getDadosDoId($data["idusuario"]);

            $_SESSION[Chaves::SESSION] = $user->getData();

            if ($conexaoAutomatica) {
                User::manterConectado($_SESSION[Chaves::SESSION]["idusuario"]);
            }

            return $user;
        } else {
            throw new \Exception("Email inexistente ou senha inválida.");
        }
    }

    public static function verificaApelido($idUsuario, $apelido)
    {
        $sql = new MySql();

        $results = $sql->select("select idusuario from tb_usuarios where lower(desapelido) = :APELIDO;", array(
            ":APELIDO"=>Funcoes::removeNonAlfaNumeric($apelido)
        ));

        if(count($results)==0)
        {
            return "livre";
        }
        else
        {
            if($results[0]['idusuario'] == $idUsuario)
            {
                return 'proprio';
            }
            else
            {
                return 'ocupado';
            }
        }
    }

    public static function FormataApelido(string $apelido){

    }

    public static function AlteraDados($desnome, $dessobrenome, $desapelido, $educacao, $habilidades, $bio, $usuario, $nascimento = null){

        if(User::verificaApelido($usuario, $desapelido) == "ocupado"){
            return "O apelido $desapelido já existe para outro usuário. Por favor, escolha outro.";
        }

        $sql = new MySql();

        $apelidoTratado = Funcoes::removeNonAlfaNumeric($desapelido);
        $nomeTratado = Funcoes::prepararParaBanco($desnome, true, true);
        $sobrenomeTratado = Funcoes::prepararParaBanco($dessobrenome, true, true);
        $educacaotratada = Funcoes::prepararParaBanco($educacao, true, false);
        $habilidadetratada = Funcoes::prepararParaBanco($habilidades, true);
        $biotratada = Funcoes::prepararParaBanco($bio, true);


        $sql->query("CALL sp_alteraDadosUsuario(:USUARIO, :NOME, :SOBRENOME, :APELIDO, :NASC, :EDUCACAO, :HABILIDADE, :BIO)",array(
            ":USUARIO"=>$usuario,
            ":NOME"=>$nomeTratado,
            ":SOBRENOME"=>$sobrenomeTratado,
            ":NASC"=>$nascimento,
            ":APELIDO"=>$apelidoTratado,
            ":EDUCACAO"=>$educacaotratada,
            ":HABILIDADE"=>$habilidadetratada,
            ":BIO"=>$biotratada
        ));

        $user = new User();
        $_SESSION[Chaves::SESSION]["deseducacao"] = $educacaotratada;
        User::atualizaSession();
        return "Sucesso!";

    }

    public static function obtemIdUsuarioSession()
    {
        return isset($_SESSION[Chaves::SESSION]["idusuario"])?$_SESSION[Chaves::SESSION]["idusuario"]:0;
    }

    public static function getDadosDoId($id)
    {
        $sql = new MySql();
        $results = $sql->select("select
        p.idpessoa,
        p.desnome,
        p.dessobrenome,
        p.desemail,
        ifnull(p.dtnascimento,'') as dtnascimento,
        ifnull(p.desdescricao,'') as desdescricao,
        ifnull(p.deseducacao,'') as deseducacao,
        ifnull(p.deshabiliadades,'') as deshabiliadades,
        p.dtcadastro as dtcadastro_pessoa,
        u.idusuario,
        u.desapelido,
        u.dessenha,
        u.dtcadastro as dtcadastro_usuario,
        u.boolcadastroativo,
        u.boolemailverificado,
        ifnull(u.desfotoperfil, '') as desfoto
        from tb_usuarios u
        left join tb_pessoas p on p.idpessoa = u.idpessoa
        where u.idusuario = :ID", array(":ID" => $id));

        $user = new User();

        if (count($results) === 0) {
            return $user;
        }

        $data = $results[0];

        //Atualiza suas empresas
        $empr = Empresas::CarregaEmpresasAdmins($id);
        $data["empresas"] = $empr;
        $data["temEmpr"] = count($empr)>0?"sim":"";
        $user->setData($data);

        return $user;
    }

    public static function manterConectado(int $id_usuario)
    {
        $chave = User::chaveCookie($id_usuario);
        User::saveCookie($chave);

        $navegador = User::getBrowser();
        $NomePC = gethostname();
        $ip = $_SERVER['REMOTE_ADDR'];

        // Cadastra no banco

        $sql = new MySql();
        $sql->query("CALL sp_cadastra_login_automatico(:COMPUTADOR, :NAVEGADOR, :CHAVE, :USUARIO, :IP);", array(
            ":COMPUTADOR" => $NomePC,
            ":NAVEGADOR" => $navegador["name"],
            ":CHAVE" => $chave,
            ":USUARIO" => $id_usuario,
            ":IP" => $ip
        ));
    }

    public static function DefineSessionCookie(bool $lido, bool $existe)
    {
        $_SESSION["ZWareCookie"]["lido"] = $lido;
        $_SESSION["ZWareCookie"]["existe"] = $existe;
    }

    public static function chaveCookie(int $id_usuario)
    {
        // Gerar hash (com o id do usuario + NomePC + id do usuario)
        $PCName = gethostname();
        $ip = $_SERVER['REMOTE_ADDR'];
        $chave = $id_usuario . $PCName . $id_usuario . $ip;

        // Criptografar a chave

        return hash('ripemd160', $chave);
    }

    public static function saveCookie(string $valor)
    {
        $cookie_name = Chaves::COOKIENAME;
        $cookie_value = $valor;
        setcookie($cookie_name, $cookie_value, time() + (86400 * 365), "/"); // 86400 = 1 day
    }

    public function loadCookie()
    {
        $cookie_name = Chaves::COOKIENAME;

        if(isset($_SESSION["ZWareCookie"])){
            if($_SESSION["ZWareCookie"]["lido"]){
                return;
            }
        }

        if (isset($_COOKIE[$cookie_name])) {

            // Verificar id_usuario no banco;
            $sql = new MySql();
            $idsUsuario = $sql->select("select idusuario from tb_login_automatico where chave = :CHAVE", array(

                ":CHAVE" => $_COOKIE[$cookie_name]
            ));

            if (COUNT($idsUsuario) > 0) {
                $_SESSION[Chaves::SESSION] = user::getDadosDoId($idsUsuario[0]["idusuario"])->getData();
                User::DefineSessionCookie(true, true);
                return $this->getData();
            }
        }
    }

    public static function verificaAdminEmpresa($empresa, $localRedirect){

        $dadosUsuario = User::retornaDadosDaSession();

        if(isset($dadosUsuario["empresas"])){


            $empresas = array_column($dadosUsuario["empresas"],'desnomeurl');

            if(!in_array($empresa, $empresas)){
                header("Location: ".$localRedirect);
                exit;
            }
        }else{
            $dadosUsuario["empresas"]['desnome']= '';
        }

    }

    public static function retornaDadosDaSession()
    {
        if (
            !isset($_SESSION[Chaves::SESSION])
            ||
            !($_SESSION[Chaves::SESSION])
            ||
            !(int) $_SESSION[Chaves::SESSION]["idusuario"] > 0
        ) {

            return [];
        } else {
            return $_SESSION[Chaves::SESSION];
        }
    }

    public static function verifyLogin($redirect = "")
    {
        if (
            !isset($_SESSION[Chaves::SESSION])
            ||
            !($_SESSION[Chaves::SESSION])
            ||
            !(int) $_SESSION[Chaves::SESSION]["idusuario"] > 0
        ) {

            if ($redirect != "") {
                header("Location: ".$redirect);
                exit;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public static function atualizaSessionLogin($id)
    {
        $user = new User();
        $user = User::getDadosDoId($id);
        unset($_SESSION[Chaves::SESSION]);
        $_SESSION[Chaves::SESSION] = $user->getData();
    }

    public static function atualizaSession(){

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if(isset($_SESSION[Chaves::SESSION])){
            $user = new User();

            $user = User::getDadosDoId(User::obtemIdUsuarioSession());
            $_SESSION[Chaves::SESSION] = $user->getData();
        }
    }

    public static function verificaAcesso($template, $dados, $pagina, $opts = ["sidebar" => ""])
    {
        if (
            !isset($_SESSION[Chaves::SESSION])
            ||
            !($_SESSION[Chaves::SESSION])
            ||
            !(int) $_SESSION[Chaves::SESSION]["idusuario"] > 0
        ) {
            //Sem login vai para o index sem login
            $page = new PaginaInicial([], $pagina, ["sidebar" => ""]);
            $page->setTpl("index");
            exit;
        } else {
            //verificar se o e-mail foi verificado, se sim, alterar a session
            if (!(bool) $_SESSION[Chaves::SESSION]["boolemailverificado"]) {

                //Com login mas e-mail não verificado volta sempre para o index do sistema
                $pagina = new Pagina($_SESSION[Chaves::SESSION], $pagina, ["sidebar" => "none"]);
                $pagina->setTpl("index_sem_email");
                exit;
            } else {
                //Com login e email verificado vai para onde tem que ir
                $pagina = new Pagina($dados, $pagina, $opts);
                $pagina->setTpl($template);
                exit;
            }
        }
    }

    public static function carregaDadosLogado()
    {
        $usuario = new User();

        if (
            !isset($_SESSION[Chaves::SESSION])
            ||
            !($_SESSION[Chaves::SESSION])
            ||
            !(int) $_SESSION[Chaves::SESSION]["idusuario"] > 0
        ) {
            return $usuario;
        } else {

            return $usuario->getDadosDoId((int) $_SESSION[Chaves::SESSION]["idusuario"]);
        }
    }

    public static function logout()
    {
        $_SESSION[Chaves::SESSION] = NULL;
        User::desconectar();
    }

    public static function desconectar()
    {
        $navegador = User::getBrowser();
        $NomePC = gethostname();
        $ip = $_SERVER['REMOTE_ADDR'];
        $nomeNavegador = $navegador["name"];

        // Remove do banco no banco

        $sql = new MySql();
        $sql->query("delete from tb_login_automatico where nome_computador = :COMPUTADOR AND ip = :IP and navegador = :NAVEGADOR;", array(
            ":COMPUTADOR" => $NomePC,
            ":IP" => $ip,
            ":NAVEGADOR" => $nomeNavegador
        ));

        unset($_COOKIE[Chaves::COOKIENAME]);
    }

    public static function recuperacaoSenha($email)
    {
        $sql = new MySql();
        $results = $sql->select("select p.idpessoa, p.desnome, p.dessobrenome, p.desemail, p.dtnascimento, p.dtcadastro as dtcadastro_pessoa,
        u.idusuario, u.desapelido, u.dessenha, u.dtcadastro as dtcadastro_usuario, u.boolcadastroativo, u.boolemailverificado
        from tb_usuarios u
        left join tb_pessoas p on p.idpessoa = u.idpessoa
        where boolcadastroativo = true and p.desemail = :EMAIL", array(":EMAIL" => $email));

        if (count($results) === 0) {
            throw new \Exception("Não foi possível recuperar a sua senha. Verifique o e-mail digitado.");
        } else {

            $data = $results[0];
            $results2 = $sql->select("CALL sp_criar_recuperacao_senha(:iduser, :desip)", array(
                ":iduser" => $data["idusuario"],
                ":desip" => $_SERVER["REMOTE_ADDR"],
            ));

            if (count($results2) === 0) {
                throw new \Exception("Não foi possível recuperar a sua senha. Verifique o e-mail digitado.");
            } else {

                $dataRecovery = $results2[0];

                $code = User::encodeBase64($dataRecovery["idrecuperacaosenha"]);

                $link = Chaves::SITEROOT . "reset?code=$code";

                $mailer = new Mailer($data["desemail"], "Recuperação de senha ZWare Flow", "recuperarSenha", array(
                    "name" =>  $data["desnome"],
                    "link" => $link
                ));

                $mailer->send();
                return $data;
            }
        }
    }


    public static function  verificarRecuperacao($codigo)
    {
        $idRecuperacao = User::decodeBase64($codigo);

        //Verificar se o Id em questão

        $sql = new MySql();
        $results = $sql->select("SELECT * FROM tb_recuperacao_senha a
        INNER JOIN tb_usuarios b ON b.idusuario = a.idusuario
        INNER JOIN tb_pessoas c ON b.idpessoa = c.idpessoa
        WHERE a.idrecuperacaosenha = :IDRECOVERY
        AND a.dtrecuperacao is NULL
        AND date_add(a.dtcadastro, interval 10 minute) >= NOW()", array(
            ":IDRECOVERY" => $idRecuperacao
        ));

        if (count($results) === 0) {
            throw new \Exception("Não foi possí­vel recuperar a senha.");
        } else {
            return $results[0];
        }
    }

    public static function alterarSenha($dados)
    {
        $idusuario = $dados["idusuario"];
        $senha = $dados["dessenha"];
        $senha2 = $dados["desrepetirsenha"];


        if ($senha === $senha2) {
            $senhaSegura = User::passwordEncript($senha);

            $sql = new MySql();
            $sql->query("UPDATE tb_usuarios set dessenha = :SENHA where idusuario = :USUARIO", array(
                ":SENHA" => $senhaSegura,
                ":USUARIO" => $idusuario
            ));

            $sql->query("UPDATE tb_recuperacao_senha set dtrecuperacao = NOW() where idusuario = :USUARIO", array(
                ":USUARIO" => $idusuario
            ));

            return array(
                "error" => "",
                "message" => "Sua senha foi alterada com sucesso."
            );
        } else {
            throw new \Exception("As duas senhas devem ser iguais.");
        }
    }

    public static function encodeBase64($valor, $method = Chaves::METHOD, $criptkey = Chaves::CRIPTKEY, $iv = Chaves::IV)
    {
        $code = base64_encode(openssl_encrypt($valor, $method, $criptkey, false, $iv));
        return $code;
    }

    public static function decodeBase64($valor, $method = Chaves::METHOD, $criptkey = Chaves::CRIPTKEY, $iv = Chaves::IV)
    {
        $code = openssl_decrypt(base64_decode($valor), $method, $criptkey, false, $iv);
        return $code;
    }

    public static function setErro($msg)
    {
        $_SESSION[User::ERROR] = $msg;
    }

    public static function getError()
    {
        $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';
        User::clearError();
        return $msg;
    }

    public static function clearError()
    {
        $_SESSION[User::ERROR] = NULL;
    }

    public static function setInfo($info)
    {
        $_SESSION[Chaves::INFO] = $info;
    }

    public static function getInfo()
    {
        $msg = (isset($_SESSION[Chaves::INFO]) && $_SESSION[Chaves::INFO]) ? $_SESSION[Chaves::INFO] : '';
        User::clearInfo();
        return $msg;
    }

    public static function clearInfo()
    {
        $_SESSION[Chaves::INFO] = NULL;
    }

    public function salvaFotoPerfil($foto)
    {
        if (!isset($foto) || !$foto) {
            throw new \Exception("Selecione uma foto válida para inserir!");
        }

        $files = new Files();
        $caminho_foto = $files->uploadFotoPerfil($foto);

        $sql = new MySql;

        $data = $this->carregaDadosLogado();

        //Pega foto antiga para poder deletar ao final do processo
        $results = $sql->select("select desfotoperfil as foto from tb_usuarios where idusuario = :USUARIO;",array(
            ":USUARIO"=>$data->getidusuario()
        ));

        $foto_antiga = "";

        if(count($results)>0){
            $foto_antiga = $results[0]["foto"];
        }

        //Verificar se a imagem criada está lá e é válida
            //Estando lá continua o processo
            //Não estando lá, retorna erro e para o processo

        if(file_exists($caminho_foto)){

            $results = $sql->select(
                "update tb_usuarios set desfotoperfil = :FOTO where idusuario = :USUARIO;",
                array(
                    ":FOTO" => $caminho_foto,
                    ":USUARIO" => $data->getidusuario()
                )
            );

            try {
                unlink($foto_antiga);
            } catch (\Exception $e) { }
        }else {
            throw new \Exception("Foto inválida. Selecione uma foto com no mínimo 160 pixels de largura e altura e no máximo 2MB.");
        }
    }

    public static function cadastro($valores = array(array()))
    {
        $retorno = [
            "valorEmail" => "",
            "valorNome" => "",
            "valorSobrenome" => "",
            "error" => "",
            "erroEmail" => "",
            "erroNome" => "",
            "erroSobrenome" => "",
            "erroSenha" => ""
        ];

        if ($valores != null) {

            $existeErros = false;
            $erros = "";

            //obter os campos inseridos
            $email = trim($valores["desemail"] ? $valores["desemail"] : "");
            $nome = $valores["desnome"] ? $valores["desnome"] : "";
            $sobrenome = $valores["dessobrenome"] ? $valores["dessobrenome"] : "";
            $senha = $valores["dessenha"] ? $valores["dessenha"] : "";
            $repetirsenha = $valores["desrepetirsenha"] ? $valores["desrepetirsenha"] : "";

            $retorno["valorEmail"] = $email;
            $retorno["valorNome"] = $nome;
            $retorno["valorSobrenome"] = $sobrenome;


            //Verificar se o email existe.
            $sql = new MySql();

            $results = $sql->select("select * from tb_pessoas where desemail = :email", array(
                ":email" => $email
            ));

            if (count($results) > 0) {
                $existeErros = true;
                $retorno["erroEmail"] = "EmailExistente";

                if ($erros != "") {
                    $erros .= " ";
                }

                $erros .= "Este e-mail já existe em nossos registros. Tente logar-se.";
            }

            //Verifica se o nome foi preenchido
            if (strlen(trim($nome)) == 0) {
                $existeErros = true;
                $retorno["erroNome"] = "NomeInvalido";

                if ($erros != "") {
                    $erros .= " ";
                }

                $erros .= "Digite um nome válido.";
            }

            //Verifica se as senhas correspondem e se foram preenchidas
            if (strlen(trim($senha)) == 0 || $senha != $repetirsenha) {
                $existeErros = true;
                $retorno["erroSenha"] = "SenhasInvalidas";

                if ($erros != "") {
                    $erros .= " ";
                }

                $erros .= "As senhas devem ser preenchidas com valores iguais.";
            }

            if (!$existeErros) {
                //Caso não existam erros cadastramos a pessoa
                $nome = User::formataCaracteres($nome, true, true, true, true);
                $sobrenome = User::formataCaracteres($sobrenome, true, true, true, true);
                $senhaCriptografada = User::passwordEncript($senha);

                $dados = $sql->select("CALL sp_cadastro (:NOME, :SOBRENOME, :SENHA, :EMAIL)", array(
                    ":NOME" => $nome,
                    ":SOBRENOME" => $sobrenome,
                    ":SENHA" => $senhaCriptografada,
                    ":EMAIL" => $email
                ));

                //Salvar na sessao os dados para exibir a tela de confirmação ()

                if (count($dados) > 0) {
                    User::setInfo($dados[0]);
                }

                USer::enviaVerificacaoEmail($dados[0]["idusuario"], $email, $nome);

                header("Location: /confirmacao_cadastro");
                exit;
            } else {
                //Caso existam erros retornamos esses erros
                $retorno["error"] = $erros;
                return $retorno;
            }
        } else {

            return $retorno;
        }
    }

    public static function enviaVerificacaoEmail($idUsuario, $email, $nome)
    {

        $sql = new MySql();

        $semente = Files::GeraNomeFoto(false, [10], "", "");
        $codigo = USer::passwordEncript($semente);
        $codigo = substr($codigo, 0, 25);

        $results = $sql->select("CALL sp_criar_verificacao_email (:USUARIO, :CODIGO)", array(
            ":USUARIO" => $idUsuario,
            ":CODIGO" => $codigo
        ));

        $link = Chaves::SITEROOT . "verificacaoEmail?codigo=$codigo";

        if (count($results) > 0) {
            $mailer = new Mailer($email, "Confirmação de e-mail - ZWare Flow", "verificarEmail", array(
                "email" =>  $email,
                "link" => $link,
                "name" => $nome
            ));

            $mailer->send();
        }
    }

    public static function removeCaracteresEspeciais($texto)
    {
        return $texto;
    }

    public static function formataCaracteres($texto, $upper = true, $trim = true, $removeInvalidChars = true, bool $utfDecode)
    {

        $retorno = $texto;

        if ($upper) {
            $encoding = $encoding = 'UTF-8';
            $texto = mb_convert_case($texto, MB_CASE_UPPER, $encoding);
        }

        if ($trim) {
            $texto = trim($texto);
        }

        if ($removeInvalidChars) {
            $texto = User::removeCaracteresEspeciais($texto);
        }

        if ($utfDecode == true) {
            $textoRetorno = utf8_encode($texto);
        }

        return $texto;
    }

    public static function passwordEncript($password)
    {
        $options = [

            'cost' => 12
        ];

        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public static function alteraCaseNomeProprio($nome, $encodado = false)
    {
        if ($encodado) {
            $nome = utf8_decode($nome);
        }
        $saida = "";
        $encoding = $encoding = 'UTF-8';
        $nome = mb_convert_case($nome, MB_CASE_LOWER, $encoding); // Converter o nome todo para minúsculo
        $nome = explode(" ", $nome); // Separa o nome por espaços
        for ($i = 0; $i < count($nome); $i++) {

            // Tratar cada palavra do nome
            if ($nome[$i] == "de" or $nome[$i] == "da" or $nome[$i] == "e" or $nome[$i] == "dos" or $nome[$i] == "do") {
                $saida .= $nome[$i] . ' '; // Se a palavra estiver dentro das complementares mostrar toda em minúsculo
            } else {
                $saida .= ucfirst($nome[$i]) . ' '; // Se for um nome, mostrar a primeira letra maiúscula
            }
        }
        return $saida;
    }

    public static function AtivaEmail($codigo)
    {

        //verificamos se existe este código válido no banco
        $sql = new MySql();

        $results = $sql->select("SELECT * FROM tb_verificacao_email WHERE boolativo = true and  dtvalidacao is null and descodigo = :CODIGO", array(
            ":CODIGO" => $codigo
        ));

        if (count($results) > 0) {
            $data = $results[0];
            $results = $sql->select("CALL sp_ativaemail(:VERIFICACAO, :USUARIO)", array(
                ":VERIFICACAO" => $data["idverificacaoemail"],
                ":USUARIO" => $data["idusuario"]
            ));

            if (count($results) > 0) {
                $data = $data = $results[0];
                User::atualizaSessionLogin($data["idusuario"]);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function gerarNomeUrlUsuario($apelido, $email)
    {

        //Os nomes de URL das divisões devem ser únicos dentro da mesma empresa
        $sql = new MySql();

        $texto = $apelido;

        if (trim($apelido) == "") {
            $texto = explode("@", $email)[0];
        }

        $nome = Funcoes::preparaParaUrl($texto);

        $retorno = $sql->select("select desnomeurl from tb_usuarios where desnomeurl :NOME", array(
            ":NOME" => $nome . "%"
        ));

        $nomesUrl = array_column($retorno, 'desnomeurl');

        $sufixo = 1;
        $procurando = true;
        $ret = "";
        while ($procurando) {
            $s = $sufixo > 1 ? strval($sufixo) : "";

            if (!in_array($nome . $s, $nomesUrl)) {
                $ret = $nome . $s;
                break;
            }

            $sufixo++;
        }

        return $ret;
    }

    public static function convidarUsuarioDivisao($email, $nome, $sobrenome, $cargo, $divisao)
    {
        $email_tratado = strtolower(Funcoes::prepararParaBanco($email, true));
        $nome_tratado = Funcoes::prepararParaBanco($nome, true, true);
        $sobrenome_tratado = Funcoes::prepararParaBanco($sobrenome, true, true);

        //Verificar se o usuário já se encontra na divisão
        $sql = new MySql();
        $cadastro = $sql->select("select * FROM tb_quadro_funcionarios q
        left join tb_usuarios u using(idusuario)
        left join tb_pessoas p using(idpessoa)
        where p.desemail = :EMAIL and q.iddivisao = :DIVISAO;",array(
            ":EMAIL"=>$email_tratado,
            ":DIVISAO"=>$divisao
        ));

        if(count($cadastro)>0)
        {
            User::setErro("O usuário com o e-mail $email_tratado já consta em seu quadro de funcionários. Por favor, verifique.");
            return false;
        }

        //Verificar se o e-mail já possui cadastro
        $dados = $sql->select("select * from tb_usuarios u
        left join tb_pessoas p using(idpessoa)
        where lower(p.desemail) = :EMAIL", array(
            ":EMAIL" => $email_tratado
        ));

        if (count($dados) > 0) {
            //Usuário já existente

            //Enviar e-mail convidando-o a participar da divisão em questão
            //Ao clicar em aceitar, automaticamente o usuário entra no quadro da divisão.
            //Ao clicar em recusar, o convite se torna inativo

            $user = new User();
            $user->setData($dados[0]);
            $dadosEmail = $user->getData();

            //Retornar dado da divisão/empresa
            $dados = $sql->select("select
            e.idempresa,
            e.desnome as nome_empresa,
            e.desapelido as apelido_empresa,
            e.destextodescritivo as texto_empresa,
            e.desnomeurl as url_empresa,
            e.desicone as icone_empresa,
            d.desnome as nome_divisao,
            d.desapelido as apelido_divisao,
            d.desnomeurl as url_divisao,
            d.desdescricao as texto_divisao,
            d.desicone as icone_divisao

             from tb_empresa e
            left join tb_divisao d using(idempresa)

            where d.iddivisao = :DIVISAO", array(
                ":DIVISAO" => $divisao
            ));

            $dadosEmail['empresa'] = $dados[0];

            $nomeEmpresa = $dadosEmail['empresa']['apelido_empresa'];
            $nomeDivisao = $dadosEmail['empresa']['apelido_divisao'];

            $retorno = $sql->select("CALL sp_convidarUsuario (:DIVISAO, :EMAIL, :NOME, :SOBRENOME, :IDUSUARIO, :CARGO)", array(
                ":DIVISAO"=>$divisao,
                ":EMAIL"=> $email,
                ":NOME"=>$nome,
                ":SOBRENOME"=>$sobrenome,
                ":IDUSUARIO"=>$dadosEmail["idusuario"],
                ":CARGO"=>$cargo
            ));

            $dataRecovery = $retorno[0];

            $code = User::encodeBase64($dataRecovery["ideconvite"]);

            $link = $link = Chaves::SITEROOT . "invitation?code=$code";

            $mailer = new Mailer($dadosEmail["desemail"], "Convite para fazer parte de $nomeDivisao da empresa $nomeEmpresa", "conviteIntegrar", array(
                "name" =>  $dadosEmail["desnome"],
                "divisao" => $dadosEmail["empresa"]["apelido_divisao"],
                "descricaoDivisao"=>$dadosEmail["empresa"]["texto_divisao"],
                "fotoDivisao"=>Chaves::SITEROOT . $dadosEmail["empresa"]["icone_divisao"],
                "empresa"=>$dadosEmail["empresa"]["apelido_empresa"],
                "link" => $link
            ));

            $mailer->send();

        } else {
            //Usuário não existente

            $dadosEmail = array();

            //Enviar e-mail convidando o usuário para se cadastrar no sistema.
            $dados = $sql->select("select
            e.idempresa,
            e.desnome as nome_empresa,
            e.desapelido as apelido_empresa,
            e.destextodescritivo as texto_empresa,
            e.desnomeurl as url_empresa,
            e.desicone as icone_empresa,
            d.desnome as nome_divisao,
            d.desapelido as apelido_divisao,
            d.desnomeurl as url_divisao,
            d.desdescricao as texto_divisao,
            d.desicone as icone_divisao

             from tb_empresa e
            left join tb_divisao d using(idempresa)

            where d.iddivisao = :DIVISAO", array(
                ":DIVISAO" => $divisao
            ));

            $dadosEmail['empresa'] = $dados[0];

            $nomeEmpresa = $dadosEmail['empresa']['apelido_empresa'];
            $nomeDivisao = $dadosEmail['empresa']['apelido_divisao'];

            $retorno = $sql->select("CALL sp_convidarUsuario (:DIVISAO, :EMAIL, :NOME, :SOBRENOME, :IDUSUARIO, :CARGO)", array(
                ":DIVISAO"=>$divisao,
                ":EMAIL"=> $email_tratado,
                ":NOME"=>$nome,
                ":SOBRENOME"=>$sobrenome,
                ":IDUSUARIO"=>User::retornaDadosDaSession()["idusuario"],
                ":CARGO"=>$cargo
            ));

            $dataRecovery = $retorno[0];

            $code = User::encodeBase64($dataRecovery["ideconvite"]);

            $link = $link = Chaves::SITEROOT."cadastro";


            $mailer = new Mailer($email_tratado, "Convite para fazer parte de $nomeDivisao da empresa $nomeEmpresa", "conviteCadastro", array(
                "name" =>  $nome_tratado,
                "divisao" => $dadosEmail["empresa"]["apelido_divisao"],
                "descricaoDivisao"=>$dadosEmail["empresa"]["texto_divisao"],
                "fotoDivisao"=>Chaves::SITEROOT . $dadosEmail["empresa"]["icone_divisao"],
                "empresa"=>$dadosEmail["empresa"]["apelido_empresa"],
                "link" => $link
            ));

            $mailer->send();

            //OBS: o cadastro do convite ficará ativo e toda a vez que a pessoa logar
            //será lembrada de que foi convidada a participar da divisão.
            //Esse lembrete sempre será exibido até que o usuário aceite ou recuse o convite.

            return false;


        }
    }

    public static function dadosConvite(int $idconvite){
        $dados = array();
        $sql = new MySql();
        $retorno = $sql->select("select p.desnome,
        p.desemail,
        cc.iddivisao,
        d.desnome as nome_divisao,
        d.desapelido as apelido_unidade,
        d.desicone as icone_divisao,
        c.desnome as cargo,
        cc.usuariocriacao,
        u.idusuario,
        ifnull(cc.idcargo, 0) as cargo
        from tb_convite_cadastro cc

        left join tb_pessoas p using(desemail)
        left join tb_usuarios u using(idpessoa)
        left join tb_divisao d using(iddivisao)
        left join tb_cargos c using(idcargo)
        where idconvite = :CONVITE", array(
            ":CONVITE"=>$idconvite
        ));

        if(count($retorno)>0){
            $dados = $retorno[0];
        }
        return $dados;
    }

    public static function ConviteNegado(string $idConvite){
        //Apenas fazer um update da tabela de convite e setar o convite_recusado para 1 e o ativo para 0

        //Verifica validade do convite. No caso de convite inativo, retorna uma mensagem
        $sql = new MySql();
        $retorno = $sql->select("select * from tb_convite_cadastro where convite_ativo = 1 and idconvite = :CONVITE;",array(
            ":CONVITE"=>$idConvite
        ));

        if(count($retorno)==0){
            Empresas::setErro("Este convite não é válido. Por favor, localize o convite mais recente ou entre em contato com quem lhe enviou o convite.");
            return false;
        }

        //No caso de convite válido, ele é marcado como recusado.
        $sql->query("update tb_convite_cadastro set convite_ativo = 1, convite_recusado = 1 where idconvite = :CONVITE;",array(
            ":CONVITE"=>$idConvite
        ));

        return true;

    }

    public static function ConviteAceito(int $idConvite){

        $dados = User::dadosConvite($idConvite);
        $sql = new MySql();

        $retorno = $sql->select("select * from tb_quadro_funcionarios where idusuario = :USUARIO and iddivisao = :DIVISAO", array(
            ":USUARIO"=>$dados["idusuario"],
            ":DIVISAO"=>$dados["iddivisao"]
        ));


        if(count($retorno)==0)
        {
            $sql->query("CALL sp_aceita_convite(:EMAIL, :DIVISAO, :USUARIO, :CONVIDADOR, :CARGO);",array(
                ":EMAIL"=>$dados["desemail"],
                ":DIVISAO"=>$dados["iddivisao"],
                ":USUARIO"=>$dados["idusuario"],
                ":CONVIDADOR"=>$dados["usuariocriacao"],
                ":CARGO"=>$dados["cargo"]
            ));
        }
        $retorno = $sql->select("select * from tb_quadro_funcionarios where idusuario = :USUARIO and iddivisao = :DIVISAO", array(
            ":USUARIO"=>$dados["idusuario"],
            ":DIVISAO"=>$dados["iddivisao"]
        ));

        if(count($retorno)>0)
        {
            return true;
        }
        else
        {
            //Erro
            Empresas::setErro("Houve um erro em processar a sua solicitação.");
            return false;
        }

    }

    public static function DesativaConvites(string $email, int $divisao){
        $sql = new MySql();
        $sql->query("UPDATE tb_convite_cadastro SET convite_ativo = 0 where desemail = :EMAIL and iddivisao = :DIVISAO", array(
            ":EMAIL"=>$email,
            ":DIVISAO"=>$divisao
        ));
    }

    public static function RemoveColaborador(string $email, int $divisao, bool $efetivo){
        $sql = new MySql();

        if($efetivo){
            $sql->query("CALL sp_excluiColaborador(:EMAIL, :DIVISAO)", array(
                ":EMAIL"=>$email,
                ":DIVISAO"=>$divisao
            ));
        }
        else
        {
            $sql->query("UPDATE tb_convite_cadastro SET convite_ativo = 0 where desemail = :EMAIL and iddivisao = :DIVISAO", array(
                ":EMAIL"=>$email,
                ":DIVISAO"=>$divisao
            ));
        }
    }
}
