<?php 

namespace Phroses; 

use \DOMDocument;
use \Exception;
use \reqc; 
use \inix\Config as inix;
use \phyrex\Template as Template;

/**
 * This class is a custom implementation of templates that provides
 * an easy way to display a consistent look across a website.  This reads
 * files from a theme directory created in /themes */ 

final class Theme extends Template {
	private $root;
    public $name;
	private $types = ["redirect"];
	private $useconst = true;
	private $type;
	private $content;
	
	/**
	* Theme constructor.  Sets up the theme root, loads stylesheets,
	* scripts, and page types.
	*
	* @param string $name The name of the theme, there must be a theme directory with the same name.
	* @param string $type The page type to use when outputting the theme
	*/
	public function __construct(string $name, string $type) {
		$this->root = INCLUDES["THEMES"]."/".strtolower($name);
		$this->setType($type);
		$this->name = $name;
		$this->content = SITE["PAGE"]["CONTENT"];
        
		// make sure theme directory and page type exists
		if(!file_exists($this->root)) throw new Exception("Theme doesn't exist");
		
		
		// load stylesheets, scripts and page types
		$this->push("scripts", [ "src" => "//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js", "attrs" => "defer"]);
		foreach(fileList("{$this->root}/assets/css") as $style) $this->push("stylesheets", [ "src" => "/css/".pathinfo($style, PATHINFO_BASENAME)]);
		foreach(fileList("{$this->root}/assets/js") as $style) $this->push("scripts", [ "src" => "/js/".pathinfo($style, PATHINFO_BASENAME), "attrs" => "defer"]);
		foreach(glob("{$this->root}/*.tpl") as $ctype) $this->types[] = pathinfo($ctype, PATHINFO_FILENAME);
		
		if($type != "redirect") {
			parent::__construct("{$this->root}/{$type}.tpl"); // redirects wont have any filters
		}
	}
	
	/**
	* Sets up sessiontools (on page buttons/screens) for page deletion, editing and more.
	*
	* @param string $type the current page type
	*/
	private function setupSessionTools() {
		if($_SESSION && reqc\METHOD == "GET" && in_array(Phroses::$response, [Phroses::RESPONSES["PAGE"][200], Phroses::RESPONSES["PAGE"][404], Phroses::RESPONSES["PAGE"][301] ])) {
            $this->push("stylesheets", [ "src" => "//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" ]);
			$this->push("stylesheets", [ "src" => SITE["ADMINURI"]."/assets/css/main.css" ]);
			$this->push("scripts", [ "src" => "//cdnjs.cloudflare.com/ajax/libs/ace/1.2.6/ace.js", "attrs" => "defer" ]);
			$this->push("scripts", [ "src" => SITE["ADMINURI"]."/assets/js/main".(inix::get("mode") == "production" ? ".min" : "").".js", "attrs" => 'defer data-adminuri="'.SITE["ADMINURI"].'" id="phroses-script"' ]);
		}
	}
	
	/**
	* Checks to see if an asset exists in the theme
	*
	* @param $asset Filename of the asset to check for
	* @return bool whether or not the specified asset exists
	*/
	public function assetExists(string $asset) : bool {
		return $asset == "/" ? false : file_exists("{$this->root}/assets{$asset}");
	}
	
	/**
	* Reads an asset from the theme
	*
	* @param $asset Filename of the asset
	*/
	public function assetRead(string $asset) {
		if($this->assetExists($asset)) readfileCached("{$this->root}/assets{$asset}");
	}
	
	public function ErrorExists(string $error) : bool {
		return (file_exists("{$this->root}/errors/{$error}.php"));
	}
	
	public function errorRead(string $error) {
        $this->setType("page", true);
		if($this->errorExists($error)) {
			ob_start();
			include "{$this->root}/errors/{$error}.php";
			$this->main = trim(ob_get_clean());
			$this->title = $title ?? "404 Not Found";
			echo $this;
		}
	}
	
	public function hasAPI() : bool {
		return (file_exists("{$this->root}/api.php"));
	}
	
