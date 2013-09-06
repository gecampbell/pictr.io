<?php
/**
 * @copyright 2013 Glen Campbell
 * @license Apache 2.0
 *
 * This script validates an uploaded object in the Swift data
 * store. It checks that the content-type is valid and it sets
 * the object expiration time. If everything's good, it redirects
 * to the home page; otherwise it displays an error message.
 */
require 'lib/pictr.php';
$config = new Pictr\Config();

?><!DOCTYPE html>
<html>
<head>
	<title>Error</title>
</head>
<body>
	<h1>Error</title>
	<?php print_r($_GET); print_r($_POST); ?>
</body>
</html>

