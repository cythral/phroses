<?php 

/**
 * This class is a custom implementation of templates that provides
 * an easy way to display a consistent look across a website.  This reads
 * files from a theme directory created in /themes.  Themes can have multiple different
 * template "types" to use when parsing the theme.  
 */ 

namespace Phroses\Theme; 

use \DOMDocument;
use \Exception;
use \reqc; 
use \inix\Config as inix;
use \phyrex\Template as Template;
use \Phroses\Phroses;
use const \Phroses\{ SITE, INCLUDES };
use const \reqc\{ VARS };
use function \Phroses\{ getTagContents };

// exception handling
use \Phroses\Exceptions\ThemeException;
use const \Phroses\Exceptions\THEME_ERRORS;

final class Theme extends Template {
	
	/** @var string the name of the theme */
	private $name;
	
	/** @var string active theme type in use */
	private $type;

	/** @var array an array of content fields */
	private $content;

	/** @var bool whether or not to use the reqc VARS in the content filter */
	public $useReqcVars = false;

	/** @var string|Loader the theme's internal loader, loads theme metadata, assets, etc. */
	private $loader = self::LOADERS["FOLDER"]; 

	/** @var array loader classes to use for the theme's internal loader */
	const LOADERS = [
		"FOLDER" => "\Phroses\Theme\Loaders\FolderLoader",
		"DUMMY" => "\Phroses\Theme\Loaders\DummyLoader"
	];

	/** @var array field templates for generating editor fields */
	const FIELDS = [
		"EDITOR" => '<div class="form_field content editor" id="<{var::type}>-main" data-id="<{var::key}>"><{var::value}></div>',
		"TEXT" => '<input id="<{var::key}>" placeholder="<{var::key}>" type="text" class="form_input form_field content" value="<{var::value}>">',
		"URL" => '<input id="<{var::key}>" placeholder="<{var::key}>" type="url" class="form_input form_field content" value="<{var::value}>">'
	];

	const DEFAULT = "bloom";
	
	/**
	* Theme constructor.  Sets up the theme root, loads stylesheets,
	* scripts, and page types.
	*
	* @param string $name The name of the theme, there must be a theme directory with the same name.
	* @param string $type The page type to use when outputting the theme
	*/
	public function __construct(string $name, string $type = "page", ?array $content = null, ?string $loader = null) {
		parent::__construct("");
		$this->name = strtolower($name);
		$this->content = $content ?? Phroses::$page->content ?? [];
		$this->setupLoader($loader);
		
        
		// make sure theme directory and page type exists
		if(!$this->loader->exists()) throw new ThemeException(THEME_ERRORS["NOT_FOUND"], $this->name);
		$this->setType($type, true);
		$this->loadAssets();
	}

	/**
	 * Sets up the theme loader
	 * 
	 * @param string $loader the loader to use.  If null, the default one is used (FolderLoader)
	 */
	public function setupLoader(?string $loader = null): void {
		if($loader) $this->loader = $loader;
		$this->loader = new $this->loader($this->name);
	}

	/**
	 * Getter for the loader property.
	 * 
	 * @param Loader the currently used loader
	 */
	public function getLoader(): Loader {
		return $this->loader;
	}


	/**
	 * Load assets from the loader, automatically pushes everything in the css and js directories.
	 */
	private function loadAssets(): void {
		foreach($this->loader->getAssets("css") as $style) $this->push("stylesheets", [ "src" => "/{$style}" ]);
		foreach($this->loader->getAssets("js") as $script) $this->push("scripts", [ "src" => "/{$script}", "attrs" => "defer"]);
	}
	
	/**
	* Sets up sessiontools (on page buttons/screens) for page deletion, editing and more
	*/
	private function loadSessionTools(): void {
		if(isset($_SESSION['live']) && reqc\METHOD == "GET" && in_array(Phroses::$response, [ Phroses::RESPONSES["PAGE"][200], Phroses::RESPONSES["PAGE"][404], Phroses::RESPONSES["PAGE"][301] ])) {
            $this->push("stylesheets", [ "src" => "//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" ]);
			$this->push("stylesheets", [ "src" => Phroses::$site->adminURI."/assets/css/main.css" ]);
			$this->push("scripts", [ "src" => "//cdnjs.cloudflare.com/ajax/libs/ace/1.2.6/ace.js", "attrs" => "defer" ]);
			$this->push("scripts", [ "src" => Phroses::$site->adminURI."/assets/js/phroses.min.js", "attrs" => 'defer data-adminuri="'.Phroses::$site->adminURI.'" id="phroses-script"' ]);
		}
	}
	
	/**
	 * Checks to see if the theme has a certain type.
	 *
	 * @param string $type the type to check for
	 * @return bool true if the theme has the specified type, false if not
	 */
	public function hasType(string $type): bool {
        return in_array($type, $this->getTypes());
	}
	
	/**
	 * Gets a list of types that the theme has
	 *
	 * @return array a list of types that the theme has
	 */
	public function getTypes(): array {
		return array_merge($this->loader->getTypes(), ["redirect"]);
	}
    
	/**
	 * Sets the active type to a different one
	 * if the theme has it. Reloads the internal template if $reload = true
	 *
	 * @param string $type the new type to use
	 * @param bool $reload if true, it will reload the internal template
	 * @return string the type that the theme was set to use.
	 */
    public function setType(string $type, bool $reload = false): string {
		if($type == "redirect") return $this->type = $type;
        if(!$this->loader->hasType($type)) throw new ThemeException(THEME_ERRORS["NO_TEMPLATE"], $this->name, $type);
        if($reload) $this->tpl = $this->loader->getType($type);
		return $this->type = $type;
    }
	
