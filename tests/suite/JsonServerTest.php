<?php

namespace Phroses\Testing;

use \Phroses\JsonServer;
use \Phroses\Exceptions\ExitException;

class JsonServerTest extends TestCase {
    public function setUp() {
        $this->setOutputCallback(function() {});
    }
    
    public function testSuccessException() {
        $this->setOutputCallback(function() {});
        
        $this->expectException(ExitException::class);
        (new JsonServer)->success();
    }
    
    public function testErrorException() {
        $this->expectException(ExitException::class);
        (new JsonServer)->error("test");
    }
    
    public function testSuccessCustomCode() {        
        try {
            (new JsonServer)->success(201, []);
        } catch(ExitException $e) {} // don't exit
        $this->assertEquals(201, http_response_code());
    }
    
    public function testErrorCustomCode() {
        try {
            (new JsonServer)->error("test", true, 401);
        } catch(ExitException $e) {}
        $this->assertEquals(401, http_response_code());
    }
    
    public function testSuccessExtra() {
        try {
            (new JsonServer)->success(200, ["a" => true]);
        } catch(ExitException $e) {}
        $this->assertArraySubset(["a" => true], json_decode($this->getActualOutput(), true));
    }
    
    public function testErrorExtra() {
        try {
            (new JsonServer)->error("test", true, 400, ["a" => true]);
        } catch(ExitException $e) {}
        $this->assertArraySubset(["a" => true], json_decode($this->getActualOutput(), true));
    }
}