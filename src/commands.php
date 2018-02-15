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
	if(isset($args["mode"])) {
		self::setMaintenance(mapValue(strtolower($args["mode"]), [ "on" => self::MM_ON, "off" => self::MM_OFF ]));
	}
});

/**
 * Updates phroses' database schema
 */
self::addCmd("update", function($args, $flags) {
	DB::Update();
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

return self::$cmds; // return a list of commands for the listen event