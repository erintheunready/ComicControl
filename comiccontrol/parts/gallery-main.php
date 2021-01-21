<? //gallery-main.php - main page for managing gallery modules ?>

<main id="content">

<? //buttons for adding and rearranging images as well as managing gallery options ?>
<div class="cc-btn-row"><a class="cc-btn light-bg" href="<?=$ccurl . $navslug . '/' . $ccpage->module->slug . '/add-image/'?>"><?=$lang['Add an image']?></a></div>
<div class="cc-btn-row"><a class="cc-btn dark-bg" href="<?=$ccurl . $navslug . '/' . $ccpage->module->slug . '/description/'?>"><?=$lang['Edit gallery description']?></a><a class="cc-btn dark-bg" href="<?=$ccurl . $navslug . '/' . $ccpage->module->slug . '/rearrange-images/'?>"><?=$lang['Rearrange images']?></a><a class="cc-btn dark-bg" href="<?=$ccurl . $navslug . '/' . $ccpage->module->slug . '/manage-options/'?>"><?=$lang['Manage gallery options']?></a></div>

<? //output container for holding AJAX request ?>
<div class="manage-container dark-bg"></div>


<script src="<?=$ccurl?>js/container.js"></script>
	<script>
	//create initial postdata for ajax request

	let container = $(".manage-container").first();
	let initialState = {
		heading: "<?=$lang['Page %s']?>",
		root: "<?=$ccsite->root?>",
		ccroot: "<?=$ccsite->ccroot?>",
		action: "gallery",
		moduleid: <?=$ccpage->module->id?>,
		moduleSlug: "<?=$ccpage->module->slug?>"
	};
	let lang = {
      delete: "<?=$lang["Delete"]?>",
      edit:"<?=$lang["Edit"]?>",
      preview:"<?=$lang["Preview"]?>",
      page:"<?=$lang["Page %s"]?>",
	};
	let galleryContainer = new GalleryContainer(container,initialState,lang);

	</script>


</main>