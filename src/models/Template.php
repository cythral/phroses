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
    
    protected function Process() {
        $callback = function($matches) {
            array_shift($matches);
            $filter = $matches[0];
            $mods = substr($matches[1], 2);
            ob_start();
            if(isset(self::$filters[$filter])) {
                $filter = self::$filters[$filter];
                $filter->call($this, ...explode("::", $mods));
                return trim(ob_get_clean());
            }
        };
        
        $this->tpl = preg_replace_callback("/<{([a-z]+)(::((?!}>).)*)?}>/is", $callback->bindTo($this), $this->tpl);
        if(preg_match("/<{([a-z]+)(::((?!}>).)*)?}>/is", $this->tpl)) $this->Process(false);
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