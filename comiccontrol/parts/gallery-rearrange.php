<? //gallery-rearrange.php - handles rearranging images in a gallery ?>

<?

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->slug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to managing %s'])
	),
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->slug . '/add-image',
		'text' => $lang['Add an image']
	)
);
quickLinks($links);

?>

<main id="content">

<? //save changes ?>
<? if(isset($_POST['neworder']) && $_POST['neworder'] != ""){
	
	$neworder = $_POST['neworder'];
	$neworder = explode('&',$neworder);
	$count = 0;
	$query = "UPDATE cc_" . $tableprefix . "galleries SET porder=:order WHERE id=:id";
	$stmt = $cc->prepare($query);
	foreach($neworder as $order){
		$thisid = filter_var($order, FILTER_SANITIZE_NUMBER_INT);
		$stmt->execute(['order' => $count, 'id' => $thisid]);
		$count++;
	}
	echo '<div class="msg success">' . $lang['Your changes have been saved.'] . '</div>';
}
?>

<? //include necessary libraries ?>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>


<? //output rearrange form ?>
<p><?=$lang['Drag and drop images to rearrange them.  Click "Save Changes" to save the new order.']?></p>

<form id="rearrange-form" action="" method="post">

	<div id="image-list">

		<?
		
			$query = "SELECT * FROM cc_" . $tableprefix . "galleries WHERE gallery=:gallery ORDER BY porder ASC";
			$stmt = $cc->prepare($query);
			$stmt->execute(['gallery' => $ccpage->module->id]);
			$result = $stmt->fetchAll();
			
			foreach($result as $row){
				echo '<div class="cc-btn-row" id="image_' . $row['id'] . '"><div class="cc-btn dark-bg"><img src="' . $ccsite->root . $ccsite->relativepath . 'uploads/' . $row['thumbname'] . '" /><div class="row-caption">' . $row['caption'] . '</div></div></div>';
			}
		
		?>

	</div>
	
	<? //close out the form ?>
	<button type="button" class="full-width light-bg" id="savechanges">Save changes</button>

</form>

<script>
//include script to make list sortable and submit form
$("#image-list").sortable();
$("#image-list").disableSelection();
$('#savechanges').on('click', function(){
	var sorted = $("#image-list").sortable("serialize"); 
	$('#rearrange-form').append('<input type="hidden" name="neworder" value="' + sorted + '" />').submit();
});
 </script>
 
 </main>