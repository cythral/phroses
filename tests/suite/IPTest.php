<?php

namespace Phroses\Testing;

use \Phroses\IP;

class IPTest extends TestCase {
    public function testInRange() {
        $ip = new IP("10.8.0.1");
        $this->assertTrue($ip->inRange("10.8.0.0/24"));
    }

    public function testInRangeFalse() {
        $ip = new IP("10.8.0.32");
        $this->assertFalse($ip->inRange("10.8.0.0/27"));
    }
}