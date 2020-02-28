<?php

namespace Zware\Model;

use Exception;
use Rain\Tpl;
use Rain;
use \Zware\DB\MySql;
use \Zware\Mailer;
use \Zware\Model;
use \Zware\Model\Files;
use \Zware\Model\User;
use \Zware\Model\Funcoes;
use \Zware\Pagina;
use \Zware\PaginaInicial;

use function PHPSTORM_META\elementType;

class Empresas extends Model{

    const ERROR = "ErroNoSistemaEmpresa";

    public static function CarregaEmpresasAdmins($idUsuario):array{
        $sql = new MySql();

        $retorno = $sql->select("select e.* from tb_empresa e
        left join tb_empresa_admins ea on ea.idempresa = e.idempresa
        where ea.idusuario = :USUARIO", array(
            ":USUARIO"=>$idUsuario
        ));

        if(count($retorno)>0)
        {
            return $retorno;
        }
        else
        {
            return array();
        }
    }

    public static function gerarNomeUrlEmpresa($apelido){
        $sql = new MySql();

        $nome = Funcoes::preparaParaUrl($apelido);

        $retorno = $sql->select("select desnomeurl from tb_empresa where desnomeurl like :NOME",array(":NOME"=>$nome."%"));

        $nomesUrl = array_column($retorno, 'desnomeurl');

        $sufixo = 1;
        $procurando = true;
        $ret = "";
        while($procurando)
        {
            $s = $sufixo>1?strval($sufixo):"";

            if(!in_array($nome.$s, $nomesUrl)){
                $ret = $nome.$s;
                break;
            }
            $sufixo++;
        }

        return $ret;
    }

    public static function gerarNomeUrlDivisao($apelido, $idempresa){

        //Os nomes de URL das divisões devem ser únicos dentro da mesma empresa

        $sql = new MySql();

        $nome = Funcoes::preparaParaUrl($apelido);

        $retorno = $sql->select("select desnomeurl from tb_divisao where desnomeurl like :NOME and idempresa = :EMPRESA",array(
            ":NOME"=>$nome."%",
            ":EMPRESA"=>$idempresa
        ));

        $nomesUrl = array_column($retorno, 'desnomeurl');

        $sufixo = 1;
        $procurando = true;
        $ret = "";
        while($procurando)
        {
            $s = $sufixo>1?strval($sufixo):"";

            if(!in_array($nome.$s, $nomesUrl)){
                $ret = $nome.$s;
                break;
            }

            $sufixo++;
        }

        return $ret;
    }

    public function criarEmpresa($nome, $apelido, $imagem, $textoDesc, $idusuario, $file_Temp = null){

        $arquivo = "";

        if(isset($file_Temp)){
            //Tratar imagem

            //Recortar essa imagem e salvar na pasta correta
            $files = new Files();
            $fotoFinal = $files->uploadFotoEmpresa($file_Temp);
            unlink($file_Temp);
            $arquivo = $fotoFinal;
        }

        $nomeUrl = Empresas::gerarNomeUrlEmpresa($apelido);

        $sql = new MySql();

        $sql->query("CALL sp_cadastra_empresa (:NOME, :APELIDO, :TEXTO, :DESURL, :ICONE, :USUARIO)", array(
            ":NOME"=>$nome,
            ":APELIDO"=>$apelido,
            ":TEXTO"=>$textoDesc,
            ":DESURL"=>$nomeUrl,
            ":ICONE"=>$arquivo,
            ":USUARIO"=>$idusuario
        ));
    }

    public function salvarFotoEmpresa($foto, $idempresa){

        if (!isset($foto) || !$foto) {
            throw new \Exception("Selecione uma foto válida para inserir!");
        }

        $files = new Files();
        $caminho_foto = $files->uploadFotoEmpresa($foto);

        $sql = new MySql;


        //Pega foto antiga para poder deletar ao final do processo
        $results = $sql->select("SELECT desicone as foto FROM tb_empresa where idempresa = :EMPRESA;",array(
            ":EMPRESA"=>$idempresa
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
                "update tb_empresa set desicone = :FOTO where idempresa = :EMPRESA;",
                array(
                    ":FOTO" => $caminho_foto,
                    ":EMPRESA" => $idempresa
                )
            );

            try {
                unlink($foto_antiga);
            } catch (\Exception $e) { }
        }else {
            throw new \Exception("Foto inválida. A foto deve ter no mínimo 160 pixels de largura e altura e no máximo 2MB.");
        }

        User::atualizaSession();
    }

    public function salvarFotoDivisao($foto, $iddivisao){

        if (!isset($foto) || !$foto) {
            throw new \Exception("Selecione uma foto válida para inserir!");
        }

        $files = new Files();
        $caminho_foto = $files->uploadFotoDivisao($foto);


        $sql = new MySql;

        //Pega foto antiga para poder deletar ao final do processo
        $results = $sql->select("SELECT desicone as foto FROM tb_divisao where iddivisao = :DIVISAO;",array(
            ":DIVISAO"=>$iddivisao
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
                "update tb_divisao set desicone = :FOTO where iddivisao = :DIVISAO;",
                array(
                    ":FOTO" => $caminho_foto,
                    ":DIVISAO" => $iddivisao
                )
            );

            try {
                unlink($foto_antiga);
            } catch (\Exception $e) { }
        }else {
            throw new \Exception("Foto inválida. A foto deve ter no mínimo 160 pixels de largura e altura e no máximo 2MB.");
        }
    }

    public function obtemDadosEmpresa($nomeEmpresaUrl, $idUsuario){
        $sql = new MySql();
        $retorno = $sql->select("select e.* from tb_empresa e
        left join tb_empresa_admins ea on ea.idempresa = e.idempresa
        where desnomeurl = :EMPRESA and ea.idusuario = :USUARIO", array(
            ":EMPRESA"=>$nomeEmpresaUrl,
            ":USUARIO"=>$idUsuario
        ));

        $data = array();

        if(count($retorno) > 0){
            $data = $retorno[0];
        }

        return $data;

    }

    public static function retornaEmpresa($nomeUrl){
        //Retorna os dados da empresa:

        $dados = array();

        $sql = new MySql();

        $results = $sql->select("SELECT e.idempresa, e.desicone, e.desnome, e.desapelido, e.destextodescritivo,
        e.desnomeurl, count(distinct d.iddivisao) as quantDivisoes,
        count(distinct s.idsetor) as quantSetores, count(distinct c.idusuario) as quantColaboradores
        FROM tb_empresa e
        left join tb_divisao d using(idempresa)
        left join tb_setor s using(iddivisao)
        left join tb_quadro_funcionarios c using(iddivisao)

        where e.desnomeurl = :NOMEURL;", array(
            ":NOMEURL"=>$nomeUrl
        ));

        if(count($results)>0)
        {
            $dados = $results[0];

            $divisoes = array();

            $divisoes = $sql->select("select d.iddivisao, d.desnome, d.desapelido, d.desnomeurl, d.desdescricao,
            d.desicone, d.ativo, d.dtcriacao, (select count(*) from tb_quadro_funcionarios where iddivisao = d.iddivisao) as quantColaboradores,
            case when d.ativo then 'ativo' else 'inativo' end as ativo
            from tb_divisao d
            where d.idempresa = :EMPRESA", array(
                ":EMPRESA"=> $dados["idempresa"]
            ));


            if(count($divisoes)>0){
                for($i = 0;$i<Count($divisoes);$i++){
                    $div = $divisoes[$i]["iddivisao"];

                    $gerentes = array();

                    $gerentes = $sql->select("select ifnull(u.desfotoperfil,'') as desfoto, p.desnome, p.dessobrenome, u.desapelido, ifnull(u.desnomeurl,'') as desnomeurl
                    from tb_gerentes g
                    left join tb_quadro_funcionarios using(id_quadro_funcionario)
                    left join tb_usuarios u using(idusuario)
                    left join tb_pessoas p using(idpessoa)
                    where g.iddivisao = :DIVISAO", array(
                    ":DIVISAO"=>$div
                    ));

                    $divisoes[$i]["gerencia"] = $gerentes;
                }
            }

            $dados["divisoes"] = $divisoes;
        }

        return $dados;

        //Icone
        //Nome
        //Texto descritivo
        //nomeurl
        //Quantidade de divisões
        //Quantidade de setores
        //Quantidade de colaboradores
        //Array => Divisões
            //Nome divisão, Data criação, Quant. colaboradores, status (ativo ou não),
            //Array => Gerentes
                //Foto, nome gerente, apelido, nomeUrl


        //Consulta de dados da empresa

        //Consulta de divisões

        //Foreach  em divisões para criar a informação das divisões
    }

    public static function verificaAdminEmpresa($empresa, $idUsuario = 0){

        $id = $idUsuario;
        if($idUsuario == 0){
            $id = User::retornaDadosDaSession()["idusuario"]??0;
        }else{

        }

        $sql = new MySql();
        $results = $sql->select("select * from tb_empresa e
        left join tb_empresa_admins ea using(idempresa)
        where e.desnomeurl = :EMPRESA and ea.idusuario = :USUARIO", array(
            ":EMPRESA"=>$empresa,
            ":USUARIO"=>$id
        ));

        if(count($results)>0){
            return true;
        }else{
            return false;
        }
    }

    public function adicionarDivisao($dados){

        if(!isset($dados))
        {
            return false;
        }
        else
        {

            try
            {
                $desnome = isset($dados["desnome"])?$dados["desnome"]:"";
                $desapelido = isset($dados["desapelido"])?$dados["desapelido"]:"";
                $desdescricao = isset($dados["textoDesc"])?$dados["textoDesc"]:"";
                $idUsuario = isset($dados["idusuario"])?$dados["idusuario"]:0;
                $idempresa = isset($dados["idempresa"])?$dados["idempresa"]:0;

                $desnomeurl = Empresas::gerarNomeUrlDivisao($desapelido, $idempresa);

                $sql = new MySql();

                $sql->query("CALL sp_cadastro_divisao(:DESNOME, :DESAPELIDO, :DESNOMEURL, :DESDESCRICAO, :USUARIO, :EMPRESA)", array(
                    ":DESNOME"=>$desnome,
                    ":DESAPELIDO"=>$desapelido,
                    ":DESNOMEURL"=>$desnomeurl,
                    ":DESDESCRICAO"=>$desdescricao,
                    ":USUARIO"=>$idUsuario,
                    ":EMPRESA"=>$idempresa
                ));

                return true;

            }
            catch(Exception $e)
            {
                return false;
            }

        }
    }

    public static function verificaDivisao($nomeUrlEmpresa, $nomeUrlDivisao){
        $empresa = isset($nomeUrlEmpresa)?$nomeUrlEmpresa:"";
        $divisao = isset($nomeUrlDivisao)?$nomeUrlDivisao:"";

        $sql=new MySql();
        $retorno = $sql->select("select * from tb_divisao d
        left join tb_empresa e using(idempresa)
        where d.desnomeurl = :DIVISAO and e.desnomeurl = :EMPRESA", array(
            ":DIVISAO"=>$divisao,
            ":EMPRESA"=>$empresa
        ));

        if(count($retorno)>0)
        {
            return true;
        }
        else
        {
            header('Location: /'.$nomeUrlEmpresa);
		    exit;
        }

    }

    public static function RecuperarDadosDivisao($nomeEmpresaUrl, $nomeDivisaoUrl){

        $sql = new MySql();

        $dadosBase = $sql->select("
        select
        e.idempresa,
        e.desnome as nome_empresa,
        e.desapelido as apelido_empresa,
        e.desnomeurl as desnomeurl_empresa,
        ifnull(e.desicone,'') as desfoto_empresa,
        e.destextodescritivo as texto_empresa,
        d.iddivisao,
        d.desapelido,
        d.desnomeurl as desnomeurl_divisao,
        d.desnome as desnome_divisao,
        d.desdescricao as texto_divisao,
        ifnull(d.desicone,'') as desfoto_divisao,
        count(distinct q.idusuario) as quant_colaboradores,
        count(distinct s.idsetor) as quant_setores,
        count(distinct gestores.id_quadro_funcionario) as quant_gestores,
        count(distinct g.id_quadro_funcionario) as qtd_ger,
        count(distinct su.id_quadro_funcionario) as qtd_sup,
        0 as quant_tarefas


        from tb_divisao d
        left join tb_empresa e using(idempresa)
        left join tb_quadro_funcionarios q using(iddivisao)


        left join tb_setor s using(iddivisao)
        left join tb_gerentes g using(id_quadro_funcionario)
        left join tb_supervisor su using(id_quadro_funcionario)

        left join (
        select iddivisao, g.id_quadro_funcionario from tb_gerentes g
        union all
        select se.iddivisao, s.id_quadro_funcionario from tb_supervisor s
        left join tb_setor se using(idsetor)
        ) as gestores on gestores.iddivisao = d.iddivisao

        where d.desnomeurl = :DIVISAO and e.desnomeurl = :EMPRESA", array(
        ":DIVISAO"=>&$nomeDivisaoUrl,
        ":EMPRESA"=>&$nomeEmpresaUrl
        ));

        if(count($dadosBase)==0){
            return false;
        }

        $divisao = $dadosBase[0];

        $id_divisao = $divisao["iddivisao"];

        //Gerencia

        $gerencia = array(
            "nome"=>"",
            "desnomeurl"=>"",
            "cargo"=>"",
            "sobre"=>"",
            "ativo"=>0,
            "afastado"=>0,
            "foto"=>"",
            "idql"=>0,
            "idgerente"=>0
        );

        $temp = $sql->select("select
        p.desnome as nome,
        p.dessobrenome as sobrenome,
        u.desnomeurl,
        cargo.desnome as cargo,
        p.desdescricao as sobre,
        ql.ativo,
        ql.afastado,
        ifnull(u.desfotoperfil,'') as foto,
        ql.id_quadro_funcionario as idql,
        g.idgerente

        from tb_gerentes g

        left join tb_quadro_funcionarios ql using(id_quadro_funcionario)

        left join tb_usuarios u using(idusuario)
        left join tb_pessoas p using(idpessoa)

        left join tb_cargos cargo using(idcargo)
        where g.iddivisao = :DIVISAO", array(
            ":DIVISAO"=>$id_divisao
        ));

        if(count($temp)>0)
        {
            $gerencia = $temp;
        }

        $divisao['gerencia'] = $gerencia;

        //Setores

        $setor = array(
            "idsetor"=>"",
            "desnome"=>"",
            "desdescricao"=>"",
            "desnomeurl"=>"",
            "ativo"=>0,
            "ql"=>0,
            "supervisao"=>array(
                "desnome"=>"",
                "desnomeurl"=>"",
                "desfoto"=>"",
                "desemail"=>""
            )
        );

        $temp = $sql->select("select
        s.idsetor,
        s.desnome,
        s.desdescricao,
        s.desnomeurl,
        s.ativo,
        (select count(*) from tb_colaborador where idsetor = s.idsetor) as ql,
        (select count(*) from tb_supervisor where idsetor = s.idsetor) as sup
        from tb_divisao d
        left join tb_setor s using(iddivisao)
        left join tb_colaborador c using(idsetor)
        where d.iddivisao = :DIVISAO
        group by s.idsetor,
        s.desnome,
        s.desdescricao,
        s.desnomeurl,
        s.ativo", array(
            ":DIVISAO"=>$id_divisao
        ));

        if(count($temp)>0){
            $setor = $temp;
        }

        for ($i = 0; $i<count($setor); $i++)
        {
            $supervisao = array(
                "nome"=>"",
                "desnomeurl"=>"",
                "desfoto"=>"",
                "desemail"=>""
            );

            $temp = $sql->select("select
            concat(p.desnome,' ',p.dessobrenome) as nome,
            u.desnomeurl,
            ifnull(u.desfotoperfil,'') as desfoto,
            p.desemail,
            s.idsupervisor

                from tb_supervisor s

            left join tb_quadro_funcionarios using(id_quadro_funcionario)
            left join tb_usuarios u using(idusuario)
            left join tb_pessoas p using(idpessoa)

            where s.idsetor = :SETOR", array(
                ":SETOR"=>$setor[$i]["idsetor"]
            ));

            if(count($temp)>0){
                $supervisao = $temp;
            }

            $setor[$i]["supervisao"] = $supervisao;
        }

        $divisao["setores"] = $setor;


        return $divisao;


		}

		public static function RetornaDivisoes($nomeEmpresaUrl){
			$sql = new MySql();
			$dados = $sql->select("select d.iddivisao, d.desnome, ifnull(d.desapelido, d.desnome) as desapelido, d.desnomeurl, d.desdescricao, d.desicone from tb_divisao d
			left join tb_empresa e on e.idempresa = d.idempresa
			where d.ativo = 1 and e.desnomeurl = :EMPRESA", array(
				":EMPRESA"=>$nomeEmpresaUrl
			));
			$retorno['quant'] = 0;

			if(count($dados) > 0){
				$retorno['quant'] = count($dados);
				$retorno['dados'] = $dados;
			}

			return $retorno;
		}

    public static function retornaFuncionariosDivisao(int $iddivisao){
        $sql = new MySql();
        $retorno = $sql->select("CALL sp_lista_funcionarios(:DIVISAO)",array(
            ":DIVISAO"=>$iddivisao
        ));

        if(count($retorno)>0){
            return $retorno;
        }else {
            return [];
        }
    }

    public static function retornaFuncionariosSetor($idsetor){
        $sql = new MySql();
        $retorno = $sql->select("select
        u.desfotoperfil as avatar
        ,p.desemail
        ,p.desnome
        ,p.dessobrenome
        ,ifnull(c.desnome,'') as cargo
        ,case when ql.ativo = 1 then 'Ativo' else 'Inativo' end as status
        ,ql.ativo
        ,'efetivo' as tipo

        from tb_colaborador col
        left join tb_quadro_funcionarios ql using(id_quadro_funcionario)
        left join tb_usuarios u using(idusuario)
        left join tb_pessoas p using(idpessoa)
        left join tb_cargos c using(idcargo)
        left join tb_setor s using(idsetor)

        where s.idsetor = :SETOR",array(
            ":SETOR"=>$idsetor
        ));

        if(count($retorno)>0){
            return $retorno;
        }else {
            return [];
        }
    }

    public static function retornaCargosCadastrados($idEmpresa){
        $retorno = array();

        $sql = new MySql();

        $dados = $sql->select("select idcargo, desnome, obs from tb_cargos where idempresa = :EMPRESA", array(
            ":EMPRESA"=>$idEmpresa
        ));

        if(count($dados)>0){
            $retorno = $dados;
        }

        return $retorno;
    }

    public static function alteraCargo($idEmpresa, $nomeAntigo, $nomeNovo, $obs, $idusuario){
        $nome_a = $nomeAntigo;
        $nome_n = Funcoes::prepararParaBanco($nomeNovo);
        $id_empresa = $idEmpresa;
        $obs = Funcoes::prepararParaBanco($obs);
        $id_usuario = $idusuario;

        //Caso os nomes antigo e novo forem diferentes, verifica se já existe um registo com o nome novo na empresa
        if(trim(strtoupper($nome_a)) != trim(strtoupper($nome_n)))
        {
            //Verificar se já existe o Cargo com o nome nove, se sim, retorna erro
            $sql = new MySql();
            $nomes = $sql->select("select trim(upper(desnome)) as desnome from tb_cargos
            where idempresa = :EMPRESA and trim(upper(desnome)) = :NOME;", array(
                ":EMPRESA"=>$id_empresa,
                ":NOME"=>trim(strtoupper($nome_n))
            ));


            if(count($nomes)>0){

                Empresas::setErro("Não foi possível alterar pois já existe um cargo chamado \"$nome_n\" nesta empresa.");

                return false;
            }

        }

        $sql = new MySql();
        $sql->query("UPDATE tb_cargos SET desnome = :NOME_N, obs = :OBS, usuariocriacao = :USUARIO
        where desnome = :NOME and idempresa = :EMPRESA", array(
            ":NOME_N"=>$nome_n,
            ":OBS"=>$obs,
            ":USUARIO"=>$id_usuario,
            ":NOME"=>$nome_a,
            ":EMPRESA"=>$id_empresa
        ));

        return true;
    }

    public static function retornaCargos(int $empresa){

        $sql = new MySql();
        $retorno = $sql->select("select 0 as idcargo, 'Selecione um cargo' as desnome
				union all
				select idcargo, desnome from tb_cargos  where idempresa = :EMPRESA;", array(
            ":EMPRESA"=>$empresa
        ));

        if(count($retorno)>0){
            return $retorno;
        }else {
            ["desnome"=>""];
        }
    }

    public static function retornaSetores(string $divisao){

        $retorno = "";
        $sql = new MySql();
        $retorno = $sql->select("SELECT s.idsetor, s.desnome, s.desdescricao, s.desnomeurl, s.ativo, s.iddivisao FROM tb_setor s
        left join tb_divisao d on d.iddivisao = s.iddivisao
        where d.desnomeurl = :DIVISAO;", array(
            ":DIVISAO"=>$divisao
        ));

        if(count($retorno)>0)
        {
            return $retorno;
        }
        else
        {
            "";
        }
    }

    public static function adicionaSetor(string $nome, string $descricao, int $iddivisao, int $idusuario){

        $sql = new MySql();
        $return = $sql->select("CALL sp_cria_setor(:NOME, :DESCRICAO, :DIVISAO, :USUARIO);", array(
            ":NOME"=>$nome,
            ":DESCRICAO"=>$descricao,
            ":DIVISAO"=>$iddivisao,
            ":USUARIO"=>$idusuario
        ));



        if(count($return) > 0)
        {
            return $return[0];
        }
        else
        {
            return "";
        }
    }

    public static function setErro($msg){
        $_SESSION[Empresas::ERROR] = $msg;
    }

    public static function getError(){
        $msg = (isset($_SESSION[Empresas::ERROR]) && $_SESSION[Empresas::ERROR])?$_SESSION[Empresas::ERROR]:'';
        Empresas::clearError();
        return $msg;
    }

    protected static function clearError(){
        $_SESSION[Empresas::ERROR] = NULL;
    }

    public static function DeletaDivisao($idDivisao){
        $sql = new MySql();
        try
        {
            $sql->query("DELETE FROM tb_divisao where iddivisao = :DIVISAO",array(
                ":DIVISAO"=>$idDivisao
            ));
        }catch(\Exception $e){
            Empresas::setErro("Erro ao excluir divisão.");
        }
    }

    public static function VerificaEmailDivisao($email, $iddivisao){
        $sql = new MySql();
        $retorno = $sql->select("select
        p.desnome,
        p.dessobrenome,
        p.desemail,
        u.desapelido,
        p.desdescricao as sobre,
        case when ifnull(u.desfotoperfil,'') <> '' then u.desfotoperfil else 'views/uploads/upics/noprofilepicP.jpg' end desfotoperfil,
        ifnull(u.desnomeurl,'') desnomeurl,
        ifnull(c.desnome,'') cargo,
        qf.ativo
        from tb_quadro_funcionarios qf
        left join tb_usuarios u using(idusuario)
        left join tb_pessoas p using(idpessoa)
        left join tb_cargos c using(idcargo)
        where qf.iddivisao = :DIVISAO and p.desemail = :EMAIL", array(
            ":DIVISAO"=>$iddivisao,
            ":EMAIL"=>$email
        ));

        if(count($retorno)>0)
        {
            $dados = $retorno[0];
            $dados['desnome'] = Funcoes::FormataNomeProprio($dados['desnome'], false);
            $dados['dessobrenome'] = Funcoes::FormataNomeProprio($dados['dessobrenome'], false);
            return $dados;
        }
        else
        {
            return array();
        }
    }

    public function DefineGerente($emails, $divisao, $usuario){

        $sql = new MySql();
        if(count($emails)>0)
        {
            foreach($emails as $value){
                $sql->query("call sp_define_gerente(:DIVISAO,:EMAIL,:USUARIO)",array(
                    ":DIVISAO"=>$divisao,
                    ":EMAIL"=>@$value,
                    ":USUARIO"=>$usuario
                ));
            }
        }
    }

    public function DefineSupervisor($emails, $setor, $divisao, $usuario){

        $sql = new MySql();
        if(count($emails)>0)
        {
            foreach($emails as $value){
                $sql->query("call sp_define_supervisor(:SETOR,:EMAIL, :DIVISAO, :USUARIO)",array(
                    ":SETOR"=>$setor,
                    ":EMAIL"=>$value,
                    ":DIVISAO"=>$divisao,
                    ":USUARIO"=>$usuario
                ));
            }
        }
    }

    public function DefineFuncionario($emails, $setor, $divisao, $usuario){

        $sql = new MySql();
        if(count($emails)>0)
        {
            foreach($emails as $value){
                $sql->query("call sp_define_colaborador(:SETOR,:EMAIL, :DIVISAO, :USUARIO)",array(
                    ":SETOR"=>$setor,
                    ":EMAIL"=>$value,
                    ":DIVISAO"=>$divisao,
                    ":USUARIO"=>$usuario
                ));
            }
        }
    }

    public static function RemoveGerente($idgerente){
        $sql = new MySql();
        $sql->query("delete from tb_gerentes where idgerente = :ID", array(
            ":ID"=>$idgerente
        ));
    }

    public static function RemoveSupervisor($id){
        $sql = new MySql();
        $sql->query("DELETE FROM tb_supervisor WHERE idsupervisor = :ID", array(
            ":ID"=>$id
        ));
    }

    public static function DeletaSetor($idSetor){
        $sql = new MySql();
        $sql->query("DELETE FROM tb_setor WHERE idsetor = :ID", array(
            ":ID"=>$idSetor
        ));
    }

    public static function AlteraSetor($idSetor, $nome, $descricao, $ativo){
        $sql = new MySql();

        $valorAtivo = 0;

        if($ativo){
            $valorAtivo = 1;
        }

        $sql->query("UPDATE tb_setor SET desnome = :NOME, desdescricao = :DESCRICAO, ativo = :ATIVO WHERE idsetor = :SETOR", array(
            ":SETOR"=>$idSetor,
            ":NOME"=>$nome,
            ":DESCRICAO"=>$descricao,
            ":ATIVO"=>$valorAtivo
        ));
		}

		public static function ListaUnidades($id){
			$sql = new MySql();
			$retorno = [];

			$dados = $sql->select("
			select d.iddivisao, d.desnome, d.desapelido from tb_divisao d
			left join tb_divisao d2 on d2.idempresa = d.idempresa
			where d2.iddivisao = :ID", array(
				":ID"=>$id
			));

			if(count($dados)>0){
				$retorno = $dados;
			}

			return $retorno;

		}

		public static function ListaSetores($id){
			$sql = new MySql();
			$retorno = [];

			//Lista de setores que tenham um responsável

			$dados = $sql->select("
			select s.idsetor, s.desnome, s.desdescricao from tb_setor s
			left join tb_supervisor sup on sup.idsetor = s.idsetor
			where sup.idsupervisor is not null and s.iddivisao = :ID", array(
				":ID"=>$id
			));

			if(count($dados)>0){
				$retorno = $dados;
			}

			return $retorno;

		}
}
