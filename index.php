<?php


/* 
COMICCONTROL
Version 4.2.7
2/23/2021
Built by Erin Burt with help from others.
Copyright 2012-2021 Erin Burt.
See comicctrl.com/thanks for contributors and included third-party MIT-licensed packages.
See github.com/erintheunready/ComicControl for current source and development branches.
*/

//start the output buffering
ob_start();
error_reporting(E_ALL & ~E_NOTICE);
header('X-Frame-Options: sameorigin');

//include main ComicControl scripts
require_once('comiccontrol/includes/dbconfig.php');
require_once('comiccontrol/includes/initialize.php');

//build the page
$ccpage = new CC_Page("$_SERVER[REQUEST_URI]");

//include admin language if logged in
if($ccuser->authlevel > 0){
	require_once('comiccontrol/languages/' . $ccuser->language . '.php');
	$adminlang = $lang;
	unset($lang);
	$lang = array();
}

//include page language
require_once('comiccontrol/languages/user-' . $ccpage->language . '.php');

//include custom file if extant
if(file_exists('custom.php')) require_once('custom.php');

//include template and build module
require_once('templates/' . $ccpage->template);


//close out the contents
$contents = ob_get_contents();
ob_end_clean();

//build preview bar if authorized
if($ccuser->authlevel > 0){ 

	$script = '<style>
		html{
			width:100% !important;
			margin-top:40px !important;
		}
	</style></head>';

	$previewbar='<body><div class="cc-previewbar"><div class="cc-leftside"><a href="' . $ccurl . '">ComicControl.</a></div><div class="cc-rightside">';
	
	switch($ccpage->moduletype){
		case "comic":
			$comicinfo = $ccpage->module->getComic();
			if($comicinfo['publishtime'] > time()){
				$previewbar .= $adminlang['PREVIEW'] . " - ";
			}
			$previewbar .= $comicinfo['comicname'] .'<a href="' . $ccurl . 'modules/' . $ccpage->module->slug . '/add-post">' . $adminlang['Add'] . '</a> | <a href="' . $ccurl . 'modules/' . $ccpage->module->slug . '/edit-post/' . $comicinfo['slug'] . '">' . $adminlang['Edit'] . '</a> | ';
			$previewbar .= '<a href="' . $ccurl . 'modules/' . $ccpage->module->slug . '">' . str_replace('%s',$ccpage->title,$adminlang['Return to %s']) . '</a>';
			$previewbar .= '</div></div>';
			break;
		case "blog":
			$bloginfo = $ccpage->module->getPost(getSlug(1));
			if(!empty($bloginfo)){
				if($bloginfo['publishtime'] > time()){
					$previewbar .= $adminlang['PREVIEW'] . " - ";
				}
				$previewbar .= $bloginfo['title'] . ' - ';
				$previewbar .= $bloginfo['comicname'] .'<a href="' . $ccurl . 'modules/' . $ccpage->module->slug . '/edit-post/' . $bloginfo['slug'] . '">' . $adminlang['Edit'] . '</a> | ';
			}
			$previewbar .= '<a href="' . $ccurl . 'modules/' . $ccpage->module->slug . '/add-post">' . $adminlang['Add'] . '</a> | ';
			$previewbar .= '<a href="' . $ccurl . 'modules/' . $ccpage->module->slug . '">' . str_replace('%s',$ccpage->title,$adminlang['Return to %s']) . '</a>';
			$previewbar .= '</div></div>';
			break;
		case "gallery":
			$previewbar .= '<a href="' . $ccurl . 'modules/' . $ccpage->module->slug . '/add-post">' . $adminlang['Add'] . '</a> | ';
			$previewbar .= '<a href="' . $ccurl . 'modules/' . $ccpage->module->slug . '">' . str_replace('%s',$ccpage->title,$adminlang['Return to %s']) . '</a>';
			$previewbar .= '</div></div>';
			break;
		case "text":
			$previewbar .= '<a href="' . $ccurl . 'modules/' . $ccpage->module->slug . '">' . $adminlang['Edit'] . '</a>';
			$previewbar .= '</div></div>';
			break;
		default:
			$previewbar .= '</div></div>';
			break;
	}
		
	//include preview bar in output
	$contents = str_replace('</head>',$script,$contents);
	$contents = str_replace('<body>',$previewbar,$contents);
}

echo $contents;

?>
