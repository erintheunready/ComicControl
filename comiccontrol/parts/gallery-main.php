<? //gallery-main.php - main page for managing gallery modules ?>

<main id="content">

<? //buttons for adding and rearranging images as well as managing gallery options ?>
<div class="btn-row"><a class="btn light-bg" href="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/add-image/'?>"><?=$lang['Add an image']?></a></div>
<div class="btn-row"><a class="btn dark-bg" href="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/description/'?>"><?=$lang['Edit gallery description']?></a><a class="btn dark-bg" href="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/rearrange-images/'?>"><?=$lang['Rearrange images']?></a><a class="btn dark-bg" href="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/manage-options/'?>"><?=$lang['Manage gallery options']?></a></div>

<? //output container for holding AJAX request ?>
<div class="manage-container dark-bg"></div>


<? 
//include relevant javascript for ajax requests
include('includes/navigate-container.php'); ?>

<script>

//create initial postdata for ajax request
var postdata = {
	page: 1,
	searchid: <?=$ccpage->module->id?>,
	type: "gallery"
};

changeContainer(postdata,'down');

</script>

</main>