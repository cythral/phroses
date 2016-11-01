<?php
namespace Phroses;
use \PDO;

abstract class DB {
	static private $db;
	static private $setup = false;

	static public function Setup() {
		if(self::$setup) return; // only run once
		self::$db = new PDO("mysql:host=".CONF["database"]["host"].";dbname=".CONF["database"]["name"], CONF["database"]["user"], CONF["database"]["password"]);
	}
	
	static public function Query(string $query, array $values, int $fetchStyle = PDO::FETCH_OBJ) {
		$q = self::$db->prepare($query);
		foreach($values as $key => $val) $q->bindValue($key + 1, $val);
		$q->execute();
		return $q->fetchAll($fetchStyle);
	}
	
	static public function Error() {
		return self::$db->errorInfo();
	}
}

DB::Setup();