	public function runAPI() {
		if($this->hasAPI()) include "{$this->root}/api.php";
	}
	
    public function hasType($type) {
        return file_exists("{$this->root}/{$type}.tpl") || $type == "redirect";
	}
	
	public function getTypes() : array {
		return $this->types;
	}
    
    public function setType(string $type, bool $reload = false) {
        if(!file_exists("{$this->root}/{$type}.tpl") && $type != "redirect") throw new Exception("Theme template doesn't exist");
        $this->type = $type;
        if($reload) $this->tpl = file_get_contents("{$this->root}/{$this->type}.tpl");
    }
	
	public function getBody() {
        $this->useconst = false;
        $src = new DOMDocument;
		$dest = new DOMDocument;
		
        @$src->loadHTML((string) $this);
        $body = $src->getElementsByTagName('body')->item(0);
        if(!$body) return;
        
        foreach ($body->childNodes as $child){
            $dest->appendChild($dest->importNode($child, true));
        }
        
        return $dest->saveHTML();
	}
    
    protected function process($parseSession = true) {
        if($parseSession) $this->setupSessionTools();
        parent::process();
    }
	
	static public function list() : array {
		$list = [];
		foreach(glob(INCLUDES["THEMES"] . '/*' , GLOB_ONLYDIR) as $dir) {
			if($dir != "") $list[] = str_replace(INCLUDES["THEMES"]."/", "", $dir);
		}
		return $list;
	}

	public function setContent($content) {
		$this->content = $content;
	}

	public function getContentFields(string $tpl) {
		if($tpl == "redirect") return ["destination" => "text"];
		if(!file_exists("{$this->root}/{$tpl}.tpl")) return [];
		preg_match_all("/<{content(::((?!}>).)*)?}>/is", file_get_contents("{$this->root}/{$tpl}.tpl"), $matches, PREG_SET_ORDER);
		$return = [];
		foreach($matches as $match) {
			$fields = explode("::", substr($match[1], 2));
			$return[$fields[0]] = $fields[1];
		} 
		return $return;
	}

	public function getEditorFields($type = null) {
		if(!$type) $type = $this->type;

		ob_start();
		foreach($this->getContentFields($type) as $key => $field) {
			if($field == "editor")  { ?><div class="form_field content editor" id="<?= $type ?>-main" data-id="<?= $key; ?>"></div><? }
			else if(in_array($field, ["text", "url"])) { ?><input id="<?= $key; ?>" placeholder="<?= $key; ?>" type="<?= $field; ?>" class="form_input form_field content" value=""><? }
		}

		return trim(ob_get_clean());
	}
}



Theme::$filters["include"] = function($file) {
	if(file_exists("{$this->root}/{$file}.php")) include "{$this->root}/{$file}.php";
};

Theme::$filters["content"] = function($key, $fieldtype) {
	$content = $this->content;
	if(!$this->useconst) $content = json_decode($_REQUEST["content"] ?? "{}", true);
	
	if(array_key_exists($key, $content ?? [])) echo $content[$key];
	else if(array_key_exists($key, $this->vars)) echo $this->vars[$key];
};

Theme::$filters["typelist"] = function($type, $field, $orderby = "id", $ordertype = "ASC") {
	if(!in_array(strtoupper($ordertype), ["ASC", "DESC"])) return;
	
    $tlist = DB::query("SELECT * FROM `pages` WHERE `siteID`=? AND `type`=? ORDER BY `{$orderby}` {$ordertype}", [ SITE["ID"], $type ]);
    foreach($tlist as $page) {
        $out = $field;
        
        $callback = function($matches) {
            $content = json_decode($this->content);
            return $this->{$matches[1]} ?? $content->{$matches[1]} ?? "";
        };
        $out = preg_replace_callback("/@([a-zA-Z0-9]+)/", $callback->bindTo($page), $out);
        echo $out;
    }
};

Theme::$filters["site"] = function($var) {
    echo SITE[strtoupper($var)] ?? "";
};
