<? //site-options.php - handles overal site options ?>

<main id="content">

<?

if($ccuser->authlevel != 2){
	echo '<div class="msg error f-c">' . $lang['You do not have permission to access this page.'] . '</div>';
}else{
$forminputs = array();

//submit options if posted
if(isset($_POST) && $_POST['sitetitle'] != ""){

	//get all the options associated with the module
	$query = "SELECT * FROM cc_" . $tableprefix . "options";
	$stmt = $cc->prepare($query);
	$stmt->execute();
	$options = $stmt->fetchAll();
	
	//build the query for changing options
	$query = "UPDATE cc_" . $tableprefix . "options SET optionvalue=:value WHERE optionname=:optionname";
	$stmt = $cc->prepare($query);
	
	//loop through all the possible modules and update them
	foreach($options as $option){
		if(array_key_exists($option['optionname'],$_POST)){
			$stmt->execute(['value' => $_POST[$option['optionname']], 'optionname' => $option['optionname']]);
		}
	}
	
	$query = "UPDATE cc_" . $tableprefix . "htaccess SET content=:content WHERE id=:id";
	$stmt = $cc->prepare($query);
	$stmt->execute(['content' => $_POST['htaccess-prepend'], 'id' => 1]);
	$stmt->execute(['content' => $_POST['htaccess-append'], 'id' => 2]);
	
	$query = "SELECT * FROM cc_" . $tableprefix . "htaccess ORDER BY id ASC";
	$stmt = $cc->prepare($query);
	$stmt->execute();
	$parts = $stmt->fetchAll();
	
	$prepend = array_shift($parts);
	$append = array_shift($parts);
	$mainhtaccess = array_shift($parts);
	
	$htaccesstext = $prepend['content'] . PHP_EOL . $mainhtaccess['content'] . PHP_EOL . $append['content'];
	file_put_contents('../.htaccess', $htaccesstext);
	
	
	//reset page so the options are correct
	$ccsite = new CC_Site();
	$ccuser = new CC_User();
	date_default_timezone_set($ccsite->timezone);
	$ccpage = new CC_Page("$_SERVER[REQUEST_URI]","admin");
	
	//output success message
	echo '<div class="msg success f-c">' . $lang['changeoptions-success'] . '</div>';
}

//get main page and all modules
$stmt = $cc->prepare("SELECT * FROM cc_" . $ccsite->tableprefix . "options WHERE optionname='homepage' LIMIT 1");
$stmt->execute();
$mainpage = $stmt->fetch();
$stmt = $cc->prepare("SELECT * FROM cc_" . $ccsite->tableprefix . "modules WHERE id=:id LIMIT 1");
$stmt->execute(['id' => $mainpage['optionvalue']]);
$main = $stmt->fetch();
$query = "SELECT * FROM cc_" . $tableprefix . "modules ORDER BY id ASC";
$stmt = $cc->prepare($query);
$stmt->execute();
$allmodules = $stmt->fetchAll();
$modulelist = array();
foreach($allmodules as $module){
	$modulelist[$module['id']] = $module['title'];
}

//get available display languages
$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "languages WHERE scope='admin'");
$stmt->execute();
$result = $stmt->fetchAll();
$displaylangs = array();
foreach($result as $value){
	if(file_exists('languages/' . $value['shortname'] . '.php')) $displaylangs[$value['shortname']] = $value['language'];
}

// get time zones
$timezonelist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
$timezones = array();
foreach($timezonelist as $key => $value){
	$timezones[$value] = $value;
}

