<?php
/**
 * This file defines a number of utility functions
 */

namespace Phroses;

use \reqc;
use \DOMDocument;
use \RecursiveIteratorIterator as FileIterator;
use \RecursiveDirectoryIterator as DirectoryIterator;

/**
 * Return a list of files
 */
function fileList($dir) : array {
    if(file_exists($dir)) {
        $files = [];
        $iterator = new FileIterator(new DirectoryIterator($dir), FileIterator::CHILD_FIRST);
        foreach($iterator as $file) {
            if(!substr($file, strrpos($file, ".")+1)) continue;
            $files[] = $file;
        }
        return $files;
    }
    return [];
}

/**
 * readfile() + add caching headers
 */
function readfileCached($file) {
    if(!file_exists($file)) return false;
    
    $lastmodified = filemtime($file);
    $gmt_mtime = gmdate('r', $lastmodified);
    $etag = md5_file($file);
    
    header("cache-control: public, max-age=86400");
    header("etag: $etag");
    header_remove("pragma");
    header_remove("expires");
    
    if(isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
        if (str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $etag) {
            http_response_code(304);
            die;
        }
    }
    
    die(readfile($file));
}

/**
 * Remove a directory and all of its contents.
 */
function rrmdir($src) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if (is_dir($full)) {
                rrmdir($full);
            } else if(!unlink($full)) return false;
        }
    }
    closedir($dir);
    if(!rmdir($src)) return false;
  	return true;
}

/**
 * Copy a directory and all of its contents to another directory
 */
function rcopy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                rcopy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

/**
 * Add functionality based on request method.  Used in routing controllers
 * that multiple methods are pointed to.
 */
function handleMethod(string $method, callable $handler, array $filters = []) {
    if(reqc\TYPE == "cli") return;
    if(strtolower(reqc\METHOD) != strtolower($method)) return;
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

/**
 * Shorthand for outputting a json error if an expression evaluates true
 */
function mapError(string $error, bool $check, ?array $extra = [], int $code = 400) {
	if($check) {
        (new reqc\JSON\Server())->send(array_merge(["type" => "error", "error" => $error], $extra), $code);
    }
}

/**
 * Array utility for checking if all keys exist
 */
function allKeysExist(array $keys, array $array) {
    return count(array_diff($keys, array_keys(array_intersect_key(array_flip($keys), $array)))) == 0;
}

/**
 * Array utility for checking if all keys exist
 */
function allKeysDontExist(array $keys, array $array) {
    return count(array_diff(array_intersect_key(array_flip($keys), $array), $keys)) == 0;
}

/**
 * Array utility for safely checking if a value in an array is present and equals a value
 */
function safeArrayValEquals(array $haystack, string $needle, string $equal) {
    return array_key_exists($needle, $haystack) && $haystack[$needle] == $equal;
}

/**
 * Parse a filesize string like 12M to bytes
 */
function parseSize($size) {
    $unit = strtolower(substr($size, -1, 1));
    return (int) $size * pow(1024, stripos('bkmgtpezy', $unit));
}

/**
 * Return the output of an include
 * 
 * @param string $include the file to include
 * @return string the output of the included file
 */
function getIncludeOutput(string $include): ?string {
    if(!file_exists($include)) return null;

    ob_start();
    include $include;
    return trim(ob_get_clean());
}

/**
 * Returns array keys whose values equal true
 * 
 * @param array $array the input array
 * @return array an array of keys whose values were equal to true
 */
function trueArrayKeys(array $input): array {
    return array_keys(array_filter($input, function($value) { return $value; }));
}

/**
 * Gets the html contents inside a tagname
 * 
 * @param string $html the source html to search
 * @param string $tag the tagname to return the contents of
 * @return string the contents of the tagname that was searched or null if that tag does not exist
 */
function getTagContents(string $html, string $tag): ?string {
    $src = new DOMDocument;
    $dest = new DOMDocument;
    @$src->loadHTML($html);

    $body = $src->getElementsByTagName($tag)->item(0);
    if(!$body) return null;
    
    foreach($body->childNodes as $child) {
        $dest->appendChild($dest->importNode($child, true));
    }
    
    return trim($dest->saveHTML());
}

/**
 * Strips strings of phyrex template fields
 * 
 * @param string $input the input string
 * @return string the input string without phyrex template fields
 */
function stripPhyrexFields(string $input): string {
    return preg_replace("/<{([a-z]+)(::((?!}>).)*)?}>/is", "", $input);
}

/**
 * Utility for checking to see if a string starts with another string
 * 
 * @param string $string the haystack / input string
 * @param string $start the string to check for at the beginning of $string
 * @return bool true if $string starts with $start, false if not
 */
function stringStartsWith(string $string, ?string $start): bool {
    return $start != null && substr($string, 0, strlen($start)) == $start;
}

/**
 * Prints a string with a newline character at the end
 */
function println(string $string) {
    echo $string.PHP_EOL;
}