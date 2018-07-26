<?
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

//include the classes
require_once('classes.php');

//create objects
$ccsite = new CC_Site();
$ccuser = new CC_User();
date_default_timezone_set($ccsite->timezone);

//quick access URL string
$siteurl = $ccsite->root.$ccsite->relativepath;
$ccurl = $ccsite->root.$ccsite->relativepath.$ccsite->ccroot;

//quick access functions
function getModuleOption($optionname){
	global $ccpage;
	
	return $ccpage->module->options[$optionname];
}
function buildButton($classes,$link,$text){
	echo '<a class="btn f-c ' . $classes . '" href="' . $link . '">' . $text . '</a>';
}
function quickLinks($links){
	echo '<div id="context-links">';
	foreach($links as $link){
		echo '<a href="' . $link['link'] . '">&gt; ' . $link['text'] . '</a>';
	}
	echo '<div style="clear:both;"></div></div>';
}

?>