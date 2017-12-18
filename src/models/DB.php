<?php
namespace Phroses;

use \inix\Config as inix;
use \PDO;

abstract class DB {
	static private $db;
	static private $setup = false;
	static public $version;
	static public $schemaVersion;
	
	static public function Setup() {
		if(self::$setup) return; // only run once
		$conf = inix::get("database");
		self::$db = new PDO("mysql:host=".$conf["host"].";dbname=".$conf["name"], $conf["user"], $conf["password"]);
		$versions = self::$db->query("select version() AS `dbver`, `value` AS `sver` FROM `options` WHERE `key`='schemaver'")->fetchAll(PDO::FETCH_OBJ)[0];
		self::$version = $versions->dbver;
		self::$schemaVersion = $versions->sver;
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
	
	static public function Update() {
		if(self::$schemaVersion >= SCHEMAVER) return;
		
		while(self::$schemaVersion < SCHEMAVER) {
			self::UnpreparedQuery(file_get_contents(SRC."/schema/update-".++self::$schemaVersion.".sql"));
		}
	}
}

DB::Setup();
