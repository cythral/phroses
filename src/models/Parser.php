<?php

/**
 * i put this together in 5 minutes
 * don't judge me
 * @todo conform to rfc2822
 */

namespace Phroses;

/**
 * Description of parser
 *
 * @author talen
 */
class Parser {
    //put your code here
    public $data;
    public $headers = [];
    public $bodies = [];
    public function __construct($data) {
        $this->data = $data;
        $this->parse();
    }
    
    private function parse() {
        $top = strstr($this->data, "\n\n", true);
        $bottom = trim(strstr($this->data, "\n\n"));
        
        $prev = null;
        foreach(explode("\n", $top) as $line) {
            if(substr($line, 0, 1) == "\t") $this->headers[$prev] .= $line;
            else {
                $header = strtolower(strstr($line, ":", true));
                $this->headers[$header] = trim(substr(strstr($line, ":"), 1));
                $prev = $header;
            }
        }
        
        foreach(explode("--", $bottom) as $section) {
            $section_top = strstr($section, "\n\n", true);
            $section_bottom = trim(strstr($section, "\n\n"));
            $section_type = "";
            
            foreach(explode("\n", $section_top) as $line) {
                if(strtolower(strstr($line, ":", true)) == "content-type") {
                    $section_type = trim(substr(strstr(strstr($line, ":"), ";", true), 2));
                    break;
                }
            }
            
            $this->bodies[$section_type] = $section_bottom;
        }
    }
}
