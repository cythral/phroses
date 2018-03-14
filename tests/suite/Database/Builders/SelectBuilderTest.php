<?php

namespace Phroses\Testing;

use \Phroses\Database\Builders\SelectBuilder;

class SelectBuilderTest extends TestCase {
    public function testAddColumns() {
        $builder = (new SelectBuilder)
            ->addColumns(["a", "b"]);
        
        $this->assertEquals("SELECT a,b FROM", trim((string) $builder));
    }
}