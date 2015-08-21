<?php

/**
 * Script to edit VRF
 ***************************/

/* functions */
require( dirname(__FILE__) . '/../../../functions/functions.php');

# initialize user object
$Database 	= new Database_PDO;
$User 		= new User ($Database);
$Admin	 	= new Admin ($Database);
$Tools	 	= new Tools ($Database);
$Result 	= new Result ();

# verify that user is logged in
$User->check_user_session();

# fetch custom fields
$custom = $Tools->fetch_custom_fields('vrf');


# Hostname must be present!
if($_POST['name'] == "") { $Result->show("danger", _("Name is mandatory"), true); }


# set update array
$values = array("vrfId"=>@$_POST['vrfId'],
				"name"=>$_POST['name'],
				"description"=>$_POST['description']
				);
# append custom
if(sizeof($custom) > 0) {
	foreach($custom as $myField) {
		# replace possible ___ back to spaces!
		$myField['nameTest']      = str_replace(" ", "___", $myField['name']);
		if(isset($_POST[$myField['nameTest']])) { $values[$myField['name']] = @$_POST[$myField['nameTest']];}
	}
}

# update
if(!$Admin->object_modify("vrf", $_POST['action'], "vrfId", $values))	{ $Result->show("danger",  _("Failed to $_POST[action] VRF").'!', true); }
else																	{ $Result->show("success", _("VRF $_POST[action] successfull").'!', false); }


# remove all references if delete
if($_POST['action']=="delete") { $Admin->remove_object_references ("subnets", "vrfId", $_POST['vrfId']); }
?>