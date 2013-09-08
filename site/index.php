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
$tcontainer = $CONFIG->thumbnailContainer();
$thumb_url_base = $tcontainer->PublicURL();
$olist = $container->ObjectList(array('limit'=>$CONFIG->max_pics_page));

/**
 * establish template variables
 */
$PICTURES = array();
while($object = $olist->Next()) {
	$pic = new \stdClass;
	$pic->name = $object->Name();
	$pic->url = $object->PublicURL();
	$pic->thumbnail = $thumb_url_base.'/'.$pic->name;
	$PICTURES[] = $pic;
}
$REFRESH = $CONFIG->auto_refresh;
include 'templates/index.html';
