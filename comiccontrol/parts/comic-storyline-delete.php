<? //comic-storyline-delete.php - handles deleting existing storylines ?>

<?

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->slug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to managing %s'])
	),
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->slug."/manage-storylines",
		'text' => $lang['Edit a different storyline']
	),
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->slug."/add-storyline",
		'text' => $lang['Add another storyline']
	)
);
quickLinks($links);

?>

<main id="content">

<? //get selected page 
$storylineid = getSlug(4);
$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE id=:id LIMIT 1";
$stmt = $cc->prepare($query);
$stmt->execute(['id' => $storylineid]);
$thisstoryline = $stmt->fetch();

//show error message if there was no storyline found
if(empty($thisstoryline)){
	echo '<div class="msg error f-c">' . $lang['No storyline was found with this information.'] . '</div>';
}
else{
	
	//delete the storyline if confirmed
	if(getSlug(5) == "confirmed"){
	
		$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE parent=:parent");
		$stmt->execute(['parent' => $thisstoryline['id']]);
		$children = $stmt->fetchAll();
		
		//move the children up a level
		foreach($children as $child){
			$stmt = $cc->prepare("UPDATE cc_" . $tableprefix . "comics_storyline SET parent=:parent WHERE id=:id");
			$stmt->execute(['parent' => $thisstoryline['parent'], 'level' => $thisstoryline['level'], 'id' => $child['id']]);
		}
		
		$stmt = $cc->prepare("DELETE FROM cc_" . $tableprefix . "comics_storyline WHERE id=:id");
		$stmt->execute(['id' => $thisstoryline['id']]);
		
		//give success message
		?>
		<div class="msg success f-c"><?=str_replace("%s",$thisstoryline['name'],$lang['%s has been deleted.'])?></div>
		
		<?
		
		
	}else{
	
		//prompt user to delete page ?>

		<div class="msg prompt f-c"><?=str_replace("%s",$thisstoryline['name'],$lang['Are you sure you want to delete %s? This action cannot be undone.'])?> <?=$lang['Sub-storylines will be moved up to this storyline\'s level.']?></div>
		<?

		echo '<div class="btn-row">';
		buildButton(
			"light-bg",
			$ccurl . $navslug.'/'.$ccpage->slug.'/delete-storyline/' . $thisstoryline['id'] . '/confirmed',
			$lang['Yes']
		);
		buildButton(
			"dark-bg",
			$ccurl . $navslug.'/'.$ccpage->slug."/manage-storylines",
			$lang['No']
		);
		echo '</div>';
		
	}

}
?>

</main>