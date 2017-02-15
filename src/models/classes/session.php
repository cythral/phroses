<?php
namespace Phroses;

class Session extends \SessionHandler {
  static private $run = false;
  
  static public function start() {
    if(self::$run) return;

    session_set_save_handler(
      "\Phroses\Session::_open",
      "\Phroses\Session::_close",
      "\Phroses\Session::_read",
      "\Phroses\Session::_write",
      "\Phroses\Session::_destroy",
      "\Phroses\Session::_gc"
    );
    
    session_start();
    self::$run = true;
  }
  
  static public function _open($a, $b) { return true; }
  static public function _close() { return true; }
  
  static public function _read($id) {
    $data = \Phroses\DB::Query("SELECT `data` FROM `sessions` WHERE `id`=?", [ $id ]);
    return ($data) ? $data[0]->data : '';
  }
  
  static public function _write($id, $data) {
    \Phroses\DB::Query("REPLACE INTO `sessions` (`id`, `data`) VALUES (?, ?)", [ $id, $data ]);
    return true;
  }
  
  static public function _gc($max) {
    $max = time() - $max;
    //\Phroses\DB::Query("DELETE FROM `sessions` WHERE `date` > $max");
    return true;
  }
  
  static public function _destroy($id) {
    \Phroses\DB::Query("DELETE FROM `sessions` WHERE `id`=?", [ $id ]);
    return true;
  }
}


