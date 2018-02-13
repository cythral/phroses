<?php

namespace Phroses;

class Session implements \SessionHandlerInterface {
  static private $run = false;
  
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
  
  public function open($a, $b) { return true; }
  public function close() { return true; }
  
  public function read($id) {
    $data = DB::Query("SELECT `data` FROM `sessions` WHERE `id`=?", [ $id ]);
    return ($data) ? $data[0]->data : '';
  }
  
  public function write($id, $data) {
    DB::Query("REPLACE INTO `sessions` (`id`, `data`) VALUES (?, ?)", [ $id, $data ]);
    return true;
  }
  
  public function gc($max) {
    DB::Query("DELETE FROM `sessions` WHERE TIMESTAMPDIFF(second, `date`, NOW()) > $max");
    return true;
  }
  
  public function destroy($id) {
    DB::Query("DELETE FROM `sessions` WHERE `id`=?", [ $id ]);
    return true;
  }
}