	/**
	* Checks to see if an asset exists in the theme
	*
	* @param string $asset Filename of the asset to check for
	* @return bool whether or not the specified asset exists
	*/
	public function hasAsset(string $asset) : bool {
		return $this->loader->hasAsset($asset);
	}
	
	/**
	* Reads an asset from the theme
	*
	* @param string $asset Filename of the asset
	*/
	public function readAsset(string $asset): void {
		$this->loader->readAsset($asset);
	}
	
	/**
	 * Checks to see if an error exists in the theme
	 *
	 * @param string $error the error to check for
	 * @return bool whether or not the specified error exists
	 */
	public function hasError(string $error) : bool {
		return $this->loader->hasError($error);
	}
	
	/**
	 * Reads an error from the theme
	 *
	 * @param string $error the error to read
	 */
	public function readError(string $error): void {
		if($this->hasError($error)) {
			$this->setType("page", true);
			$this->main = $this->loader->getError($error);
			$this->title = "404 Not Found";
			echo $this;
		}
	}
	
	/**
	 * Checks to see if the theme has an API
	 *
	 * @return bool true if the theme has an api, false if not
	 */
	public function hasApi(): bool {
		return $this->loader->hasApi();
	}
	
	/**
	 * Runs the theme API if it has one
	 *
	 * @return mixed whatever the API returns
	 */
	public function runApi() {
		return $this->loader->runApi();
	}
	
	/**
	 * Sets the content variable (used in the content filter)
	 *
	 * @param array $content the content variable to set to
	 * @return the content variable that the theme set
	 */
	public function setContent(array $content): array {
		return $this->content = $content;
	}
	
	/**
	 * Gets content inside the <body> tag from the parsed theme
	 *
	 * @return string the content inside the parsed theme's <body> tag
	 */
	public function getBody(): ?string {
		return getTagContents((string) $this, "body");
	}
	
	/**
	 * Gets the content fields/tags from a theme type.
	 *
	 * @param string $tpl (optional) the template to get content fields from.
	 * @return array an array of content fields
	 */
	public function getContentFields(?string $tpl = null): array {
		if($tpl == "redirect") return [ "destination" => "text" ];
		preg_match_all("/<{content(::((?!}>).)*)?}>/is", $this->loader->getType($tpl ?? $this->type), $matches, PREG_SET_ORDER);
		
		$return = [];
		foreach($matches as $match) {
			$fields = explode("::", substr($match[1], 2));
			$return[$fields[0]] = $fields[1];
		} 
		return $return;
	}
	
	/**
	 * Generate editor fields.  (These are passed to the session tools for editing)
	 *
	 * @param string $type the theme type to get editor fields from
	 * @param array $content an array of content to populate the editor fields with
	 * @return string the editor fields
	 */
	public function getEditorFields(?string $type = null, array $content = []): string {
		ob_start();

		foreach($this->getContentFields($type ?? $this->type) as $key => $field) {
			$tpl = new Template(self::FIELDS[strtoupper($field)]);
			$tpl->type = $type ?? $this->type;
			$tpl->key = $key;
			$tpl->value = trim(htmlspecialchars($content[$key] ?? ""));
			echo $tpl;
		}

		return trim(ob_get_clean());
	}
	
	/**
	 * Process filters, gets called on __toString
	 * 
	 * @param bool $loadSessionTools whether or not to load the session tools
	 */
	protected function process(bool $loadSessionTools = true) {
        if($loadSessionTools) $this->loadSessionTools();
        parent::process();
	}
	
	/**
	 * Getter for the tpl property
	 * 
	 * @return string the tpl property
	 */
	public function getTpl() {
		return $this->tpl;
	}

	/**
	 * Getter for the name property
	 * 
	 * @return string the theme name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Getter for the theme's path
	 * 
	 * @return string the path returned by the theme's loader
	 */
	public function getPath() {
		return $this->loader->getPath();
	}
	
	/**
	 * Returns a list of themes that are available. 
	 *
	 * @return array a list of theme names
	 */
	static public function list() : array {
		return array_merge(...array_map(function($val) { return $val::list(); }, array_values(self::LOADERS)));
	}
}



Theme::$filters["include"] = function($file) {
	if(file_exists("{$this->loader->getPath()}/{$file}.php")) {
		// allow easy access to site, page variables
		extract([ "page" => Phroses::$page, "site" => Phroses::$site ]);
		include "{$this->loader->getPath()}/{$file}.php";
	}
};

Theme::$filters["content"] = function($key, $fieldtype) {
	$content = $this->content;
	if($this->useReqcVars) $content = json_decode(VARS["content"] ?? "{}", true);
	
	if(array_key_exists($key, $content ?? [])) echo $content[$key];
	else if(array_key_exists($key, $this->vars)) echo $this->vars[$key];
};

Theme::$filters["typelist"] = function($type, $field, $orderby = "id", $ordertype = "ASC") {
	if(!in_array(strtoupper($ordertype), ["ASC", "DESC"])) return;
	
    $tlist = DB::query("SELECT * FROM `pages` WHERE `siteID`=? AND `type`=? ORDER BY `{$orderby}` {$ordertype}", [ Phroses::$site->id, $type ]);
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
    echo Phroses::$site->{$var} ?? "";
};
