<?

//find available slug
$slug = toSlug($_POST['install-pagetitle']);
while(strpos($slug, '--') !== false){
	$slug = str_replace('--','-',$slug);
}
$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "modules WHERE slug=:slug LIMIT 1");
$stmt->execute(['slug' => $slug]);
$count = 2;
$slugfinal = $slug;
while($stmt->fetch()){
	$slugfinal = $slug . '-' . $count;
	$stmt->execute(['slug' => $slugfinal]);
	$count++;
}

//add the module to the database
$query = "INSERT INTO cc_" . $tableprefix . "modules(title,moduletype,template,language,slug) VALUES(:title,:moduletype,:template,:language,:slug)";
$stmt = $cc->prepare($query);
$stmt->execute(['title' => $_POST['install-pagetitle'], 'moduletype' => $_POST['install-moduletype'], 'template' => $_POST['install-template'], 'language' => $_POST['install-language'],  'slug' => $slugfinal]);

//get the new module id for adding options
$moduleid = $cc->lastInsertId();

//add default options depending on the module
$option = array();

switch($_POST['install-moduletype']){
	case 'comic':
		$option['displaytags'] = "on";
		$option['newsmode'] = "eachpost";
		$option['clickaction'] = "next";
		$option['comicwidth'] = 900;
		$option['navaux'] = "rss";
		$option['thumbwidth'] = 200;
		$option['thumbheight'] = 200;
		$option['touchaction'] = "hovertext";
		$option['swipeaction'] = "navigate";
		$option['navorder'] = "first|prev|aux|next|last";
		$option['perpage'] = 15;
		$option['displaytranscript'] = "off";
		$option['displaycomments'] = "on";
		$option['contentwarnings'] = "off";
		$option['chapterthumbs'] = "off";
		$option['pagethumbs'] = "off";
		$option['pagetitles'] = "off";
		$option['transcriptclick'] = "on";
		$option['firsttext'] = "";
		$option['prevtext'] = "";
		$option['nexttext'] = "";
		$option['lasttext'] = "";
		$option['auxtext'] = "";
		break;
	case 'blog':
		$option['perpage'] = 10;
		$option['displaycomments'] = "on";
		$option['displaytags'] = "on";
		$option['archiveorder'] = "DESC";
		break;
	case 'gallery':
		$option['showTitle'] = "on";
		$option['showDescription'] = "on";
		$option['thumbwidth'] = 200;
		$option['thumbheight'] = 200;
		break;
	case 'text':
		$option['showTitle'] = "on";
		break;
}

$query = "INSERT INTO cc_" . $tableprefix . "modules_options(moduleid,optionname,value) VALUES(:moduleid,:optionname,:value)";
$stmt = $cc->prepare($query);
foreach($option as $optionname => $value){
	$stmt->execute(['moduleid' => $moduleid, 'optionname' => $optionname, 'value' => $value]);
}

if($_POST['moduletype'] == "text" || $_POST['moduletype'] == "gallery"){
	//if text module or gallery module, add to text table
	$query = "INSERT INTO cc_" . $tableprefix . "text(id,content) VALUES(:id,'')";
	$stmt = $cc->prepare($query);
	$stmt->execute(['id' => $moduleid]);
}

//make sure this is set as the home page
$query = "UPDATE cc_" . $tableprefix . "options SET optionvalue=:optionvalue WHERE optionname=:optionname LIMIT 1";
$stmt = $cc->prepare($query);
$stmt->execute(['optionname' => 'homepage','optionvalue' => $moduleid]);

$installed = "complete";
?>