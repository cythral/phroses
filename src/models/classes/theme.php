<?php
namespace Phroses;

/**
* This class is a custom implementation of templates that provides
* an easy way to display a consistent look across a website.  This reads
* files from a theme directory created in /themes
*/
final class Theme extends Template {
	private $root;
	private $types = ["redirect"];
	private $useconst = true;
	
	/**
	* Theme constructor.  Sets up the theme root, loads stylesheets,
	* scripts, and page types.
	* 
	* @param string $name The name of the theme, there must be a theme directory with the same name.
	* @param string $type The page type to use when outputting the theme 
	*/
	public function __construct(string $name, string $type) {
		$this->root = INCLUDES["THEMES"]."/".strtolower($name);
		
		// make sure theme directory and page type exists
		if(!file_exists($this->root)) throw new \Exception("Theme doesn't exist");
		if(!file_exists("{$this->root}/{$type}.tpl") && $type != "redirect") throw new \Exception("Theme template doesn't exist");
		
		// load stylesheets, scripts and page types
		$this->Push("scripts", [ "src" => "//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js", "attrs" => "defer"]);
		foreach(FileList("{$this->root}/assets/css") as $style) $this->Push("stylesheets", [ "src" => "/css/".pathinfo($style, PATHINFO_BASENAME)]);
		foreach(FileList("{$this->root}/assets/js") as $style) $this->Push("scripts", [ "src" => "/js/".pathinfo($style, PATHINFO_BASENAME)]);
		foreach(glob("{$this->root}/*.tpl") as $ctype) $this->types[] = pathinfo($ctype, PATHINFO_FILENAME);
		
		if($type != "redirect") {
			parent::__construct("{$this->root}/{$type}.tpl"); // redirects wont have any filters
			$this->SetupSessionTools($type);
		}
	}
	
	/**
	* Sets up sessiontools (on page buttons/screens) for page deletion, editing and more.
	* 
	* @param string $type the current page type
	*/
	private function SetupSessionTools(string $type) {
		if($_SESSION && SITE["RESPONSE"] == Phroses::RESPONSES["PAGE"][200] && REQ["METHOD"] == "GET") {
			$this->Push("stylesheets", [ "src" => "/phr-assets/css/main.css" ]);
			$this->Push("scripts", [ "src" => "//cdnjs.cloudflare.com/ajax/libs/ace/1.2.6/ace.js", "attrs" => "defer" ]);
			$this->Push("scripts", [ "src" => "/phr-assets/js/main.js", "attrs" => "defer" ]);
			
			$pst = new Template(INCLUDES["TPL"]."/pst.tpl");
			$pst->id = SITE["PAGE"]["ID"];
			$pst->title = SITE["PAGE"]["TITLE"];
			$pst->uri = REQ["URI"];
			
			ob_start();
			foreach($this->GetContentFields($type) as $key => $field) { 
				if($field == "editor")  { ?><div class="form_field content editor" id="<?= $type; ?>-main" data-id="<?= $key; ?>"><?= trim(htmlspecialchars(SITE["PAGE"]["CONTENT"][$key] ?? "")); ?></div><? }
				else if(in_array($field, ["text", "url"])) { ?><input id="<?= $key; ?>" placeholder="<?= $key; ?>" type="<?= $field; ?>" class="form_input form_field content" value="<?= SITE["PAGE"]["CONTENT"][$key] ?? ""; ?>"><? }	
			}
			$pst->fields = trim(ob_get_clean());
			foreach($this->GetTypes() as $type2) $pst->Push("types", ["type" => $type2, "checked" => ($type == $type2) ? "selected" : "" ]);
			
			$this->tpl = str_replace("<body>", '<body><div id="phr-container">', $this->tpl);
			$this->tpl = str_replace("</body>", "</div>".$pst."</body>", $this->tpl);
		}
	}
	
	/**
	* Checks to see if an asset exists in the theme
	*
	* @param $asset Filename of the asset to check for
	* @return bool whether or not the specified asset exists
	*/
	public function AssetExists(string $asset) : bool {
		return (file_exists("{$this->root}/assets{$asset}"));
	}
	
	/**
	* Reads an asset from the theme
	* 
	* @param $asset Filename of the asset
	*/
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
			$this->main = trim(ob_get_clean());
			$this->title = $title ?? "404 Not Found";
			echo $this;
		}
	}
	
	public function HasAPI() : bool {
		return (file_exists("{$this->root}/api.php"));
	}
	
	public function RunAPI() {
		if($this->HasAPI()) include "{$this->root}/api.php";
	}
	
	public function GetContentFields(string $tpl) {
		if($tpl == "redirect") return ["destination" => "text"];
		if(!file_exists("{$this->root}/{$tpl}.tpl")) return [];
		preg_match_all("/<\{content((:[a-zA-Z0-9_\-=<>\'\"@\/ ]+)+)?\}>/", file_get_contents("{$this->root}/{$tpl}.tpl"), $matches, PREG_SET_ORDER);
		$return = [];
		foreach($matches as $match) {
			$fields = explode(":", substr($match[1], 1));
			$return[$fields[0]] = $fields[1];
		} 
		return $return;
	}
	
	public function GetTypes() : array { 
		return $this-> types; 
	}
	
	public function GetBody() {
		$this->useconst = false;
		$matches = [];
		preg_match("/<body>(.*)?<\/body>/is", (String)$this, $matches);
		return $matches[1] ?? "";
	}
	
	static public function List() : array {
		$list = [];
		foreach(glob(INCLUDES["THEMES"] . '/*' , GLOB_ONLYDIR) as $dir) {
			if($dir != "") $list[] = str_replace(INCLUDES["THEMES"]."/", "", $dir);
		}
		return $list;
	}
}


/** ==========================================
* Populate default theme filters
 ================================================ */

Theme::$filters["include"] = function($file) {
	if(file_exists("{$this->root}/{$file}.php")) include "{$this->root}/{$file}.php";
};

Theme::$filters["content"] = function($key, $fieldtype) {
	$content = SITE["PAGE"]["CONTENT"];
	if(!$this->useconst) $content = json_decode($_REQUEST["content"] ?? "{}", true);
	
	if(array_key_exists($key, $content ?? [])) echo $content[$key];
	else if(array_key_exists($key, $this->vars)) echo $this->vars[$key];
};