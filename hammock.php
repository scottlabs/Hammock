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
error_reporting(E_ALL - E_NOTICE);
session_start();
class Hammock {
	private $errors = array();
	public $params = array();
	public $view = null;
	public $post = array();
	public $get = array();
	public $plugins = array();
	public $time;
	public $model;
	function __construct() {
		//print_r($_SESSION);exit;
		
		$this->time = microtime();
		if (! $this->load('../app/config/envir.php')) {
			define('environment','development');
		}
		
		if ($this->load('../app/config/database.php')) {
			$this->load('../hammock/database.php');	
		}
		

		// are we submitting a form? check the session key, to prevent repeated submissions
		$this->post = $this->sanitize($_POST);
		$this->get = $this->sanitize($_GET);
		$this->files = $_FILES;
		//$this->file = $_FILES;
		
		if (in_array(environment,array('staging','development','local'))) {
			error_reporting(E_ALL - E_NOTICE);
		} else {
			error_reporting(0);
		}

	}
	
	function attachPlugins(&$obj) {
		foreach($this->plugins as $i=>$plugin ) {
			if (! class_exists($plugin)) {
				if (! $this->load('../app/plugins/'.$plugin.'/'.$plugin.'.php')) {
					$this->load('../hammock/plugins/'.$plugin.'.php');
				}
			}
			if (class_exists($plugin) && ! method_exists($obj,$plugin) ) { 
				$obj->$plugin = new $plugin($this,$obj);
				
				if (! isset($obj->$plugin->_extendedCorrectly)) {
					echo "Looks like you didn't extend the plugin '".$plugin."' correctly; please try again.";exit;
				}
			}
		}
	}

		
	function run() {

		$this->load('../hammock/settings.php');
		$this->load('../hammock/routes.php');
		$this->load('../app/config/routes.php');
		$this->load('../hammock/config.php');
		$this->load('../app/config/settings.php');

		define('html_directory',substr($_SERVER['SCRIPT_NAME'],0,-10));
		define('domain',html_directory.'/');		

				
		$this->config = Config::data();
		
		$request_uri = substr($_SERVER['REQUEST_URI'],strlen(html_directory));
		$this->request_uri = $request_uri;
		$url = Router::getConnection($request_uri);		
		$vars = $url;
		$action = $vars['action'];

		require 'functions.php';
		
		$this->load('../hammock/controller.php');
		$this->load('../app/controllers/'.$vars['controller'].'.php');
		
		if (class_exists($vars['controller'])) {			
			$controller = new $vars['controller']($this);
			if (! isset($controller->_extendedCorrectly)) {
				echo "Looks like you didn't extend the controller '".$vars['controller']."' correctly; please try again.";exit;
			}		
			if (substr($vars['controller'],-1)=='s') { $model = substr($vars['controller'],0,-1); }
			else { $model = $vars['controller']; }

			$this->load('../hammock/model.php');
			if ($handle = opendir('../app/models')) {
			    while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						$this->load('../app/models/'.$file);
					}
					
			    }
				
				//$this->load('../app/models/'.$model.'.php');
			    closedir($handle);
			}							

			if (class_exists($model.'Model')) {
				$model = $model.'Model';
				$controller->model = new $model(null,$this);
				$this->model = $controller->model;
			}

