<?php

namespace Phroses\Testing;

use \Phroses\Database\Queries\SelectQuery;

class SelectQueryTest extends TestCase {
    public function testAddColumns() {
        $Query = (new SelectQuery)
            ->addColumns(["a", "b"]);
        
        $this->assertEquals("SELECT a,b FROM", trim((string) $Query));
    }
}