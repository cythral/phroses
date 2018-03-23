<?php

namespace Phroses\Testing;

use \Phroses\Sanitizer;

class SanitizerTest extends TestCase {
    public function testDefaultCallbacks() {
        $sanitizer = new Sanitizer([ " test1 ", "%2B" ]);
        $this->assertArrayEquals(["test1", "+"], $sanitizer());
    }

    public function testAdditionalCallback() {
        $sanitizer = new Sanitizer([ "&gt;", "&lt;" ]);
        $sanitizer->applyCallback("htmlspecialchars_decode");
        $this->assertArrayEquals([">", "<"], $sanitizer());
    }

    public function testAdditionalCallbackOneElement() {
        $sanitizer = new Sanitizer([ "&gt;", "&lt;" ]);
        $sanitizer->applyCallback("htmlspecialchars_decode", [ 1 ]);
        $this->assertArrayEquals(["&gt;", "<"], $sanitizer());
    }
}