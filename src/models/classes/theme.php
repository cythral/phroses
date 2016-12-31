<?php

namespace Phroses;

final class Theme extends Template {
	private $root;
	private $types = [];
	
	public function __construct(string $name, string $type) {
		$this->root = INCLUDES["THEMES"]."/".strtolower($name);		
		if(!file_exists($this->root)) throw new \Exception("Theme doesn't exist");
		if(!file_exists("{$this->root}/{$type}.tpl")) throw new \Exception("Theme template doesn't exist");
		
		foreach(FileList("{$this->root}/assets/css") as $style) $this->Push("stylesheets", [ "src" => "/css/".pathinfo($style, PATHINFO_BASENAME)]);
		foreach(FileList("{$this->root}/assets/js") as $style) $this->Push("scripts", [ "src" => "/js/".pathinfo($style, PATHINFO_BASENAME)]);
		foreach(glob("{$this->root}/*.tpl") as $ctype) $this->types[] = pathinfo($ctype, PATHINFO_FILENAME);
		
		parent::__construct("{$this->root}/{$type}.tpl");
	}
	
	public function AssetExists(string $asset) : bool {
		return (file_exists("{$this->root}/assets{$asset}"));
	}
	
	public function AssetRead(string $asset) {
		if($this->AssetExists($asset)) readfile("{$this->root}/assets{$asset}");
	}
	
	public function ErrorExists(string $error) : bool {
		return (file_exists("{$this->root}/errors/{$error}.php"));
	}
	
	public function ErrorRead(string $error) {
		if($this->ErrorExists($error)) {
			ob_start();
			include "{$this->root}/errors/{$error}.php";
			$this->title = $title ?? "404 Not Found";
			$this->content = trim(ob_get_clean());
			echo $this;
			ob_end_flush();
		}
	}
	
	public function HasAPI() : bool {
		return (file_exists("{$this->root}/api.php"));
	}
	
	public function RunAPI() {
		if($this->HasAPI()) include "{$this->root}/api.php";
	}
	
	public function GetContentFields(string $tpl) {
		if(!file_exists("{$this->root}/{$tpl}.tpl")) return [];
		preg_match_all("/<\{content((:[a-zA-Z0-9_\-=<>\'\"@\/ ]+)+)?\}>/", file_get_contents("{$this->root}/{$tpl}.tpl"), $matches, PREG_SET_ORDER);
		$return = [];
		foreach($matches as $match) {
			$fields = explode(":", substr($match[1], 1));
			$return[$fields[0]] = $fields[1];
		} 
		return $return;
	}
	
	public function GetTypes() : array { return $this->types; }
}


Theme::$filters["include"] = function($file) {
	if(file_exists("{$this->root}/{$file}.php")) include "{$this->root}/{$file}.php";
};

Theme::$filters["content"] = function($key, $fieldtype) {
	if(array_key_exists($key, SITE["PAGE"]["CONTENT"] ?? [])) echo SITE["PAGE"]["CONTENT"][$key];
	else if(array_key_exists($key, $this->vars)) echo $this->vars[$key];
};