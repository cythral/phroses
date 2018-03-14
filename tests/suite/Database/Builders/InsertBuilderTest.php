<?php

namespace Phroses\Testing;

use \Phroses\Database\Builders\InsertBuilder;

class InsertBuilderTest extends TestCase {

    /**
     * setTable should return itself
     * @covers \Phroses\Database\Builders\InsertBuilder::setTable
     */
    public function testSetTableReturn() {
        $builder = new InsertBuilder;
        $this->assertInstanceOf(InsertBuilder::class, $builder->setTable("sites"));
    }

    /**
     * the setTable filter should replace <{var::table}> with the tablename
     * @covers \Phroses\Database\Builders\InsertBuilder::setTable
     */
    public function testSetTableFilter() {
        $builder = new InsertBuilder;
        $builder->setTable("sites");
        $this->assertEquals("INSERT INTO `sites` () VALUES ()", (string) $builder);
    }

    /**
     * addColumns should return $this for chaining
     * @covers \Phroses\Database\Builders\InsertBuilder::addColumns
     */
    public function testAddColumnsReturn() {
        $builder = new InsertBuilder;
        $this->assertInstanceOf(InsertBuilder::class, $builder->addColumns([]));
    }

    /**
     * addColumns should replace both <{var::columns}> and <{var::values}> with `key` and :key
     * respectively
     * @covers \Phroses\Database\Builders\InsertBuilder::addColumns
     */
    public function testAddColumns() {
        $builder = new InsertBuilder;
        $builder->addColumns(["a","b"]);
        $this->assertEquals("INSERT INTO `` (`a`,`b`) VALUES (:a,:b)", (string) $builder);
    }
}