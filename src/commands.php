<?php
/**
 * This file sets up CLI commands
 */

namespace Phroses;

use \listen\Events;
use \ZBateson\MailMimeParser\MailMimeParser;

/**
 * Turns application-wide maintenance mode off and on
 */
self::addCmd(new class extends Command {
	public $name = "maintenance";
	
	public function execute(array $args, array $flags) {
		$this->requireConfigFile();

		if(isset($args["mode"])) {
			Phroses::setMaintenance([ "on" => self::MM_ON, "off" => self::MM_OFF ][strtolower($args["mode"])]);
		}
	}
});

/**
 * Updates phroses' database schema
 */
self::addCmd(new class extends Command {
	public $name = "update";

	public function execute(array $args, array $flags) {
		$this->requireConfigFile();
		DB::update();
	}
});

/**
 * Processes an email that was piped to phroses.  There is
 * no default functionality for this, so a listen event is triggered instead.
 */
self::addCmd(new class extends Command {
	public $name = "email";

	public function execute(array $args, array $flags) {
		$data = stream_get_contents($this->stream);
		$email = (new MailMimeParser())->parse((string) $data);

		Events::trigger("email", [
			$email->getHeaderValue('from'),
			$email->getHeaderValue('to'),
			$email->getHeaderValue('subject'),
			$email->getTextContent() ?? $email->getHtmlContent()
		]);
	}
});

/**
 * This command is used during testing
 */
self::addCmd(new class extends Command {
	public $name = "test";

	public function execute(array $args, array $flags) {
		// will do more here later
		echo "TEST OK";
	}
});

/**
 * Resets the database
 */
self::addCmd(new class extends Command {
	public $name = "reset";

	public function execute(array $args, array $flags) {
		$this->requireConfigFile();
		$answer = strtolower($this->read("Are you sure?  Doing this will reset the database, all data will be lost (Y/n): "));
			
		if(in_array($answer, ['y', ''])) {
			$tpl = new Template(SRC."/schema/install.sql");
			$tpl->schemaver = SCHEMAVER;

			DB::unpreparedQuery($tpl);
			println("The database has been successfully reset.");
		}
	}
});

/**
 * Restores the database from a backup.  A sql file should be piped to the script
 */
self::addCmd(new class extends Command {
	public $name = "restore";

	public function execute(array $args, array $flags) {
		$this->requireConfigFile();

		if(!@DB::unpreparedQuery(stream_get_contents($this->stream))) {
			$this->error("There was an error restoring the database.");
		}

		println("Successfully restored the database from your backup.");
	}
});

/**
 * Displays the current version of Phroses
 */
self::addCmd(new class extends Command {
	public $name = "version";
	public function execute(array $args, array $flags) {
		$out = "Phroses ".VERSION;
		if(defined("Phroses\BUILD_TIMESTAMP")) $out .= " (built: ".date('F j, Y @ H:i:s e', BUILD_TIMESTAMP).")";
		$out .= " created by Cythral";

		println($out);
	}
});

return self::$commands; // return a list of commands for the listen event