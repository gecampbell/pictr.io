<?php
/**
 * @copyright 2013 Glen Campbell
 * @license Apache 2.0
 */
require 'lib/pictr.php';
// create a new configuration object
$CONFIG = new Pictr\Config();

if (!isset($_GET['id']))
	$ERROR = "Sorry, no picture with that ID";

// get object stuff
$container = $CONFIG->Container();
try {
	$obj = $container->DataObject($_GET['id']);

	/**
	 * handle Like, Dislike
	 */
	if (isset($_POST['Hate'])) {
		$obj->delete();
		header('Location: http://'.$_SERVER['HTTP_HOST']);
		exit;
	}

	/**
	 * create template variables
	 */
	$PIC = new \stdClass;
	$PIC->name = $obj->Name();
	$PIC->url = $obj->PublicURL();
} catch (Exception $e) {
	header('HTTP/1.1 404 NOT FOUND');
	$TITLE = 'Pictr - NOT FOUND OMG';
	$PIC = FALSE;
}

/**
 * establish template variables
 */
$REFRESH = 0;

include 'templates/pic.html';
