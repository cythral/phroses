<?php
/**
 * Class for representing a row from a database, like a Site or Page for example.
 * Provides methods for retrieving and persisting changes to data.
 * 
 * This acts like a generic Data Access Object
 */
namespace Phroses;

use \PDO;
use \Phroses\Exceptions\ReadOnlyException;
use \Phroses\Database\Database;
use \Phroses\Database\Builders\InsertBuilder;
use \Phroses\Database\Builders\SelectBuilder;
use \Phroses\Database\Builders\DeleteBuilder;

abstract class DataClass {
    protected $db;

    static protected $tableName;
    static protected $virtualProperties = [];
    
    use \Phroses\Traits\Properties;
    use \Phroses\Traits\UnpackOptions;

    const DEFAULT_DB = "\Phroses\DB";
    
    /**
     * Constructor 
     * 
     * @param array $data an array of column => value pairs
     * @param mixed $db the database class to use
     * @return void
     */
    public function __construct(array $data, $db = null) {
        $data = array_change_key_case($data);
        $this->unpackOptions($data, $this->properties);
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Getter. Retrieves column from the database if not loaded already
     * 
     * @param string $key the property/column to get
     * @return mixed the value of the property/column
     */
    public function _get(string $key) {
        $table = static::$tableName;

        return 
            ($this->properties[$key] = (
                (new SelectBuilder)
                    ->setTable(static::$tableName)
                    ->addColumns([ $key ])
                    ->addWhere("id", "=", ":id")
                    ->execute([ ":id" => $this->properties["id"] ])
                    ->fetchColumn()
             ) ?? null);
    }

    /**
     * Setter. Persists changes to properties/columns in the database
     * 
     * @param string $key the property/column to set
     * @param mixed $val the value to set the property/column to
     * @return void
     */
    public function _set(string $key, $val): void {
        $table = static::$tableName;
        if(array_search(strtolower($key), static::$readOnlyProperties ?? []) !== false) throw new ReadOnlyException($key);

        $this->db->prepare(
            "UPDATE `{$table}` SET `{$key}`=:val WHERE `id`=:id", 
            [ 
                ":val" => $val, 
                ":id" => $this->id 
            ]
        );
    }

    /**
     * Getter for the data object that stores all properties/columns
     * 
     * @return array the data object containing properties/columns
     */
    public function getData(): array {
        return $this->properties;
    }

    /**
     * Checks to see if the data loaded exists in the database (searches by id)
     * 
     * @return bool true if the id exists in the database and false if not
     */
    public function exists(): bool {
        $table = static::$tableName;

        return ($this->id) ? 
            ((new SelectBuilder)
                ->setTable(static::$tableName)
                ->addColumns([ "count(`id`)" ])
                ->addWhere("id", "=", ":id")
                ->execute([ ":id" => $this->id ])
                ->fetchColumn(0) > 0) : false;
    }

    /**
     * Inserts data into the database if it doesn't exist already
     * 
     * @return bool true on success or false on failure
     */
    public function persist(): bool {
        if(!$this->exists()) {
            unset($this->properties["id"]);
            $this->db->insert(static::$tableName, $this->properties);
            return ($this->properties["id"] = $this->db->lastID());
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
        $table = static::$tableName;
        
        return ((new DeleteBuilder)
            ->setTable(static::$tableName)
            ->addWhere("id", "=", ":id")
            ->execute([ ":id" => $this->id ])
            ->rowCount() > 0);
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
    static public function lookup($val, string $column = "id", array $args = [], $db = null): ?self {
        $db = $db ?? Database::getInstance();
        $table = static::$tableName;

        $info = (new SelectBuilder)
            ->setTable(static::$tableName)
            ->addColumns(["*"])
            ->addWhere($column, "=", ":{$column}")
            ->execute([ ":{$column}" => $val ])
            ->fetchAll(PDO::FETCH_ASSOC);

        return (isset($info[0])) ? new static($info[0], ...$args) : null;
    }
}