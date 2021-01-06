<?
//rss switch
if($ccpage->subslug == "rss"){ 

	include($ccsite->ccroot . 'parts/comic-rss.php');
	
}else{
	
	//header
	include('templates/cc-4/includes/header.php');
	
	//archive
	if($ccpage->subslug == "archive"){ 		?>
		<div id="text-area">
		<h1><?=$lang['userlatestpage'];?></h1>
		<p><?=$lang['usergotolatest'];?><a href="<?=$ccsite->root.$ccsite->relativepath.$ccpage->slug?>"><?=$lang['usergohere']?></a></p>
		<h1><?=$lang['archive']?></h1>
		<p><?=$lang['userselectapage'];?></p>
		<? $ccpage->module->displayDropdown(); ?><p><?=$lang['userselectachapter'];?></p>
		<? $ccpage->module->displayChapters();
			?></div><?
	}
	
	//search
	else if($ccpage->subslug == "search"){  
	?><div id="text-area"><?
			$ccpage->module->search();
		?></div><?	
	}else{
		?><div id="comic-area"><?
		$ccpage->module->display();
		$ccpage->module->navDisplay();
		?></div>
		<div id="text-area">
		<?
		$ccpage->module->displayAll();
		//example case for including blog posts
		
		/*
		if($ccpage->subslug == ""){
			$blog = $ccpage->buildModule('blog');
			$blog->recentPosts(1);
		}
		*/

			
		?></div><?
	}
	
	//footer
	include('templates/cc-4/includes/footer.php');
}
 ?>