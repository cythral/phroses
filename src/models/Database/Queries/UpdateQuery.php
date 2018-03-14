<?php

namespace Phroses\Database\Queries;

use \Phroses\Database\Query;

class UpdateQuery extends Query {

    use \Phroses\Database\Queries\Traits\Where;

    /** @inheritDoc */
    protected $prefix = "UPDATE";

    /** @var array an array of columns to insert (INSERT INTO table ({columns})) */
    protected $columns = [];

    /** @inheritDoc */
    protected $queryTemplate = "<{var::prefix}> `<{var::table}>` SET <{var::columns}> <{var::where}>";


    public function addColumns(array $columns): self {
        $this->columns = array_merge($this->columns, $columns);
        return $this;
    }

    protected function filterColumns() {
        $this->tpl->columns = implode(",", array_map(function($value, $column) {
            return "`{$column}`={$value}";
        }, $this->columns, array_keys($this->columns)));
    }
    
}