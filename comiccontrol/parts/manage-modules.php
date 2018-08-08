<? //manage-modules.php - handles all module management except options 

$action = getSlug(2);

//get available template files
$templatefiles = array();
$templatefiles = recurseDirectories("../templates/",$templatefiles);
$temparr = array();
foreach($templatefiles as $file){
	$tempstr = substr($file, 13);
	if(substr($tempstr, -1) != "." && substr($tempstr,-1) != "/" && strpos($tempstr, 'includes/') === false){
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

//get available module types
$moduletypes = array();
$moduletypes['comic'] = 'Comic';
$moduletypes['blog'] = 'Blog';
$moduletypes['gallery'] = 'Gallery';
$moduletypes['text'] = 'Text';

$successmsg = "";

//handle adding a new module
if($action == "add-module"){
	
	//create and output quick links
	$links = array(
		array(
			'link' => $ccurl . $navslug,
			'text' => str_replace('%s',$ccpage->title,$lang['Return to module management'])
		),
		array(
			'link' => $ccurl . $navslug.'/add-module',
			'text' => $lang['Add another module']
		)
	);
	quickLinks($links);
	
	echo '<main id="content">';
	
	//add the module if submitted
	if(isset($_POST) && $_POST['title'] != ""){
		
		//find available slug
		$slug = toSlug($_POST['title']);
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
		$stmt->execute(['title' => $_POST['title'], 'moduletype' => $_POST['moduletype'], 'template' => $_POST['template'], 'language' => $_POST['language'], 'slug' => $slugfinal]);
		
		//get the new module id for adding options
		$moduleid = $cc->lastInsertId();
		
		//add default options depending on the module
		$option = array();
		
		switch($_POST['moduletype']){
			case 'comic':
				$option['displaytags'] = "on";
				$option['newsmode'] = "eachpost";
				$option['clickaction'] = "next";
				$option['comicwidth'] = 900;
				$option['navaux'] = "rss";
				$option['thumbwidth'] = 200;
				$option['thumbheight'] = 200;
				$option['touchaction'] = "hovertext";
				$option['swipeaction'] = "none";
				$option['navorder'] = "first|prev|aux|next|last";
				$option['perpage'] = 15;
				$option['displaytranscript'] = "off";
				$option['displaycomments'] = "on";
				$option['contentwarnings'] = "off";
				$option['chapterthumbs'] = "on";
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
		
		//output success message
		$successmsg = $lang['createmodule-success'];
		
	}else{
		
		//build form info for adding a module
		$forminputs = array();
		
		array_push($forminputs,
			array(
				array(
					'type' => "text",
					'label' => $lang['Page title'],
					'tooltip' => $lang['tooltip-pagetitle'],
					'name' => "title",
					'regex' => "normal-text"
				)
			),
			array(
				array(
					'type' => "select",
					'label' => $lang['Module type'],
					'tooltip' => $lang['tooltip-moduletype'],
					'name' => "moduletype",
					'regex' => "select",
					'options' => $moduletypes
				)
			),
			array(
				array(
					'type' => "select",
					'label' => $lang['Display language'],
					'tooltip' => $lang['tooltip-displaylanguage'],
					'name' => "language",
					'regex' => "select",
					'options' => $displaylangs
				)
			),
			array(
				array(
					'type' => "select",
					'label' => $lang['Page template'],
					'tooltip' => $lang['tooltip-pagetemplate'],
					'name' => "template",
					'regex' => "select",
					'options' => $templatefiles
				)
			)
		);
		
		//output the form for adding a module
		echo '<form name="siteoptions" action="" method="post" id="siteoptions">';
		buildForm($forminputs);

		//close out the form
		?>
		<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Submit changes']?></button>
		</form>
		
		<?
		
		//include relevant javascript
		include('includes/form-submit-js.php');
		
	}
	
}

//handle module deletion actions
else if($action == "delete-module"){
	
	//create and output quick links
	$links = array(
		array(
			'link' => $ccurl . $navslug,
			'text' => str_replace('%s',$ccpage->title,$lang['Return to module management'])
		),
		array(
			'link' => $ccurl . $navslug.'/add-module',
			'text' => $lang['Add another module']
		)
	);
	quickLinks($links);
	
	echo '<main id="content">';
		
	//get the selected module
	$slug = getSlug(3);
	$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE slug=:slug LIMIT 1";
	$stmt = $cc->prepare($query);
	$stmt->execute(["slug" => $slug]);
	$thismodule = $stmt->fetch();
	
	
	//throw error if the module wasn't found
	if(empty($thismodule)){
		echo $lang['There was no module found with this information.'];
	}
	
	//proceed if the module was found
	else{
		
		//if confirmed, delete the module
		if(getSlug(4) == "confirmed"){
			
			$query = "DELETE FROM cc_" . $tableprefix . "modules WHERE id=:id";
			$stmt = $cc->prepare($query);
			$stmt->execute(["id" => $thismodule['id']]);
			
			$query = "DELETE FROM cc_" . $tableprefix . "modules_options WHERE moduleid=:id";
			$stmt = $cc->prepare($query);
			$stmt->execute(["id" => $thismodule['id']]);
			
			$successmsg = str_replace("%s",$thismodule['title'],$lang['%s has been deleted.']);
			
			
		}
		
		//prompt user to delete page
		else{
			?>
			<div class="msg prompt f-c"><?=str_replace("%s",$thismodule['title'],$lang['Are you sure you want to delete %s? This action cannot be undone.'])?></div>
			<?

			echo '<div class="cc-btn-row">';
			buildButton(
				"light-bg",
				$ccurl . $navslug.'/delete-module/'.$thismodule['slug'].'/confirmed',
				$lang['Yes']
			);
			buildButton(
				"dark-bg",
				$ccurl . $navslug,
				$lang['No']
			);
			echo '</div>';
			
		}
	}
	
}

//output list of modules if no other action selected
if(($action != "add-module" && $action != "delete-module") || $successmsg != ""){
	
	echo '<main id="content">';
		
	//get all the modules
	$query = "SELECT * FROM cc_" . $tableprefix . "modules";
	$stmt = $cc->prepare($query);
	$stmt->execute();
	$results = $stmt->fetchAll();
	
	//echo a success message if one is set
	if($successmsg != "") echo '<div class="msg success f-c">' . $successmsg . '</div>';

	//output button for adding a module
	echo '<div class="cc-btn-row">';
	buildButton(
		"light-bg",
		$ccurl . $navslug.'/add-module',
		$lang['Add a module']
	);
	echo '</div>';

	//output list of modules
	?>
	<div class="manage-container dark-bg">
		<div class="row-container">
			<?

			$gray = false;

			foreach($results as $row){
				echo '<div class="zebra-row';
				if($gray){ 
					echo ' gray-bg';
					$gray = false;
				}
				else $gray = true;
				echo '">';
				echo '<div class="row-title">' . $row['title'] . '</div>';
				echo '<a href="' . $ccurl . getSlug(1) . '/delete-module/' . $row['slug'] . '">' . $lang['Delete'] . '</a>';
				echo '<a href="' . $ccurl . 'modules/' . $row['slug'] . '/manage-options">' . $lang['Edit'] . '</a>';
				echo '<div style="clear:both;"></div></div>';
			}

			?>
		</div>
	</div>

	<? 

} 

//include function for getting list of templates
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

</main>