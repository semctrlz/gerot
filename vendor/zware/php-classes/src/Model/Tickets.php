<?php
namespace Zware\Model;

use \Zware\DB\MySql;
use \Zware\Mailer;
use \Zware\Model;

//Nivel abertura Ticket

class Tickets extends Model
{
	public static function GeraTickets(int $idUsuario){

		$retorno = array("quant"=>0);

		$sql = new Mysql();

		$results = $sql->select("select
		t.idticket,
		t.destitulo,
		t.dtcriacao,
		t.descorpo,
		rem.desfotoperfil as foto_rem,
		remp.desnome as nome_rem,
		rem.desnomeurl as url_rem,
        car.desnome as cargo_rem,

		ifnull(dest.desfotoperfil, 'null') as foto_dest,
		ifnull(destp.desnome,'null') as nome_dest,
		ifnull(dest.desnomeurl,'null') as url_dest

		,case
			when prioridade = 1 then 'Normal'
				when prioridade = 2 then 'Moderada'
				when prioridade = 3 then 'Alta'
			when prioridade = 4 then 'Urgente'
		end as prioridade

		,case
			when prioridade = 1 then 'badge-info'
				when prioridade = 2 then 'badge-warning'
				when prioridade = 3 then 'badge-danger'
			when prioridade = 4 then 'badge-danger'
		end as prioridade_classe

		,case
			when tipo_ticket = 1 then 'Serviço'
				when tipo_ticket = 2 then 'Relatório'
				end as tipo
		,t.dtprazo
		,s.desnome as nome_setor
		,cat.desnome as categoria
		,sc.desnome as subcategoria
		,case
				when t.status_ticket = 0 then 'Novo'
				when t.status_ticket = 1 then 'Aberto'
				when t.status_ticket = 2 then 'Finalizado'
				when t.status_ticket = 3 then 'Cancelado'
				when t.status_ticket = 4 then 'Excluído'
		end as status_ticket
        ,case
				when t.status_ticket = 0 then 'novo'
				when t.status_ticket = 1 then 'aberto'
				when t.status_ticket = 2 then 'finalizado'
				when t.status_ticket = 3 then 'cancelado'
				when t.status_ticket = 4 then 'excluído'
		end as classe_status_ticket,
		dt.lido,
		datediff(t.dtprazo, curdate()) as dif_prazo

		 from tb_tickets t
		left join tb_destinatarios_tickets dt using(idticket)
		left join tb_usuarios rem on rem.idusuario = t.usuariocriacao
		left join tb_pessoas remp on remp.idpessoa = rem.idpessoa

		left join tb_usuarios dest on dest.idusuario = t.usuario_designado
		left join tb_pessoas destp on destp.idpessoa = dest.idpessoa

		left join tb_setor s on s.idsetor = t.idsetor_solicitacao

		left join tb_categorias cat on cat.idcategoria = t.idcategoria
		left join tb_subcategorias sc on sc.idsubcategoria = t.idsubcategoria


        left join tb_divisao divi on divi.iddivisao = s.iddivisao
        left join tb_quadro_funcionarios ql on ql.iddivisao = divi.iddivisao and ql.idusuario = rem.idusuario
        left join tb_cargos car on car.idcargo = ql.idcargo

		where dt.idusuario = :USUARIO and t.status_ticket = 1

        order by t.status_ticket, t.prioridade desc, dtprazo asc, datediff(t.dtprazo, curdate()) desc", array(
			":USUARIO" => $idUsuario
		));

