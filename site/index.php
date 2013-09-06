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
$olist = $container->ObjectList();
if ($olist->Size() == 0) {
	echo "No pictures are currently available";
	die;
}
echo "<ol>\n";
while($object = $olist->Next()) {
	printf("<li>%s</li>\n", $object->Name());
}
echo "</ol>\n";
