<?php

namespace Phroses\Database;

use \Closure;
use \phyrex\Template;
use function \Phroses\{ callPrefixedMethods };

abstract class Query {

    use \Phroses\Traits\PrefixedMethods;

    /** @var string the very first part of a query */
    protected $prefix;

    /** @var string the table to operate on */
    protected $table;

    /** @var string the template string for the query */
    protected $queryTemplate = "";

    /** @var Template the template object used for parsing the resulting query */
    protected $tpl;

    /** @var string the resulting query */
    protected $query;

    /** @var string stores the extra clauses to append to the end of the query */
    protected $extra = "";

    public function __construct(?Database $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Returns the built query
     * 
     * @return string the built query
     */
    public function __toString(): string {
        $this->tpl = new Template($this->queryTemplate);

        $this->callPrefixedMethods("filter");
        return trim($this->tpl);
    }

    /**
     * Sets the table to operate on
     * 
     * @param string $table the table to use
     * @return Query returns itself for chaining
     */
    public function setTable(string $table): Query {
        $this->table = $table;
        return $this;
    }

    /**
     * Filter method for replacing <{var::table}> with $this->table
     * 
     * @return void
     */
    public function filterTable(): void {
        $this->tpl->table = $this->table;
    }

    /**
     * Filter method for replacing <{var::prefix}> with $this->prefix
     * 
     * @return void
     */
    public function filterPrefix(): void {
        $this->tpl->prefix = $this->prefix;
    }

    public function execute(array $parameters = []) {
        return $this->db->prepare((string) $this, $parameters);
    }
}