<?php

namespace Phroses\Routes;

use \Phroses\Cascade;
use \Phroses\Page;
use \Phroses\Site;
use \Phroses\Output;

abstract class Route {
    public $method = null;
    public $response;

    abstract public function follow(Page &$page, Site &$site, Output &$out);

    public function rules(?Cascade $cascade, ?Page $page, ?Site $site) {
        return [];
    }
}