<? //comic-storyline-edit.php - handles editing existing storylines ?>
<? //include necessary libraries ?>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js" type="text/javascript" /></script>

<?

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

<?

//submit page if posted
if(isset($_POST) && $_POST['storyline-title'] != ""){
	
	$error = false;
	
	//set the storyline 
	$storyline = 1;
	if(filter_var($_POST['storyline-id'], FILTER_VALIDATE_INT)) $storyline = $_POST['storyline-id'];
	
	//check that storyline exists
	$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE id=:storyline";
	$stmt = $cc->prepare($query);
	$stmt->execute(['storyline' => $storyline]);
	$thisstoryline = $stmt->fetch();

	//set values for the query
	$name = $_POST['storyline-title'];
	$caption = $_POST['storyline-caption'];
	if(isset($_POST['image-finalfile']) && $_POST['image-finalfile'] != "") $thumbnail = $_POST['image-finalfile'];
	else $thumbnail = $thisstoryline['thumbnail'];
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
		
			
		//check that parent is not parent of this storyline's tree
		$currparent = $parent;
		while($currparent != $storyline && $currparent != 0){
			$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE id=:parent";
			$stmt = $cc->prepare($query);
			$stmt->execute(['parent' => $currparent]);
			$parentrow = $stmt->fetch();
			$currparent = $parentrow['parent'];
		}
		if($currparent == $storyline){
			$error = $lang['NOTE: You cannot make a storyline a sub-storyline of itself. The parent storyline was not changed.'];
			$parent = $thisstoryline['parent'];
			$level = $thisstoryline['level'];
		}
	}
	
	//don't change order if it's in the same parent storyline
	if($parent == $thisstoryline['parent']){
		$sorder = $thisstoryline['sorder'];
	}else{
	
		//get siblings to determine order in parent storyline
		$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE parent=:parent ORDER BY sorder ASC";
		$stmt = $cc->prepare($query);
		$stmt->execute(['parent' => $parent]);
		$siblings = $stmt->fetchAll();
		if(count($siblings) > 0){
			$lastsibling = array_pop($siblings);
			$sorder = $lastsibling['sorder'] + 1;
		}else $sorder = 0;
		
	}
	
	//modify storyline to reflect new info
	$query = "UPDATE cc_" . $tableprefix . "comics_storyline SET name=:name, sorder=:sorder, parent=:parent, level=:level, thumbnail=:thumbnail, caption=:caption WHERE id=:storyline LIMIT 1";
	$stmt = $cc->prepare($query);
	$stmt->execute(['name' => $name, 'sorder' => $sorder, 'parent' => $parent, 'level' => $level, 'caption' => $caption, 'thumbnail' => $thumbnail, 'storyline' => $storyline]);
	
	//continue if storyline successfully edited
	
	if($stmt->rowCount() > 0){
		
		//output results
		?>
		<div class="msg success f-c"><?=str_replace('%s',$name,$lang['%s has been successfully edited.'])?></div>
		<?		
		if($error != false){
			?><div class="msg error f-c"><?=$error?></div><?
		}
		echo '<div class="cc-btn-row">';
		buildButton(
			"light-bg",
			$ccurl . $navslug . '/' . $ccpage->module->slug . '/edit-storyline/' . $thisstoryline['id'],
			str_replace('%s',htmlentities($title),$lang['Edit %s again'])
		);
		echo '</div>';
		
	}
		
	//output error message if failed
	else{
		?>
		<div class="msg error f-c"><?=$lang['There was an error editing your storyline.  Please try again.']?></div>
		<?
	}

}else{
	
	//set the storyline 
	$storyline = getSlug(4);
	if(getSlug(5) == "remove-thumbnail"){
		$query = "UPDATE cc_" . $tableprefix . "comics_storyline SET thumbnail='' WHERE id=:storyline";
		$stmt = $cc->prepare($query);
		$stmt->execute(['storyline' => $storyline]);
	}
	
	//get the storyline info
	$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE id=:storyline";
	$stmt = $cc->prepare($query);
	$stmt->execute(['storyline' => $storyline]);
	$thisstoryline = $stmt->fetch();
	
	//if not found, throw error
	if(count($thisstoryline) == 0){
		echo '<div class="msg error f-c">' . $lang['No storyline was found with this information.'] . '</div>';
	}
	//if found, proceed
	else{
		?>

		<form action="" method="post" enctype="multipart/form-data">
			<div class="formcontain">
				<input type="hidden" name="storyline-id" value="<?=$thisstoryline['id']?>" />
				<?
				
				//build array of form info
				$forminputs = array();
				
				array_push($forminputs,
					array(
						array(
							'type' => "text",
							'label' => $lang['Storyline title'],
							'tooltip' => $lang['tooltip-storylinetitle'],
							'name' => "storyline-title",
							'regex' => "normal-text",
							'current' => $thisstoryline['name']
						)
					),array(
						array(
							'type' => "storylines",
							'label' => $lang['Parent storyline'],
							'tooltip' => $lang['tooltip-parentstoryline'],
							'name' => "comic-storyline",
							'regex' => "storyline",
							'current' => $thisstoryline['parent'],
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
				buildForm($forminputs);

				?><div class="custom-thumbnail"><?
				if($thisstoryline['thumbnail'] == "") echo '<p>' . $lang['There is currently no custom thumbnail.  The thumbnail of the first page in this chapter will be used as the thumbnail.'] . '</p>';
				else{
					?>
					<div class="currentfileholder"><button class="full-width dark-bg toggle-current-file"><span class="current-file-text"><?=$lang['View current custom thumbnail']?></span> <i class="fa fa-angle-down"></i></button>
						<div class="currentfile"><img src="<?=$ccsite->root . 'comicsthumbs/' . $thisstoryline['thumbnail']?>" /></div>
					</div>
					<?
				}
				
				// image uploader area 
				buildImageInput($lang['Choose custom thumbnail...'],false);
				if($thisstoryline['thumbnail'] != ""){
				?><button class="full-width dark-bg" id="remove-thumbnail"><?=$lang['Remove custom thumbnail']?></button>
				<script>
				$('#remove-thumbnail').click(function(e){
					e.preventDefault();
					window.location.href='<? echo $ccurl . $navslug . '/' . $ccpage->module->slug . '/edit-storyline/' . $thisstoryline['id'] . '/remove-thumbnail';?>';
				});
				</script>
				<?}
				
				//close out the form
				?>
				</div>
				<button class="full-width light-bg" style="margin:20px 0;" type="button" id="submitform"><?=$lang['Submit changes']?></button>
			</div>
		</form>
		<script>
		//manages avatar image pull down area
		$('.toggle-current-file').on('click',function(e){
			e.preventDefault();
			$(this).parent().find('.currentfile').slideToggle();
			$(this).find('.current-file-text').text(function(i, text){
				  return text === '<?=$lang['View current custom thumbnail']?>' ? '<?=$lang['Hide current avatar']?>' : '<?=$lang['View current custom thumbnail']?>';
			});
			$(this).find('i').toggleClass('fa-angle-down fa-angle-up');
		});
		</script>
		<? 
	}
}

//include relevant javascript
$imgfolder = "comicsthumbs/";
include('includes/img-upload-js.php');
include('includes/form-submit-js.php'); ?>

</main>