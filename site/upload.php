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
if (isset($_POST['picturefile'])) {
	$ERROR = NULL;

	// process it
	$filename = microtime(TRUE);
	
	// validate expiration
	
	
	// redirect home
	header('Location: http://'.$config->domain);
}

// form URL
$container = $config->Container();
$mtime = microtime();
$arr = split(' ', $mtime);

$filename = $arr[1].'-'.$arr[0];
$action = $container->Url();
$path = parse_url($action, PHP_URL_PATH);
$redirect = 'http://'.$config->domain.'/validate.php?name='.$filename;
$max_file_size = $config->max_file_size;
$max_file_count = $config->max_file_count;
$expires = time()+(60*10);
$mykey = $config->temp_url_secret;
$hmac_body = sprintf('%s\n%s\n%s\n%s\n%s',
	$path, $redirect, $max_file_size, $max_file_count, $expires);
$signature = hash_hmac('sha1', $hmac_body, $mykey);
?><!DOCTYPE html>
<html>
<head>
	<title>Upload</title>
</head>
<body>
	<h1>Upload</h1>
	<p><em>Note: This form is only valid for 10 minutes</em></p>
	<form action="<?php echo $action?>>" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="redirect" value="<?php echo $redirect?>">
		<input type="hidden" name="max_file_size" value="<?php echo $max_file_size?>">
		<input type="hidden" name="max_file_count" value="<?php echo $max_file_count?>">
		<input type="hidden" name="expires" value="<?php echo $expires?>">
		<input type="hidden" name="signature" value="<?php echo $signature?>">
		<input type="file" name="<?php echo $filename?>">
		<p>
		<select>
		<?php
			foreach($config->expirations() as $value => $title)
				printf("\t<option value=\"%s\">%s</option>\n",
					$value, $title);
		?>
		</select>
		</p>
		<button type="submit" >Upload</button>
	</form>
</body>
</html>