			if(count($results) >0){
				$retorno["dados"] = $results;
				$retorno["quant"] = count($results);

				return $retorno;
			}else{
				return array("quant"=>0);
			}

	}

	public static function GeraArvoreEmpresas($idusuario){
		$sql = new MySql();

		$retorno = $sql->select("
			select distinct d.iddivisao, concat(e.desnome, ' - ', d.desapelido) as desnome FROM dbflow.tb_liberacao_ticket lt
			left join tb_quadro_funcionarios ql on ql.id_quadro_funcionario = lt.idquadro
			left join tb_divisao d on d.iddivisao = lt.iddivisao
			left join tb_empresa e on e.idempresa = d.idempresa
			where d.ativo = 1 and ql.idusuario = :USUARIO
			ORDER BY e.desnome, d.desapelido", array(
			":USUARIO"=>$idusuario
		));

		return $retorno;
	}

	public static function SetoresDestino($idusuario){
		$sql = new MySql();
		$dados = $sql->select("select e.idempresa, e.desnome nome_empresa, d.iddivisao, d.desnome as nome_divisao, s.idsetor, s.desnome as nome_setor from tb_setor s
		left join tb_divisao d on s.iddivisao = d.iddivisao
		left join tb_empresa e on e.idempresa = d.idempresa

		where e.idempresa in (select distinct e.idempresa from tb_quadro_funcionarios ql
		left join tb_divisao d on d.iddivisao = ql.iddivisao
		left join tb_empresa e on e.idempresa = d.idempresa
		where ql.idusuario = :USUARIO)

		order by e.desnome, d.desnome, s.desnome", array(
			":USUARIO"=>$idusuario
		));
		$retorno = [];
		$retorno['quantidade'] = count($retorno);
		$retorno['dados']= $dados;

		return $retorno;

	}

	public static function RecuperaNotificacoes($usuario, $tipo, $nr){

		$lida = 2;
		if($nr){
			$lida = 1;
		}

		$sql = new MySql();
		$dados = $sql->select("select
			n.idnotificacao,
			n.tipo,
			case
				when n.tipo ='a' then 'fa-users'
					when n.tipo ='c' then 'fa-sign-in-alt'
					when n.tipo ='d' then 'fa-calendar-plus'
					else 'fa-info'
			end as icone,

			n.titulo,
			n.corpo_notificacao,
			n.link_destino,
			n.lida,
			case
				when TIMESTAMPDIFF(DAY, n.dtcriacao,current_timestamp()) / 365 between 1 and 2 then concat('mais de ', 1, ' ano')
				when TIMESTAMPDIFF(DAY, n.dtcriacao,current_timestamp()) / 365 >= 2 then concat('mais de ', TIMESTAMPDIFF(YEAR ,n.dtcriacao, NOW()), ' anos')

				when TIMESTAMPDIFF(DAY, n.dtcriacao,current_timestamp()) / 30 between 1 and 2 then concat('mais de ', 1, ' mês')
				when TIMESTAMPDIFF(DAY, n.dtcriacao,current_timestamp()) / 30 > 1 then concat('mais de ', TIMESTAMPDIFF(MONTH ,n.dtcriacao, NOW()), ' meses')

				when TIMESTAMPDIFF(DAY ,n.dtcriacao,current_timestamp()) between 1 and 2 then concat('mais de ', 1, ' dia')
				when TIMESTAMPDIFF(DAY ,n.dtcriacao,current_timestamp()) > 1 then concat(TIMESTAMPDIFF(DAY ,n.dtcriacao, now()), ' dias')

				when TIMESTAMPDIFF(HOUR ,n.dtcriacao,current_timestamp()) between 1 and 2 then concat(1, ' hora')
				when TIMESTAMPDIFF(HOUR ,n.dtcriacao,current_timestamp()) > 1 then concat(TIMESTAMPDIFF(HOUR ,n.dtcriacao, now()), ' horas')

				when TIMESTAMPDIFF(MINUTE ,n.dtcriacao,current_timestamp()) between 1 and 2 then concat(1, ' min')
				when TIMESTAMPDIFF(MINUTE ,n.dtcriacao,current_timestamp()) > 1 then concat(TIMESTAMPDIFF(MINUTE ,n.dtcriacao,now()), ' mins')

				ELSE 'segundos atrás'
			end as tempo,
			n.dtcriacao,

			case
				when n.tipo = 'a' or n.tipo = 'd' then ifnull(u.desfotoperfil, 'views/uploads/upics/noprofilepicP.jpg')
					when n.tipo = 'c' then ifnull(d.desicone, 'views/uploads/upics/noempresapicP.jpg')
					else 'src/imagens/ZwFIconex250.png'
			end as pic,
			case
				when n.tipo = 'a' or n.tipo = 'd' then ifnull(p.desnome, '')
					when n.tipo = 'c' then ifnull(d.desnome, '')
					else 'Z-Ticket'
			end as title_img,
			case
				when n.tipo = 'a' or n.tipo = 'd' then ifnull(u.desnomeurl, '')
					when n.tipo = 'c' then ifnull(d.desnomeurl, '')
					else '/'
			end as link_ticket

			from tb_notificacoes n
			left join tb_usuarios u on u.idusuario = n.idusuario
			left join tb_pessoas p on p.idpessoa = u.idpessoa
			left join tb_divisao d on d.iddivisao = n.iddivisao

			where n.usuario_dest = :USUARIO and n.tipo like :TIPO and n.lida < :LIDA

			order by n.lida, n.dtcriacao desc;", array(
				":USUARIO"=>$usuario,
				":TIPO"=>"%".$tipo."%",
				":LIDA"=>$lida
		));

		if(count($dados)>0){
			return $dados;
		}else{
			return [];
		}
	}

	public static function DefineComoLida($id){
		$sql = new MySql();

		$sql->query("update tb_notificacoes SET lida = 1 WHERE idnotificacao = :ID;",
		array(":ID"=>$id));

		return "OK";

	}

	public static function ConsultaCategoria($idDivisao)
	{
		$sql = new MySql();
		$retorno = [];

		$dados = $sql->select('
		select c.* from tb_categorias c
		left join tb_divisao d on d.idempresa = c.idempresa
		where d.iddivisao = :ID', array(
			":ID"=>$idDivisao
		));

		if(count($dados)>0){
			$retorno = $dados;
		}

		return $retorno;

	}

	public static function ListaAssuntos($idCategoria)
	{
		$sql = new MySql();
		$retorno = [];

		$dados = $sql->select('
		select idsubcategoria as id, desnome from tb_subcategorias where idcategoria = :ID', array(
			":ID"=>$idCategoria
		));

		if(count($dados)>0){
			$retorno = $dados;
		}

		return $retorno;

	}

	public static function CadastraTicket($dados)
	{
		//Recuperar os campos
		$titulo = $dados['ptitulo'];
		$texto = $dados['pcorpo'];
		$prazo = $dados['pprazo'];
		$unidade_solicitacao = $dados['punidadeSolicitacao'];
		$unidade_destino = $dados['punidadeDestino'];
		$setor_destino = $dados['psetorDestino'];
		$prioridade = $dados['pprioridade'];
		$categoria = $dados['pcategoria'];
		$subcategoria = $dados['pidSubcategoria'];
		$usuario_criacao = $dados['pidusuarioCriacao'];

		$sql = new MySql();










		return false;
	}
}
?>
