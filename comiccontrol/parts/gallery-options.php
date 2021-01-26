<?php //gallery-options.php - handles options editing for gallery modules ?>

<?php

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->module->slug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to managing %s'])
	),
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->module->slug . '/add-image',
		'text' => $lang['Add an image']
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
	
	//rebuild module so correct options display
	$ccpage = new CC_Page("$_SERVER[REQUEST_URI]","admin");
	
	//output success message
	echo '<div class="msg success f-c">' . $lang['changeoptions-success'] . '</div>';
	
}


//include general module options
require_once('module-options.php');

//build text display options
$forminputs = array();
array_push($forminputs,
	array(
		array(
			'type' => "select",
			'label' => $lang['Display title'],
			'tooltip' => $lang['tooltip-displaygallerytitle'],
			'name' => 'showTitle',
			'options' => array(
				'on' => $lang['Yes'],
				'off' => $lang['No']
			),
			'current' => $ccpage->module->options['showTitle']
		),
		array(
			'type' => "select",
			'label' => $lang['Display description'],
			'tooltip' => $lang['tooltip-displaydescription'],
			'name' => 'showDescription',
			'options' => array(
				'on' => $lang['Yes'],
				'off' => $lang['No']
			),
			'current' => $ccpage->module->options['showDescription']
		)
	)
);

//echo text display options
echo '<h2 class="formheader">' . $lang['Text display options'] . '</h2>';
buildForm($forminputs);

//build thumbnail options
$forminputs = array();
array_push($forminputs,
	array(
		array(
			'type' => "text",
			'label' => $lang['Thumbnail width'],
			'tooltip' => $lang['tooltip-gallerythumbwidth'],
			'name' => 'thumbwidth',
			'current' => $ccpage->module->options['thumbwidth'],
			'regex' => 'int'
		),
		array(
			'type' => "text",
			'label' => $lang['Thumbnail height'],
			'tooltip' => $lang['tooltip-gallerythumbheight'],
			'name' => 'thumbheight',
			'current' => $ccpage->module->options['thumbheight'],
			'regex' => 'int'
		)
	)
);

//echo thumbnail options
echo '<h2 class="formheader">' . $lang['Thumbnail options'] . '</h2>';
buildForm($forminputs);




?>

<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Submit changes']?></button>
</form>

<?php

include('includes/form-submit-js.php');
?>

</main>