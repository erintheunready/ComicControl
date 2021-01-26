<?php
//rss switch
if($ccpage->subslug == "rss"){ 

	include($ccsite->ccroot . 'parts/comic-rss.php');
	
}else{
	
	//header
	include('templates/basic/includes/header.php');
	
	//archive
	if($ccpage->subslug == "archive"){ 		?>
		<div id="text-area">
		<h1><?=$user_lang['Latest Page'];?></h1>
		<p><?=str_replace('%l', $ccsite->root.$ccsite->relativepath.$ccpage->module->slug, $user_lang['You can read the newest page by going <a href="%l">here!</a>'])?></p>
		<h1><?=$user_lang['Archive']?></h1>
		<p><?=$user_lang['Select a page from the drop-down menu to start reading the comic.'];?></p>
		<?php $ccpage->module->displayDropdown(); ?><p><?=$lang['Or, you can select a chapter to start from:'];?></p>
		<?php $ccpage->module->displayChapters();
			?></div><?php
	}
	
	//search
	else if($ccpage->subslug == "search"){  
	?><div id="text-area"><?php
			$ccpage->module->search();
		?></div><?php	
	}else{
		?><div id="comic-area"><?php
		$ccpage->module->display();
		$ccpage->module->navDisplay();
		?></div>
		<div id="text-area">
		<?php
		$ccpage->module->displayAll();
		//example case for including blog posts
		
		/*
		if($ccpage->subslug == ""){
			$blog = $ccpage->buildModule('blog');
			$blog->recentPosts(1);
		}
		*/

			
		?></div><?php
	}
	
	//footer
	include('templates/basic/includes/footer.php');
}
 ?>