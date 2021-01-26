<?php
//blog-post-delete.php
//handles deletion of existing blog posts.

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->module->slug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to managing %s'])
	),
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->module->slug.'/',
		'text' => $lang['Edit another blog post']
	),
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->module->slug.'/add-post',
		'text' => $lang['Add another blog post']
	)
);
quickLinks($links);
?>

<main id="content">

<?php 

//get selected post
$thispost = $ccpage->module->getPost(getSlug(4));

//output error if that's not a valid post
if(empty($thispost)){
	echo '<div class="msg error">' . $lang['No blog post was found with this information.'] . '</div>';
}

//if found, proceed
else{
	
	if(getSlug(5) == "confirmed"){
		
		$stmt = $cc->prepare("DELETE FROM cc_" . $tableprefix . "blogs WHERE id=:id");
		$stmt->execute(['id' => $thispost['id']]);
		$stmt = $cc->prepare("DELETE FROM cc_" . $tableprefix . "blogs_tags WHERE blogid=:id");
		$stmt->execute(['id' => $thispost['id']]);
		?>
		
		<div class="msg success f-c"><?=str_replace("%s",$thispost['title'],$lang['%s has been deleted.'])?></div>
		
		<?php
		
		
	}else{
	
		//prompt user to delete post ?>

		<div class="msg prompt f-c"><?=str_replace("%s",$thispost['title'],$lang['Are you sure you want to delete %s? This action cannot be undone.'])?></div>
		<?php

		echo '<div class="cc-btn-row">';
		buildButton(
			"light-bg",
			$ccurl . $navslug.'/'.$ccpage->module->slug.'/delete-post/' . $thispost['slug'] . '/confirmed',
			$lang['Yes']
		);
		buildButton(
			"dark-bg",
			$ccurl . $navslug.'/'.$ccpage->module->slug."/",
			$lang['No']
		);
		echo '</div>';
		
	}

}
?>

</main>