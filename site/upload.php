<?php
/**
 * @copyright 2013 Glen Campbell
 * @license Apache 2.0
 */

require 'lib/pictr.php';
require 'lib/resize.php';
// create a new configuration object
$CONFIG = new Pictr\Config();

// get the container
$container = $CONFIG->Container();

/**
 * handle POST requests
 */
if (isset($_POST['signature'])) {

	// error flag/message
	$ERROR = array();

	// find the filename
	foreach($_FILES as $name => $info) {
		$filename = $name;
		break;
	}

	// validate signature
	if ($CONFIG->signature($filename) != $_POST['signature']) {
		$ERROR[] = "Invalid signature";
		$ERROR[] = print_r($_FILES, TRUE);
	}

	// validate expiration
	if (!array_key_exists($_POST['expiration'], $CONFIG->expirations()))
		die("Invalid expiration value");

	// validate content-type
	if (substr($_FILES[$filename]['type'], 0, 5) != 'image')
		$ERROR[] = sprintf("Invalid content-type [%s]",
						$_FILES[$filename]['type']);

	// check for upload error
	if ($_FILES[$filename]['error'])
		$ERROR[] = $_FILES[$filename]['error'];

	// check for valid tmp file
	if (!is_uploaded_file($_FILES[$filename]['tmp_name']))
		$ERROR[] = 'Error in uploaded file';

	// if we're ok, create the object
	if (empty($ERROR)) {
		$obj = $container->DataObject();
		$obj->extra_headers['X-Delete-After'] = $_POST['expiration'];

		// resize the image
		$tmp = '/tmp/pictr'.microtime(TRUE);
		$thumb = $tmp.'SQ';
		switch($_FILES[$filename]['type']) {
			case 'image/jpeg':
				$outfile = $filename . '.jpg';
				$tmp .= '.jpg';
				$thumb .= '.jpg';
				$im = new Resize(
						$_FILES[$filename]['tmp_name'],
						$_FILES[$filename]['type']);
				$im->resizeImage(
					$CONFIG->max_pic_size,
					$CONFIG->max_pic_size,
					'landscape');
				$im->saveImage($tmp);
				$im->resizeImage(
					$CONFIG->thumb_size,
					$CONFIG->thumb_size,
					'crop');
				$im->saveImage($thumb);
				break;
			case 'image/png':
				$outfile = $filename . '.png';
				$tmp .= '.png';
				$thumb .= '.png';
				$im = new Resize(
						$_FILES[$filename]['tmp_name'],
						$_FILES[$filename]['type']);
				$im->resizeImage(
					$CONFIG->max_pic_size,
					$CONFIG->max_pic_size,
					'landscape');
				$im->saveImage($tmp);
				$im->resizeImage(
					$CONFIG->thumb_size,
					$CONFIG->thumb_size,
					'crop');
				$im->saveImage($thumb);
				break;
			case 'image/gif':
				$outfile = $filename . '.gif';
				$tmp = $_FILES[$filename]['tmp_name'];
				$thumb = $tmp;
				break;
			default:
				$outfile = $filename;
				$tmp = $_FILES[$filename]['tmp_name'];
				$thumb = $tmp;
		}

		// create the thumbnail
		$tcontainer = $CONFIG->Container($CONFIG->thumbnail_suffix);
		$tobj = $tcontainer->DataObject();
		$tobj->extra_headers['X-Delete-After'] = $_POST['expiration'];
		$tobj->create(
			array('name' => $outfile),
			$thumb);
		$tobj = $tcontainer->DataObject($outfile);

		// create the object
		$obj->create(
			array('name' => $outfile),
			$tmp);
		$obj = $container->DataObject($outfile);
		$obj->metadata->thumbnail_url = $tobj->PublicURL();
error_log('thumbnail: '.$tobj->PublicURL());
		$obj->updateMetadata(array('X-Delete-After'=>$_POST['expiration']));
		@unlink($tmp);
		@unlink($thumb);

		header('Location: http://'.$_SERVER['HTTP_HOST']);
		exit;
	}
}

/**
 * establish template variables
 */
$arr = split(' ', microtime());
$prefix = strtotime('2031-12-31 11:59:59')-time();
$filename = $prefix . str_replace('.', '_', $arr[1].'_'.$arr[0]);
$signature = $CONFIG->signature($filename);
$TITLE = "Pictr - up yours!";

include 'templates/upload.html';
