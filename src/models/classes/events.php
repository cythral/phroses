<?php

namespace Phroses;

abstract class Events {
	static private $listeners = [];
	
	static public function Listen(string $event, callable $listener) {
		if(!array_key_exists($event, self::$listeners)) self::$listeners[$event] = [];
		self::$listeners[$event][] = $listener;
	}
	
	static public function Trigger(string $event, array $args) : int {
		if(!array_key_exists($event, self::$listeners)) return 0;
		foreach(self::$listeners[$event] as $listener) $listener(...$args);
		return self::Count($event);
	}
	
	static public function Count(string $event) {
		return (array_key_exists($event, self::$listeners)) ? count(self::$listeners[$event]) : 0;
	}
}