<?php
/**
 * @copyright 2013 Glen Campbell
 * @license Apache 2.0
 */
require 'lib/pictr.php';
// create a new configuration object
$config = new Pictr\Config();

if (!isset($_GET['id']))
	$ERROR = "Sorry, no picture with that ID";

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

/**
 * establish template variables
 */
$PIC = new \stdClass;
$PIC->name = $obj->Name();
$PIC->url = $obj->PublicURL();
$REFRESH = 0;

include 'templates/pic.html';
