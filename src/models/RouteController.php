<?php

namespace Phroses;

class RouteController {
    private $routes = [];
    private $rules = [];
    private $ruleArgs = [];
    private $cascade;
       
    const METHODS = [
        "GET",
        "POST",
        "PUT",
        "PATCH",
        "DELETE"
    ];
    
    public function __construct($defaultRoute = Phroses::RESPONSES["PAGE"][200]) {
        $this->cascade = new Cascade($defaultRoute);
    }
    
    public function addRuleArgs(array $args) {
        $this->ruleArgs = array_merge($this->ruleArgs, $args);
    }
    
    public function addRoute(Route $route) {
        $methods = ($route->method) ? [ strtoupper($route->method) ] : self::METHODS;
        
        foreach($methods as $method) {
			if(!isset($this->routes[$method])) $this->routes[$method] = [];
			$this->routes[$method][$route->response] = $route;
		}
        
        $this->setupRules($route);
    }
    
    private function setupRules(Route $route) {
        $rules = array_map(function($expr) use ($route) { 
            return [ $route->response, $expr ]; 
        }, $route->rules($this->cascade, ...$this->ruleArgs));
        
        $this->rules = array_merge($this->rules, $rules);
    }
}