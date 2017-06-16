<?php
namespace Phroses;
use \PDO;

abstract class DB {
	static private $db;
	static private $setup = false;
	static public $version;
	
	static public function Setup() {
		if(self::$setup) return; // only run once
		$conf = Config::Get("database");
		self::$db = new PDO("mysql:host=".$conf["host"].";dbname=".$conf["name"], $conf["user"], $conf["password"]);
		self::$version = self::$db->query("select version()")->fetchColumn();
		self::$setup = true;
	}
	
	static public function Query(string $query, array $values, int $fetchStyle = PDO::FETCH_OBJ) {
		$q = self::$db->prepare($query);
		foreach($values as $key => $val) $q->bindValue($key + 1, $val);
		$q->execute();
		
		return $q->fetchAll($fetchStyle);
	}
	
	static public function UnpreparedQuery(string $query) {
		self::$db->query($query);
	}
	
	static public function Error() {
		return self::$db->errorInfo();
	}
	
	static public function LastID() {
		return self::$db->lastInsertId();
	}
}

DB::Setup();
