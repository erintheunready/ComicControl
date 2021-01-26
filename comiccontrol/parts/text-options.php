<?php //text-edit.php - handles editing text modules 

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->module->slug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to editing %s'])
	)
);
quickLinks($links);

?>

<main id="content">
<?php
$forminputs = array();

//submit options if posted
if(isset($_POST) && $_POST['page-title'] != ""){

	require_once('save-options.php');
	
	$ccpage = new CC_Page("$_SERVER[REQUEST_URI]","admin");
	
	echo '<div class="msg success f-c">' . $lang['changeoptions-success'] . '</div>';
	
}

//output general module options
require_once('module-options.php');

//build text display options
$forminputs = array();
array_push($forminputs,
	array(
		array(
			'type' => "select",
			'label' => $lang['Display title'],
			'tooltip' => $lang['tooltip-displaytitle'],
			'name' => 'showTitle',
			'options' => array(
				'on' => $lang['Yes'],
				'off' => $lang['No']
			),
			'current' => $ccpage->module->options['showTitle']
		)
	)
);

//echo text display options
echo '<h2 class="formheader">' . $lang['Text display options'] . '</h2>';
buildForm($forminputs);


//close out the options form
?>
<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Submit changes']?></button>
</form>

<?php

//include relevant javascript
include('includes/form-submit-js.php');

?>

</div>