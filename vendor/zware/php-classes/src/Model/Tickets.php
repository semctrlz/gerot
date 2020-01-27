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
        car.desnome as cargo_rem,

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

	public static function GeraArvoreEmpresas(int $idusuario){
		$sql = new MySql();

		$retorno = $sql->select("call sp_arvore_empresa(:USUARIO)", array(
			":USUARIO"=>$idusuario
		));

		$dados = array();

		$ultimaEmpresa = 0;
		$tempEmpresa = [];
		if(count($retorno)>0){
			$empresa_repeat = -1;
			$divisao_repeat = -1;
			for($i = 0; $i<count($retorno);$i++)
			{
				if($retorno[$i]['idempresa'] != $ultimaEmpresa){
					$empresa_repeat += 1;
					$ultimaEmpresa = $retorno[$i]['idempresa'];
				}

				$dados[$empresa_repeat]["nomeEmp"] = $retorno[$i]['nome_empresa'];
				$dados[$empresa_repeat]["idEmp"] = $retorno[$i]['idempresa'];



				$dados[$empresa_repeat]['divisao'] =





			}
		}

		return json_encode($dados);

	}

}
?>
