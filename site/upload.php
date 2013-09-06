<?php
/**
 * @copyright 2013 Glen Campbell
 * @license Apache 2.0
 */

require 'lib/pictr.php';
// create a new configuration object
$config = new Pictr\Config();

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
	if ($config->signature($filename) != $_POST['signature']) {
		$ERROR[] = "Invalid signature";
		$ERROR[] = print_r($_FILES, TRUE);
	}
	
	// validate expiration
	if (!array_key_exists($_POST['expiration'], $config->expirations()))
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
		$container = $config->container();
		$obj = $container->DataObject();
		$obj->extra_headers['X-Delete-After'] = $_POST['expiration'];
		$obj->Create(
			array('name' => $filename), 
			$_FILES[$filename]['tmp_name']);
		
		header('Location: http://'.$config->domain);
		exit;
	}
}

// form URL
$container = $config->Container();
$arr = split(' ', microtime());
$prefix = strtotime('2031-12-31 11:59:59')-time();
$filename = $prefix . str_replace('.', '_', $arr[1].'_'.$arr[0]);
$signature = $config->signature($filename);
?><!DOCTYPE html>
<html>
<head>
	<title>Upload</title>
	<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
	<h1>Pictr Upload</h1>
	<?php
	if (!empty($ERROR)) {
		$msg = implode("<br>\n", $ERROR);
		print("<p class=\"error\">$msg</p>\n");
	}
	?>
	<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST" enctype="multipart/form-data">
		<input type="file" name="<?php echo $filename;?>">
		<p>
		<select name="expiration">
		<?php
			foreach($config->expirations() as $value => $title) {
				if ($value == 300) 
					$def = ' selected="selected"';
				else
					$def = '';
				print("\t<option value=\"$value\"$def>$title</option>\n");
			}
		?>
		</select>
		<input type="hidden" name="signature" value="<?php echo $signature;?>">
		</p>
		<button type="submit">Upload the picture</button>
	</form>
</body>
</html>