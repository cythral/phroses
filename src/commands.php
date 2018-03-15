<?php
/**
 * This file sets up CLI commands
 */

namespace Phroses;

use \PDO;
use \phyrex\Template;
use \listen\Events;
use \ZBateson\MailMimeParser\MailMimeParser;
use \LucidFrame\Console\ConsoleTable;
use \Phroses\Exceptions\ExitException;
use \Phroses\Commands\Command;

$commands = [];
/**
 * Turns application-wide maintenance mode off and on
 */
$commands[] = new class extends Command {
	public $name = "maintenance";
	
	public function execute(?string $mode = null) {
		$this->requireConfigFile();

		// manipulation
		if($mode) {

			// turn off/on maintenance for specific sites
			if(isset($this->flags["site"])) {

				if(!($site = Site::generate($this->flags["site"]->value))) {
					$this->error("Could not find that site");
				}

				$site->maintenance = [ "on" => 1, "off" => 0 ][$mode];
				println("Turned maintenance mode for ".$this->flags["site"]->value." {$mode}");
				throw new ExitException(0);

			}
			
			// turn off/on global maintenance mode
			Phroses::setMaintenance([ "on" => Phroses::MM_ON, "off" => Phroses::MM_OFF ][strtolower($mode)]);
			println("Turned global maintenance mode ".$mode);
			throw new ExitException(0);

		}

		// display
		$sites = $this->db->fetch("SELECT `url`, `maintenance` FROM `sites`", [], PDO::FETCH_ASSOC);
		
		$table = new ConsoleTable;
		$table->setHeaders([ "URL", "on/off"]);
		
		foreach($sites as $site) {
			$table->addRow([ $site["url"], [ 0 => "no", 1 => "yes" ][$site["maintenance"]] ]);
		}

		echo PHP_EOL;
		$table->display();
		println(PHP_EOL."Global maintenance mode is ".((file_exists(ROOT."/.maintenance")) ? "on" : "off").PHP_EOL);
	}
};

/**
 * Updates phroses' database schema
 */
$commands[] = new class extends Command {
	public $name = "update";

	public function execute() {
		$this->requireConfigFile();
		
		if(!$this->db->updateSchema()) {
			$this->error("An error occurred when trying to update the database schema");
		}
	}
};

/**
 * Processes an email that was piped to phroses.  There is
 * no default functionality for this, so a listen event is triggered instead.
 */
$commands[] = new class extends Command {
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
};

/**
 * This command is used during testing
 */
$commands[] = new class extends Command {
	public $name = "test";

	public function execute() {
		// will do more here later
		echo "TEST OK";
	}
};

/**
 * Resets the database
 */
$commands[] = new class extends Command {
	public $name = "reset";

	public function execute() {
		$this->requireConfigFile();
		$answer = strtolower($this->read("Are you sure?  Doing this will reset the database, all data will be lost (Y/n): "));
			
		if(in_array($answer, ['y', ''])) {
			if(!$this->db->installSchema()) {
				$this->error("There was an error resetting the database.");
			}

			println("The database has been successfully reset.");
		}
	}
};

/**
 * Restores the database from a backup.  A sql file should be piped to the script
 */
$commands[] = new class extends Command {
	public $name = "restore";

	public function execute() {
		$this->requireConfigFile();

		if($this->db->getHandle()->query(stream_get_contents($this->stream)) === false) {
			$this->error("There was an error restoring the database.");
		}

		println("Successfully restored the database from your backup.");
	}
};

/**
 * Displays the current version of Phroses
 */
$commands[] = new class extends Command {
	public $name = "version";

	public function execute() {
		$out = "Phroses ".VERSION;
		if(defined("Phroses\BUILD_TIMESTAMP")) $out .= " (built: ".date('F j, Y @ H:i:s e', BUILD_TIMESTAMP).")";
		$out .= " created by Cythral";

		println($out);
	}

};

return $commands; // return a list of commands for the listen event