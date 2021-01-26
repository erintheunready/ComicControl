<?php //comic-options.php - handles options for comic modules ?>

<main id="content">

<?php
$forminputs = array();

//submit options if posted
if(isset($_POST) && $_POST['page-title'] != ""){

	//save general modules options
	require_once('save-options.php');
	
	//parse and save navigation order
	$navorder = "";
	$piped = false;
	for($i = 0; $i < 5; $i++){
		$navname = "navorder" . $i;
		if($_POST[$navname] != "none"){
			if($piped){
				$navorder .= "|";
			}else{
				$piped = true;
			}
			$navorder .= $_POST[$navname];
		}
	}
	$stmt->execute(['value' => $navorder, 'optionname' => 'navorder']);
	
	//rebuild the module so options are updated
	$ccpage = new CC_Page("$_SERVER[REQUEST_URI]","admin");
	
	//output success message
	echo '<div class="msg success f-c">' . $lang['changeoptions-success'] . '</div>';
	
}

//output general module options
require_once('module-options.php');

//build comic display options
$forminputs = array();
array_push($forminputs,
	array(
		array(
			'type' => "text",
			'label' => $lang['Maximum comic width'],
			'tooltip' => $lang['tooltip-maxcomicwidth'],
			'name' => "comicwidth",
			'regex' => "int",
			'current' => $ccpage->module->options['comicwidth']
		),
	),
	array(
		array(
			'type' => "select",
			'label' => $lang['News display mode'],
			'tooltip' => $lang['tooltip-newsmode'],
			'name' => "newsmode",
			'options' => array(
				'eachpost' => $lang['Each post'],
				'latestnews' => $lang['Latest news']
			),
			'current' => $ccpage->module->options['newsmode']
		),
		array(
			'type' => "select",
			'label' => $lang['Display tags'],
			'tooltip' => $lang['tooltip-displaytags'],
			'name' => 'displaytags',
			'options' => array(
				'on' => $lang['Yes'],
				'off' => $lang['No']
			),
			'current' => $ccpage->module->options['displaytags']
		)
	),
	array(
		array(
			'type' => "select",
			'label' => $lang['Use transcripts'],
			'tooltip' => $lang['tooltip-displaytranscript'],
			'name' => 'displaytranscript',
			'options' => array(
				'on' => $lang['Yes'],
				'off' => $lang['No']
			),
			'current' => $ccpage->module->options['displaytranscript']
		),
		array(
			'type' => "select",
			'label' => $lang['Click to view transcript'],
			'tooltip' => $lang['tooltip-clicktoview'],
			'name' => 'transcriptclick',
			'options' => array(
				'on' => $lang['Yes'],
				'off' => $lang['No']
			),
			'current' => $ccpage->module->options['transcriptclick']
		)
	),
	array(
		array(
			'type' => "select",
			'label' => $lang['Use content warnings'],
			'tooltip' => $lang['tooltip-usecontentwarnings'],
			'name' => 'contentwarnings',
			'options' => array(
				'on' => $lang['Yes'],
				'off' => $lang['No']
			),
			'current' => $ccpage->module->options['contentwarnings']
		),
		array(
			'type' => "select",
			'label' => $lang['Display comments'],
			'tooltip' => $lang['tooltip-displaycomments'],
			'name' => 'displaycomments',
			'options' => array(
				'on' => $lang['Yes'],
				'off' => $lang['No']
			),
			'current' => $ccpage->module->options['displaycomments']
		)
	)
);

//echo comic display options
echo '<h2 class="formheader">' . $lang['Comic display options'] . '</h2>';
buildForm($forminputs);

//build comic navigation options
$forminputs = array(
	array(
		array(
			'type' => "select",
			'label' => $lang['Auxiliary button destination'],
			'tooltip' => $lang['tooltip-auxdestination'],
			'name' => 'navaux',
			'options' => array(
				$ccpage->module->slug . '/rss' => $lang['Comic RSS'],
				$ccpage->module->slug . '/archive' => $lang['Comic archive']
			),
			'current' => $ccpage->module->options['navaux']
		),
		array(
			'type' => "select",
			'label' => $lang['Action on clicking comic'],
			'tooltip' => $lang['tooltip-clickaction'],
			'name' => 'clickaction',
			'options' => array(
				'next' => $lang['Advance to next comic'],
				'fullscreen' => $lang['Enlarge comic'],
				'fullscreenbig' => $lang['Enlarge comic if oversized']
			),
			'current' => $ccpage->module->options['clickaction']
		)
	),
	array(
		array(
			'type' => "text",
			'label' => $lang['First button text'],
			'tooltip' => $lang['tooltip-firstbutton'],
			'name' => 'firsttext',
			'current' => $ccpage->module->options['firsttext']
		),
		array(
			'type' => "text",
			'label' => $lang['Previous button text'],
			'tooltip' => $lang['tooltip-prevbutton'],
			'name' => 'prevtext',
			'current' => $ccpage->module->options['prevtext']
		)
	),
	array(
		array(
			'type' => "text",
			'label' => $lang['Next button text'],
			'tooltip' => $lang['tooltip-nextbutton'],
			'name' => 'nexttext',
			'current' => $ccpage->module->options['nexttext']
		),
		array(
			'type' => "text",
			'label' => $lang['Last button text'],
			'tooltip' => $lang['tooltip-lastbutton'],
			'name' => 'lasttext',
			'current' => $ccpage->module->options['lasttext']
		)
	),
	array(
		array(
			'type' => "text",
			'label' => $lang['Auxiliary button text'],
			'tooltip' => $lang['tooltip-auxbutton'],
			'name' => 'auxtext',
			'current' => $ccpage->module->options['auxtext']
		),
		array(
			'type' => "select",
			'label' => $lang['Arrow key navigation'],
			'tooltip' => $lang['tooltip-arrowkey'],
			'name' => 'arrowkey',
			'options' => array(
				'on' => $lang['On'],
				'off' => $lang['Off']
			),
			'current' => $ccpage->module->options['arrowkey']
		)
	)
);

