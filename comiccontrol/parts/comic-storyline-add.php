<?php //comic-storyline-add.php - handles adding new storylines ?>
<?php //include necessary libraries ?>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js" type="text/javascript" /></script>


<?php

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->module->slug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to managing %s'])
	),
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->module->slug."/manage-storylines",
		'text' => $lang['Edit a different storyline']
	),
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->module->slug."/add-storyline",
		'text' => $lang['Add another storyline']
	)
);
quickLinks($links);

?>

<main id="content">

<?php

//submit page if posted
if(isset($_POST) && $_POST['storyline-title'] != ""){

	//set values for the query
	$name = $_POST['storyline-title'];
	$caption = $_POST['storyline-caption'];
	if(isset($_POST['image-finalfile']) && $_POST['image-finalfile'] != "") $thumbnail = $_POST['image-finalfile'];
	else $thumbnail = "";
	$parent = 0;
	if(filter_var($_POST['comic-storyline'], FILTER_VALIDATE_INT)) $parent = $_POST['comic-storyline'];
	$comic = $ccpage->id;
	
	//get information about parent storyline
	if($parent == 0) $level = 0;
	else{
		$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE id=:parent";
		$stmt = $cc->prepare($query);
		$stmt->execute(['parent' => $parent]);
		$parentrow = $stmt->fetch();
		$level = $parentrow['level'] + 1;
	}
	
	//get siblings to determine order in parent storyline
	$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE parent=:parent ORDER BY sorder ASC";
	$stmt = $cc->prepare($query);
	$stmt->execute(['parent' => $parent]);
	$siblings = $stmt->fetchAll();
	if(count($siblings) > 0){
		$lastsibling = array_pop($siblings);
		$sorder = $lastsibling['sorder'] + 1;
	}else $sorder = 0;
	
	//insert new storyline into database
	$query = "INSERT INTO cc_" . $tableprefix . "comics_storyline(name,sorder,comic,parent,level,thumbnail,caption) VALUES(:name,:sorder,:comic,:parent,:level,:thumbnail,:caption)";
	$stmt = $cc->prepare($query);
	$stmt->execute(['name' => $name, 'sorder' => $sorder, 'comic' => $comic, 'parent' => $parent, 'level' => $level, 'thumbnail' => $thumbnail, 'caption' => $caption]);
	$thisstoryline =  $cc->lastInsertId();
	
	//continue if storyline successfully added
	
	if($stmt->rowCount() > 0){
		
		?>
		<div class="msg success f-c"><?=str_replace('%s',$name,$lang['%s has been successfully added.'])?></div>
		<?php		
		if($error != false){
			?><div class="msg error f-c"><?=$error?></div><?php
		}
		echo '<div class="cc-btn-row">';
		buildButton(
			"light-bg",
			$ccurl . $navslug . '/' . $ccpage->module->slug . '/edit-storyline/' . $thisstoryline,
			str_replace('%s',htmlentities($name),$lang['Edit %s'])
		);
		echo '</div>';
		
	}
		
	//output error message if failed
	else{
		?>
		<div class="msg error f-c"><?=$lang['There was an error adding your storyline.  Please try again.']?></div>
		<?php
	}

}else{
?>

<form action="" method="post" enctype="multipart/form-data">
	<div class="formcontain">
		<?php

		//check storyline is set
		$storyline = 0;
		if(filter_var($ccpage->slugarr[4], FILTER_VALIDATE_INT)) $storyline = $ccpage->slugarr[4];
		
		//build array of form info
		$forminputs = array();
		array_push($forminputs,
			array(
				array(
					'type' => "text",
					'label' => $lang['Storyline title'],
					'tooltip' => $lang['tooltip-storylinetitle'],
					'name' => "storyline-title",
					'regex' => "normal-text"
				)
			),array(
				array(
					'type' => "storylines",
					'label' => $lang['Parent storyline'],
					'tooltip' => $lang['tooltip-parentstoryline'],
					'name' => "comic-storyline",
					'regex' => "storyline",
					'current' => $storyline,
					'needsparent' => true
				)
			),array(
							array(
								'type' => "text",
								'label' => $lang['Storyline caption'],
								'name'=> "storyline-caption",
								'current' => $thisstoryline['caption']
							)
						)
		);

		//build the form
		buildForm($forminputs) 

			?><div class="custom-thumbnail"><?php
			
				// image uploader area 
				buildImageInput($lang['Choose custom thumbnail...'],false);
				

		//close out the form
		?>
		<button class="full-width light-bg" style="margin:20px 0;" type="button" id="submitform"><?=$lang['Submit new storyline']?></button>
	</div>
</form>
<?php 
}

//include relevant javascript
$imgfolder = "comicsthumbs/";
include('includes/img-upload-js.php');
include('includes/form-submit-js.php'); ?>

</main>