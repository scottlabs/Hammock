<?php
$file = pathinfo($_SERVER['REQUEST_URI']);
$path = substr($file['dirname'], 11);
$file = array_shift(explode('?',$file['basename']));
if (file_exists($file)) {
	require '../../hammock/plugins/cssmin.php';

	$css = file_get_contents($file);
	
	$css = CssMin::minify($css, array
	        (
	        "remove-empty-blocks"           => true,
	        "remove-empty-rulesets"         => true,
	        "remove-last-semicolons"        => true
	        ));
	header ("Content-type: text/css");	
	echo $css;
	//file_put_contents("path/to/target.css", $css);


} else {
	require '../../app/views/application/404.php';
}