//set date and time formats
$dateformats = array(
	'F j, Y' => 'February 1, 2016',
	'F d, Y' => 'February 01, 2016',
	'M j, Y' => 'Feb 1, 2016',
	'M d, Y' => 'Feb 01, 2016',
	'Y-m-d' => '2016-02-01',
	'Y-n-j' => '2016-2-1',
	'Y.m.d' => '2016.02.01',
	'Y.n.j' => '2016.2.1',
	'm/d/Y' => '02/01/2016',
	'n/j/Y' => '2/1/2016',
	'd/m/Y' => '01/02/2016',
	'j/n/Y' => '1/2/2016',
	'm-d-Y' => '02-01-2016',
	'n-j-Y' => '2-1-2016',
	'd-m-Y' => '01-02-2016',
	'j-n-Y' => '1-2-2016',
	'm.d.Y' => '02.01.2016',
	'n.j.Y' => '2.1.2016',
	'd.m.Y' => '01.02.2016',
	'j.n.Y' => '1.2.2016'
);
$timeformats = array(
	'g:i a' => '1:23 pm',
	'h:i a' => '01:23 pm',
	'g:i A' => '1:23 PM',
	'h:i A' => '01:23 PM',
	'H:i' => '13:23'
);

//get htaccess prepend and append
$query = "SELECT * FROM cc_" . $tableprefix . "htaccess WHERE id=1";
$stmt = $cc->prepare($query);
$stmt->execute();
$prepend = $stmt->fetch();
$query = "SELECT * FROM cc_" . $tableprefix . "htaccess WHERE id=2";
$stmt = $cc->prepare($query);
$stmt->execute();
$append = $stmt->fetch();
			
			
echo '<form name="siteoptions" action="" method="post" id="siteoptions">';

//build site options form
array_push($forminputs,
	array(
		array(
			'type' => "text",
			'label' => $lang['Site title'],
			'tooltip' => $lang['tooltip-sitetitle'],
			'name' => "sitetitle",
			'regex' => "normal-text",
			'current' => $ccsite->sitetitle
		)
	),
	array(
		array(
			'type' => "select",
			'label' => $lang['Home page'],
			'tooltip' => $lang['tooltip-homepage'],
			'name' => "homepage",
			'regex' => "select",
			'options' => $modulelist,
			'current' => $main['id']
		)
	),
	array(
		array(
			'type' => "select",
			'label' => $lang['Admin language'],
			'tooltip' => $lang['tooltip-adminlanguage'],
			'name' => "language",
			'regex' => "select",
			'options' => $displaylangs,
			'current' => $ccsite->language
		),
		array(
			'type' => "select",
			'label' => $lang['Time zone'],
			'tooltip' => $lang['tooltip-timezone'],
			'name' => "timezone",
			'regex' => "select",
			'options' => $timezones,
			'current' => $ccsite->timezone
		)
	),
	array(
		array(
			'type' => "select",
			'label' => $lang['Date format'],
			'tooltip' => $lang['tooltip-dateformat'],
			'name' => "dateformat",
			'regex' => "select",
			'options' => $dateformats,
			'current' => $ccsite->dateformat
		),
		array(
			'type' => "select",
			'label' => $lang['Time format'],
			'tooltip' => $lang['tooltip-timeformat'],
			'name' => "timeformat",
			'regex' => "select",
			'options' => $timeformats,
			'current' => $ccsite->timeformat
		)
	),
	array(
		array(
			'type' => "text",
			'label' => $lang['Disqus shortname'],
			'tooltip' => $lang['tooltip-disqusshortname'],
			'name' => "commentname",
			'current' => $ccsite->commentname
		)
	),
	array(
		array(
			'type' => "text",
			'label' => $lang['Meta description'],
			'tooltip' => $lang['tooltip-description'],
			'name' => "description",
			'current' => $ccsite->description
		)
	)
);

//echo site optiosn form
buildForm($forminputs);


//output htaccess prepend/append content
buildTextArea($lang['.htaccess prepend code'],"htaccess-prepend",$lang['tooltip-htaccessprepend'],$prepend['content']);
buildTextArea($lang['.htaccess append code'],"htaccess-append",$lang['tooltip-htaccessappend'],$append['content']);


//close out the form
?>
<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Submit changes']?></button>
</form>

<?
//include relevant javascript
include('includes/form-submit-js.php');
}
?>

</main>