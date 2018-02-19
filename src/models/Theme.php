<?php 

namespace Phroses; 

use \DOMDocument;
use \Exception;
use \reqc; 
use \inix\Config as inix;
use \phyrex\Template as Template;

// exception handling
use \Phroses\Exceptions\ThemeException;
use const \Phroses\Exceptions\THEME_ERRORS;

/**
 * This class is a custom implementation of templates that provides
 * an easy way to display a consistent look across a website.  This reads
 * files from a theme directory created in /themes */ 

final class Theme extends Template {
	
    public $name;
	private $useconst = true;
	private $type;
	private $content;
	private $loader = self::LOADERS["FOLDER"];

	// deprecated
	private $types = ["redirect"];
	private $root;


	const LOADERS = [
		"FOLDER" => "\Phroses\Theme\Loaders\FolderLoader"
	];
	
	/**
	* Theme constructor.  Sets up the theme root, loads stylesheets,
	* scripts, and page types.
	*
	* @param string $name The name of the theme, there must be a theme directory with the same name.
	* @param string $type The page type to use when outputting the theme
	*/
	public function __construct(string $name, string $type, ?array $content = null) {
		$this->name = strtolower($name);
		$this->loader = new $this->loader($this->name);
		$this->content = $content ?? @SITE["PAGE"]["CONTENT"] ?? [];
        
		// make sure theme directory and page type exists
		if(!$this->loader->exists()) throw new ThemeException(THEME_ERRORS["NOT_FOUND"], $this->name);
		$this->loadAssets();
		
		if($type == "redirect") return;
		parent::__construct($this->loader->getType($type));
	}

	/**
	 * Load assets from the loader, automatically pushes everything in the css and js directories.
	 */
	private function loadAssets() {
		$this->push("scripts", [ "src" => "//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js", "attrs" => "defer"]);
		foreach($this->loader->getAssets("css") as $style) $this->push("stylesheets", [ "src" => "/{$style}" ]);
		foreach($this->loader->getAssets("js") as $script) $this->push("scripts", [ "src" => "/{$script}", "attrs" => "defer"]);
	}
	
	/**
	* Sets up sessiontools (on page buttons/screens) for page deletion, editing and more
	*/
	private function loadSessionTools() {
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
	public function hasAsset(string $asset) : bool {
		return $this->loader->hasAsset($asset);
	}
	
	/**
	* Reads an asset from the theme
	*
	* @param $asset Filename of the asset
	*/
	public function readAsset(string $asset) {
		$this->loader->readAsset($asset);
	}
	
	public function hasError(string $error) : bool {
		return $this->loader->hasError($error);
	}
	
	public function readError(string $error) {
        $this->setType("page", true);
		if($this->hasError($error)) {
			$this->main = $this->loader->getError($error);
			$this->title = "404 Not Found";
			echo $this;
		}
	}
	
	public function hasApi() : bool {
		return $this->loader->hasApi();
	}
	
	public function runApi() {
		return $this->loader->runApi();
	}
	
    public function hasType($type) {
        return in_array($type, $this->getTypes());
	}
	
	public function getTypes(): array {
		return array_merge($this->loader->getTypes(), ["redirect"]);
	}
    
    public function setType(string $type, bool $reload = false) {
        if(!$this->loader->hasType($type)) throw new ThemeException(THEME_ERRORS["NO_TEMPLATE"], $this->name, $type);
        $this->type = $type;
        if($reload) $this->tpl = $this->loader->getType($type);
    }
	
	public function getBody() {
        $this->useconst = false;
        $src = new DOMDocument;
		$dest = new DOMDocument;
		
        @$src->loadHTML((string) $this);
        $body = $src->getElementsByTagName('body')->item(0);
        if(!$body) return;
        
        foreach ($body->childNodes as $child) {
            $dest->appendChild($dest->importNode($child, true));
        }
        
        return $dest->saveHTML();
	}
    
    protected function process($loadSessionTools = true) {
        if($loadSessionTools) $this->loadSessionTools();
        parent::process();
    }
	
	static public function list() : array {
		return array_map(
			function($value) { return str_replace(INCLUDES["THEMES"]."/", "", $value); },
			array_filter(glob(INCLUDES["THEMES"]."/*", GLOB_ONLYDIR), function($val) { return $val != ""; }) // get directories in the themes folder
		);
	}

	public function setContent($content) {
		$this->content = $content;
	}

	public function getContentFields(string $tpl) {
		if($tpl == "redirect") return [ "destination" => "text" ];
		preg_match_all("/<{content(::((?!}>).)*)?}>/is", $this->loader->getType($tpl), $matches, PREG_SET_ORDER);
		
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
	if(file_exists("{$this->loader->getPath()}/{$file}.php")) include "{$this->loader->getPath()}/{$file}.php";
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
