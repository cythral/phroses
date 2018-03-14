<?php
/**
 * Representation of a site
 */
namespace Phroses;

use \PDO;
use \inix\Config as inix;
use \Phroses\Database\Database;
use \Phroses\Database\Queries\SelectQuery;
use \Phroses\Exceptions\ReadOnlyException;

class Site extends DataClass {
    /** @var string the name of the table this class corresponds to in the database */
    static protected $tableName = "sites";

    static protected $readOnlyProperties = [
        "views",
        "pagecount",
        "uploads",
        "pages"
    ];
    
    /** @var array an array of required options that must be passed to the constructor */
    const REQUIRED_OPTIONS = [
        "id",
        "name",
        "theme",
        "url",
        "adminURI",
        "adminUsername",
        "adminPassword",
        "maintenance"
    ];

    /**
     * Setter for the adminPassword property, automatically hashes the password
     * 
     * @param string $password the password to set it to
     * @return string a hash of the password
     */
    protected function setAdminPassword(string $password): string {
        return password_hash(inix::get("pepper").$password, PASSWORD_DEFAULT);
    }

    /**
     * Getter for the uploads property, returns an array of uploads
     * 
     * @return array an array of uploads
     */
    protected function getUploads(): array {
        return Upload::list($this);
    }

    /**
     * Get all pages on a site
     * 
     * @return array an array of pages indexed by uri
     */
    protected function getPages(): array {
        $pages = (new SelectQuery)
            ->setTable("pages")
            ->addColumns(["*"])
            ->addWhere("siteID", "=", ":id")
            ->execute([ ":id" => $this->id ])
            ->fetchAll(PDO::FETCH_ASSOC);
        
        return array_combine(
            array_map(function($pagedata) { return $pagedata["uri"]; }, $pages),
            array_map((function($pagedata) { return new Page($pagedata); })->bindTo($this), $pages)
        );
    }

    /**
     * Retrieve single page based on uri
     * 
     * @param string $uri the uri of the page to select
     * @return Page|null the retrieved page object or null if not found.
     */
    public function getPage(string $uri): ?Page {
        $query = (new SelectQuery)
            ->setTable("pages")
            ->addColumns(["*"])
            ->addWhere("id", "=", ":id")
            ->addWhere("uri", "=", ":uri")
            ->execute([ ":id" => $this->id, ":uri" => $uri ])
            ->fetchAll(PDO::FETCH_ASSOC);

        return isset($query[0]) ? new Page($query[0]) : null;
    }

    /**
     * Checks to see if a page exists in the site
     * 
     * @param string $uri the uri of the page to check for
     * @return bool true if the page exists and false if not
     */
    public function hasPage(string $uri): bool {
        return $this->getPage($uri) != null;
    }

    /**
     * Getter for total view count
     * 
     * @return int the total number of views the site has gotten
     */
    protected function getViews(): int {
        return (new SelectQuery)
            ->setTable("pages")
            ->addColumns([ "SUM(`views`)" ])
            ->addWhere("siteID", "=", ":id")
            ->execute([ ":id" => $this->id ])
            ->fetchColumn() ?? 0;
    }

    /**
     * Getter for site page count (this is faster than count($site->pages))
     */
    protected function getPageCount(): int {
        return (new SelectQuery)
            ->setTable("pages")
            ->addColumns([ "COUNT(`id`)"])
            ->addWhere("siteID", "=", ":id")
            ->execute([ ":id" => $this->id ])
            ->fetchColumn();
    }
    
    /**
     * Validates a user provided username and password against the sites login info
     * 
     * @param string $username the username to check
     * @param string $password the password to check
     * @return bool true if username / password combo is ok, false if not
     */
    public function login(string $username, string $password): bool {
        if($username == $this->adminUsername && password_verify(inix::get("pepper").$password, $this->adminPassword)) {
            if(password_needs_rehash($this->adminPassword, PASSWORD_DEFAULT)) {
                $this->adminPassword = $password;
            }
            return true;
        }
        return false;
    }

    /**
     * Creates a new site
     * 
     * @param string $name the site's name
     * @param string $url the site's url
     * @param string $theme the name of the theme to use
     * @param string $adminUri the dashboard/admin URI
     * @param string $adminUsername the username that is used to login to the dashboard
     * @param string $adminPassword the password that is used to login to the dashboard
     * @param bool $maintenance whether or not to put it into maintenance mode (defaults to false)
     * @return Site the created site or null on failure
     */
    static public function create(string $name, string $url, string $theme, string $adminUri, string $adminUsername, string $adminPassword, bool $maintenance = false): ?Site {
        $site = new Site([
            "id" => null,
            "name" => $name,
            "url" => $url,
            "theme" => $theme,
            "adminUri" => $adminUri,
            "adminUsername" => $adminUsername,
            "adminPassword" => password_hash(inix::get("pepper").$adminPassword, PASSWORD_DEFAULT),
            "maintenance" => (int)$maintenance
        ]);

        return ($site->persist() && $site->exists()) ? $site : null;
    }

    /**
     * Generates a site object based on lookup key
     * 
     * @param int|string $lookup the site id or url to generate an object for
     * @return Site a site object created based on data pulled from the db about the site id given
     */
    static public function generate($lookup): ?Site {
        $column = "url";
        if(is_numeric($lookup)) $column = "id";

       return self::lookup($lookup, $column);
    }

    /**
     * list of site ids and urls
     * 
     * @return array an array containing items that are id => url
     */
    static public function list(): array {
        return array_map(
            
            function($val) { 
                return $val[0]; 
            }, 
            
            ((new SelectQuery)
                ->setTable(static::$tableName)
                ->addColumns(["id", "url"])
                ->execute()
                ->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP))
        );
    }
}