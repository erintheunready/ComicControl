<? //text-edit.php - handles editing text modules 

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->slug.'/manage-options',
		'text' => $lang['Manage options']
	),
	array(
		'link' => $ccsite->root . $ccsite->relativepath . $ccpage->slug . '/' . $ccpage->slug,
		'text' => str_replace('%s',htmlentities($ccpage->title),$lang['Preview %s'])
	)
);
quickLinks($links);

?>

<main id="content">

<? //include necessary libraries ?>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js" type="text/javascript" /></script>

<? 

//get the text content
$query = "SELECT * FROM cc_" . $tableprefix . "text WHERE id=:id";
$stmt = $cc->prepare($query);
$stmt->execute(['id' => $ccpage->module->id]);
$text = $stmt->fetch();

//submit content if posted
if(isset($_POST) && $_POST['submitted'] != ""){
	
	//set values for the query 
	$content = $_POST['text-content'];
	
	//execute query
	$query = "UPDATE cc_" . $tableprefix . "text SET content=:content WHERE id=:id LIMIT 1";
	$stmt = $cc->prepare($query);
	$stmt->execute(['content' => $content, 'id' => $ccpage->module->id]);
	
	//continue if the text was successfully edited
	if($stmt->rowCount() > 0){
		
		?>
		<div class="msg success f-c"><?=str_replace('%s',$title,$lang['Your changes have been saved.'])?></div>
		<?		
		
		//get the edited text
		$query = "SELECT * FROM cc_" . $tableprefix . "text WHERE id=:id";
		$stmt = $cc->prepare($query);
		$stmt->execute(['id' => $ccpage->module->id]);
		$text = $stmt->fetch();
	}
		
	//output error message if failed
	else{
		?>
		<div class="msg error f-c"><?=$lang['There was an error editing your text page.  Please try again.']?></div>
		<?
	}
	
}

//output the form for editing the text ?>

<h2><?=str_replace("%s",$ccpage->title,$lang['Editing <i>%s</i>'])?></h2>

<form action="" method="post">
		
	<input type="hidden" value="submitted" name="submitted" />	
		
	<div class="formcontain">
		<?
			buildTextEditor($lang['Text content'],"text-content",$lang['tooltip-textcontent'],$text['content']);
		?>
	</div>
	
	<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Submit changes']?></button>
</form>

<? 
//include relevant javascript
include('includes/content-editor-js.php');
include('includes/form-submit-js.php');

?>

</main>