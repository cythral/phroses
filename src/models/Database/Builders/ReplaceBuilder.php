<?php
/**
 * Replace into has the same syntax as insert, so we'll just use InsertBuilder as a base and simply change the prefix
 */
namespace Phroses\Database\Builders;

class ReplaceBuilder extends InsertBuilder {
    protected $prefix = "REPLACE INTO";
}