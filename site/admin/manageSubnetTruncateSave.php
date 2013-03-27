<?php

/*
 * truncate subnet result
 *********************/

/* required functions */
require_once('../../functions/functions.php'); 

/* verify that user has write permissions for subnet */
$subnetPerm = checkSubnetPermission ($_REQUEST['subnetId']);
if($subnetPerm != "2") 	{ die('<div class="alert alert-error">You do not have permissions to resize subnet!</div>'); }


/* verify post */
CheckReferrer();

# get all site settings
$settings = getAllSettings();

# truncate network
if(!truncateSubnet($_POST['subnetId'])) {}
else 									{ print "<div class='alert alert-success'>Subnet truncated succesfully!</div>"; }

?>