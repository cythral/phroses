<?php

namespace Phroses;

use \SessionHandlerInterface;
use \Phroses\Database\Database;
use \Phroses\Database\Builders\{ SelectBuilder, ReplaceBuilder, DeleteBuilder };

class Session implements SessionHandlerInterface {
    static private $run = false;
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    static public function start() {
        if(self::$run) return;

        session_set_save_handler(new Session, true);
        session_start();

        self::$run = true;
        return session_id();
    }

    static public function end() {
        session_destroy();
        session_write_close();
    }

    public function open($a, $b) { 
        return true; 
    }

    public function close() { 
        return true; 
    }

    public function read($id): string {
        return ((new SelectBuilder)
            ->setTable("sessions")
            ->addColumns([ "data" ])
            ->addWhere("id", "=", ":id")
            ->execute([ ":id" => $id ])
            ->fetchColumn()) ?? "";
    }

    public function write($id, $data): bool {
        $this->db->replace("sessions", [ "id" => $id, "data" => $data ]);
        return true;
    }

    public function gc($max) {
        (new DeleteBuilder)
            ->setTable("sessions")
            ->addWhere("TIMESTAMPDIFF(second, `date`, NOW())", ">", ":max")
            ->execute([ ":max" => $max ]);
        return true;
    }

    public function destroy($id) {
        return ((new DeleteBuilder)
            ->setTable("sessions")
            ->addWhere("id", "=", ":id")
            ->execute([ ":id" => $id ])
            ->rowCount() > 0);
    }
}


