<?php

namespace Phroses\Testing;

use \Phroses\Database\Queries\InsertQuery;

class InsertQueryTest extends TestCase {

    /**
     * setTable should return itself
     * @covers \Phroses\Database\Queries\InsertQuery::setTable
     */
    public function testSetTableReturn() {
        $Query = new InsertQuery;
        $this->assertInstanceOf(InsertQuery::class, $Query->setTable("sites"));
    }

    /**
     * the setTable filter should replace <{var::table}> with the tablename
     * @covers \Phroses\Database\Queries\InsertQuery::setTable
     */
    public function testSetTableFilter() {
        $Query = new InsertQuery;
        $Query->setTable("sites");
        $this->assertEquals("INSERT INTO `sites` () VALUES ()", (string) $Query);
    }

    /**
     * addColumns should return $this for chaining
     * @covers \Phroses\Database\Queries\InsertQuery::addColumns
     */
    public function testAddColumnsReturn() {
        $Query = new InsertQuery;
        $this->assertInstanceOf(InsertQuery::class, $Query->addColumns([]));
    }

    /**
     * addColumns should replace both <{var::columns}> and <{var::values}> with `key` and :key
     * respectively
     * @covers \Phroses\Database\Queries\InsertQuery::addColumns
     */
    public function testAddColumns() {
        $Query = new InsertQuery;
        $Query->addColumns(["a","b"]);
        $this->assertEquals("INSERT INTO `` (`a`,`b`) VALUES (:a,:b)", (string) $Query);
    }
}