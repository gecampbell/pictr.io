<!DOCTYPE html>
<html>
<head>
	<title>Pictr - anonymous, ephemeral photo-sharing</title>
	<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
<h1>Pictr</h1>
<p><a href="/upload.php">Upload a picture</a></p>
<ol>
<?php
/**
 * @copyright 2013 Glen Campbell
 * @license Apache 2.0
 */
require 'lib/pictr.php';
// create a new configuration object
$config = new Pictr\Config();

// get object stuff
$container = $config->Container();
$olist = $container->ObjectList(array('limit'=>$config->max_pics_page));
if ($olist->Size() == 0) {
	print("<p>No pictures are currently available</p>\n");
	die;
}
echo "<ol>\n";
while($object = $olist->Next()) {
	$imgurl = $object->PublicURL();
	printf("<li><a href=\"/pic.php?id=%s\"><img src=\"%s\" alt=\"\" title=\"%s\" width=\"300\"></a></li>\n", 
		urlencode($object->Name()), $imgurl, $object->Name());
}
?>
</ol>
</body>
</html>
