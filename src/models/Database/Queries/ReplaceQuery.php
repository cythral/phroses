<?php
/**
 * Replace into has the same syntax as insert, so we'll just use InsertQuery as a base and simply change the prefix
 */
namespace Phroses\Database\Queries;

class ReplaceQuery extends InsertQuery {
    protected $prefix = "REPLACE INTO";
}