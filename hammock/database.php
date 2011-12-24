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

class database {
	private $db;
	private $prefix;
	private $model;
	public $connect_error;
	private $queries = array();
	
	function __construct($app=null,$cred=null) {
		if (class_exists('DATABASE_CONFIG')) { $config = new DATABASE_CONFIG(); }
		$envir = environment;
		$cred = ($cred) ? $cred : $config->$envir;

		$this->prefix = (isset($cred['prefix'])) ? $cred['prefix'] : null;
		//print_r($cred);		

		$this->db = new mysqli($cred['host'],$cred['login'],$cred['password']);
		if ($this->db->connect_error) {
			$this->connect_error = 'Connect Error (' . $this->db->connect_errno . ') '. $this->db->connect_error;
		} else {			
			mysqli_select_db($this->db, $cred['database']);
			if ($this->db->connect_error) {
				$this->connect_error = 'Connect Error (' . $this->db->connect_errno . ') '. $this->db->connect_error;
			}
		}
		
		//////// SET DB TIME ZONE
		
		$this->db->query("SET time_zone = '-4:00'"); // set to Eastern
	}
	function put($model) {
		$table = $this->prefix.substr(get_class($model),0,-5).'s';
		$keys = array();
		$vals = array();
		foreach($model->schema as $name=>$val) {

			if ($model->$val) {
				$keys[] = "`$val`";
				$vals[] = "'".$this->sanitize($model->$val)."'";
			}
		}
		$query = "INSERT INTO $table (".implode(", ",$keys).") VALUES (".implode(", ",$vals).") ";
		return $this->query($query,'insert_id');
	}
	function exists($model,$key,$val) {
		$table = $this->prefix.substr(get_class($model),0,-5).'s';
		$query = "SELECT count(1) as cnt FROM $table WHERE `$key` = '$val' ";
		//echo $query;
	}
	
	function get($model,$key,$val,$opts=array()) {

		$table = $this->prefix.substr(get_class($model),0,-5).'s';
		if (is_array($val)) { $val = array_shift($val); }
		$val = $this->sanitize($val); // sanitize
		
		if (! is_array($val) && $key && $val) {

			$where = array("`".$key."` = '".$val."'");
		} else {
			$where = array();
		}
		/*
		if ($val=='all') {
			$where = array();
		} else if (! is_array($val)) {
			$where = array("`".$key."` = '".$val."'");
		}*/
		if (isset($opts['where']) && is_array($opts['where'])) {
			foreach($opts['where'] as $k=>$v) {
				$v = $this->sanitize($v); // sanitize
				if ($opts['where_type']=='like') {
					if (substr(trim($v),0,1)=='(' && substr(trim($v),-1)==')') {
						$where[] = "`".$k."` = ".$v."";
					} else {
						$where[] = "`".$k."` LIKE '".$v."%'";
					}
				} else {
					$where[] = "`".$k."` = '".$v."'";
				}
			}
		}
		$where = (count($where)) ? "WHERE ".implode(" AND ",$where) : null;
		

		if ($opts['return_type']) {
			$return_type = $opts['return_type'];
		} else {
			$return_type = ($where) ? 'row' : 'assoc';
		}
		
		if (isset($opts['order'])) {
			$order = $opts['order'];
		} else {
			$order = " ORDER BY added DESC ";
		}
		
		if (isset($opts['limit'])) {
			$page = (isset($opts['page'])) ? $opts['page']*$opts['limit'] : '0';
			$limit = " LIMIT ".$page.", ".$opts['limit'];
		} else {
			$limit = null;
		}
		
		if (isset($opts['pagination'])) {
			$select = "SELECT SQL_CALC_FOUND_ROWS * ";
		} else {
			$select = "SELECT * ";
		}

		
		$query = "$select FROM $table $where $order $limit";
		//echo "<br />".$query."<br />";
		$return = array('data'=>$this->query($query,$return_type));
		//print_r($return);exit;
		$return['error'] = $this->error();

		if ($opts['pagination']) {
			$return['totalRows'] = $this->query("SELECT FOUND_ROWS() AS `found_rows`;",'single');
		}
		return $return;
	}
	
	function remove($model) {
		$table = $this->prefix.substr(get_class($model),0,-5).'s';
		$wheres = array();
		foreach($model->schema as $val) {
			$wheres[] = "`".$val."` = '".$model->$val."' ";
		}
		$query = "UPDATE $table SET deleted = 1 WHERE ".implode(" AND ",$wheres);
		return $this->query($query);
	}
	
	function update($model) {
		$table = $this->prefix.substr(get_class($model),0,-5).'s';
		$updates = array();
		foreach($model->schema as $val) {
			if ($val!= 'id') {
				$updates[] = "`".$val."` = '".$model->$val."' ";
			}
		}
		$updates = implode(',',$updates);
		$query = "UPDATE $table SET ".$updates." WHERE id = '".$model->id."' ";
		return $this->query($query);
	}
	
	function query($query,$return_type='none') {
		$this->queries[] = $query;
		//echo '<div style="display:none;">'.$query.'</div>';
		//$query = implode("FROM ".$this->prefix,explode("FROM",$query));
		//$query = implode("JOIN ".$this->prefix,explode("JOIN",$query));
		if ($result = $this->db->query($query)) {
			switch($return_type) {
				case 'assoc' :
					$data = array();
			    	while ($row = $result->fetch_assoc()) {
						$data[] = $row;
					}
				break;
				case 'single' :
					$data = $result->fetch_assoc();
					if (is_array($data)) { $data = array_pop($data); }
				break;
				case 'row' :
					$data = $result->fetch_assoc();
				break;
				case 'insert_id' :				
					return $this->db->insert_id;
				break;
				case 'none' :
					$data = null;
				break;
			}

			return $data;
		} else { return array('errno'=>$this->db->errno); }	
	}
	
	function returnQueries() {
		return $this->queries;
	}
	
	function sanitize($val) {
		if (is_array($val)) {
			foreach($val as &$v) {
				$v = $this->sanitize($v);
			}
			return $val;			
		} else {
			return mysqli_real_escape_string($this->db,$val);
		}
	}
	
	function error() {
		return $this->db->error;
	}
	
}