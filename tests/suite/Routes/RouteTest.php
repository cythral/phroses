<?php

namespace Phroses\Testing;

use \Phroses\Site;
use \Phroses\Page;
use \Phroses\Routes\Route;
use \Phroses\Routes\Controller;

use \listen\Events;
use \reqc\Output;

class RouteTest extends TestCase {

    /**
     * @dataProvider FollowEventProvider
     */
    public function testFollowEvent($page, $site, $output) {
        Events::listen("route.follow", (function($response, $method) {
            $this->assertEquals(Controller::RESPONSES["PAGE"][404], $response);
            $this->assertEquals("GET", $method);
        })->bindTo($this));

        $route = new class extends Route {
            public $response = Controller::RESPONSES["PAGE"][404];
            public $method = "GET";

            public function follow(&$page, &$site, &$out) {
                parent::follow($page, $site, $out);
            }

            public function rules($cascade, $page, $site) {

            }
        };

        $route->follow($page, $site, $output);
    }

    public function FollowEventProvider() {
        $page = new Page([
            "id" => "",
            "type" => "",
            "content" => "",
            "datecreated" => "",
            "datemodified" => "",
            "title" => "",
            "views" => "",
            "public" => ""
        ]);

        $site = new Site([
            "id" => "",
            "name" => "",
            "theme" => "",
            "url" => "",
            "adminURI" => "",
            "adminUsername" => "",
            "adminPassword" => "",
            "maintenance" => ""
        ]);

        $out = new Output();

        return [
            [ $page, $site, $out ]
        ];
    }
}