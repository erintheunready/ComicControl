<?php //plugins.php - main page for switching between plugins ?>

<main id="content">

<?php


if($ccuser->authlevel != 2){
	echo '<div class="msg error f-c">' . $lang['You do not have permission to access this page.'] . '</div>';
}else{
$plugin = getSlug(2);

$query = "SELECT * FROM cc_" . $tableprefix . "plugins WHERE slug=:slug LIMIT 1";
$stmt = $cc->prepare($query);
$stmt->execute(['slug' => $plugin]);

if($stmt->rowCount() > 0){
	
	$plugin = $stmt->fetch();
	include('plugin-files/' . $plugin['filepath']);
	
}
else{
	
	$query = "SELECT * FROM cc_" . $tableprefix . "plugins ORDER BY name ASC";
	$stmt = $cc->prepare($query);
	$stmt->execute();
	if($stmt->rowCount() > 0){
		echo '<p>' . $lang['The following plugins are currently installed:'] . '</p>';
		$plugins = $stmt->fetchAll();	
		echo '<div class="manage-container dark-bg"><div class="row-container">';
		echo '<div class="zebra-row gray-bg" style="font-weight:bold">';
		echo '<div class="row-pluginname">' . $lang['Plugin name'] . '</div>';
		echo '<div class="row-caption">' . $lang['Plugin description'] . '</div>';
		echo '<div style="clear:both;"></div></div>';
		foreach($plugins as $plugin){
			echo '<div class="zebra-row';
			if($gray){ 
				echo ' gray-bg';
				$gray = false;
			}
			else $gray = true;
			echo '">';
			echo '<div class="row-pluginname"><a href="' . $ccurl . 'plugins/' . $plugin['slug'] . '">' . $plugin['name'] . '</a></div>';
			echo '<div class="row-caption">' . $plugin['description'] . '</div>';
			echo '<div style="clear:both;"></div></div>';
		}
		echo '</div></div>';
	}else{
		echo '<p>' . $lang['There are currently no plugins installed.'] . '</p>';
	}
	
	echo '<p>' . $lang['To learn about installing and removing plugins, please read the ComicControl documentation.'] . '</p>';
	
	
}
}
?>

</main>