<?php 
namespace Zware\DB;

use \Zware\Model\Chaves;

class MySql {	
	private $conn;

	public function __construct(){
		$this->conn = new \PDO(
			"mysql:dbname=".Chaves::DBNAME.";host=".Chaves::HOSTNAME,
			 Chaves::USERNAME, 
			 Chaves::PASSWORD,
			 array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, 
                \PDO::ATTR_PERSISTENT => false,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            )
		);

		
		
	}

	private function setParams($statement, $parameters = array())	{
		foreach ($parameters as $key => $value) {
			
			$this->bindParam($statement, $key, $value);
		}
	}

	private function bindParam($statement, $key, $value)	{
		$statement->bindParam($key, $value);
	}

	public function query($rawQuery, $params = array())	{
		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();
	}

	public function select($rawQuery, $params = array()):array
	{		
		$stmt = $this->conn->prepare($rawQuery);		

		$this->setParams($stmt, $params);

		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

}
