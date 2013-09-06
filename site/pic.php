<?php
/**
 * @copyright 2013 Glen Campbell
 * @license Apache 2.0
 */
require 'lib/pictr.php';
// create a new configuration object
$config = new Pictr\Config();

if (!isset($_GET['id']))
	die("Sorry, you did that wrong");

// get object stuff
$container = $config->Container();
try {
	$obj = $container->DataObject($_GET['id']);
} catch (Exception $e) {
	die("Sorry! That image has expired");
}

if (isset($_POST['bad'])) {
	$obj->delete();
	header('Location: http://'.$config->domain);
	exit;
}
?><!DOCTYPE html>
<html>
<head>
	<title>Pictr - anonymous, ephemeral photo-sharing</title>
	<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
<h1><a href="/">Pictr</a></h1>
<p id="menu"><a href="/upload.php">Upload a picture</a></p>
<p class="pic">
<?php
printf("<img src=\"%s\" alt=\"\" title=\"%s\" width=\"800\"></a>\n", 
		$obj->PublicURL(), $obj->Name());
?>
</p>
<form action="<?php print($_SERVER['PHP_SELF']).'?id='.$_GET['id'];?>" method="POST" enctype="multipart/form-data">
<button type="submit" value="Offensive" name="bad">Offensive</button>
</form>
</body>
</html>
