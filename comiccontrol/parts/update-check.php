<?

//update-check.php
//check version against master version

?>

<div id="content">

<?php

//get file contents function
function get_info($url){
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($curl);
	curl_close($curl);
	
	return $output;
}
function get_file($url,$fileloc){
	$file = fopen($fileloc, 'w');
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL,$url);
	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_FILE, $file);
	curl_exec($curl);
	curl_close($curl);
	fclose($file);
}
	

//get the newest version from the CC server
$version = get_info("http://www.comicctrl.com/version-control/getversion.php");
$query = "SELECT * FROM cc_" . $tableprefix . "options WHERE optionname='version' LIMIT 1";
$stmt = $cc->prepare($query);
$stmt->execute();

//get the current version in the db
$row = $stmt->fetch();
$currentversion = $row['optionvalue'];

//if up to date, do nothing
if($currentversion==$version){
	echo '<p>' . $lang['Your version of ComicControl is up to date!'] . '</p>';
}

//otherwise, download upgrade scripts and give options for updating
else{
	
	$query = "UPDATE cc_" . $tableprefix . "options SET optionvalue=:version WHERE optionname=:option";
	$stmt = $cc->prepare($query);
	$stmt->execute(['version' =>$version,'option'=>'newestversion']);
	
	get_file("http://www.comicctrl.com/version-control/upgradescripts/upgrade.txt",dirname(__FILE__) . '/upgrade.php');
	echo '<p>' . $lang['Your version of ComicControl needs updating!  Here are change notes for this update:'] . '</p>';
	$versionnotes = get_info("http://www.comicctrl.com/version-control/versionnotes.php?version=" . $currentversion);
	echo '<p>' . $versionnotes . '</p>';
	echo '<p>' . $lang['Please click the button below to update your version of ComicControl.  After clicking the button, please do not leave the page until you receive confirmation that ComicControl has been updated.  Please be aware that this will overwrite the main ComicControl files.'] . '</p>';
	echo '<div class="cc-btn-row">';
	buildButton(
		"light-bg",
		$ccurl . "upgrade",
		$lang['Upgrade ComicControl']
	);
	echo '</div>';
}
?>

</div>