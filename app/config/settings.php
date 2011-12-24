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
	define ('phpsalt','234@(*erw0d_9isf+odsjio1$$2e9!_0uqw'); // make this something wild
	date_default_timezone_set('America/New_York');
	
	/****** Do we want to display errors to the end user? Recommended false in production systems *******/
	if (environment=='development'||environment=='local') {
		Config::display_errors(false);
	} else {
		Config::display_errors(false);	
	}
	
	/****** Do we want to email errors? Recommended true in production systems *******/
	Config::mail_errors(false);
	
	/****** Do we want to log errors? Recommended true in production systems *******/
	Config::log_errors(true);
	
	Config::benchmark(false);