<?php

namespace Phroses;

class Template {
	protected $tpl;
	protected $vars = [];
	public $arrays = [];
	static public $filters = [];
    
    public function __construct(string $tpl, array $vars = []) {
		if(is_file($tpl) && !file_exists($tpl)) throw new \Exception("Bad Parameter \$tpl");
        $this->tpl = (is_file($tpl)) ? file_get_contents($tpl) : $tpl;
		$this->vars = $vars;
    }
	
	protected function Filter(string $name, callable $filter) {
        $return = "";
        $this->tpl = preg_replace_callback("/<\{{$name}((:[a-zA-Z0-9_\-=<>\'\"@\/ ]+)+)?\}>/", function($matches) use (&$return, $filter) {
            array_shift($matches);
            ob_start();
            $return = $filter->call($this, ...((isset($matches[0])) ? (explode(":", substr($matches[0], 1))) : []));
            return trim(ob_get_clean());
        }, $this->tpl);
        
        return $return;
    } 
    
    protected function Process() {
		foreach(self::$filters as $key => $filter) $this->Filter($key, $filter);
    }
	
	public function Push($array, $value) {
		if(!array_key_exists($array, $this->arrays)) $this->arrays[$array] = [];
		$this->arrays[$array][] = $value;
	}
	
	public function __set($key, $val) {
		$this->vars[$key] = $val;
	}
	
	public function __toString() {
		$this->Process();
		return $this->tpl;
	}
}


Template::$filters = [
	"include" => function($file) { 
		if(file_exists("{$file}.php")) include "{$file}.php"; 
	},
	
	"var" => function($var) {
		if(array_key_exists($var, $this->vars)) echo $this->vars[$var];
	},
	
	"array" => function($key, $tpl) {
		if(array_key_exists($key, $this->arrays) && is_array($this->arrays[$key])) {
			foreach($this->arrays[$key] as $i) {
				if(is_array($i)) {
					$tplc = $tpl;
					foreach($i as $k => $v) $tplc = str_replace("@{$k}", $v, $tplc);
					echo $tplc;
				}
			}
		}
	}
];