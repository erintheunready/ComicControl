<? include('includes/install-header.php');  ?>

<?=$ilang['fourthstep']?>

<form action="" method="post">
<?

//get available module types
$moduletypes = array();
$moduletypes['comic'] = 'Comic';
$moduletypes['blog'] = 'Blog';
$moduletypes['gallery'] = 'Gallery';
$moduletypes['text'] = 'Text';

//get available template files
$templatefiles = array();
$templatefiles = recurseDirectories("../templates/",$templatefiles);
$temparr = array();
foreach($templatefiles as $file){
	$tempstr = substr($file, 13);
	if(substr($tempstr, -1) != "." && substr($tempstr,-1) != "/" && strpos($tempstr, 'includes/') === false && substr($tempstr,-4) != ".txt"){
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
	$displaylangs[$value['shortname']] = $value['language'];
}

//build form info for adding a module
$forminputs = array();

array_push($forminputs,
	array(
		array(
			'type' => "text",
			'label' => $ilang['Page title'],
			'tooltip' => $ilang['pagetitle-tooltip'],
			'name' => "install-pagetitle",
			'regex' => "normal-text"
		)
	),
	array(
		array(
			'type' => "select",
			'label' => $ilang['Module type'],
			'tooltip' => $ilang['moduletype-tooltip'],
			'name' => "install-moduletype",
			'regex' => "select",
			'options' => $moduletypes
		)
	),
	array(
		array(
			'type' => "select",
			'label' => $ilang['Display language'],
			'tooltip' => $ilang['displaylang-tooltip'],
			'name' => "install-language",
			'regex' => "select",
			'options' => $displaylangs
		)
	),
	array(
		array(
			'type' => "select",
			'label' => $ilang['Page template'],
			'tooltip' => $ilang['template-tooltip'],
			'name' => "install-template",
			'regex' => "select",
			'options' => $templatefiles
		)
	)
);

//build the form
buildForm($forminputs); 

?>

<? // close the form ?>
<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$ilang['next']?></button>

</form>
<?

//include relevant javascript
include('includes/form-submit-js.php'); 

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
<? include('includes/install-footer.php'); ?>