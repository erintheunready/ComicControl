<?php
//initialize.php - builds base classes and objects for both front and backend

//universal functions
function toSlug($input){
	$input = str_replace('%20','-',$input);
	$input = preg_replace('/[^A-Za-z0-9 \-]/', '', $input);
	$input = trim($input);
	$input = str_replace(' ','-',$input);
	$input = strtolower($input);
	return $input;
}
function getSlug($slugnum){
	
	global $ccpage;
	
	return $ccpage->slugarr[$slugnum];
	
}
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

//initialize the lang array
$lang = array();

//include the classes
require_once('classes.php');

//create objects
$ccsite = new CC_Site();
$ccuser = new CC_User();
date_default_timezone_set($ccsite->timezone);

//quick access URL string
$siteurl = $ccsite->root;
$ccurl = $ccsite->root.$ccsite->ccroot;

//quick access functions
function getModuleOption($optionname){
	global $ccpage;
	
	return $ccpage->module->options[$optionname];
}
function buildButton($classes,$link,$text){
	echo '<a class="cc-btn f-c ' . $classes . '" href="' . $link . '">' . $text . '</a>';
}
function quickLinks($links){
	echo '<div id="context-links">';
	foreach($links as $link){
		echo '<a href="' . $link['link'] . '">&gt; ' . $link['text'] . '</a>';
	}
	echo '<div style="clear:both;"></div></div>';
}

?>