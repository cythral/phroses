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
	
	/**
	 * Sets up the database
	 */
	static public function setup() {
		if(self::$setup) return; // only run once

		$conf = inix::get("database");
		self::$db = new PDO("mysql:host=".$conf["host"].";dbname=".$conf["name"], $conf["user"], $conf["password"]);

		$versions = self::$db->query("select version() AS `dbver`, `value` AS `sver` FROM `options` WHERE `key`='schemaver'")->fetchAll(PDO::FETCH_OBJ)[0];
		self::$version = $versions->dbver;
		self::$schemaVersion = $versions->sver;
		self::$setup = true;
	}
	
	/**
	 * Performs a query against the database
	 * 
	 * @param string $query the sql query to execute
	 * @param array $values an array of values to bind to prepared parameters
	 * @param int $fetchStyle the style to fetch results in (PDO::FETCH_OBJ, PDO::FETCH_ARRAY, etc.)
	 * @return array an array of rows returned by the query
	 */
	static public function query(string $query, array $values, int $fetchStyle = PDO::FETCH_OBJ): array {
		$q = self::$db->prepare($query);
		foreach($values as $key => $val) $q->bindValue($key + 1, $val);
		$q->execute();
		
		return $q->fetchAll($fetchStyle);
	}
	
	/**
	 * Performs an unprepared query against the database
	 * 
	 * @param string $query the sql query to execute
	 * @return mixed a PDOStatement object or false on failure
	 */
	static public function unpreparedQuery(string $query) {
		return self::$db->query($query);
	}
	
	/**
	 * Gets the last error from sql performed directly on the 
	 * database handle.  
	 * 
	 * @return array an array of error information
	 */
	static public function error() {
		return self::$db->errorInfo();
	}
	
	/**
	 * Gets the last inserted id from the database
	 * 
	 * @return mixed the last inserted id
	 */
	static public function lastID() {
		return self::$db->lastInsertId();
	}
	

	/**
	 * Updates the database schema if it is out of date
	 */
	static public function update() {
		if(self::$schemaVersion >= SCHEMAVER) return;
		
		while(self::$schemaVersion < SCHEMAVER) {
			self::unpreparedQuery(file_get_contents(SRC."/schema/update-".++self::$schemaVersion.".sql"));
		}
	}

}

DB::setup(); // setup immediately
