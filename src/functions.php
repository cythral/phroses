<?php

namespace Phroses;

use \reqc;

function FileList($dir) : array {
    if(file_exists($dir)) {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($iterator as $file) {
            if(!substr($file, strrpos($file, ".")+1)) continue;
            $files[] = $file;
        }
        return $files;
    }
    return [];
}

/**
 * @deprecated use reqc's json server instead
 */
function JsonOutput($array, $code = 400) {
    http_response_code($code);
    header("content-type: application/json; charset=utf8");
    die(json_encode($array));
}

/**
 * @deprecated use reqc's json server instead.
 */
function JsonOutputSuccess($array = [ "type" => "success" ], $code = 200) {
  JsonOutput($array, $code);
}


function HandleMethod(string $method, callable $handler, array $filters = []) {
    if(reqc\TYPE == "cli") return;
    
    if(strtolower($_SERVER["REQUEST_METHOD"]) == strtolower($method)) {
        $out = new reqc\JSON\Server();
        
        if(count($filters) > 0) {
            foreach($filters as $k => $f) {                
                if(is_array($f)) {
                    if(!array_key_exists($k, $_REQUEST)) $out->send([ "type" => "error", "error" => "missing_value", "field" => $k ], 400);
                    $val = $_REQUEST[$k];

                    if(isset($f["filter"]) && !filter_var($val, [
                        "url" => FILTER_VALIDATE_URL,
                        "int" => FILTER_VALIDATE_INT,
                        "email" => FILTER_VALIDATE_EMAIL
                    ][$f["filter"]])) $out->send([ "type" => "error", "error" => "bad_filter", "filter" => $f["filter"], "field" => $k ], 400);

                    if((isset($f["size"]["min"]) && strlen($val) < $f["size"]["min"]) ||
                        (isset($f["size"]["max"]) && strlen($val) > $f["size"]["max"])) {
                        JsonOutput([ "type" => "error", "error" => "bad_size", "field" => $k ]);
                    }
                } else if(!array_key_exists($f, $_REQUEST)) $out->send([ "type" => "error", "error" => "missing_value", "field" => $f ]. 400);
            }    
        }
        
        $out->setCode(200);
        $handler($out);
        die;
    }
}

function rrmdir($src) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if ( is_dir($full) ) {
                rrmdir($full);
            }
            else {
                if(!unlink($full)) return false;
            }
        }
    }
    closedir($dir);
    if(!rmdir($src)) return false;
  return true;
}

/**
 * @deprecated use reqc's eventsream server instead
 */
function sendEvent(string $event, array $data) {
  $data = json_encode($data);
  echo "event:$event\ndata:$data\n\n";
  ob_end_flush();
  flush();
}

function ReadfileCached($file) {
    if(!file_exists($file)) return false;
    
    
    $lastmodified = filemtime($file);
    $gmt_mtime = gmdate('r', $lastmodified);
    $etag = md5_file($file);
    
    header("Cache-Control: public");
    header("Last-Modified: $gmt_mtime");
    header("Etag: $etag");
    header_remove("pragma");
    header_remove("expires");
    
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
        if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime || str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $etag) {
            http_response_code(304);
            die;
        }
    }
    
    die(readfile($file));
}

function mapValue($value, $map) {
    foreach($map as $key => $val) {
        if($value == $key) return $val;
    }

    return false;
}