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
class model {
	public $_extendedCorrectly = true;
	private $validationErrors = array();
	
	function __construct($params=array(),&$app=null) {

		if (! $app) { // that means we didn't pass app in. take params to be app
			$app = $params;
		}

		if (count($params)) {
			foreach($params as $name=>$val) {
				if (in_array($name,$this->schema)) {
					$this->$name = $val;
				}
			}
		}
		$this->db = new database($app);
		if ($this->db->connect_error && $app) {
			$app->throwError($this->db->connect_error);
		}
		if (method_exists($app,'attachPlugins')) {
			$app->attachPlugins($this);
		}
		
	}
	
	
	
	public function __call($name, $arguments) {
        // Note: value of $name is case sensitive.
		if (substr($name,0,6)=='findBy') {
			$args = array();
			foreach($arguments as $n=>$a) {
				$args[$n] =$a;
			}
			$args = $arguments[1];	
			$args['key'] = strtolower(substr($name,6));
			$return = $this->find($arguments[0],$args);
			return $return;
		}
    }

	function checkValidation() {
		foreach($this->validate as $key=>$v) {
			foreach($v as $rule=>$throwaway) {
				switch($rule) {
					case 'notempty' :
						if (! isset($this->$key) || ! $this->$key) {
							$this->validationErrors[] = "Missing Key ".$this->$key;
						}
					break;
					case 'unique' :
						$this->db->exists($this,$key,'t');
						if (! isset($this->$key) || ! $this->$key) {
							$this->validationErrors[] = "Missing Key ".$this->$key;
						}						
					break;
				}
			}
		}
		if (count($this->validationErrors)) {
			return false;
		} else {
			return true;
		}
	}
	
	function save() {
		if (method_exists($this,'beforeSave')){
			$this->beforeSave();
		}
		if ($this->checkValidation() ) {
			if ($this->id) {
				$result = $this->db->update($this);
			} else {
				$result = $this->db->put($this);
				$this->id = $result;
			}
			return $result;
		} else {
			return $this->validationErrors;
		}
	}
	
	function update($params) {
		foreach($params as $name=>$val) {
			if (in_array($name,$this->schema)) {
				if ($name=='password'&&!$val) {
					// do nothing
				} else {
					$this->$name = $val;					
				}

			}
		}
	}
	
	function remove() {
		return $this->db->remove($this);
	}
	

	function findLike($opts=array()) {
		$opts['where_type'] = 'like';
		$opts['return_type'] = 'assoc';
		$vars = array('opts'=>$opts,'val'=>'all');
		$this->find('all',$opts);
	}
	
	function find($val,$vars=null) {		
		if (is_array($vars) && isset($vars['key'])) {
			$key = strtolower($vars['key']);
		} else {
			$key = null;
		}
		if (isset($vars['opts']) && is_array($vars)) {
			$val = $vars['val'];
			if (is_array($val)) { $val = array_shift($val); }
		}

		if (is_array($val)) { $val = array_shift($val); }


		$result = $this->db->get($this,$key,$val,$vars);

		$data = $result['data'];

		if ($result['totalRows']) { $this->totalRows = $result['totalRows']; }


		if ($val=='all') {
			$array = substr(get_class($this),0,-5).'s';
			$arr = array();
			if (count($data)) { 
				foreach($data as $a=>$v) {
					$arr[$a] = $v;
				}
			}
			$this->$array = $arr;
		} else {
			if (count($data)) { 
				foreach($data as $a=>$v) {
					$this->$a = $v;
				}
			}
		}
		return $data;
		
	}
	
	public function __get($name) {

		$association = $this->association;
		if ($association['hasMany']) {
			$hasMany = $association['hasMany'];
			$name = substr(ucfirst($name),0,-1);
			if ($hasMany[$name]) {
				$modelName = strtolower($name).'Model';
				$this->children = new $modelName();
				$action = 'findBy'.$hasMany[$name]['foreignKey'];
				//echo "<pre>";
				//print_r($this);
				//echo $this->id;exit;
				$opts = array('return_type'=>'assoc','where'=>array('deleted'=>'0'));				

				$children = $this->children->$action($this->id,$opts);
				$this->$name = $children;
				return $this->$name;
			}
		}
    }

	/////////////////// THIS SHOULDNT BE HERE
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
		$file = $this->dir.$file;

		if (file_exists($file)) { require $file; return true; }
		else { return false; }
	}
	
	public function query($query,$return_type)
	{
		return $this->db->query($query,$return_type);
	}

}