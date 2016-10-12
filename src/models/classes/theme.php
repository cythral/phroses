<?php

namespace Phroses;

final class Theme extends Template {
	private $root;
	
	public function __construct(string $name) {
		$this->root = INCLUDES["THEMES"]."/".strtolower($name);
		if(!file_exists($this->root)) throw new \Exception("Theme doesn't exist");
		if(!file_exists("{$this->root}/page.tpl")) throw new \Exception("Theme template doesn't exist");
		
		// Theme Includes Shortcut Filter
		$this->filters["theme"] = function($file) {
			if(file_exists("{$this->root}/{$file}.php")) include "{$this->root}/{$file}.php";
		};
		
		parent::__construct("{$this->root}/page.tpl");
	}
}