			////////////////// Load Plugins ///////////////////
			$this->plugins = (isset($controller->plugins)) ? explode(',',$controller->plugins) : array();
			$this->load('../hammock/plugin.php');
			foreach($this->plugins as $i=>$plugin ) {
				if (! $this->load('../app/plugins/'.$plugin.'/'.$plugin.'.php')) {
					$this->load('../hammock/plugins/'.$plugin.'.php');
				}
				

				if (class_exists($plugin) && ! method_exists($controller,$plugin) ) { 
					$plugin_settings = (isset($controller->plugin_settings) && isset($controller->plugin_settings[$plugin])) ?
														$controller->plugin_settings[$plugin] : array();
					$controller->$plugin = new $plugin($this,$controller,$plugin_settings);
					
					if (! isset($controller->$plugin->_extendedCorrectly)) {
						echo "Looks like you didn't extend the plugin '".$plugin."' correctly; please try again.";exit;
					}
				}
			}
					//print_r($vars);exit;			
			if (method_exists($controller,$action)) {
				$controller->params = $vars['params'];
				$redirect = false;
				if (method_exists($controller,'before')) { $redirect = $controller->before(); }
				if (! $redirect) {
					//echo 'before';exit;
					$controller->$action();
					//if (! isset($vars['params'])) { $vars['params'] = array(); }
					$vars['data'] = array();
					$vars['data']['pageTitle'] = $vars['controller']. ': '.$vars['action'];	
					if (is_array($controller->data)) { $vars['data'] = array_merge($vars['data'],$controller->data); }
					
					$vars['view'] = (isset($controller->view)) ? $controller->view : $action;						

					echo $this->view($controller,$vars,$controller->layout,$controller->controller_layout);
				}
			} else if (method_exists($controller,'before')) { 

				$controller->before(); 
			} else {

				$this->throwError("No Action Matches That Route");
			}
			
		} else {
			
			$this->throwError("No Controller Found");
			
			$this->load('../app/controllers/fourohfour.php');
			$controller = new fourohfour($this);

			$this->load('../hammock/model.php');
			$this->load('../app/models/fourohfour.php');			
			$controller->model = new fourohfourModel(null,$this);

			////////////////// Load Plugins ///////////////////
			$this->plugins = (isset($controller->plugins)) ? explode(',',$controller->plugins) : array();
			$this->load('../hammock/plugin.php');

			foreach($this->plugins as $i=>$plugin ) {

				if (! $this->load('../app/plugins/'.$plugin.'/'.$plugin.'.php')) {
					$this->load('../hammock/plugins/'.$plugin.'.php');
				}
				if (class_exists($plugin) && ! method_exists($controller,$plugin) ) { 
					$controller->$plugin = new $plugin($this,$controller);
					
					if (! isset($controller->$plugin->_extendedCorrectly)) {
						echo "Looks like you didn't extend the plugin '".$plugin."' correctly; please try again.";exit;
					}
				}
			}
			
			
			echo $this->view('controller',array('view'=>'404','controller'=>'application'),false);
		}
		
		
		
		
	}
	

	
	function __destruct() {
		
		if ($this->config['benchmark']) {
			$time = number_format(microtime()-$this->time,15);
			echo '<hr class="c" style="margin-top: 150px;"/>';
			echo '<div id="totaltime">PHP load time: '.$time.' seconds</div>';
		}
		if (count($this->errors)) { $this->handleErrors(); }
	}
	
	function view($controller,$vars=array(),$headers=true,$controller_headers=null) {

		$data = (isset($vars['data'])) ? $vars['data'] : null ;

		if (gettype($controller)!='string') {
			$data['controller'] = get_class($controller);
		}
		
		$content = $this->returnLoad('../app/views/'.$vars['controller'].'/'.$vars['view'].'.php',
									array('allowNull'=>false,'data'=>$data,'lookForDefault'=>true));

		if($controller_headers===null && $headers) {
			$controller_headers = true;
		} 
		if ($controller_headers) {
			$content = $this->returnLoad('../app/views/'.$vars['controller'].'/layout.php',
										array('content'=>$content,'data'=>$data));

		}
		
		if ($headers) {

			$content = $this->returnLoad('../app/views/application/layout.php',
										array('content'=>$content,'data'=>$data));
		}
		return $content;		
	}
	
	
	function returnLoad($file,$opts) {
		$lookForDefault = (isset($opts['lookForDefault'])) ? $opts['lookForDefault'] : false;

		if ($this->exists($file)) {
			$content = (isset($opts['content'])) ? $opts['content'] : null;
			ob_start();
			$this->load($file,$opts);
			$content = ob_get_contents();
			ob_end_clean();
			return $content;

		} else if ($lookForDefault) {
			$file = explode('/',$file);
			array_pop($file);
			$file[] = 'default.php';
			$file = implode('/',$file);
			if ($this->exists($file)) {			
				$content = (isset($opts['content'])) ? $opts['content'] : null;
				ob_start();
				$this->load($file,$opts);
				$content = ob_get_contents();
				ob_end_clean();
				return $content;
			}
		} else if (isset($opts['allowNull']) && $opts['allowNull']) {
			$this->throwError("Missing View File");
		} else {
			return (isset($opts['content'])) ? $opts['content'] : null;
		}
	}
	
	
	
	function load($file,$opts=array()) {
		if (isset($opts)) { 
			foreach($opts as $a=>$v){
				if (is_array($v)) {
					foreach($v as $aa=>$vv) { $$aa = $vv; }
				} else {
					$$a = $v;
				}
			} 
		}
		if (! isset($this->dir)) { $this->dir = dirname(__FILE__).'/'; }
		$file = $file;

		if ($this->exists($file)) { require $file; return true; }
		else { return false; }
	}
	function exists($file) {
		return file_exists($file);
	}
	function sanitize($input) {
		if (is_array($input)) {
			$sanitize = array();			
			foreach($input as $key=>$val) {
				$sanitize[$key] = $this->sanitize($val);
			}
			return $sanitize;
		} else {
			return htmlspecialchars($input); // this is where sanitizing would go
		}
	}
	public function throwError($error) {
		if (is_array($error)) {
			$error = print_r($error,true);
		}
		$this->errors[] = $error;
	}
	function handleErrors() {
		$errors = implode("<br />",$this->errors);

		if ($this->config['display_errors']) {
			echo "<div style='color: red;'><hr />".$errors."</div>";
		}
		if ($this->config['mail_errors']) {
			mail($this->config['mail_errors'],'Errors',$errors);
		}
		if ($this->config['log_errors']) {
			// log error goes here
		}
		
		
	}
}
$Hammock = new Hammock();
$Hammock->run();