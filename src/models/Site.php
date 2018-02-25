<?php
/**
 * Representation of a site
 */
namespace Phroses;

use \PDO;
use \inix\Config as inix;

class Site {
    private $data = [];
    public $useDB = true;

    use \Phroses\Traits\UnpackOptions;
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
     * Creates a site object
     * 
     * @param array $data the site data to use
     */
    public function __construct(array $data) {
        $this->unpackOptions($data, $this->data);
    }

    /**
     * Getter for the data property
     * 
     * @return array $data all site data
     */
    public function getData(): array {
        return $this->data;
    }

    /**
     * Getter for site data fields
     */
    public function __get($key) {
        return $this->data[$key] ?? null;
    }

    /**
     * Setter for site data fields
     */
    public function __set($key, $val) {
        if($key == "adminPassword") $val = password_hash(inix::get("pepper").$val, PASSWORD_DEFAULT);
        if($this->id && $this->useDB) DB::query("UPDATE `sites` SET `$key`=? WHERE `id`=?", [ $val, $this->id ]);

        $this->data[$key] = $val;
    }

    /**
     * Deletes the site
     *
     * @return boolean true on success and false on failure
     */
    public function delete(): bool {
        if(!($this->id && $this->useDB)) return false;
        return DB::affected("DELETE FROM `sites` WHERE `id`=:id", [ ":id" => $this->id ]) > 0;
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
    static public function generate(int $id): ?Site {
        $siteInfo = DB::query("SELECT * FROM `sites` WHERE `id`=?", [ $id ], PDO::FETCH_ASSOC)[0] ?? null;
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