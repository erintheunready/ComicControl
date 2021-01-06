<?
//rss switch
if($ccpage->subslug == "rss"){ 

	include($ccsite->ccroot . 'parts/comic-rss.php');
	
}else{
	
	//header
	include('templates/cc-3/includes/header.php');
	
	//archive
	if($ccpage->subslug == "archive"){ 		?>
		<div id="text-area">
		<h1><?=$user_lang['Latest Page'];?></h1>
		<p><?=str_replace('%l', $ccsite->root.$ccsite->relativepath.$ccpage->slug, $user_lang['You can read the newest page by going <a href="%l">here!</a>']?><</p>
		<h1><?=$user_lang['Archive']?></h1>
		<p><?=$user_lang['Select a page from the drop-down menu to start reading the comic.'];?></p>
		<? $ccpage->module->displayDropdown(); ?><p><?=$lang['Or, you can select a chapter to start from:'];?></p>
		<? $ccpage->module->displayChapters();
		?></div><?
			
	}
	
	//search
	else if($ccpage->subslug == "search"){  
	
			$ccpage->module->search();
			
	}else{
		$ccpage->module->display();
		$ccpage->module->navDisplay();
		$ccpage->module->displayAll();
	
		//example case for blog posts
		
		/*
		if($ccpage->subslug == ""){
			$blog = $ccpage->buildModule('blog');
			$blog->recentPosts(1);
		}
		*/
	}
	
	//footer
	include('templates/cc-3/includes/footer.php');
}
 ?>