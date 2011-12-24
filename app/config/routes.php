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
	Router::connect('/', array('controller' => 'clear'));
	Router::connect('/blog/{post}', array('controller' => 'blog', 'action' => 'index'));