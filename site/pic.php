<?php
/**
 * @copyright 2013 Glen Campbell
 * @license Apache 2.0
 */
require 'lib/pictr.php';
// create a new configuration object
$CONFIG = new Pictr\Config();

$FOUND = TRUE; // assume we have one

// id= specified?
if (!isset($_GET['id']))
	$FOUND = FALSE;

// get object stuff
$container = $CONFIG->Container();
try {
	$obj = $container->DataObject($_GET['id']);
	$delete_at = $obj->extra_headers['X-Delete-At'];
	// how many seconds till expired?
	$diff = $delete_at - time();

	/**
	 * handle Like, Dislike
	 */
	if (isset($_POST['Love'])) {
		// love it! add time
		$delete_at += rand(10, $CONFIG->love_extra*2);
		$obj->updateMetadata(array(
			'X-Delete-At' => $delete_at
		));
		$LOVED=TRUE;
	}
	if (isset($_POST['Hate'])) {
		/*
		error_log(sprintf('Hate[%s]: %d seconds left',
			$_GET['id'], $diff));
		*/

		// if less than a minute, delete it
		if ($diff < 60) {
			$obj->delete();
			header('Location: http://'.$_SERVER['HTTP_HOST']);
			exit;
		}
		else {
			// otherwise, cut the remaining time in half
			$delete_at = time() + round($diff/2);
			$obj->updateMetadata(array(
				'X-Delete-At' => $delete_at
			));
			$HATED=TRUE;
		}
	}

	/**
	 * create template variables
	 */
	$PIC = new \stdClass;
	$PIC->name = $obj->Name();
	$PIC->url = $obj->PublicURL();
	$PIC->expiration = $delete_at;
} catch (Exception $e) {
	$FOUND=FALSE;
}

// set 404 headers if not found
if (!$FOUND) {
	header('HTTP/1.1 404 NOT FOUND');
	$TITLE = 'Pictr - NOT FOUND OMG';
	$PIC = FALSE;
}
/**
 * establish template variables
 */
$REFRESH = 0;

include 'templates/pic.html';
