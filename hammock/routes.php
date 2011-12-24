<?php
/*******************************************************************
*
*	Hammock
*
*
*	Hammock is free software
*
*	Free for commercial use, free for redistribution and modification
*
*	It is an extremely lightweight framework, loosely modeled after 
* 	the Rails framework
*
*	It abstracts the database layer and routing.
*
*	2010
*
*
*******************************************************************/
class Router {
	public $routes = array();
	public $connections = array();
	function connect($url, $connection) {
		$this->connections[] = $connection;
		$count = count($this->connections) - 1;
		
		//if (substr($url,-1))
		$url = explode('/',substr($url,1));
		//print_r($url);exit;
		foreach($url as $i=>$c) {
			if (! isset($this->routes[$count])) { $this->routes[$count] = array(); }
			$this->routes[$count][$i] = $c;
		}

	}
	
	function getConnection($url) {
		$index = null;
		if (substr($url,-1)=='/') { $url = substr($url,0,-1); }

		//$url = str_replace('//','/',$url); // get rid of double slashes
		$url = explode('/',array_shift(explode('?',substr($url,1))));
		
		
		
		$count = count($url);
		$match = 0;
		
		$urlIndex = false;
		$params = array();
		foreach($this->routes as $index=>$route) {
			$wildcard = false;
			if ($urlIndex===false) {
				
				$match = 0;
				foreach($route as $i=>$piece) {
					if (strpos($piece,'{')!==false && strpos($piece,'}') !== false ) {
						$piece = substr($piece,1,-1);
						$wildcard = true;
					}
					
					if ($piece==$url[$i] || $wildcard) {
						$match++;
						if ($wildcard) {
							$params[$piece] = $url[$i];
						}
					} else {
						$match = 0;
					}
				}
				if ($match==count($url) && $match == count($route)) {
					$urlIndex = $index;
				}
			}
		}
		if ($urlIndex!==false) {
			if (isset($this->connections[$urlIndex]['controller'])) {
				$controller = $this->connections[$urlIndex]['controller'];
			} else if ($this->params['controller']) {
				$controller = $this->params['controller'];
			}
			
			
			if (isset($this->connections[$urlIndex]['action'])) {
				$action = $this->connections[$urlIndex]['action'];
			} else if ($params['action']) {
				$action = $params['action'];
			} else {
				$action = 'index';
			}
			
			if (! count($params) && $url[2]) { $params['id'] = $url[2]; }
		} else {
			
			$controller = array_shift($url);
			$action = (isset($url[0])) ? array_shift($url) : 'index';
			
			$params = $url;
		}
		return array('controller'=>$controller,'action'=>$action,'params'=>$params);
		
	}
}