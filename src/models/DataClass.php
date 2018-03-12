<?php
/**
 * Class for representing a row from a database, like a Site or Page for example.
 * Provides methods for retrieving and persisting changes to data
 */
namespace Phroses;

use \PDO;

abstract class DataClass {
    protected $db;
    protected $data = [];

    static protected $tableName;
    public const DEFAULT_DB = "\Phroses\DB";
    
    use \Phroses\Traits\UnpackOptions;
    
    /**
     * Constructor 
     * 
     * @param array $data an array of column => value pairs
     * @param mixed $db the database class to use
     * @return void
     */
    public function __construct(array $data, $db = self::DEFAULT_DB) {
        $data = array_change_key_case($data);
        $this->unpackOptions($data, $this->data);
        $this->db = $db;
    }

    /**
     * Getter. Retrieves column from the database if not loaded already
     * 
     * @param string $key the property/column to get
     * @return mixed the value of the property/column
     */
    public function __get(string $key) {
        if(method_exists($this, "get{$key}") && (new \ReflectionMethod($this, "get{$key}"))->isProtected()) {
            return $this->{"get{$key}"}();
        }

        $table = get_called_class()::$tableName;
        return $this->data[$key] ?? 
            ($this->data[$key] = $this->db::query("SELECT `{$key}` FROM `{$table}` WHERE `id`=:id", [ ":id" => $this->data["id"] ])[0]->{$key} ?? null);
    }

    /**
     * Setter. Persists changes to properties/columns in the database
     * 
     * @param string $key the property/column to set
     * @param mixed $val the value to set the property/column to
     * @return void
     */
    public function __set(string $key, $val): void {
        if(method_exists($this, "set{$key}") && (new \ReflectionMethod($this, "set{$key}"))->isProtected()) {
            $val = $this->{"set{$key}"}($val);
            if(!$val) return;
        }
        
        $table = get_called_class()::$tableName;
        $this->db::query("UPDATE `{$table}` SET `{$key}`=:val WHERE `id`=:id", [ ":val" => $val, ":id" => $this->id ]);
        $this->data[$key] = $val;
    }

    /**
     * Getter for the data object that stores all properties/columns
     * 
     * @return array the data object containing properties/columns
     */
    public function getData(): array {
        return $this->data;
    }

    /**
     * Checks to see if the data loaded exists in the database (searches by id)
     * 
     * @return bool true if the id exists in the database and false if not
     */
    public function exists(): bool {
        $table = get_called_class()::$tableName;
        return ($this->id) ? $this->db::column("SELECT count(`id`) FROM `{$table}` WHERE `id`=:id", [ ":id" => $this->id ]) > 0 : false;
    }

    /**
     * Inserts data into the database if it doesn't exist already
     * 
     * @return bool true on success or false on failure
     */
    public function persist(): bool {
        if(!$this->exists()) {
            $table = get_called_class()::$tableName;

            $query = "INSERT INTO `{$table}` ({columns}) VALUES ({values})";
            $values = [];
            unset($this->data["id"]);

            foreach($this->data as $key => $val) {
                $query = str_replace("{columns}", "`{$key}`,{columns}", $query);
                $query = str_replace("{values}", ":{$key},{values}", $query);
                $values[":{$key}"] = $val;
            }

            $query = str_replace([",{columns}", ",{values}"], "", $query);
            $this->db::query($query, $values);
            return ($this->data["id"] = $this->db::lastID());
        }

        return true;
    }

    /**
     * Deletes the data from the database
     * 
     * @return bool true on success and false on failure
     */
    public function delete(): bool {
        if(!$this->id) return false;
        $table = get_called_class()::$tableName;
        return $this->db::affected("DELETE FROM `{$table}` WHERE `id`=:id", [ ":id" => $this->id ]) > 0;
    }

    /**
     * Looks up a row in the database by a unique column, return new instance of that dataclass
     * if it exists.
     * 
     * @param mixed $val the value to lookup
     * @param string $column the column to match the $val to
     * @param array $args an array of extra args to be passed to the created dataclass
     * @param mixed $db the database class to use
     */
    static public function lookup($val, string $column = "id", array $args = [], $db = self::DEFAULT_DB): ?self {
        $class = get_called_class();
        $table = $class::$tableName;
        $info = $db::query("SELECT * FROM `{$table}` WHERE `{$column}`=:{$column}", [ ":{$column}" => $val ], PDO::FETCH_ASSOC)[0] ?? null;

        return ($info) ? new $class($info, ...$args) : null;
    }
}