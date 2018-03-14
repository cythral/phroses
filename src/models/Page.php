<?php
/**
 * Page object, provides an interfaces for displaying, updating and getting information
 * about a page.  
 */

namespace Phroses;

use \Phroses\Phroses;
use \Phroses\Patterns\DataClass;
use \Phroses\Theme\Theme;
use \Exception;
use \reqc\Output;
use \inix\Config as inix;
use \PDO;

use const \reqc\{ MIME_TYPES };

class Page extends DataClass {
    /** @var string the name of the table in the database this class corresponds to */
    static protected $tableName = "pages";

    /** @var Theme the theme to use for displaying the page */
    protected $_theme;

    /** @var array required options to be passed to the constructor */
    const REQUIRED_OPTIONS = [
        "id",
        "type",
        "content",
        "datecreated",
        "datemodified",
        "title",
        "views",
        "public"
    ];

    /**
     * Creates a new page object based on the array of options
     * passed to it.
     * 
     * @param array $options an array containing page data (see self::REQUIRED_OPTIONS for the required keys)
     * @param string $theme the name of the theme to use for displaying the page.  Defaults to the default theme name
     */
    public function __construct(array $options, ?string $theme = null, $db = null) {
        parent::__construct($options, $db);
        
        if($theme) $this->theme = $theme;
    }

    /**
     * Setter for type, reloads the theme with the new type
     * 
     * @param string $type the new type to use
     * @return string the new type
     */
    protected function setType(string $type): string {
        if($this->theme) $this->theme->setType($type, true);
        return $type;
    }

    /**
     * Setter for content, reloads the theme with new content
     * 
     * @param string|array $val the new content to use
     * @return array the new content
     */
    protected function setContent($val): string {
        $original = $val;
        if(is_string($val)) $val = json_decode($val, true);
        $this->theme->setContent($val);
        return $original;
    }

    /**
     * Sets the page's theme
     * 
     * @param string $theme the name of the theme to set it to
     * @return void
     */
    protected function setTheme(string $theme): void {
        $this->_theme = new Theme($theme, $this->type);
    }

    /**
     * Gets the current theme in use
     * 
     * @return Theme|null the theme in use or null if not set
     */
    protected function getTheme(): ?Theme {
        return $this->_theme ?? null;
    }

    /**
     * Displays a page.  Can pass a new content variable to override what
     * content is displayed on the page.
     * 
     * @param array $content an array of content variables
     * @return void
     */
    public function display(?array $content = null): void {
        if(!$this->theme) throw new \Exception("The \$theme property has not been set, cannot display the page.");
        
        ob_start("ob_gzhandler");
        (new Output)->setContentType(MIME_TYPES["HTML"]); 

        $this->theme->title = $this->title;
        $this->theme->setContent($content ?? $this->content);
        echo $this->theme;

        if(inix::get("mode") == "production") {
            ob_end_flush();
            flush();
        }
    }

    /**
     * Creates a page if it does not exist
     * 
     * @param string $path the uri
     * @param string $title the page title
     * @param string $type the page type
     * @param string $content the page content
     * @param int $siteId the id of the site to attach to
     * @param Theme $theme the name of the theme to use
     * @return Page the created page
     */
    static public function create(string $path, string $title, string $type, string $content = "{}", int $siteId, string $theme = Theme::DEFAULT): Page {
        $page = new Page([
            "id" => null,
            "uri" => $path,
            "title" => $title,
            "type" => $type,
            "content" => $content,
            "siteID" => $siteId
        ], $theme);

        return ($page->persist() && $page->exists()) ? $page : null;
    }

    /**
     * Generates a Page object from an id
     * 
     * @param int $id the id to generate a Page object for
     * @param string $theme the name of the theme to use
     * @return Page|null the page object that was created or null if it doesn't exist.
     */
    static public function generate(int $id, string $theme = Theme::DEFAULT): ?Page {
       return self::lookup($id, "id", [ $theme ]);
    }
}