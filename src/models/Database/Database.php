<?php

namespace Phroses\Database;

use \PDO;
use \PDOStatement;
use \Phroses\Patterns\Singleton;
use \Phroses\Database\Queries\{ InsertQuery, SelectQuery, ReplaceQuery, DeleteQuery };
use \phyrex\Template;
use const \Phroses\{ SRC, SCHEMAVER };

class Database extends Singleton {
    private $con;
    private $schemaVersion;

    const SCHEMA_ROOT = SRC."/schema";

    /**
     * Creates a new database object
     */
    protected function __construct(string $host, string $database, string $username, string $password) {
        $this->con = new PDO("mysql:host={$host};dbname={$database};", $username, $password);
    }

    /**
     * Getter for $this->con aka the connection handle
     * 
     * @return PDO the connection handle
     */
    public function getHandle(): PDO {
        return $this->con;
    }

    /**
     * Shorthand for preparing a statement, binding an array of values to it and executing
     * 
     * @param string $query the sql statement to execute
     * @param array $values an array of values to bind to the query
     * @return PDOStatement an object that can be used to fetch values from the query
     */
    public function prepare(string $query, array $values = []): PDOStatement {
        $statement = $this->con->prepare($query);

        foreach($values as $key => $val) {
            $statement->bindValue(is_integer($key) ? $key + 1 : $key, $val);
        }

        $statement->execute();
        return $statement;
    }

    /**
     * Shorthand for using $this->prepare and then running fetchAll
     * 
     * @param string $query the sql query to run
     * @param array $values an array of values to bind to the sql statement
     * @param int $fetchStyle the style to fetch results as
     */
    public function fetch(string $query, array $values, int $fetchStyle = PDO::FETCH_OBJ): array {		
		return $this->prepare($query, $values)->fetchAll($fetchStyle);
    }

    /**
	 * Executes a sql query and fetches a column from the query
	 * 
	 * @param string $query the sql query to execute
	 * @param array $values an array of values to bind
	 * @param int $column the column to fetch
	 * @return mixed the value of the colum
	 */
	public function fetchColumn(string $query, array $values, int $column = 0) {
		return $this->prepare($query, $values)->fetchColumn($column);
    }

    /**
     * Executes a sql query and returns the number of affected rows
     * 
     * @param string $query the query to run
     * @param array $values an array of values to bind to the query
     * @return int the number of rows affected by the query
     */
    public function fetchAffected(string $query, array $values): int {
        return $this->prepare($query, $values)->rowCount();
    }
    
    /**
     * Runs an unprepared query
     * 
     * @param string $query the sql query to run
     * @return PDOStatement|false a PDOStatement on success or false on failure
     */
    public function query(string $query) {
        return $this->con->query($query);
    }

    /**
     * Returns an array with error info from the last query
     * 
     * @return array an array of error info
     */
    public function getError(): array {
        return $this->con->errorInfo();
    }

    /**
     * Returns the last inserted id into the database
     * 
     * @return int the last inserted id
     */
    public function lastID(): int {
        return $this->con->lastInsertId();
    }


    /**
     * Shorthand for using an InsertQuery to insert 
     * a row into the database
     * 
     * @param string $table the table to insert the row into
     * @param string $values an array of columns => values to insert
     * @return bool true on success and false on failure
     */
    public function insert(string $table, array $values, ?InsertQuery $Query = null): bool {
        $query = ($Query ?? new InsertQuery)
            ->setTable($table)
            ->addColumns(array_keys($values));

        $parameters = array_combine($query->getParameters(), array_values($values));
        return $this->fetchAffected((string) $query, $parameters) > 0;
    }

    /**
     * Same as insert but uses replace into instead
     * 
     * @param string $table the table to replace into
     * @param array $values an array of column => value to insert
     * @return bool true on success and false on failure
     */
    public function replace(string $table, array $values): bool {
        return $this->insert($table, $values, (new ReplaceQuery));
    }

    /**
     * Performs an update on the database schema
     * 
     * @return bool true on success and false on failure
     */
    public function updateSchema(): bool {
        $version = $this->getSchemaVersion();
        if($version >= SCHEMAVER) return true;
        
        while($version < SCHEMAVER) {
            if($this->con->query(file_get_contents(self::SCHEMA_ROOT."/update-{$version}.sql")) === false) return false;
        }

        return true;
    }

    /**
     * Installs the database schema
     * 
     * @return bool true on success and false on failure
     */
    public function installSchema($schemaFile = self::SCHEMA_ROOT."/install.sql"): bool {
        $tpl = new Template($schemaFile);
        $tpl->schemaver = SCHEMAVER;

        // remove custom delimiters
        $tpl = preg_replace_callback("/DELIMITER ([^\n ]+)\n(.*)DELIMITER ([^\n ]+)/is", function($matches) {
            return str_replace($matches[1], ";", $matches[2]);
        }, $tpl);

        return $this->con->query($tpl) !== false;
    }

    /**
     * Gets the schema version from the database
     *
     * @return int the schema version in use
     */
    public function getSchemaVersion(): int {
        return $this->schemaVersion ?? $this->schemaVersion = (new SelectQuery)
            ->setTable("options")
            ->addColumns(["value"])
            ->addWhere("key", "=", "'schemaver'")
            ->execute()
            ->fetchColumn();
    }
}