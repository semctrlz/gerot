<?php
namespace Zware\Model;

use \Zware\DB\MySql;
use \Zware\Mailer;
use \Zware\Model;

//Nivel abertura Ticket

class Clientes extends Model
{
	public function BuscaDadosCNPJ(String $cnpj)
	{
		try
		{
			//Buscar dados do cnpj na api da Receita federal.
			$url = "https://www.receitaws.com.br/v1/cnpj/".$cnpj;
			$dados = json_decode(file_get_contents($url));
			$this->setData($dados);
			return $this;
		}

		catch(\Exception $e)
		{
			throw new \Exception("Muitas solicitações seguidas. Tente novamente em 30 segundos.");
		}
	}

	public static function ListaRotasPorEmail(string $email){

		$retorno = [];
		$sql = new MySql();
		$dados = $sql->select("CALL sp_listaRotas(:MAIL)", array(
			":MAIL"=>$email
		));

		if(count($dados)>0){
			$retorno["lista"] = $dados;
			$retorno["quantRotas"] = count($dados);
			return $retorno;
		}else{
			$retorno["quantRotas"] = 0;
			return $retorno;
		}
	}

	public static function ListarFormasPagto(){
		$sql = new MySql();
		$dados = $sql->select("select cd_forma_pagto, descricao FROM dbflow.tb_forma_pagto");

		$retorno = [];
		$retorno["quant"] = count($dados);

		if(count($dados)> 0)
		{
			$retorno["formas"] = $dados;
		}

		return $retorno;

	}

	public static function ListarCondPagto(){
		$sql = new MySql();
		$dados = $sql->select("select cd_cond_pagto, descricao FROM dbflow.tb_cond_pagto order by descricao;");

		$retorno = [];
		$retorno["quant"] = count($dados);

		if(count($dados)> 0)
		{
			$retorno["condicao"] = $dados;
		}

		return $retorno;

	}

	public static function ListarSegmentos(){
		$sql = new MySql();
		$dados = $sql->select("SELECT cdMercado as cod, mercado as descricao FROM tb_mercado;");

		$retorno = [];
		$retorno["quant"] = count($dados);

		if(count($dados)> 0)
		{
			$retorno["segmentos"] = $dados;
		}

		return $retorno;

	}

