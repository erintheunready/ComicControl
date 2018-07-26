<? //comic-storyline-add.php - handles adding new storylines ?>

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
	$query = "INSERT INTO cc_" . $tableprefix . "comics_storyline(name,sorder,comic,parent,level) VALUES(:name,:sorder,:comic,:parent,:level)";
	$stmt = $cc->prepare($query);
	$stmt->execute(['name' => $name, 'sorder' => $sorder, 'comic' => $comic, 'parent' => $parent, 'level' => $level]);
	$thisstoryline =  $cc->lastInsertId();
	
	//continue if storyline successfully added
	
	if($stmt->rowCount() > 0){
		
		?>
		<div class="msg success f-c"><?=str_replace('%s',$name,$lang['%s has been successfully added.'])?></div>
		<?		
		if($error != false){
			?><div class="msg error f-c"><?=$error?></div><?
		}
		echo '<div class="btn-row">';
		buildButton(
			"light-bg",
			$ccurl . $navslug . '/' . $ccpage->slug . '/edit-storyline/' . $thisstoryline,
			str_replace('%s',htmlentities($name),$lang['Edit %s'])
		);
		echo '</div>';
		
	}
		
	//output error message if failed
	else{
		?>
		<div class="msg error f-c"><?=$lang['There was an error adding your storyline.  Please try again.']?></div>
		<?
	}

}else{
?>

<form action="" method="post" enctype="multipart/form-data">
	<div class="formcontain">
		<?

		//check storyline is set
		$storyline = 0;
		if(filter_var($_POST['storyline'], FILTER_VALIDATE_INT)) $storyline = $_POST['storyline'];
		
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
			)
		);

		//build the form
		buildForm($forminputs) 

		//close out the form
		?>
		<button class="full-width light-bg" style="margin:20px 0;" type="button" id="submitform"><?=$lang['Submit new storyline']?></button>
	</div>
</form>
<? 
}

//include relevant javascript
include('includes/form-submit-js.php'); ?>

</main>