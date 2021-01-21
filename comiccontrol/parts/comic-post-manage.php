<? //comic-storyline-manage.php - handles navigating through storylines for editing and deletion ?>

<?

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->module->slug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to managing %s'])
	),
);
quickLinks($links);

?>

<main id="content">

<? //output container for holding AJAX request results ?>
<div class="manage-container dark-bg"></div>

	<script src="<?=$ccurl?>js/container.js"></script>
	<script>
	//create initial postdata for ajax request

	let container = $(".manage-container").first();
	let initialState = {
		heading: "<?=$lang['Top level']?>",
		root: "<?=$ccsite->root?>",
		ccroot: "<?=$ccsite->ccroot?>",
		action: "storyline",
		moduleid: <?=$ccpage->module->id?>,
		moduleSlug: "<?=$ccpage->module->slug?>"
	};
	let lang = {
      delete: "<?=$lang["Delete"]?>",
      edit:"<?=$lang["Edit"]?>",
      preview:"<?=$lang["Preview"]?>",
      toplevel:"<?=$lang["Top level"]?>",
      storylines:"<?=$lang["Storylines"]?>",
      pages:"<?=$lang["Pages"]?>",
      addstoryline:"<?=$lang["Add a storyline here"]?>",
      rearrange:"<?=$lang["Rearrange these storylines"]?>"
	};
	let storylineContainer = new StorylineContainer(container,initialState,lang);

	</script>
</script>

</main>