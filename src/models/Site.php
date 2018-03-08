<?php
/**
 * Representation of a site
 */
namespace Phroses;

use \PDO;
use \inix\Config as inix;

class Site extends DataClass {
    protected $tableName = "sites";
    
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

    public function setAdminPassword($password) {
        return password_hash(inix::get("pepper").$password, PASSWORD_DEFAULT);
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
     * @return Site the created site
     */
    static public function create(string $name, string $url, string $theme, string $adminUri, string $adminUsername, string $adminPassword, bool $maintenance = false): ?Site {
        $adminPassword = password_hash(inix::get("pepper").$adminPassword, PASSWORD_DEFAULT);

        DB::query("INSERT INTO `sites` (`name`, `url`, `theme`, `adminURI`, `adminUsername`, `adminPassword`, `maintenance`) VALUES (?, ?, ?, ?, ?, ?, ?)", [
            $name,
            $url,
            $theme,
            $adminUri, 
            $adminUsername,
            $adminPassword,
            (int)$maintenance
        ]);

        return self::generate(DB::lastID());
    }

    /**
     * Generates a site object based on id
     * 
     * @param int id the site id to generate an object for
     * @return Site a site object created based on data pulled from the db about the site id given
     */
    static public function generate($id): ?Site {
        $column = "url";
        if(is_numeric($id)) $column = "id";

        $siteInfo = @DB::query("SELECT * FROM `sites` WHERE `{$column}`=?", [ $id ], PDO::FETCH_ASSOC)[0] ?? null;
        return ($siteInfo) ? new Site($siteInfo) : null;
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
            DB::query("SELECT `id`,`url` FROM `sites`", [], PDO::FETCH_COLUMN|PDO::FETCH_GROUP)
        );
    }
}