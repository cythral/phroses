<?php

namespace Phroses\Database\Queries;

use \phyrex\Template;
use \Phroses\Database\Query;

class InsertQuery extends Query {
    /** @inheritDoc */
    protected $prefix = "INSERT INTO";

    /** @var array an array of columns to insert (INSERT INTO table ({columns})) */
    protected $columns = [];

    /** @inheritDoc */
    protected $queryTemplate = "<{var::prefix}> `<{var::table}>` (<{var::columns}>) VALUES (<{var::values}>)";

    /**
     * Adds columns to the Query
     * 
     * @param array $columns an array of columns to add
     * @return self returns itself for chaining
     */
    public function addColumns(array $columns): self {
        $this->columns = array_merge($this->columns, $columns);
        return $this;
    }

    /**
     * Returns the columns as parameters for binding to PDO
     * 
     * @return array an array of :parameters
     */
    public function getParameters(): array {
        return array_map(function($val) {
            return ":{$val}";
        }, array_values($this->columns));
    }

    /**
     * The filter used to replace columns into <{var::columns}> and <{var::values}>
     * 
     * @return void
     */
    protected function filterColumns(): void {
        $this->tpl->columns = implode(",", array_map(function($val) {
            return "`{$val}`";
        }, array_values($this->columns)));

        // <{var::values}>
        $this->tpl->values = implode(",", $this->getParameters());
    }
}