<?php
//blog-options.php - page for managing blog module options

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->module->slug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to managing %s'])
	),
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->module->slug . '/add-post',
		'text' => $lang['Add a blog post']
	)
);
quickLinks($links);
?>

<main id="content">

<?php

//submit options if posted
if(isset($_POST) && $_POST['page-title'] != ""){

	//save options
	require_once('save-options.php');
	
	//rebuild page and module so options are updated
	$ccpage = new CC_Page("$_SERVER[REQUEST_URI]","admin");
	
	//success message
	echo '<div class="msg success f-c">' . $lang['changeoptions-success'] . '</div>';
	
}

//include default module options
require_once('module-options.php');

//build post options
$forminputs = array();
array_push($forminputs,
	array(
		array(
			'type' => "select",
			'label' => $lang['Display comments'],
			'tooltip' => $lang['tooltip-blogdisplaycomments'],
			'name' => 'displaycomments',
			'options' => array(
				'on' => $lang['Yes'],
				'off' => $lang['No']
			),
			'current' => $ccpage->module->options['displaycomments']
		),
		array(
			'type' => "select",
			'label' => $lang['Display tags'],
			'tooltip' => $lang['tooltip-blogdisplaytags'],
			'name' => 'displaytags',
			'options' => array(
				'on' => $lang['Yes'],
				'off' => $lang['No']
			),
			'current' => $ccpage->module->options['displaytags']
		)
	)
);

//echo post options
echo '<h2 class="formheader">' . $lang['Post options'] . '</h2>';
buildForm($forminputs);

//build archive options
$forminputs = array();
array_push($forminputs,
	array(
		array(
			'type' => "text",
			'label' => $lang['Posts per page'],
			'tooltip' => $lang['tooltip-blogperpage'],
			'name' => 'perpage',
			'current' => $ccpage->module->options['perpage'],
			'regex' => 'int'
		),
		array(
			'type' => "select",
			'label' => $lang['Archive order'],
			'tooltip' => $lang['tooltip-blogarchiveorder'],
			'name' => 'archiveorder',
			'options' => array(
				'DESC' => $lang['Latest posts first'],
				'ASC' => $lang['Oldest posts first']
			),
			'current' => $ccpage->module->options['archiveorder']
		)
	)
);

//echo archive options
echo '<h2 class="formheader">' . $lang['Archive options'] . '</h2>';
buildForm($forminputs);

//close out the form
?>
<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Submit changes']?></button>
</form>

<?php

//include necessary scripts
require_once('includes/form-submit-js.php');

?>

</main>