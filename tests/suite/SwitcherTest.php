<?php

namespace Phroses\Testing;

use \Phroses\Switcher;

class SwitcherTest extends TestCase {

    public function testCaseReturnValue() {
        $this->assertInstanceOf(Switcher::class, (new Switcher(null))->case(null, function() {}));
    }

    public function testIsResolvedTrue() {
        $switcher = (new Switcher(2))
            ->case(2, function() {});
        
        $this->assertTrue($switcher->isResolved());
    }

    public function testIsResolvedFalse() {
        $switcher = (new Switcher(2))
            ->case(1, function() {});
        
        $this->assertFalse($switcher->isResolved());
    }

    public function testIsResolvedFromDefault() {
        $switcher = (new Switcher(2))
            ->case(1, function() {})
            ->case(null, function() {});
        
        $this->assertTrue($switcher->isResolved());
    }

    public function testGetResult() {
        $switcher = (new Switcher(2))
            ->case(2, function() {
                return 3;
            });
        
        $this->assertEquals(3, $switcher->getResult());
    }
}