<?
//comic-post-delete.php - handles deletion of comic posts

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->module->slug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to managing %s'])
	),
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->module->slug."/manage-posts",
		'text' => $lang['Edit another comic post']
	),
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->module->slug."/add-post",
		'text' => $lang['Add another comic post']
	)
);
quickLinks($links);

?>

<main id="content">

<? 

//get selected comic post
$thiscomic = $ccpage->module->getPost(getSlug(4));

//handle case if no comic post was found
if(empty($thiscomic)){
	echo '<div class="msg error f-c">' . $lang['No comic was found with this information.'] . '</div>';
}

//proceed if found
else{
	
	//delete the post if they confirmed deletion
	if(getSlug(5) == "confirmed"){
		
		//delete post and tags
		$stmt = $cc->prepare("DELETE FROM cc_" . $tableprefix . "comics WHERE id=:id");
		$stmt->execute(['id' => $thiscomic['id']]);
		$stmt = $cc->prepare("DELETE FROM cc_" . $tableprefix . "comics_tags WHERE comicid=:id");
		$stmt->execute(['id' => $thiscomic['id']]);
		
		//output success message
		?>
		<div class="msg success f-c"><?=str_replace("%s",$thiscomic['title'],$lang['%s has been deleted.'])?></div>
		<?
		
		
	}else{
	
		//prompt user to delete page ?>

		<div class="msg prompt f-c"><?=str_replace("%s",$thiscomic['title'],$lang['Are you sure you want to delete %s? This action cannot be undone.'])?></div>
		<?

		echo '<div class="cc-btn-row">';
		buildButton(
			"light-bg",
			$ccurl . $navslug.'/'.$ccpage->module->slug.'/delete-post/' . $thiscomic['slug'] . '/confirmed',
			$lang['Yes']
		);
		buildButton(
			"dark-bg",
			$ccurl . $navslug.'/'.$ccpage->module->slug."/manage-posts",
			$lang['No']
		);
		echo '</div>';
		
	}

}
?>

</main>