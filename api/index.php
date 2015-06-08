<?php

/**
 *	phpIPAM API
 *
 *		please read README on how to use API
 */

# include funtions
include_once '../functions/functions.php';

# database object
$Database 	= new Database_PDO;
# initialize objects
//$Sections	= new Sections ($Database);
//$Subnets	= new Subnets ($Database);
//$Addresses	= new Addresses ($Database);
//$Tools	    = new Tools ($Database);
$Admin		= new Admin ($Database, false);

# get phpipam settings
$settings 	= $Admin->fetch_object ("settings", "id", 1);

/* include models */
include_once 'models/common.php';						//common functions
include_once 'models/address.php';						//address actions
include_once 'models/subnet.php';						//subnet actions
include_once 'models/section.php';						//section actions
include_once 'models/vlan.php';							//vlan actions
include_once 'models/vrf.php';							//vrf actions


/* wrap in a try-catch block to catch exceptions */
try {


	/* Do some checks before processing request ---------- */

	# verify php extensions
	foreach (array("mcrypt", "curl") as $extension) {
    	if (!in_array($extension, get_loaded_extensions())) {
        	throw new Exception('php extension '.$extension.' missing');
		}
	}
	# verify that API is enabled on server
	if($settings->api!=1) {
		throw new Exception('API server disabled');
	}
	# fetch app
	$app = $Admin->fetch_object ("api", "app_id", $_REQUEST['app_id']);

	# verify app_id
	if($app === false) {
		throw new Exception('Invalid application id');
	}
	# check that app is enabled
	if($app->app_permissions==="0") {
		throw new Exception('Application disabled');
	}


	/* Check app security and prepare request parameters ---------- */

	# crypt
	if($app->app_security=="crypt") {
		// decrypt request - to JSON
		$params = json_decode(trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $app[$_REQUEST['app_id']], base64_decode($_REQUEST['enc_request']), MCRYPT_MODE_ECB)));
	}
	# SSL
	elseif($app->app_security=="ssl") {
		// verify SSL
		if (!((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443)) {
			throw new Exception('App requires SSL connection');
		}
		$params = (object) $_REQUEST;
	}
	# none
	elseif($app->app_security=="none") {
		$params = (object) $_REQUEST;
	}
	# error
	else {
		throw new Exception('Invalid app security');
	}

	# sanitize edit ction for previous releases
	if(@$params->action=="update")	{ $params->action = "edit"; }



	/* verify request ---------- */

	// check if the request is valid by checking if it's an array and looking for the controller and action
	if( $params == false || isset($params->controller) == false || isset($params->action) == false ) {
		throw new Exception('Request is not valid');
	}
	// verify permissions for admin
	if(strtolower($params->controller=="admin" && $app->app_permissions!=3)) {
		throw new Exception('Invalid permissions');
	}
	// verify permissions for delete/create/edit
	if( (strtolower($params->action)=="delete" || strtolower($params->action)=="create" || strtolower($params->action)=="edit")  && $app->app_permissions<2) {
		throw new Exception('Invalid permissions');
	}



	/* Temporary workarounds ---------- */
	if($params->controller=="sections")		{ $params->controller = "sections_old"; }
	if($params->controller=="subnets")		{ $params->controller = "subnets_old"; }
	if($params->controller=="addresses")	{ $params->controller = "addresses_old"; }

	include_once 'dbfunctions.php';
	$database = new database($db['host'], $db['user'], $db['pass'], $db['name'], NULL, false);



	/* Initialize controllers ---------- */

	//get the controller and format it correctly
	$controller = ucfirst(strtolower($params->controller));

	//get the action and format it correctly
	$action = strtolower($params->action).$controller;

	//check if the controller exists. if not, throw an exception
	if( file_exists("controllers/$controller.php") ) {
		include_once "controllers/$controller.php";										//preveri, ce obstaja controller
	} else {
		throw new Exception('Controller is invalid');
	}

	//create a new instance of the controller, and pass
	//it the parameters from the request
	$controller = new $controller((array) $params);

	//check if the action exists in the controller. if not, throw an exception.
	if( method_exists($controller, $action) === false ) {
		throw new Exception('Invalid action');
	}

	//execute the action
	$result['success'] = true;
	$result['data'] = $controller->$action();

} catch( Exception $e ) {
	//catch any exceptions and report the problem
	$result = new StdClass();
	$result->success = false;
	$result->errormsg = $e->getMessage();
}

//echo the result of the API call
echo json_encode($result);
exit();

?>