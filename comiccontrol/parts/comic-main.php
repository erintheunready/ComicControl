<?

//comic-main.php - main page for comic modules

//part functions
function listPages($pageArr){
	global $ccpage;
	global $ccsite;
	global $ccurl;
	global $navslug;
	global $lang;
	
	foreach($pageArr as $post){
		echo '<li class="normal"><a class="arrow-toggle">' . $post['title'] . '</a><ul><li><a href="' . $ccurl . $navslug . '/' . $ccpage->slug . '/edit-post/' . $post['slug'] . '">' . $lang['Edit'] . '</a></li><li><a href="' . $ccurl . $navslug . '/' . $ccpage->slug . '/delete-post/' . $post['slug'] . '">' . $lang['Delete'] . '</a></li><li><a href="' . $ccsite->root . $ccsite->relativepath . $ccpage->slug . '/' . $post['slug']. '">' . $lang['Preview'] . '</a></li></ul></li>';
	}
}

?>

<main id="content">

<?

//output buttons for going to comic module management pages
echo '<div class="cc-btn-row">';
buildButton(
	"light-bg",
	$ccurl . $navslug.'/'.$ccpage->slug."/add-post",
	$lang['Add a comic post']
);
echo '</div>';
echo '<div class="cc-btn-row tall-row">';
buildButton(
	"dark-bg",
	$ccurl . $navslug . '/' . $ccpage->slug . '/manage-posts',
	$lang['Manage comic posts']
);
buildButton(
	"dark-bg",
	$ccurl . $navslug . '/' . $ccpage->slug . '/manage-storylines',
	$lang['Manage storylines']
);
buildButton(
	"dark-bg",
	$ccurl . $navslug . '/' . $ccpage->slug . '/manage-options',
	$lang['Manage options']
);
echo '</div>';

//output recently published and publishing soon pages with editing options
?>
<div class="cc-btn-row tall-row">
	<div class="blue-bg row-col">
		<div class="col-header dark-bg"><?=$lang['Recently published']?></div>
		<ul> <?
		$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "comics WHERE comic=:comicid AND publishtime <= " . time() . " ORDER BY publishtime DESC LIMIT 5");
		$stmt->execute(['comicid' => $ccpage->module->id]);
		$recentpages = $stmt->fetchAll();
		listPages($recentpages);
		?>
		</ul>
	</div>
	<div class="blue-bg row-col">
		<div class="col-header dark-bg"><?=$lang['Publishing soon']?></div>
		<ul><?
		$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "comics WHERE comic=:comicid AND publishtime > " . time() . " ORDER BY publishtime ASC LIMIT 5");
		$stmt->execute(['comicid' => $ccpage->module->id]);
		$nextpages = $stmt->fetchAll();
		listPages($nextpages);
		?>
		</ul>
	</div>
</div>
<script>
$('.arrow-toggle').on('click',function(){
	$(this).siblings('ul').slideToggle();
	$(this).parent().toggleClass('normal special');
});
</script>

</main>