<?php

//users.php - switchboard for user management scripts

$action = getSlug(2);
$userid = getSlug(3);

//if a top-level user, allow access to all user functions
if($ccuser->authlevel == 2){
	
	switch($action){
		
		case "edit-user":
			require_once("user-edit.php");
			break;
		case "delete-user":
			require_once("user-delete.php");
			break;
		case "add-user":
			require_once("user-add.php");
			break;
		case "permissions-user":
			require_once("user-permissions.php");
			break;
		default:
			require_once("user-main.php");
			break;
			
	}
	
}

//otherwise, only allow user to edit their own info
else{
	
	$userid = $ccuser->id;
	require_once("user-edit.php");
	
}

?>