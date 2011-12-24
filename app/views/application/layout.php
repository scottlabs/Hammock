<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"> 
<html lang="en">
<head>
<title><?php echo $pageTitle ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="keywords" content="" />
<meta http-equiv="description" content="" />
<?php if(!empty($meta_rss)) echo $meta_rss; ?>

<link rel="icon" type="image/ico" href="<?php echo domain ?>favicon.ico" />

<?php stylesheet('reset-min.css'); ?>
<?php stylesheet('style.css'); ?>
<!--[if IE]>
<?php stylesheet('ie.css'); ?>
<![endif]-->
<!--[if lt IE 8]>
<?php stylesheet('ie7.css'); ?>
<![endif]-->
<?php javascript('jquery.1.5.1.js'); ?>
</head>
<body>
	<?php echo $content ?>
</body>
</html>