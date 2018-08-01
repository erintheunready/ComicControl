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

<? 
//include script to manage javascript and AJAX for container
include('includes/navigate-container.php');


//do initial AJAX request to get first page of blog posts ?>
<script>
var postdata = {
	page: 1,
	searchid: <?=$ccpage->module->id?>,
	type: "blog"
};
changeContainer(postdata,'down');
</script>

</main>