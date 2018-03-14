<?php

namespace Phroses\Testing;

use \Phroses\Database\Query;


class QueryTest extends TestCase {

    /**
     * Querys should return itself on setTable
     */
    public function testSetTableReturnValue() {
        $Query = new class extends Query {};
        $this->assertInstanceOf(Query::class, $Query->setTable("pages"));
    }

}