<?php

namespace Phroses\Testing;

use \Phroses\Database\Builder;


class BuilderTest extends TestCase {

    /**
     * Builders should return itself on setTable
     */
    public function testSetTableReturnValue() {
        $builder = new class extends Builder {};
        $this->assertInstanceOf(Builder::class, $builder->setTable("pages"));
    }

}