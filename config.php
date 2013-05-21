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
class Config {
	private $internalData = array();
	
	public function display_errors($val) {
		$this->internalData['display_errors'] = $val;
	}
	
	public function mail_errors($val) {
		$this->internalData['mail_errors'] = $val;
	}
	
	public function log_errors($val) {
		$this->internalData['log_errors'] = $val;
	}
	
	public function benchmark($val) {
		$this->internalData['benchmark'] = $val;
	}
	
	public function data() {

		
		if (! isset($this->internalData['display_errors'])) { $this->internalData['display_errors'] = true; }
		if (! isset($this->internalData['mail_errors'])) { $this->internalData['mail_errors'] = false; }
		if (! isset($this->internalData['log_errors'])) { $this->internalData['log_errors'] = true; }
		if (! isset($this->internalData['benchmark'])) { $this->internalData['benchmark'] = true; }
		return $this->internalData;
	}
	
	public function set($key,$val)
	{
		if (! in_array($key,'display_errors','mail_errors','log_errors','benchmarks'))
		{
			$this->internalData[$key] = $val;
		}
	}
	
	public function get($key)
	{
		return $this->internalData[$key];
	}
}