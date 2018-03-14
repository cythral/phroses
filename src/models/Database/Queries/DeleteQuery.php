<?php

namespace Phroses\Database\Queries;

use \Phroses\Database\Query;

class DeleteQuery extends Query {
    use \Phroses\Database\Queries\Traits\Where;

    /** @inheritDoc */
    protected $prefix = "DELETE FROM";

    /** @var array an array of columns to insert (INSERT INTO table ({columns})) */
    protected $columns = [];

    /** @inheritDoc */
    protected $queryTemplate = "<{var::prefix}> `<{var::table}>` <{var::where}>";
}