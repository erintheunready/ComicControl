<?
//module-options.php - outputs generic options for modules
	
//get available template files
$templatefiles = array();
$templatefiles = recurseDirectories("../templates/",$templatefiles);
$temparr = array();
foreach($templatefiles as $file){
	$tempstr = substr($file, 13);
	if(substr($tempstr, -1) != "." && substr($tempstr,-1) != "/" && strpos($tempstr, 'includes/') === false && substr($tempstr,-4) == ".php"){
		$temparr[$tempstr] = $tempstr;
	}
}
$templatefiles = $temparr;

//get available display languages
$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "languages WHERE scope='user'");
$stmt->execute();
$result = $stmt->fetchAll();
$displaylangs = array();
foreach($result as $value){
	if(file_exists('languages/user-' . $value['shortname'] . '.php')) $displaylangs[$value['shortname']] = $value['language'];
}

echo '<form name="moduleoptions" action="" method="post" id="moduleoptions">';

//build array of module options
$forminputs = array();
array_push($forminputs,
	array(
		array(
			'type' => "text",
			'label' => $lang['Page title'],
			'tooltip' => $lang['tooltip-pagetitle'],
			'name' => "page-title",
			'regex' => "normal-text",
			'current' => $ccpage->title
		)
	),
	array(
		array(
			'type' => "select",
			'label' => $lang['Display language'],
			'tooltip' => $lang['tooltip-displaylanguage'],
			'name' => "page-language",
			'regex' => "select",
			'options' => $displaylangs,
			'current' => $ccpage->language
		)
	),
	array(
		array(
			'type' => "select",
			'label' => $lang['Page template'],
			'tooltip' => $lang['tooltip-pagetemplate'],
			'name' => "page-template",
			'regex' => "select",
			'options' => $templatefiles,
			'current' => $ccpage->template
		)
	),
	array(
		array(
			'type' => "text",
			'label' => $lang['Meta description'],
			'tooltip' => $lang['tooltip-metadescription'],
			'name' => "page-description",
			'current' => $ccpage->description
		)
	)
);

//echo generic module options
echo '<h2 class="formheader">' . $lang['Module options'] . '</h2>';
buildForm($forminputs);

echo '<div class="formline"><div class="module-url">' . $lang['Module URL:'] . ' ' . $siteurl . $ccpage->slug . '</div></div>';

//function to recurse through directories to get templates
function recurseDirectories($dir,$arr){
	$temparr = scandir($dir);
	foreach($temparr as $file){
		if(is_dir($dir.$file) && $file != "." && $file != ".."){
			$arr = recurseDirectories($dir . $file . '/',$arr);
		}
		else array_push($arr,$dir.$file);
	}
	return $arr;
}

?>