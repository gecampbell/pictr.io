<?php

namespace Pictr;

// increase the connect timeout
define('RAXSDK_CONNECTTIMEOUT', 20);
require 'php-opencloud/php-opencloud.php';

/**
 * The Config class contains global configuration data
 */
class Config {

	const
		TMP_CREDENTIALS = '/tmp/pictr.credentials',
		APC_CLOUD_HANDLE = 'PICTR_IO_APC_CLOUD_HANDLE',
		APC_SWIFT_HANDLE = 'PICTR_IO_APC_SWIFT_HANDLE',
		APC_CONTAINER_HANDLE = 'PICTR_IO_CONTAINER',
		APC_TTL = 300;

	public
		$domain,			// domain name of site
		$username,			// Swift/Cloud Files user name
		$apikey,			// Swift/Cloud Files API key

		$swift_name,		// name of Swift service
		$swift_region,		// Swift region identifier
		$container_name,	// the container name
		$thumbnail_container,	// the thumbnail container
		$secret,			// secret for temporary URL
		$cdn_ttl;			// TTL for CDN container

	private
		$expirations=array();	// expiration values

	/**
	 * creates a new configuration object, optional config file name
	 */
	public function __construct($ini_name="/var/www/pictr.ini") {
		$ini = parse_ini_file($ini_name, TRUE);
		foreach($ini as $key => $value)
			$this->$key = $value;
	}

	/**
	 * return a link to the container
	 */
	public function container($name=NULL) {

		if (!isset($name))
			$name = $this->container_name;

		$cachename = self::APC_CONTAINER_HANDLE.'__'.$name;

		// see if we have this in the APC cache
		// look for the swift
		$cloud = apc_fetch(self::APC_CLOUD_HANDLE, $in_cache);

		// if we couldn't retrieve it, then authenticate
		if (!$in_cache) {

			// configure our credentials
			if (strpos($this->endpoint, 'rackspace')) {
				$credentials = array(
					'username' => $this->username,
					'apiKey' => $this->apikey);
				$cloud = new \OpenCloud\Rackspace(
					$this->endpoint,
					$credentials);
			}
			else {
				$credentials = array(
					'username' => $this->username,
					'password' => $this->password
				);
				$cloud = new \OpenCloud\OpenStack(
					$this->endpoint,
					$credentials);
			}

			$old_token = $cloud->token();
			$cloud->Authenticate();
			// if we re-authenticated, save the new cloud
			if ($cloud->token() != $old_token) {
				error_log('pictr.io: saving to cache');
				$res = apc_store(self::APC_CLOUD_HANDLE, $cloud, self::APC_TTL);
				apc_delete(self::APC_SWIFT_HANDLE);
				apc_delete($cachename);
			}
		}

		/* no need to save credentials
		// import saved credentials
		$fp = @fopen(self::TMP_CREDENTIALS, 'r');
		if (!$fp) {		// no saved credentials
			$cloud->Authenticate();
			$fp = @fopen(self::TMP_CREDENTIALS, 'w');
			if (!$fp)
				die("Unable to save credentials");
			fwrite($fp, serialize($cloud->ExportCredentials()));
			fclose($fp);
		}
		else {
			$str = fread($fp, 99999);
			fclose($fp);
			$cloud->ImportCredentials(unserialize($str));
		}
		*/

		// connect to Swift
		$swift = apc_fetch(self::APC_SWIFT_HANDLE, $in_cache);
		if (!$in_cache) {
			$swift = $cloud->ObjectStore(
				$this->swift_name,
				$this->swift_region);
			apc_store(self::APC_SWIFT_HANDLE, $swift, self::APC_TTL);
			apc_delete($cachename);
		}

		// return/create the container
		$_container = apc_fetch($cachename, $in_cache);
		if ($in_cache) {
			//error_log("Fetched $cachename from APC");
			return $_container;
		}

		// otherwise, put it there
		$_container = $swift->Container();
		$_container->Create(array('name'=>$name));
		$_cdncontainer = $_container->enableCDN($this->cdn_ttl+0);
		apc_store($cachename, $_container, self::APC_TTL);

		return $_container;
	}

	/**
	 * returns the thumbnail container
	 */
	public function thumbnailContainer() {
		return $this->container($this->thumbnail_container);
	}

	/**
	 * returns the array of expiration values
	 */
	public function expirations() {
		return $this->expirations;
	}

	/**
	 * compute a signature
	 */
	function signature($name) {
		return sha1($name . '==' . $this->secret);
	}

} // end class Config

/**
 * formats a time string
 */
function time_seconds($seconds) {
	$units = array('hour','minute','second');
	$val = explode(':', gmdate('H:i:s', $seconds));
	$out = array();
	for($i=0; $i<3; $i++) {
		if (($val[$i] == 0) && empty($out))
			continue;
		$out[] = (0+$val[$i]).' '.$units[$i].($val[$i]==1?'':'s');
	}
	switch(count($out)) {
	case 3:
		return $out[0].', '.$out[1].', and '.$out[2];
	case 2:
		return $out[0].' and '.$out[1];
	default:
		return $out[0];
	}
}
