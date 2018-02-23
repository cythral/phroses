<?php

use \Phroses\Site;
use \Phroses\DB;
use \Phroses\Testing\TestCase;

class SiteTest extends TestCase {

    /**
     * @covers \Phroses\Site::__construct
     * @dataProvider constructProvider
     */
    public function testSiteConstruct(array $options, bool $expectException) {
        if($expectException) $this->expectException(\Exception::class);
        
        $this->assertInstanceOf(Site::class, new Site($options));
    }

    public function constructProvider() {
        return [
            [ [ "id" => null, "name" => null ], true ],
            [ [ "id" => null, "name" => null, "url" => null, "adminUri" => null, "adminUsername" => null, "adminPassword" => null, "maintenance" => null, "theme" => null ], false ]
        ];
    }

    public function testGenerate() {
        $site = Site::generate(1);
        $this->assertInstanceOf(Site::class, $site);
    }
}