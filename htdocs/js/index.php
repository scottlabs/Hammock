<?php
$file = pathinfo($_SERVER['REQUEST_URI']);
$path = substr($file['dirname'], 11);
$file = array_shift(explode('?',$file['basename']));
if (file_exists($file)) {
	require '../../hammock/plugins/jsmin.php';

	$charset = "utf-8";
	$mime = "text/javascript";

	header("Content-Type: $mime;charset=$charset");
	// Output a minified version of JavaScript file
	$file = file_get_contents($file);
	$file = JSMin::minify($file);
	echo $file;
} else {
	require '../../app/views/application/404.php';
}