<?php
namespace Phroses;

class Config {
  static private $loaded = false;
  static private $config = [];
  
  static public function Load() {
    if(self::$loaded) return true;
    if(!file_exists(ROOT."/phroses.conf")) return false;
    
    self::$config = parse_ini_file(ROOT."/phroses.conf", true);
    define("Phroses\CONF", self::$config); // keep for backwards compatibility
    self::$loaded = true;
    return true;
  }
  
  static public function Get($config) {
    return self::$config[$config] ?? null;
  }
  
  static public function Set($config, $value) {
    self::$config[$config] = $value;
    self::Array2IniFile(self::$config, ROOT."/phroses.conf");
  }
  
  static public function Array2IniStr(array $config, array $parent = []) : string {
    $out = "";
    foreach($config as $key => $val) {
      if(is_array($val)) {
        $section = array_merge((array)$parent, (array)$key);
        $out .= '[' . join('.', $section) . ']' . PHP_EOL;
        $out .= self::ArrayToConf($val, $section);
      } else $out .= "$key=$val";
    }
    return $out;
  }
  
  static public function Array2IniFile(array $config, string $file) {
    $out = self::Array2IniStr($config);
    file_put_contents($file, $out);
  } 
}