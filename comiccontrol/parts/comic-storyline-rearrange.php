<? //comic-storyline-rearrange.php - handles rearranging storylines ?>

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

<? //save changes if form is submitted ?>
<? if(isset($_POST['neworder']) && $_POST['neworder'] != ""){
	
	$neworder = $_POST['neworder'];
	$neworder = explode('&',$neworder);
	$count = 0;
	$query = "UPDATE cc_" . $tableprefix . "comics_storyline SET sorder=:order WHERE id=:id";
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
<p><?=$lang['Drag and drop storylines to rearrange them.  Click "Save Changes" to save the new order.']?></p>

<form id="rearrange-form" action="" method="post">
	<input type="hidden" name="storyline" value="<?=$_POST['storyline']?>" />

	<div id="storyline-list">

		<?
		
			$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE parent=:parent ORDER BY sorder ASC";
			$stmt = $cc->prepare($query);
			$stmt->execute(['parent' => $_POST['storyline']]);
			$result = $stmt->fetchAll();
			
			foreach($result as $row){
				echo '<div class="btn-row" id="storyline_' . $row['id'] . '"><div class="btn dark-bg">' . $row['name'] . '</div></div>';
			}
		
		?>

	</div>
	
	<? //close out the form ?>
	<button type="button" class="full-width light-bg" id="savechanges">Save changes</button>

</form>

<script>

//script making list sortable and handling form submission
$("#storyline-list").sortable();
$("#storyline-list").disableSelection();
$('#savechanges').on('click', function(){
	var sorted = $("#storyline-list").sortable("serialize"); 
	$('#rearrange-form').append('<input type="hidden" name="neworder" value="' + sorted + '" />').submit();
});
 </script>
 
 </main>