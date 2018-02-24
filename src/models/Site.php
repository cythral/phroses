<?php

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

    static public function create(string $name, string $url, string $theme, string $adminUri, string $adminUsername, string $adminPassword, bool $maintenance) {
        $adminPassword = password_hash(inix::get("pepper").$adminPassword, PASSWORD_DEFAULT);

        DB::query("INSERT INTO `sites` (`name`, `url`, `theme`, `adminURI`, `adminUsername`, `adminPassword`, `maintenance`) VALUES (?, ?, ?, ?, ?, ?, ?)", [
            $name,
            $url,
            $theme,
            $adminUri, 
            $adminUsername,
            $adminPassword,
            $maintenance
        ]);

        return self::generate(DB::lastID());
    }

    static public function generate(int $id): ?Site {
        $siteInfo = DB::query("SELECT * FROM `sites` WHERE `id`=?", [ $id ], PDO::FETCH_ASSOC)[0] ?? null;
        return ($siteInfo) ? new Site($siteInfo) : null;
    }

    static public function list(): array {
        return array_map(function($val) { return $val[0]; }, DB::query("SELECT `id`,`url` FROM `sites`", [], PDO::FETCH_COLUMN|PDO::FETCH_GROUP));
    }
}