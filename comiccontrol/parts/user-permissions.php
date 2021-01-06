<? //user-permissions.php - form for editing user info 

if($ccuser->authlevel == 2){
	//create and output quick links if top level user
	$links = array(
		array(
			'link' => $ccurl . $navslug,
			'text' => str_replace('%s',$ccpage->title,$lang['Return to managing users'])
		),
		array(
			'link' => $ccurl . $navslug . '/add-user',
			'text' => $lang['Add another user']
		)
	);
	quickLinks($links);
}

?>

<main id="content">
<? 
//get selected user
$query = "SELECT * FROM cc_" . $tableprefix . "users WHERE id=:id LIMIT 1";
$stmt = $cc->prepare($query);
$stmt->execute(['id' => $userid]);

//throw error if the user wasn't found
if($stmt->rowCount() < 1){
	echo '<div class="msg error">' . $lang['There is no user with this ID.'] . '</div>';
}

//if user found, proceed
else{
	
    $thisuser = $stmt->fetch();
    echo '<h2>' . str_replace('%u', $thisuser['username'],$lang['Editing permissions for the user "%u"']) . '</h2>';
    $action = getSlug(4);
    $module = getSlug(5);
	if($action == "grant"){
        $stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "modules WHERE id=:id");
        $stmt->execute(['id' => $module]);
        if($stmt->rowCount() > 0){
            $module = $stmt->fetch();
            $stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "users_permissions WHERE userid=:userid AND moduleid=:moduleid");
            $stmt->execute(['userid' => $thisuser['id'],'moduleid'=>$module['id']]);
            if($stmt->rowCount() < 1){
                $stmt = $cc->prepare("INSERT INTO cc_" . $tableprefix . "users_permissions(userid,moduleid) VALUES(:userid,:moduleid)");
                $stmt->execute(['userid' => $thisuser['id'],'moduleid'=>$module['id']]);
            }
            echo '<div class="msg success f-c">' . str_replace('%m', $module['title'], $lang['This user has been granted permissions to the module "%m".']) . '</div>';
        }
    }
	if($action == "revoke"){
        $stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "modules WHERE id=:id");
        $stmt->execute(['id' => $module]);
        if($stmt->rowCount() > 0){
            $module = $stmt->fetch();
            $stmt = $cc->prepare("DELETE FROM cc_" . $tableprefix . "users_permissions WHERE userid=:userid AND moduleid=:moduleid");
            $stmt->execute(['userid' => $thisuser['id'],'moduleid'=>$module['id']]);
            echo '<div class="msg success f-c">' . str_replace('%m', $module['title'], $lang['This user has had their permissions to the module "%m" revoked.']) . '</div>';
        }
    }
        echo '<p>' . $lang['The user has permissions for the site modules as follows.  Click "grant" to allow the user access to the module, or "revoke" to disallow the user access to the module.'] . '</p>';
        $stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "modules");
        $stmt->execute();
        $modules = $stmt->fetchAll();
        echo '<div class="row-container">';
        $graybg = true;
        foreach($modules as $module){
            $stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "users_permissions WHERE userid=:userid AND moduleid=:moduleid");
            $stmt->execute(['userid' => $thisuser['id'],'moduleid'=>$module['id']]);
            $permitted = false;
            if($stmt->rowCount() > 0) $permitted = true;
            echo '<div class="zebra-row';
            if($graybg){ 
                echo ' gray-bg';
                $graybg = false;
            }
            else $graybg = true;
            echo '"><div class="row-title">' . $module['title'] . '</div><a href="' . $ccurl . 'users/permissions-user/' . $thisuser['id'] . '/';
            if($permitted) echo 'revoke';
            else echo 'grant';
            echo '/' . $module['id'] . '">';
            if($permitted) echo $lang['Revoke Permissions'];
            else echo $lang['Grant Permissions'];
            echo '</a></div>';
        }
        echo '</div>';
		
}
?>

</main>