<?php

namespace Phroses;

self::addCmd("maintenance", function($args, $flags) {
	if(isset($args["mode"])) self::setMaintenance(mapValue(strtolower($args["mode"]), [ "on" => self::ON, "off" => self::OFF ]));
});

self::addCmd("update", function($args, $flags) {
	DB::Update();
});

self::addCmd("email", function($args, $flags) {
	self::handleEmail();
});

return self::$cmds;