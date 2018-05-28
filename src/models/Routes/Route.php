<?php

namespace Phroses\Routes;

use \reqc\Output;
use \listen\Events;
use \Phroses\Cascade;
use \Phroses\Page;
use \Phroses\Site;

abstract class Route {
    public $method = null;
    public $response;
    public $controller;

    public function follow(Page &$page, Site &$site, Output &$out) {
        Events::trigger("route.follow", [ $this->response, $this->method, $site, $page ]);
    }

    public function rules(?Cascade $cascade, ?Page $page, ?Site $site) {
        return [];
    }
}