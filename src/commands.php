<?php

namespace Phroses;

use \ZBateson\MailMimeParser\MailMimeParser;

self::addCmd("maintenance", function($args, $flags) {
	if(isset($args["mode"])) {
		self::setMaintenance(mapValue(strtolower($args["mode"]), [ "on" => self::MM_ON, "off" => self::MM_OFF ]));
	}
});

self::addCmd("update", function($args, $flags) {
	DB::Update();
});

self::addCmd("email", function($args, $flags) {
	$data = file_get_contents("php://stdin");
	$m = (new MailMimeParser())->parse((string) $data);

	Events::trigger("email", [
		$m->getHeaderValue('from'),
		$m->getHeaderValue('to'),
		$m->getHeaderValue('subject'),
		$m->getTextContent() || $m->getHtmlContent()
	]);
});

return self::$cmds;