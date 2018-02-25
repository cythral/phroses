<?php
/**
 * Page object, provides an interfaces for displaying, updating and getting information
 * about a page.  
 */

namespace Phroses;

use \Phroses\Phroses;
use \Phroses\Theme\Theme;
use \Exception;
use \reqc\Output;
use \inix\Config as inix;

use const \reqc\{ MIME_TYPES };

class Page {
    private $data;
    private $oh;
    
    public $theme;
    public $useDB = true;

    use \Phroses\Traits\UnpackOptions;
    const REQUIRED_OPTIONS = [
        "id",
        "type",
        "content",
        "datecreated",
        "datemodified",
        "title",
        "views",
        "visibility"
    ];

    /**
     * Creates a new page object based on the array of options
     * passed to it.
     * 
     * @param array $options an array containing page data (see self::REQUIRED_OPTIONS for the required keys)
     * @param string $theme the name of the theme to use for displaying the page.  Defaults to the default theme name
     */
    public function __construct(array $options, string $theme = Theme::DEFAULT) {
        $options = array_change_key_case($options);
        $this->unpackOptions($options, $this->data);

        $this->data = $options;
        $this->oh = new Output();
        $this->theme = new Theme($theme, $this->type);
    }

    /**
     * Getter, gets a page data variable
     */
    public function __get($key) {
        return $this->data[$key] ?? null;
    }

    /**
     * Setter, sets a page data variable.  Updates the database
     * if the page id is not empty.
     */
    public function __set($key, $val) {
        if($this->id && $this->useDB) DB::query("UPDATE `pages` SET `$key`=? WHERE `id`=?", [$val, $this->id]);
        
        if($key == "type") $this->theme->setType($val, true);
        if($key == "content") {
            if(is_string($val)) $val = json_decode($val, true);
            $this->theme->setContent($val);
        }

        $this->data[$key] = $val;
        return true;
    }

    /**
     * Gets all data about a page
     * 
     * @return array $data an array of page data
     */
    public function getData(): array {
        return $this->data;
    }

    /**
     * Displays a page.  Can pass a new content variable to override what
     * content is displayed on the page.
     * 
     * @param array $content an array of content variables
     */
    public function display(?array $content = null) {
        ob_start("ob_gzhandler");
        $this->oh->setContentType(MIME_TYPES["HTML"]); 

        $this->theme->title = $this->title;
        $this->theme->setContent($content ?? $this->content);
        echo $this->theme;

        if(inix::get("mode") == "production") {
            ob_end_flush();
            flush();
        }
    }

    /**
     * Deletes a page if the id is not empty
     * 
     * @return bool true on success, false on failure
     */
    public function delete(): bool {
        if(!($this->id && $this->useDB)) return false;
        return DB::affected("DELETE FROM `pages` WHERE `id`=:id", [ ":id" => $this->id ]) > 0;
    }

    /**
     * Creates a page if it does not exist
     * 
     * @param $path the uri
     * @param $title the page title
     * @param $type the page type
     * @param $content the page content
     * @param $siteId the id of the site to attach to
     * @return int the id of the page inserted
     */
    static public function create($path, $title, $type, $content = "{}", $siteId) {
        DB::query("INSERT INTO `pages` (`uri`,`title`,`type`,`content`, `siteID`,`dateCreated`) VALUES (?, ?, ?, ?, ?, NOW())", [
            $path,
            $title,
            $type,
            $content,
            $siteId
        ]);

        return DB::lastID();
    }

    /**
     * Generates a Page object from an id
     * 
     * @param int $id the id to generate a Page object for
     * @return Page|null the page object that was created or null if it doesn't exist.
     */
    static public function generate(int $id): ?Page {
        $pageData = DB::query("SELECT * FROM `pages` WHERE `id`=?", [ $id ])[0] ?? null;
        return ($pageData) ? new Page($pageData) : null;
    }
}