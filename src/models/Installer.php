<?php

namespace Phroses;

use \Phroses\Database\Database;
use \phyrex\Template;
use \Phroses\Exceptions\InstallerException;

class Installer {

    private $db;
    private $dbVersion;
    private $dbHost;
    private $dbName;
    private $dbUser;
    private $dbPass;

    const DSN = [
        "mysql" => "<{var::driver}>:host=<{var::host}>;dbname=<{var::database}>"
    ];

    public function setupDatabase(string $host, string $database, string $username, string $password, string $min, string $driver = "mysql") {
        try {

            $this->db = Database::getInstance($host, $database, $username, $password);
            $this->dbVersion = $this->db->getHandle()->query("select version()")->fetchColumn();
            $this->dbHost = $host;
            $this->dbName = $database;
            $this->dbUser = $username;
            $this->dbPass = $password;

            if(version_compare($this->dbVersion, $min, "<")) throw new InstallerException("dbver");

        } catch(PDOException $e) {
            throw new InstallerException("dbconf");
        }
    }

    public function installSchema($schemaFile, $version) {
        if(!file_exists($schemaFile)) throw new InstallerException("noschemafile");

        $schema = new Template($schemaFile);
        $schema->version = $version;
        if(!$this->db->installSchema()) throw new InstallerException("schemainstallfail");
    }


    public function setupConfFile(string $confSrc, string $confDest, array $options) {
        if(!file_exists($confSrc)) throw new InstallerException("noconfsrc");

        $conf = new Template($confSrc);
        $conf->host = $this->dbHost;
        $conf->database = $this->dbName;
        $conf->username = $this->dbUser;
        $conf->password = $this->dbPass;

        array_walk($options, function($value, $key) use (&$conf) {
            $conf->{$key} = $value;
        });

        if(!file_put_contents($confDest, $conf)) throw new InstallerException("confputfail");
        if(!chown($confDest, posix_getpwuid(posix_geteuid())['name'])) throw new InstallerException("confchownfail");
        if(!chmod($confDest, 0775)) throw new InstallerException("confchmodfail");
    }

    private function getDSN($driver, $host, $database): string {
        $dsn = new Template(self::DSN[$driver]);
        $dsn->driver = $driver;
        $dsn->host = $host;
        $dsn->database = $database;
        return (string) $dsn;
    }
}