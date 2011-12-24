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
class DATABASE_CONFIG {
	var $development = array(
		'host' => '',
		'login' => '',
		'password' => '',
		'database' => '',
		'prefix' => '',
	);
	
	var $staging = array(
		'host' => '',
		'login' => '',
		'password' => '',
		'database' => '',
		'prefix' => '',
	);	
	
	var $production = array(
		'host' => '',
		'login' => '',
		'password' => '',
		'database' => '',
		'prefix' => '',
	);
	
}