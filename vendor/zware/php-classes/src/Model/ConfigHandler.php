<?php
namespace Zware\Model;

use \Zware\DB\MySql;
use \Zware\Mailer;
use \Zware\Model;

class ConfigHandler extends Model
{

	public function Default_Empresa(){
		return [
			'permitir_ticket_direto_usuario' => false,
			'teste2'=>"oi",
			'teste3'=>15
		];
	}

	public static function SetConfigsEmpresa($idEmpresa, $usuario, $config = []){
		$valores = ConfigHandler::Default_Empresa();

		if(count($config) > 0){
			foreach($config as $key=>$value){
				$valores[$key] = $value;
			}
		}

		$sql = new MySql();
		$dados = $json = $sql->select("CALL sp_salva_configs(:EMPRESA, :USUARIO, :CONFIG);", array(
			":EMPRESA"=>$idEmpresa,
			":USUARIO"=>$usuario,
			":CONFIG"=>json_encode($valores)
		));
	}

	public static function GetOneConfig($idEmpresa, $nomeConfig){
		$configs = ConfigHandler::CarregaConfigsEmpresa($idEmpresa);

		if (array_key_exists($nomeConfig,$configs))
		{
			return $configs[$nomeConfig];
		}else{
			return '';
		}

	}

	public function CarregaConfigsEmpresa($idEmpresa){
		$default = ConfigHandler::Default_Empresa();

		$sql = new MySql();
		$json = $sql->select("select configs from tb_configs_empresas where idempresa = :EMPRESA", array(
			":EMPRESA"=>$idEmpresa
		));

		$configs = json_decode($json[0]['configs'], true);


		if(count($configs) > 0){
			foreach($configs as $key=>$value){
				$default[$key] = $value;
			}
		}

		return $default;

	}



}
?>
