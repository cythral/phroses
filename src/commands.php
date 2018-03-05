<?php
/**
 * This file sets up CLI commands
 */

namespace Phroses;

use \ZBateson\MailMimeParser\MailMimeParser;

/**
 * Turns application-wide maintenance mode off and on
 */
self::addCmd("maintenance", function($args, $flags) {
	if(!self::$configFileLoaded) {
		echo "Config file not present, please complete the installation of Phroses before proceeding.".PHP_EOL;
		exit(1);
	}

	if(isset($args["mode"])) {
		self::setMaintenance([ "on" => self::MM_ON, "off" => self::MM_OFF ][strtolower($args["mode"])]);
	}
});

/**
 * Updates phroses' database schema
 */
self::addCmd("update", function($args, $flags) {
	if(!self::$configFileLoaded) {
		echo "Config file not present, please complete the installation of Phroses before proceeding.".PHP_EOL;
		exit(1);
	}

	DB::update();
});

/**
 * Processes an email that was piped to phroses.  There is
 * no default functionality for this, so a listen event is triggered instead.
 */
self::addCmd("email", function($args, $flags) {
	$data = file_get_contents("php://stdin");
	$email = (new MailMimeParser())->parse((string) $data);

	Events::trigger("email", [
		$email->getHeaderValue('from'),
		$email->getHeaderValue('to'),
		$email->getHeaderValue('subject'),
		$email->getTextContent() || $email->getHtmlContent()
	]);
});

/**
 * This command is used during testing
 */
self::addCmd("test", function() {
	// will do more here later
	echo "TEST OK";
});

/**
 * Resets the database
 */
self::addCmd("reset", function() {
	if(!self::$configFileLoaded) {
		echo "Config file not present, please complete the installation of Phroses before proceeding.".PHP_EOL;
		exit(1);
	}

	$answer = strtolower(ask("Are you sure?  Doing this will reset the database, all data will be lost (Y/n): "));
		
	if(in_array($answer, ['y', ''])) {
		DB::unpreparedQuery(file_get_contents(SRC."/schema/install.sql"));
		echo "The database has been successfully reset.".PHP_EOL;
	}
});

/**
 * Restores the database from a backup.  A sql file should be piped to the script
 */
self::addCmd("restore", function() {
	if(!self::$configFileLoaded) {
		echo "Config file not present, please complete the installation of Phroses before proceeding.".PHP_EOL;
		exit(1);
	}

	if(!@DB::unpreparedQuery(file_get_contents("php://stdin"))) {
		echo "There was an error restoring the database.".PHP_EOL;
		exit(1);
	}

	echo "Successfully restored the database from your backup.".PHP_EOL;
});

self::addCmd("version", function() {
	echo VERSION.PHP_EOL;
});

return self::$commands; // return a list of commands for the listen event