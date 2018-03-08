<?php

use \Phroses\Page;
use \Phroses\Testing\TestCase;
use \inix\Config as inix;

class PageTest extends TestCase {
    protected function setUp() {
        $dataset = file_get_contents(\Phroses\ROOT."/tests/dataset.json");
        $dataset = str_replace("{password}", inix::get("test.password"), $dataset);
        $this->data = json_decode($dataset)->pages;
    }
    
    /**
     * @covers \Phroses\Page::generate
     */
    public function testGenerate() {
        $page = Page::generate(1);
        $this->assertInstanceOf(Page::class, $page); // make sure we got a page and not null
        
        foreach($this->data[0] as $key => $val) { // check that each property matches the value from the dataset
            $this->assertEquals($val, $page->{$key});   
        }
    }
}