<?php

namespace Phroses\Testing;

use \Phroses\Database\Queries\UpdateQuery;

class UpdateQueryTest extends TestCase {

    public function testAddColumnsReturnValue() {
        $this->assertInstanceOf(UpdateQuery::class, (new UpdateQuery)->addColumns([]));
    }

    public function testFilterColumn() {
        $query = (new UpdateQuery);
        $query->addColumns([ "id" => 1 ]);
        $this->assertEquals("UPDATE `` SET `id`=1", (string) $query);
    }
}