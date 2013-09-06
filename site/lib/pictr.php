<?php

namespace Pictr;

require 'php-opencloud/php-opencloud.php';

/**
 * The Config class contains global configuration data
 */
class Config {

	public
		$domain,			// domain name of site
		$username,			// Swift/Cloud Files user name
		$apikey,			// Swift/Cloud Files API key
		
		$swift_name,		// name of Swift service
		$swift_region,		// Swift region identifier
		$container_name,	// the container name
		$temp_url_secret;	// secret for temporary URL
	
	private
		$_container,		// the base container
		$_cdncontainer;		// points to the CDN container
	
	/**
	 * creates a new configuration object, optional config file name
	 */
	public function __construct($ini_name="/etc/pictr.ini") {
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
		
		// connect to Swift
		$swift = $cloud->ObjectStore(
			$this->swift_name,
			$this->swift_region);
		
		// set the temporary URL secret
		$swift->SetTempUrlSecret($this->temp_url_secret);
			
		// return/create the container
		$this->_container = $swift->Container();
		$this->_container->Create(array('name'=>$this->container_name));
		$this->_cdncontainer = $this->_container->PublishToCDN();
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
			60*60*3			=> '3 hours',
			60*60*6			=> '6 hours',
			60*60*12		=> '12 hours',
			60*60*24		=> '1 day',
			60*60*24*7		=> '1 week'
		);
	}
	
} // end class Config