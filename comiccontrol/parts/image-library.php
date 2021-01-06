<?

//image-library.php - manages all functions for the image library

if($ccuser->authlevel != 2){
	echo '<main id="content"><div class="msg error f-c">' . $lang['You do not have permission to access this page.'] . '</div>';
}else{
//handle image deletion
if(getSlug(2) == "delete-image"){

//create and output quick links
	$links = array(
		array(
			'link' => $ccurl . $navslug,
			'text' => str_replace('%s',$ccpage->title,$lang['Return to managing the image library'])
		)
	);
	quickLinks($links);
	
	echo '<main id="content">';

	//get current image
	$query = "SELECT * FROM cc_" . $tableprefix . "images WHERE id=:id";
	$stmt = $cc->prepare($query);
	$stmt->execute(['id' => getSlug(3)]);
	$thisimage = $stmt->fetch();

	//output error if the image wasn't found
	if(empty($thisimage)){
		echo '<div class="msg error f-c">' . $lang['No image was found with this information.'] . '</div>';
	}
	
	//proceed if the image was found
	else{
		
		//delete the image if confirmed
		if(getSlug(4) == "confirmed"){
			
			$stmt = $cc->prepare("DELETE FROM cc_" . $tableprefix . "images WHERE id=:id");
			$stmt->execute(['id' => $thisimage['id']]);
			?>
			
			<div class="msg success f-c"><?=$lang['This image has been deleted.']?></div>
			
			<?			
			
		}else{
		
			//prompt user to delete page ?>

			<div class="msg prompt f-c"><?=$lang['Are you sure you want to delete this image? This action cannot be undone.']?></div>
			<?

			echo '<div class="cc-btn-row">';
			buildButton(
				"light-bg",
				$ccurl . $navslug.'/delete-image/' . $thisimage['id'] . '/confirmed',
				$lang['Yes']
			);
			buildButton(
				"dark-bg",
				$ccurl . $navslug,
				$lang['No']
			);
			echo '</div>';
			
		}

	}
}else{
	
	echo '<main id="content">';

	//include necessary libraries
	?><script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js" type="text/javascript" /></script><?

	//submit image if posted
	if(isset($_POST) && $_POST['image-finalfile'] != ""){
		
		//set values for the query 
		$imgname = $_POST['image-finalfile'];
		$thumbname = $_POST['image-thumbnail'];
		
		//create query
		$query = "INSERT INTO cc_" . $tableprefix . "images(imgname, thumbname) VALUES(:imgname,:thumbname)";
		$stmt = $cc->prepare($query);
		$stmt->execute(['imgname' => $imgname, 'thumbname' => $thumbname]);
		$imageid = $cc->lastInsertId();
		
		//continue if post successfully added
		if($stmt->rowCount() > 0){
			?>
			<div class="msg success f-c"><?=$lang['Your image was successfully added.']?></div>
			<?
		}
			
		//output error message if failed
		else{
			?>
			<div class="msg error f-c"><?=$lang['There was an error adding your image.  Please try again.']?></div>
			<?
		}
		
	}

	//start the form for adding an image ?>

	<form action="" method="post" enctype="multipart/form-data">
		
	<? // image uploader area ?>
	<? buildImageInput($lang['Add image...'],false); ?>
	</form>
	<div style="clear:both; height:15px;"></div>
	
	<? 
	//include relevant javascript
	$imgfolder = "uploads";
	include('includes/form-submit-js.php');
	include('includes/img-upload-js.php');
	
	//include container for holding ajax request results
	?>
	<div class="manage-container dark-bg"></div>
	
	<script src="<?=$ccurl?>js/container.js"></script>
	<script>
	//create initial postdata for ajax request

	let container = $(".manage-container").first();
	let initialState = {
		heading: "<?=$lang['Page %s']?>",
		root: "<?=$ccsite->root?>",
		ccroot: "<?=$ccsite->ccroot?>",
		action: "media"
	};
	let lang = {
      delete: "<?=$lang["Delete"]?>",
      edit:"<?=$lang["Edit"]?>",
      preview:"<?=$lang["Preview"]?>",
      page:"<?=$lang["Page %s"]?>",
	};
	let mediaContainer = new MediaContainer(container,initialState,lang);

	</script>

<? 

}
}

?>

</main>