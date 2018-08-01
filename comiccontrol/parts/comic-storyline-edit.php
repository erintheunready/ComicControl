<? //comic-storyline-edit.php - handles editing existing storylines ?>

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
	$query = "UPDATE cc_" . $tableprefix . "comics_storyline SET name=:name, sorder=:sorder, parent=:parent, level=:level WHERE id=:storyline LIMIT 1";
	$stmt = $cc->prepare($query);
	$stmt->execute(['name' => $name, 'sorder' => $sorder, 'parent' => $parent, 'level' => $level, 'storyline' => $storyline]);
	
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
			$ccurl . $navslug . '/' . $ccpage->slug . '/edit-storyline/' . $thisstoryline['id'],
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
					)
				);

				//build the form
				buildForm($forminputs) 

				//close out the form
				?>
				<button class="full-width light-bg" style="margin:20px 0;" type="button" id="submitform"><?=$lang['Submit changes']?></button>
			</div>
		</form>
		<? 
	}
}

//include relevant javascript
include('includes/form-submit-js.php'); ?>

</main>