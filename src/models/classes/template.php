<?php

namespace Phroses;

class Template {
	protected $tpl;
    protected $filters = [];
	protected $vars = [];
	static public $defaultFilters = [];
    
    public function __construct(string $tpl, array $vars = []) {
		if(is_file($tpl) && !file_exists($tpl)) throw new \Exception("Bad Parameter \$tpl");
        $this->tpl = (is_file($tpl)) ? file_get_contents($tpl) : $tpl;
		$this->vars = $vars;
		$this->Process();
    }
	
	protected function Filter(string $name, callable $filter) {
        $return = "";
        $this->tpl = preg_replace_callback("/<\{{$name}((:[a-zA-Z0-9_\-=]+)+)?\}>/", function($matches) use (&$return, $filter) {
            array_shift($matches);
            ob_start();
            $return = $filter->call($this, ...((isset($matches[0])) ? (explode(":", substr($matches[0], 1))) : []));
            return trim(ob_get_clean());
        }, $this->tpl);
        
        return $return;
    } 
    
    protected function Process() {
		foreach(self::$defaultFilters as $key => $filter) $this->Filter($key, $filter);
        foreach($this->filters as $key => $filter) $this->Filter($key, $filter);
    }
	
	public function __set($key, $val) {
		$this->tpl = str_replace("<{var:{$key}}>", $val, $this->tpl);
	}
	
	public function __toString() {
		return $this->tpl;
	}
}


Template::$defaultFilters = [
	"include" => function($file) { 
		if(file_exists("{$file}.php")) include "{$file}.php"; 
	},
	
	"var" => function($var) {
		if(array_key_exists($var, $this->vars)) echo $this->vars[$var];
		else echo "<{var:{$var}}>";
	}
];