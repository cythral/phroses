<?php
/**
 * This file sets up CLI commands
 */

namespace Phroses;

use \PDO;
use \phyrex\Template;
use \listen\Events;
use \ZBateson\MailMimeParser\MailMimeParser;
use \Phroses\Exceptions\ExitException;

/**
 * Turns application-wide maintenance mode off and on
 */
self::addCmd(new class extends Command {
	public $name = "maintenance";
	
	public function execute(?string $mode = null) {
		$this->requireConfigFile();

		if($mode) {

			if(isset($this->flags["site"])) {

				if(!($site = Site::generate($this->flags["site"]))) {
					$this->error("Could not find that site");
				}

				$site->maintenance = [ "on" => 1, "off" => 0 ][$mode];
				println("Turned maintenance mode for ".$this->flags["site"]." {$mode}");
				throw new ExitException(0);

			} else {
				
				Phroses::setMaintenance([ "on" => Phroses::MM_ON, "off" => Phroses::MM_OFF ][strtolower($mode)]);
				println("Turned global maintenance mode ".$mode);
				throw new ExitException(0);
			}

		}

		/** DISPLAY */
		$sites = DB::query("SELECT `url`, `maintenance` FROM `sites`", [], PDO::FETCH_ASSOC);
		
		$minwidthURL = max(array_map(function($val) { return strlen($val); }, array_column($sites, "url")));
		$minwidthMaintenance = 3;

		echo PHP_EOL;
		$mask = "| %-{$minwidthURL}s | %{$minwidthMaintenance}s |\n";
		$top = sprintf($mask, "URL", "on");
		
		for($i = 1; $i < strlen($top); $i++) echo "-";
		echo PHP_EOL.$top;
		for($i = 1; $i < strlen($top); $i++) echo "-";
		echo PHP_EOL;
		
		foreach($sites as $site) {
			printf($mask, $site["url"], [ 0 => "no", 1 => "yes" ][$site["maintenance"]]);
		}

		for($i = 1; $i < strlen($top); $i++) { echo "-"; }
		echo PHP_EOL;

		println(PHP_EOL."Global maintenance mode is ".((file_exists(ROOT."/.maintenance")) ? "on" : "off").PHP_EOL);
	}
});

/**
 * Updates phroses' database schema
 */
self::addCmd(new class extends Command {
	public $name = "update";

	public function execute() {
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

	public function execute() {
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

	public function execute() {
		// will do more here later
		echo "TEST OK";
	}
});

/**
 * Resets the database
 */
self::addCmd(new class extends Command {
	public $name = "reset";

	public function execute() {
		$this->requireConfigFile();
		$answer = strtolower($this->read("Are you sure?  Doing this will reset the database, all data will be lost (Y/n): "));
			
		if(in_array($answer, ['y', ''])) {
			$tpl = new Template(SRC."/schema/install.sql");
			$tpl->schemaver = SCHEMAVER;

			if(!@DB::unpreparedQuery($tpl)) {
				$this->error("There was an error resetting the database.");
			}

			println("The database has been successfully reset.");
		}
	}
});

/**
 * Restores the database from a backup.  A sql file should be piped to the script
 */
self::addCmd(new class extends Command {
	public $name = "restore";

	public function execute() {
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

	public function execute() {
		$out = "Phroses ".VERSION;
		if(defined("Phroses\BUILD_TIMESTAMP")) $out .= " (built: ".date('F j, Y @ H:i:s e', BUILD_TIMESTAMP).")";
		$out .= " created by Cythral";

		println($out);
	}

});

return self::$commands; // return a list of commands for the listen event