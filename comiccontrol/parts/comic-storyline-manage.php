<? //comic-storyline-manage.php - handles navigating through storylines for editing and deletion ?>

<?

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->slug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to managing %s'])
	),
);
quickLinks($links);

?>

<main id="content">

<? //output container for holding AJAX request results ?>
<div class="manage-container dark-bg"></div>

<? //output buttons for adding storylines and rearranging storylines ?>
<form method="post" action="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/add-storyline'?>">
<button type="button" class="btn light-bg addstoryline"><?=str_replace('%s',$lang['Top level'],$lang['Add a storyline to %s'])?></button>
<input type="hidden" name="storyline" value="0" />
</form>

<form method="post" action="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/rearrange-storylines'?>">
<button type="button" class="btn dark-bg rearrangestorylines"><?=$lang['Rearrange these storylines']?></button>
<input type="hidden" name="storyline" value="0" />
</form>

<? 
//include javascript for AJAX request
include('includes/navigate-container.php'); 
?>
<script>

//create initial postdata for ajax request
var postdata = {
	searchid: 0,
	storylinename: 'Top level',
	getPages: false,
	type: "storyline",
	moduleid: <?=$ccpage->module->id?>
};

$('.addstoryline,.rearrangestorylines').on('click',function(){
	$(this).closest('form').submit();
});

changeContainer(postdata);

</script>

</main>