	public static function CadastraCliente($valores) {

		$resposta = array(
			"status"=>"success",
			"retorno"=>""
		);

		$cnpjCpf = $valores["pcnpjCpf"]??"";
		$pessoa = "";

		if(strlen($cnpjCpf) == 11){
			$pessoa = "F";
		}else if(strlen($cnpjCpf) == 14){
			$pessoa = "J";
		}

		$fantasia = $valores["pfantasia"]??"";
		$razaoSocial = $valores["prazaoSocial"]??"";
		$cep = $valores["pcep"]??"";
		$logradouro = $valores["plogradouro"]??"";
		$numero = $valores["pnumero"]??"";
		$complemento = $valores["pcomplemento"]??"";
		$bairro = $valores["pnairro"]??"";
		$municipio = $valores["pmunicipio"]??"";
		$estado = $valores["pestado"]??"";
		$pais = $valores["ppais"]??"";
		$fonePrincipal = $valores["pfonePrincipal"]??"";
		$emailXml = $valores["pemailXML"]??"";
		$foneComprador = $valores["pfoneComprador"]??"";
		$emailComprador = $valores["pemailComprador"]??"";
		$nomeComprador = $valores["pnomeComprador"]??"";
		$foneFinanceiro = $valores["pfoneFinanceiro"]??"";
		$emailFinanceiro = $valores["pemailFinanceiro"]??"";
		$foneFiscal = $valores["pfoneFiscal"]??"";
		$emailFiscal = $valores["pemailFiscal"]??"";
		$rota = $valores["prota"]??"";
		$segmento = $valores["psegmento"]??"";
		$formaPagto = $valores["pformaPagamento"]??"";
		$condPagto = $valores["pcondPagamento"]??"";
		$vl = $valores["pvalorPrimeiraCompra"]??"";
		$valorPrimeiraCompra =  is_numeric($vl) ? (float)$vl : 0;
		$obs = $valores["pobs"]??"";
		$usuario = User::retornaDadosDaSession()["idusuario"];
		//$date = DateTime::createFromFormat('d/m/Y', "30/09/2012");



		$dataNascimento = "01/01/2000";
		if($pessoa == "F")
		{
			$dataNascimento = $valores["pnascimento"];
		}

		$dataNascimento = implode('-', array_reverse(explode('/', $dataNascimento)));

		$sql = new MySql();

		$dados = $sql->select("select idpedido, cpf_cnpj, status from tb_cadastro_cliente where cpf_cnpj = :cnpj",array(
			":cnpj"=>$cnpjCpf
		));

		//Verificar se o cliente em questão já está cadastrado
		if(count($dados) > 0)
		{
			$registro = $dados[0];
			$_id = $registro["idpedido"];
			$_status = $registro["status"];

			$consulta = "update tb_cadastro_cliente set
			tipo = :tipo,
			nome_fantasia = :nome_fantasia,
			razao_social = :razao_social,
			cep = :cep,
			logradouro = :logradouro,
			numero = :numero,
			complemento = :complemento,
			bairro = :bairro,
			municipio = :municipio,
			estado = :estado,
			pais = :pais,
			fonePrincipal = :fonePrincipal,
			emailXml = :emailXml,
			foneComprador = :foneComprador,
			emailComprador = :emailComprador,
			nomeComprador = :nomeComprador,
			foneFinanceiro = :foneFinanceiro,
			emailFinanceiro = :emailFinanceiro,
			foneFiscal = :foneFiscal,
			emailFiscal = :emailFiscal,
			rota = :rota,
			segmento = :segmento,
			formaPagamento = :formaPagamento,
			condicaoPagamento = :condicaoPagamento,
			valorPrimeiraCompra = :valorPrimeiraCompra,
			obsVendedor = :obsVendedor,
			status = :status,
			usuarioCadastro = :usuarioCadastro
			where idpedido = :idPedido";

			if($pessoa == "F"){
				$consulta = "update tb_cadastro_cliente set
				tipo = :tipo,
				nome_fantasia = :nome_fantasia,
				razao_social = :razao_social,
				cep = :cep,
				logradouro = :logradouro,
				numero = :numero,
				complemento = :complemento,
				bairro = :bairro,
				municipio = :municipio,
				estado = :estado,
				pais = :pais,
				fonePrincipal = :fonePrincipal,
				emailXml = :emailXml,
				foneComprador = :foneComprador,
				emailComprador = :emailComprador,
				nomeComprador = :nomeComprador,
				foneFinanceiro = :foneFinanceiro,
				emailFinanceiro = :emailFinanceiro,
				foneFiscal = :foneFiscal,
				emailFiscal = :emailFiscal,
				rota = :rota,
				segmento = :segmento,
				formaPagamento = :formaPagamento,
				condicaoPagamento = :condicaoPagamento,
				valorPrimeiraCompra = :valorPrimeiraCompra,
				obsVendedor = :obsVendedor,
				status = :status,
				dataNascimento = :nascimento,
				usuarioCadastro = :usuarioCadastro
				where idpedido = :idPedido";
			}

			//Caso esteja cadastrado e com status pendente, edita as informações do cadastro
			if($_status == "P")
			{
				try{
					$sql->query($consulta, array(
					":tipo"=>$pessoa,
					":nome_fantasia"=>$fantasia,
					":razao_social"=>$razaoSocial,
					":cep"=>$cep,
					":logradouro"=>$logradouro,
					":numero"=>$numero,
					":complemento"=>$complemento,
					":bairro"=>$bairro,
					":municipio"=>$municipio,
					":estado"=>$estado,
					":pais"=>$pais,
					":fonePrincipal"=>$fonePrincipal,
					":emailXml"=>$emailXml,
					":foneComprador"=>$foneComprador,
					":emailComprador"=>$emailComprador,
					":nomeComprador"=>$nomeComprador,
					":foneFinanceiro"=>$foneFinanceiro,
					":emailFinanceiro"=>$emailFinanceiro,
					":foneFiscal"=>$foneFiscal,
					":emailFiscal"=>$emailFiscal,
					":rota"=>$rota,
					":segmento"=>$segmento,
					":formaPagamento"=>$formaPagto,
					":condicaoPagamento"=>$condPagto,
					":valorPrimeiraCompra"=>$valorPrimeiraCompra,
					":obsVendedor"=>$obs,
					":status"=>"P",
					":usuarioCadastro"=>$usuario,
					":idPedido"=>$_id,
					":nascimento"=>$dataNascimento
					));
				}
				catch(Exception $e)
				{
					$resposta["retorno"] = "Erro: $e";
					$resposta["status"] = "success";
					return $resposta;
				}

				$resposta["status"] = "success";
				$resposta["retorno"] = "Já havia um cadastro pendente com este mesmo CNPJ/CPF e ele foi alterado com sucesso!";
				return $resposta;

			}
			else if($_status == "N")
			{
				$resposta["status"] = "erro";
				$resposta["retorno"] = "O Cadastro para este cliente foi negado. Verifique com o setor de cadastro.";
				return $resposta;
			}
			else if($_status == "F")
			{
				$resposta["status"] = "erro";
				$resposta["retorno"] = "Este cliente consta como cadastrado. Verifique com o setor de cadastro.";
				return $resposta;
			}
			else if($_status == "R")
			{
				$resposta["status"] = "erro";
				$resposta["retorno"] = "O cadastro deste cliente já está em processamento. Impossível alterá-lo agora. Verifique com o setor de cadastro.";
				return $resposta;
			}

		}

		//CAso esteja cadastrado e com status Negado, retornar dizendo que o cadastro foi negado pelo setor de cadastros e pedindo para entrar em contato

		//Caso esteja cadastrado e com status Finalizado, avisa que o cadastro já foi efetuado

		$consulta = "insert into tb_cadastro_cliente
			(cpf_cnpj,
			tipo,
			nome_fantasia,
			razao_social,
			cep,
			logradouro,
			numero,
			complemento,
			bairro,
			municipio,
			estado,
			pais,
			fonePrincipal,
			emailXml,
			foneComprador,
			emailComprador,
			nomeComprador,
			foneFinanceiro,
			emailFinanceiro,
			foneFiscal,
			emailFiscal,
			rota,
			segmento,
			formaPagamento,
			condicaoPagamento,
			valorPrimeiraCompra,
			obsVendedor,
			status,
			usuarioCadastro)
			VALUES
			(:cpf_cnpj,
			:tipo,
			:nome_fantasia,
			:razao_social,
			:cep,
			:logradouro,
			:numero,
			:complemento,
			:bairro,
			:municipio,
			:estado,
			:pais,
			:fonePrincipal,
			:emailXml,
			:foneComprador,
			:emailComprador,
			:nomeComprador,
			:foneFinanceiro,
			:emailFinanceiro,
			:foneFiscal,
			:emailFiscal,
			:rota,
			:segmento,
			:formaPagamento,
			:condicaoPagamento,
			:valorPrimeiraCompra,
			:obsVendedor,
			:status,
			:usuarioCadastro)";

		if($pessoa == "F"){
			$consulta = "insert into tb_cadastro_cliente
			(cpf_cnpj,
			dataNascimento,
			tipo,
			nome_fantasia,
			razao_social,
			cep,
			logradouro,
			numero,
			complemento,
			bairro,
			municipio,
			estado,
			pais,
			fonePrincipal,
			emailXml,
			foneComprador,
			emailComprador,
			nomeComprador,
			foneFinanceiro,
			emailFinanceiro,
			foneFiscal,
			emailFiscal,
			rota,
			segmento,
			formaPagamento,
			condicaoPagamento,
			valorPrimeiraCompra,
			obsVendedor,
			status,
			usuarioCadastro)
			VALUES
			(:cpf_cnpj,
			:nascimento,
			:tipo,
			:nome_fantasia,
			:razao_social,
			:cep,
			:logradouro,
			:numero,
			:complemento,
			:bairro,
			:municipio,
			:estado,
			:pais,
			:fonePrincipal,
			:emailXml,
			:foneComprador,
			:emailComprador,
			:nomeComprador,
			:foneFinanceiro,
			:emailFinanceiro,
			:foneFiscal,
			:emailFiscal,
			:rota,
			:segmento,
			:formaPagamento,
			:condicaoPagamento,
			:valorPrimeiraCompra,
			:obsVendedor,
			:status,
			:usuarioCadastro)";
		}

		try{
			$sql->query($consulta, array(
			":cpf_cnpj"=>$cnpjCpf,
			":nascimento"=>$dataNascimento,
			":tipo"=>$pessoa,
			":nome_fantasia"=>$fantasia,
			":razao_social"=>$razaoSocial,
			":cep"=>$cep,
			":logradouro"=>$logradouro,
			":numero"=>$numero,
			":complemento"=>$complemento,
			":bairro"=>$bairro,
			":municipio"=>$municipio,
			":estado"=>$estado,
			":pais"=>$pais,
			":fonePrincipal"=>$fonePrincipal,
			":emailXml"=>$emailXml,
			":foneComprador"=>$foneComprador,
			":emailComprador"=>$emailComprador,
			":nomeComprador"=>$nomeComprador,
			":foneFinanceiro"=>$foneFinanceiro,
			":emailFinanceiro"=>$emailFinanceiro,
			":foneFiscal"=>$foneFiscal,
			":emailFiscal"=>$emailFiscal,
			":rota"=>$rota,
			":segmento"=>$segmento,
			":formaPagamento"=>$formaPagto,
			":condicaoPagamento"=>$condPagto,
			":valorPrimeiraCompra"=>$valorPrimeiraCompra,
			":obsVendedor"=>$obs,
			":status"=>"P",
			":usuarioCadastro"=>$usuario
			));

			$resposta["retorno"] = "Cliente cadastrado com sucesso!";

		}
		catch(Exception $e)
		{
			$resposta["retorno"] = "Erro: $e";
			$resposta["status"] = "erro";
		}

		return $resposta;
	}


}
?>
