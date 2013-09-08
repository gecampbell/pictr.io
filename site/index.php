<?php
/**
 * @copyright 2013 Glen Campbell
 * @license Apache 2.0
 */
require 'lib/pictr.php';
// create a new configuration object
$CONFIG = new Pictr\Config();

// get object stuff
$container = $CONFIG->Container();
$olist = $container->ObjectList(array('limit'=>$CONFIG->max_pics_page));

/**
 * establish template variables
 */
$PICTURES = array();
while($object = $olist->Next()) {
	$pic = new \stdClass;
	$pic->name = $object->Name();
	$pic->url = $object->PublicURL();
	if (isset($object->metadata->thumbnail_url))
		$pic->thumbnail = $object->metadata->thumbnail_url;
	else
		$pic->thumbnail = 'No thumbnail';
	$PICTURES[] = $pic;
}
$REFRESH = $CONFIG->auto_refresh;
include 'templates/index.html';
