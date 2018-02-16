<?php
/**
 * Phroses' database handler -- this is an abstract class
 * and has only static methods to keep only one connection handle
 * open per script run.
 */

namespace Phroses;

use \inix\Config as inix;
use \PDO;

abstract class DB {

	static private $db;
	static private $setup = false;
	static public $version;
	static public $schemaVersion;
	
	static public function setup() {
		if(self::$setup) return; // only run once

		$conf = inix::get("database");
		self::$db = new PDO("mysql:host=".$conf["host"].";dbname=".$conf["name"], $conf["user"], $conf["password"]);

		$versions = self::$db->query("select version() AS `dbver`, `value` AS `sver` FROM `options` WHERE `key`='schemaver'")->fetchAll(PDO::FETCH_OBJ)[0];
		self::$version = $versions->dbver;
		self::$schemaVersion = $versions->sver;
		self::$setup = true;
	}
	
	static public function query(string $query, array $values, int $fetchStyle = PDO::FETCH_OBJ) {
		$q = self::$db->prepare($query);
		foreach($values as $key => $val) $q->bindValue($key + 1, $val);
		$q->execute();
		
		return $q->fetchAll($fetchStyle);
	}
	
	static public function unpreparedQuery(string $query) {
		self::$db->query($query);
	}
	
	static public function error() {
		return self::$db->errorInfo();
	}
	
	static public function lastID() {
		return self::$db->lastInsertId();
	}
	
	static public function update() {
		if(self::$schemaVersion >= SCHEMAVER) return;
		
		while(self::$schemaVersion < SCHEMAVER) {
			self::unpreparedQuery(file_get_contents(SRC."/schema/update-".++self::$schemaVersion.".sql"));
		}
	}
	
}

DB::setup(); // setup immediately
