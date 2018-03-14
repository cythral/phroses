<?php
/**
 * A simple sql select statement builder
 * 
 * @todo add joins
 * @todo add OR for where
 */
namespace Phroses\Database\Builders;

use \InvalidArgumentException;
use \Phroses\Database\Builder;

class SelectBuilder extends Builder {

    use \Phroses\Database\Builders\Traits\Where;

    /** @inheritDoc */
    protected $prefix = "SELECT";

    /** @var array an array of columns to insert (INSERT INTO table ({columns})) */
    protected $columns = [];

    /** @var string order by column */
    protected $orderByColumn;

    /** @var string order by direction */
    protected $orderByDirection;

    /** @inheritDoc */
    protected $queryTemplate = "<{var::prefix}> <{var::columns}> FROM <{var::table}> <{var::where}> <{var::order}>";

    /**
     * Add columns to select in the query
     * 
     * @param array $columns the columns to select
     * @return SelectBuilder return itself for chaining
     */
    public function addColumns(array $columns): self {
        $this->columns = array_merge($this->columns, $columns);
        return $this;
    }

    public function orderBy(string $column, string $direction) {
        $this->orderByColumn = $column;
        $this->orderByDirection = $direction;
    }

    /**
     * Filter method for replacing <{var::columns}>
     */
    public function filterColumns() {
        $this->tpl->columns = implode(",", $this->columns);
    }

    public function filterOrderBy() {
        $this->order = (isset($this->orderByColumn, $this->orderByDirection)) ? "ORDER BY `{$this->orderByColumn}` {$this->orderByDirection}" : "";
    }
}