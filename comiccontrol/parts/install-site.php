<? include('includes/install-header.php');  ?>

<?=$ilang['secondstep']?>

<form action="" method="post">
<?

//get available display languages
$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "languages WHERE scope='admin'");
$stmt->execute();
$result = $stmt->fetchAll();
$displaylangs = array();
foreach($result as $value){
	$displaylangs[$value['shortname']] = $value['language'];
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

//get URLs
$url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
$url .= $_SERVER['SERVER_NAME'];
$url .= $_SERVER['REQUEST_URI'];
$url = dirname($url);
$url .= "/";
$notdomain = $_SERVER['REQUEST_URI'];
$notdomainarr = explode("/",$notdomain);
$comiccontrolfolder = array_pop($notdomainarr)."/";
if($comiccontrolfolder == "/") $comiccontrolfolder = array_pop($notdomainarr)."/";
$relativefolder = "";
if(count($notdomainarr) != 0){
	foreach($notdomainarr as $value){
		if($value != ""){
			$relativefolder .= $value;
			$relativefolder .= "/";
		}
	}
}

$forminputs = array();
array_push($forminputs,
	array(
		array(
			'type' => "text",
			'label' => $ilang['Site title'],
			'tooltip' => $ilang['sitetitle-tooltip'],
			'name' => "install-sitetitle",
			'regex' => "normal-text"
		)
	),array(
		array(
			'type' => "text",
			'label' => $ilang['Site root'],
			'tooltip' => $ilang['siteroot-tooltip'],
			'name' => "install-siteroot",
			'regex' => "normal-text",
			'current' => $url
		)
	),array(
		array(
			'type' => "text",
			'label' => $ilang['Relative path'],
			'tooltip' => $ilang['relativepath-tooltip'],
			'name' => "install-relativepath",
			'current' => $relativefolder
		)
	),array(
		array(
			'type' => "text",
			'label' => $ilang['ComicControl path'],
			'tooltip' => $ilang['ccpath-tooltip'],
			'name' => "install-ccpath",
			'regex' => "normal-text",
			'current' => $comiccontrolfolder
		)
	),array(
		array(
			'type' => "select",
			'label' => $ilang['Administrator language'],
			'tooltip' => $ilang['language-tooltip'],
			'name' => "install-language",
			'regex' => "select",
			'options' => $displaylangs
		)
	),array(
		array(
			'type' => "select",
			'label' => $ilang['Time zone'],
			'tooltip' => $ilang['timezone-tooltip'],
			'name' => "install-timezone",
			'regex' => "select",
			'options' => $timezones
		)
	),array(
		array(
			'type' => "select",
			'label' => $ilang['Date format'],
			'tooltip' => $ilang['dateformat-tooltip'],
			'name' => "install-dateformat",
			'regex' => "select",
			'options' => $dateformats
		)
	),array(
		array(
			'type' => "select",
			'label' => $ilang['Time format'],
			'tooltip' => $ilang['timeformat-tooltip'],
			'name' => "install-timeformat",
			'regex' => "select",
			'options' => $timeformats
		)
	),
	array(
		array(
			'type' => "text",
			'label' => $ilang['Disqus shortname'],
			'tooltip' => $ilang['disqus-tooltip'],
			'name' => "install-shortname"
		)
	),
	array(
		array(
			'type' => "text",
			'label' => $ilang['Site description'],
			'tooltip' => $ilang['description-tooltip'],
			'name' => "install-description"
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

?>
<? include('includes/install-footer.php'); ?>