<?php
namespace Zware\Model;

use \Zware\DB\MySql;
use \Zware\Mailer;
use \Zware\Model;

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

		ifnull(dest.desfotoperfil, 'null') as foto_dest,
		ifnull(destp.desnome,'null') as nome_dest,
		ifnull(dest.desnomeurl,'null') as url_dest

		,case
			when prioridade = 1 then 'Baixa'
				when prioridade = 2 then 'Moderada'
				when prioridade = 3 then 'Alta'
			when prioridade = 4 then 'Urgente'
		end as prioridade

		,case
			when prioridade = 1 then 'priori-baixa'
				when prioridade = 2 then 'priori-moderada'
				when prioridade = 3 then 'priori-alta'
			when prioridade = 4 then 'priori-urgente'
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
			when t.status_ticket = 1 then 'Aberto'
				when t.status_ticket = 2 then 'Finalizado'
				when t.status_ticket = 3 then 'Cancelado'
				when t.status_ticket = 4 then 'Excluído'
		end as status_ticket,
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


		where dt.idusuario = :USUARIO and t.status_ticket = 1", array(
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

}
?>
