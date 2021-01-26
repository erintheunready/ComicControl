<?php 
//module.php - switchboard for managing module actions

//get the 'action' being performed
$action = getSlug(3);

$permission = false;
if($ccuser->authlevel == 2) $permission = true;
else{
	$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "users_permissions WHERE userid=:userid AND moduleid=:moduleid");
	$stmt->execute(['userid' => $ccuser->id, 'moduleid' => $ccpage->module->id]);
	if($stmt->rowCount() < 1){
		
		echo '<main id="content"><div class="msg error f-c">' . $lang['You do not have permission to edit this module.'] . '</div></main>';
	}
	else $permission = true;
}
if($permission){
//include appropriate script for the action
switch($ccpage->moduletype){
	case "comic":
		switch($action){
			case "add-post":
				require_once('comic-post-add.php');
				break;
			case "edit-post":
				require_once('comic-post-edit.php');
				break;
			case "manage-posts":
				require_once('comic-post-manage.php');
				break;
			case "delete-post":
				require_once('comic-post-delete.php');
				break;
			case "manage-storylines":
				require_once('comic-storyline-manage.php');
				break;
			case "add-storyline":
				require_once('comic-storyline-add.php');
				break;
			case "edit-storyline":
				require_once('comic-storyline-edit.php');
				break;
			case "rearrange-storylines":
				require_once('comic-storyline-rearrange.php');
				break;
			case "delete-storyline":
				require_once('comic-storyline-delete.php');
				break;
			case "manage-options":
				require_once('comic-options.php');
				break;
			default:
				require_once('comic-main.php');
				break;
		}
		break;
	case "blog":
		switch($action){
			case "add-post":
				require_once('blog-post-add.php');
				break;
			case "edit-post":
				require_once('blog-post-edit.php');
				break;
			case "delete-post":
				require_once('blog-post-delete.php');
				break;
			case "manage-options":
				require_once('blog-options.php');
				break;
			default:
				require_once('blog-main.php');
				break;
			
		}
		break;
	case "text":
		switch($action){
			case "manage-options":
				require_once('text-options.php');
				break;
			default:
				require_once('text-edit.php');
				break;	
		}
		break;
	case "gallery":
		switch($action){
			case "add-image":
				require_once('gallery-add.php');
				break;
			case "rearrange-images":
				require_once('gallery-rearrange.php');
				break;
			case "edit-image":
				require_once('gallery-edit.php');
				break;
			case "delete-image":
				require_once('gallery-delete.php');
				break;
			case "manage-options":
				require_once('gallery-options.php');
				break;
			case "description":
				require_once('gallery-description.php');
				break;
			default:
				require_once('gallery-main.php');
				break;
		}
		break;
}
}
?>