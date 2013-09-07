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
		TMP_CREDENTIALS = '/tmp/pictr.credentials';

	public
		$domain,			// domain name of site
		$username,			// Swift/Cloud Files user name
		$apikey,			// Swift/Cloud Files API key

		$swift_name,		// name of Swift service
		$swift_region,		// Swift region identifier
		$container_name,	// the container name
		$secret;			// secret for temporary URL

	private
		$_container,		// the base container
		$_cdncontainer;		// points to the CDN container

	/**
	 * creates a new configuration object, optional config file name
	 */
	public function __construct($ini_name="/var/www/pictr.ini") {
		$ini = parse_ini_file($ini_name);
		foreach($ini as $key => $value)
			$this->$key = $value;
	}

	/**
	 * return a link to the container
	 */
	public function container() {

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

		// connect to Swift
		$swift = $cloud->ObjectStore(
			$this->swift_name,
			$this->swift_region);

		// set the temporary URL secret
		$swift->setTempUrlSecret($this->secret);

		// return/create the container
		$this->_container = $swift->Container();
		$this->_container->Create(array('name'=>$this->container_name));
		$this->_cdncontainer = $this->_container->enableCDN(60);
		return $this->_container;
	}

	/**
	 * returns the array of expiration values
	 */
	public function expirations() {
		return array(
			60 				=> '1 minute',
			60*5			=> '5 minutes',
			60*10			=> '10 minutes',
			60*30			=> '30 minutes',
			60*60			=> '1 hour',
			60*60*3			=> '3 hours'
		);
	}

	/**
	 * compute a signature
	 */
	function signature($name) {
		return sha1($name . '==' . $this->secret);
	}

} // end class Config
