<?php

namespace Phroses\Database\Builders;

use \Phroses\Database\Builder;

class DeleteBuilder extends Builder {
    use \Phroses\Database\Builders\Traits\Where;

    /** @inheritDoc */
    protected $prefix = "DELETE FROM";

    /** @var array an array of columns to insert (INSERT INTO table ({columns})) */
    protected $columns = [];

    /** @inheritDoc */
    protected $queryTemplate = "<{var::prefix}> `<{var::table}>` <{var::where}>";
}