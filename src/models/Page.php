<?php

namespace Phroses;

use \reqc\Output;
use \inix\Config as inix;

class Page {
    private $data;
    private $oh;
    public $theme;

    const REQUIRED_OPTIONS = [
        "id",
        "type",
        "content",
        "datecreated",
        "datemodified",
        "title",
        "views",
        "visibility"
    ];

    public function __construct(array $options, Output $oh) {
        $options = array_change_key_case($options);
        foreach(self::REQUIRED_OPTIONS as $option) {
            if(!array_key_exists($option, $options)) throw new \Exception("Missing required option $option");
        }

        $this->data = $options;
        $this->oh = $oh;
        $this->theme = new Theme(SITE["THEME"], $this->type);
    }

    public function __get($key) {
        return $this->data[$key] ?? null;
    }

    public function __set($key, $val) {
        if($this->id) DB::Query("UPDATE `pages` SET `$key`=? WHERE `id`=?", [$val, $this->id]);
        $this->data[$key] = $val;
        return true;
    }

    public function getAll() {
        return $this->data;
    }

    public function display(?array $content = null) {
        ob_start("ob_gzhandler");
        $this->oh->setContentType(\reqc\MIME_TYPES["HTML"]); 

        $this->theme->title = $this->title;
        $this->theme->setContent($content ?? $this->content);
        echo $this->theme;

        if(inix::get("mode") == "production") {
            ob_end_flush();
            flush();
        }
    }

    public function delete() {
        DB::Query("DELETE FROM `pages` WHERE `id`=?", [ $this->id ]);
    }

    static public function create($path, $title, $type, $content = "{}", $siteId = null) {
        if(!$siteId && !defined("SITE")) throw new \Exception("No siteID Present");

        DB::Query("INSERT INTO `pages` (`uri`,`title`,`type`,`content`, `siteID`,`dateCreated`) VALUES (?, ?, ?, ?, ?, NOW())", [
            $path,
            $title,
            $type,
            $content,
            $siteId ?? SITE["ID"]
        ]);

        return DB::LastID();
    }
}