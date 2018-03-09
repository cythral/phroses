<?php


use Phroses\Cascade;
use Phroses\Testing\TestCase;

/**
 * Unit testing for the Cascade class
 * @covers \Phroses\Cascade
 */
class CascadeTest extends TestCase {
    
    /**
     * @covers \Phroses\Cascade::__construct
     */
    public function testInitialValue() {
        $cascade = new Cascade(1);
        $this->assertEquals(1, $cascade->getResult());
    }
    
    /**
     * Tests to see if after adding a rule that evaluates to true, the new value is the result.
     * @covers \Phroses\Cascade::addRule
     */
    public function testCascadedNewValue() {
        $cascade = new Cascade(1);
        $cascade->addRule(true, 2);
        $this->assertEquals(2, $cascade->getResult());
    }
    
    /**
     * Tests to see if after adding a rule that evaluates to false, the old value is the result.
     * @covers \Phroses\Cascade::addRule
     */
    public function testCascadeOldValue() {
        $cascade = new Cascade(1);
        $cascade->addRule(false, 2);
        $this->assertEquals(1, $cascade->getResult());
    }
}