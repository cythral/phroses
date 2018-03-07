<?php

use \Phroses\Site;
use \Phroses\DB;
use \Phroses\Testing\TestCase;
use \inix\Config as inix;

class SiteTest extends TestCase {
    protected $data = [];
    
    protected function setUp() {
        $dataset = file_get_contents(\Phroses\ROOT."/tests/dataset.json");
        $dataset = str_replace("{password}", inix::get("test.password"), $dataset);
        $this->data = json_decode($dataset)->sites;
    }

    /**
     * @covers \Phroses\Site::__construct
     * @dataProvider constructProvider
     */
    public function testSiteConstruct(array $options, bool $expectException) {
        if($expectException) $this->expectException(\Exception::class);
        
        $this->assertInstanceOf(Site::class, new Site($options));
    }

    /**
     * Tests the generate method with an existing site id
     * @covers Phroses\Site::generate
     */
    public function testGenerate() {
        $site = Site::generate(1);
        $this->assertInstanceOf(Site::class, $site);
        $expected = $this->data[0];

        foreach((array)$expected as $key => $val) {
            $this->assertEquals($val, $site->{$key});
        }        
    }

    /**
     * Tests the list method with only existing sites
     * @covers Phroses\Site::list
     */
    public function testList() {
        $this->assertArrayEquals([ 1 => "phroses.dev" ], Site::list());
    }

    /**
     * Tests the create method
     * 
     * @covers Phroses\Site::create
     * @dataProvider createProvider
     */
    public function testCreate(string $name, string $url, string $theme, string $adminUri, string $adminUsername, string $adminPassword, bool $maintenance) {
        $site = Site::create($name, $url, $theme, $adminUri, $adminUsername, $adminPassword, $maintenance);
        $this->assertInstanceOf(Site::class, $site);

        $this->assertEquals($name, $site->name);
        $this->assertEquals($url, $site->url);
        $this->assertEquals($theme, $site->theme);
        $this->assertEquals($adminUri, $site->adminURI);
        $this->assertEquals($adminUsername, $site->adminUsername);
        $this->assertTrue(password_verify(inix::get("pepper").$adminPassword, $site->adminPassword));
        $this->assertEquals($maintenance, (bool)$site->maintenance);

        $this->resetDB();
    }

    /**
     * Tests the delete method
     * 
     * @covers Phroses\Site::delete
     * @depends testCreate
     */
    public function testDelete() {
        $site = new Site((array) $this->data[0]);
        $this->assertTrue($site->delete());
        $this->resetDB();        
    }

    public function createProvider() {
        return [
            [ "cythral", "cythral.com", "bloom", "/admin", "john", "thisisapassword", false ]
        ];
    }

    public function constructProvider() {
        return [
            [ [ "id" => null, "name" => null ], true ],
            [ [ "id" => null, "name" => null, "url" => null, "adminUri" => null, "adminUsername" => null, "adminPassword" => null, "maintenance" => null, "theme" => null ], false ]
        ];
    }
}