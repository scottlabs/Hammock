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
class controller {
	public $_extendedCorrectly = true;
	public $layout = true;
	
	function __construct(&$hammock) {
		$this->app = $hammock;
		$this->post = $this->app->post;

		$this->get = $this->app->get;
		$this->files = $this->app->files;
	}
	
	function view($page) {
		return $this->app->view($this,array(
			'data'=>$this->data,
			'controller'=>get_class($this),
			'view'=>$page,
			),false);
	}
}