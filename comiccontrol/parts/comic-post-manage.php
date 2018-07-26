<? //comic-post-manage.php - handles adding new comic posts ?>

<? //include necessary libraries ?>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js" type="text/javascript" /></script>

<?

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->slug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to managing %s'])
	),
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->slug."/add-post",
		'text' => $lang['Add a comic post']
	)
);
quickLinks($links);

?>

<main id="content">

<? //container to hold information from AJAX request ?>
<div class="manage-container dark-bg"></div>

<? // show button for adding storyline ?>
<form method="post" action="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/add-post'?>">
<button type="button" class="btn light-bg addstoryline"><?=str_replace('%s',$lang['Top level'],$lang['Add a comic to %s'])?></button>
<input type="hidden" name="storyline" value="0" />
</form>

<?
//include script to manage javascript and AJAX for container 
include('includes/navigate-container.php'); ?>

<script>

//create initial postdata for ajax request
var postdata = {
	searchid: 0,
	storylinename: 'Top level',
	getPages: true,
	type: "storyline",
	moduleid: <?=$ccpage->module->id?>
};

$('.addstoryline').on('click',function(){
	$(this).closest('form').submit();
});

//initiate first request
changeContainer(postdata);

</script>

</main>