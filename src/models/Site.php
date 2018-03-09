<?php
/**
 * Representation of a site
 */
namespace Phroses;

use \PDO;
use \inix\Config as inix;

class Site extends DataClass {
    static protected $tableName = "sites";
    
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

    protected function setAdminPassword($password) {
        return password_hash(inix::get("pepper").$password, PASSWORD_DEFAULT);
    }

    protected function getPages(): array {
        $pages = $this->db::query("SELECT * FROM `pages` WHERE `siteID`=:id", [ ":id" => $this->id ], PDO::FETCH_ASSOC);
        return array_map((function($pagedata) { return new Page($pagedata); })->bindTo($this), $pages);
    }

    protected function setPages() {
        throw new \Exception("Pages is a readonly property");
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
    static public function list($db = self::DEFAULT_DB): array {
        return array_map(
            function($val) { 
                return $val[0]; 
            }, 
            $db::query("SELECT `id`,`url` FROM `sites`", [], PDO::FETCH_COLUMN|PDO::FETCH_GROUP)
        );
    }
}