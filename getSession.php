<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
//Includes the client library and starts a Kaltura session to access the API
//More informatation about this process can be found at
//http://knowledge.kaltura.com/introduction-kaltura-client-libraries
require_once('lib/php5/KalturaClient.php');
//Use user->loginByLoginId rather than adminUser->login which has been deprecated
$partnerId = $_REQUEST['partnerId'];
$config = new KalturaConfiguration($partnerId);
$config->serviceUrl = 'http://www.kaltura.com/';
$client = new KalturaClient($config);
$loginId = $_REQUEST['email'];
$password = $_REQUEST['password'];
$expiry = null;
$privileges = null;
try {
	$ks = $client->user->loginByLoginId($loginId, $password, $partnerId, $expiry, $privileges);
	echo $ks;
}
catch(Exception $ex) {
	if(strpos($ex->getMessage(), 'Unknown') === false)
		echo 'loginfail';
	else
		echo 'idfail';
}