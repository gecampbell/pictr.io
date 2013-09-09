<?php
/**
 * @copyright 2013 Glen Campbell
 * @license Apache 2.0
 */

define('APC_OLIST', 'PICTR_IO_index_olist');

require 'lib/pictr.php';
// create a new configuration object
$CONFIG = new Pictr\Config();

// get object stuff
$container = $CONFIG->Container();
$tcontainer = $CONFIG->thumbnailContainer();
$thumb_url_base = $tcontainer->PublicURL();

$olist = apc_fetch(APC_OLIST, $in_cache);
if (!$in_cache) {
	$olist = $container->ObjectList(array('limit'=>$CONFIG->max_pics_page));
	apc_store(APC_OLIST, $olist, 2);
}

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
