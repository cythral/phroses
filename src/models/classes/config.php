<?php
namespace Phroses;

class Config {
  static private $loaded = false;
  static private $config = [];
  
  static public function Load() {
    if(self::$loaded) return true;
    if(!file_exists(ROOT."/phroses.conf")) return false;
    
    self::$config = parse_ini_file(ROOT."/phroses.conf", true);
    define("Phroses\CONF", self::$config); // backwards compatibility
    self::$loaded = true;
    return true;
  }
  
  static public function Get($config) {
    return self::$config[$config] ?? null;
  }
  
  static public function Set($config, $value) {
    self::$config[$config] = $value;
    
  }
}