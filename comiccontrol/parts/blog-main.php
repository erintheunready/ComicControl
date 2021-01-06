<? //blog-main.php - the main page for any blog module. ?>

<main id="content">

<?

//build action buttons (add post and manage options)
echo '<div class="cc-btn-row">';
buildButton(
	"light-bg",
	$ccurl . $navslug . '/' . $ccpage->slug . '/add-post/',
	$lang['Add a blog post']
);
buildButton(
	"dark-bg",
	$ccurl . $navslug . '/' . $ccpage->slug . '/manage-options/',
	$lang['Manage blog options']
);
echo '</div>';
?>

<? //container to hold blog posts from AJAX request ?>
<div class="manage-container dark-bg"></div>


<script src="<?=$ccurl?>js/container.js"></script>
	<script>
	//create initial postdata for ajax request

	let container = $(".manage-container").first();
	let initialState = {
		heading: "<?=$lang['Page %s']?>",
		root: "<?=$ccsite->root?>",
		ccroot: "<?=$ccsite->ccroot?>",
		action: "blog",
		moduleid: <?=$ccpage->module->id?>,
		moduleSlug: "<?=$ccpage->module->slug?>"
	};
	let lang = {
      delete: "<?=$lang["Delete"]?>",
      edit:"<?=$lang["Edit"]?>",
      preview:"<?=$lang["Preview"]?>",
      page:"<?=$lang["Page %s"]?>",
	};
	let blogContainer = new BlogContainer(container,initialState, lang);

	</script>

</main>