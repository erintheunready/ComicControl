<? //gallery-add.php - handles adding new images ?>

<?

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->module->slug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to managing %s'])
	),
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->module->slug . '/add-image',
		'text' => $lang['Add another image']
	)
);
quickLinks($links);

?>

<main id="content">

<? //include necessary libraries ?>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js" type="text/javascript" /></script>

<? 

//submit page if posted
if(isset($_POST) && $_POST['image-finalfile'] != ""){
	
	//set values for the query 
	$imgname = $_POST['image-finalfile'];
	$thumbname = $_POST['image-thumbnail'];
	$caption = $_POST['image-caption'];
	$gallery = $ccpage->module->id;
	
	//get order in gallery
	$query = "SELECT porder FROM cc_" . $tableprefix . "galleries WHERE gallery=:gallery ORDER BY porder DESC LIMIT 1";
	$stmt = $cc->prepare($query);
	$stmt->execute(['gallery' => $gallery]);
	$row = $stmt->fetch();
	$porder = $row['porder'] + 1;
	
	//create query
	$query = "INSERT INTO cc_" . $tableprefix . "galleries(gallery, imgname, thumbname, caption, porder) VALUES(:gallery,:imgname,:thumbname,:caption,:porder)";
	$stmt = $cc->prepare($query);
	$stmt->execute(['gallery' => $gallery, 'imgname' => $imgname, 'thumbname' => $thumbname, 'caption' => $caption, 'porder' => $porder]);
	$imageid = $cc->lastInsertId();
	
	//continue if image successfully added
	if($stmt->rowCount() > 0){
		?>
		<div class="msg success f-c"><?=$lang['Your image was successfully added.']?></div>
		<?
		echo '<div class="cc-btn-row">';
		buildButton(
			"dark-bg",
			$ccurl . $navslug . '/' . $ccpage->module->slug . '/edit-image/' . $imageid,
			$lang['Edit this image']
		);
		echo '</div>';
	}
		
	//output error message if failed
	else{
		?>
		<div class="msg error f-c"><?=$lang['There was an error adding your image.  Please try again.']?></div>
		<?
	}
	
}else{

	//start the form ?>

	<form action="" method="post" enctype="multipart/form-data">
		
		<? 
		// image uploader area 
		buildImageInput($lang['Choose file...'],true);
		
		//build text editor for caption
		buildTextEditor($lang['Image caption'],"image-caption",$lang['tooltip-gallerycaption']);
		
		//close out the form
		?>
		<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Submit image']?></button>
	</form>
	<? 
	
	//include relevant javascript
	$imgfolder = "uploads/";
	include('includes/form-submit-js.php');
	include('includes/img-upload-js.php');
	include('includes/content-editor-js.php');
}
?>

</main>