//get navigation order
$navorder = $ccpage->module->options['navorder'];
$navorder = explode('|',$navorder);

//echo comic navigation options
echo '<h2 class="formheader">' . $lang['Comic navigation options'] . '</h2>';
echo '<div class="formline">';
echo '<div class="forminput"><label><div class="v-c">' . $lang['Navigation buttons'] . ':</div></label>';
echo '<div class="navselect">';
for($i = 0; $i < 5; $i++){
	echo '<select name="navorder' . $i . '">';
	echo '<option value="none">' . $lang['None'] . '</option>';
	echo '<option value="first"';
	if($navorder[$i] == "first") echo ' SELECTED';
	echo '>' . $lang['First'] . '</option>';
	echo '<option value="prev"';
	if($navorder[$i] == "prev") echo ' SELECTED';
	echo '>' . $lang['Previous'] . '</option>';
	echo '<option value="aux"';
	if($navorder[$i] == "aux") echo ' SELECTED';
	echo '>' . $lang['Auxiliary'] . '</option>';
	echo '<option value="next"';
	if($navorder[$i] == "next") echo ' SELECTED';
	echo '>' . $lang['Next'] . '</option>';
	echo '<option value="last"';
	if($navorder[$i] == "last") echo ' SELECTED';
	echo '>' . $lang['Last'] . '</option>';
	echo '</select>';
	if($i != 5) echo '&nbsp;';
}
echo '</div><div class="tooltip"><a class="f-c">?</a>';
echo '<div class="tooltip-help"><div class="tooltip-triangle"></div>' . $lang['tooltip-navorder'] . '</div>';
echo '</div></div></div>';
buildForm($forminputs);

//build mobile navigation options
$forminputs = array(
	array(
		array(
			'type' => "select",
			'label' => $lang['Action on tapping comic'],
			'tooltip' => $lang['tooltip-tapaction'],
			'name' => 'touchaction',
			'current' => $ccpage->module->options['touchaction'],
			'options' => array(
				'hovertext' => $lang['View hovertext'],
				'next' => $lang['Advance to next page']
			)
		)
	)
);

//echo mobile navigation options
echo '<h2 class="formheader">' . $lang['Mobile navigation options'] . '</h2>';
buildForm($forminputs);

//build archive options
$forminputs = array(
	array(
		array(
			'type' => "text",
			'label' => $lang['Maximum thumbnail width'],
			'tooltip' => $lang['tooltip-thumbwidth'],
			'name' => 'thumbwidth',
			'regex' => 'int',
			'current' => $ccpage->module->options['thumbwidth']
		),
		array(
			'type' => "text",
			'label' => $lang['Maximum thumbnail height'],
			'tooltip' => $lang['tooltip-thumbheight'],
			'name' => 'thumbheight',
			'regex' => 'int',
			'current' => $ccpage->module->options['thumbheight']
		)
	),
	array(
		array(
			'type' => "select",
			'label' => $lang['Display chapter thumbnails'],
			'tooltip' => $lang['tooltip-chapterthumbs'],
			'name' => 'chapterthumbs',
			'options' => array(
				'on' => $lang['Yes'],
				'off' => $lang['No']
			),
			'current' => $ccpage->module->options['chapterthumbs']
		)
	),
	array(
		array(
			'type' => "select",
			'label' => $lang['Display page thumbnails'],
			'tooltip' => $lang['tooltip-pagethumbs'],
			'name' => 'pagethumbs',
			'options' => array(
				'on' => $lang['Yes'],
				'off' => $lang['No']
			),
			'current' => $ccpage->module->options['pagethumbs']
		),
		array(
			'type' => "select",
			'label' => $lang['Display page titles'],
			'tooltip' => $lang['tooltip-pagetitles'],
			'name' => 'pagetitles',
			'options' => array(
				'on' => $lang['Yes'],
				'off' => $lang['No']
			),
			'current' => $ccpage->module->options['pagetitles']
		)
	)
);


//echo archive options
echo '<h2 class="formheader">' . $lang['Archive options'] . '</h2>';
buildForm($forminputs);

//build search options
$forminputs = array();
array_push($forminputs,
	array(
		array(
			'type' => "text",
			'label' => $lang['Results per page'],
			'tooltip' => $lang['tooltip-resultsperpage'],
			'name' => 'perpage',
			'current' => $ccpage->module->options['perpage'],
			'regex' => 'int'
		)
	)
);

//echo search options
echo '<h2 class="formheader">' . $lang['Search options'] . '</h2>';
buildForm($forminputs);

//close out the form
?>
<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Submit changes']?></button>
</form>

<?php

//include relevant javascript
include('includes/form-submit-js.php');

?>

</main>