<?php

namespace Phroses;

abstract class Route {
    public $method = null;
    public $response;

    abstract public function follow(Page &$page, Site &$site, Output &$out);

    public function rules(?Page &$page, ?Site &$site, ?Cascade &$cascade) {
        return [];
    }
}