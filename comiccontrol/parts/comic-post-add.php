<? //comic-post-add.php - handles adding new comic posts ?>

<? //include necessary libraries ?>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js" type="text/javascript" /></script>

<?

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->slug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to managing %s'])
	),
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->slug."/manage-posts",
		'text' => $lang['Edit another comic post']
	),
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->slug."/add-post",
		'text' => $lang['Add another comic post']
	)
);
quickLinks($links);

?>

<main id="content">

<? 

//submit page if posted
if(isset($_POST) && $_POST['comic-title'] != ""){
	
	//set values for the query 
	$comic = $ccpage->module->id;
	$comichighres = $_POST['image-highres'];
	$comicthumb = $_POST['image-thumbnail'];
	$imgname = $_POST['image-finalfile'];
	$timestring = $_POST['comic-date'] . ' ' . sprintf('%02d',$_POST['hour']) . ':' . sprintf('%02d',$_POST['minute']) . ':' . sprintf('%02d',$_POST['second']);
	$publishtime = strtotime($timestring);
	$title = $_POST['comic-title'];
	$newstitle = $_POST['news-title'];
	$newscontent = trim($_POST['news-content']);
	$transcript = $_POST['comic-transcript'];
	$storyline = $_POST['comic-storyline'];
	$hovertext = $_POST['comic-hovertext'];
	$imginfo = getimagesize('../comicshighres/' . $comichighres);
	$width = $imginfo[0];
	$height = $imginfo[1];
	$mime = $imginfo['mime'];
	$contentwarning = $_POST['comic-content-warning'];
	$altnext = $_POST['comic-alternative-link'];
	
	//find available slug
	$slug = toSlug($title);
	while(strpos($slug, '--') !== false){
		$slug = str_replace('--','-',$slug);
	}
	$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "comics WHERE comic=:comic AND slug=:slug LIMIT 1");
	$stmt->execute(['slug' => $slug, 'comic' => $comic]);
	$count = 2;
	$slugfinal = $slug;
	while($stmt->fetch()){
		$slugfinal = $slug . '-' . $count;
		$stmt->execute(['slug' => $slugfinal, 'comic' => $comic]);
		$count++;
	}
	
	//execute query
	$query = "INSERT INTO cc_" . $tableprefix . "comics(comic,comichighres,comicthumb,imgname,publishtime,title,newstitle,newscontent,transcript,storyline,hovertext,slug,width,height,mime,contentwarning,altnext) VALUES(:comic,:comichighres,:comicthumb,:imgname,:publishtime,:title,:newstitle,:newscontent,:transcript,:storyline,:hovertext,:slug,:width,:height,:mime,:contentwarning,:altnext)";
	$stmt = $cc->prepare($query);
	$stmt->execute(['comic' => $comic, 'comichighres' => $comichighres, 'comicthumb' => $comicthumb, 'imgname' => $imgname, 'publishtime' => $publishtime, 'title' => $title, 'newstitle' => $newstitle, 'newscontent' => $newscontent, 'transcript' => $transcript, 'storyline' => $storyline, 'hovertext' => $hovertext, 'slug' => $slugfinal, 'width' => $width, 'height' => $height, 'mime' => $mime, 'contentwarning' => $contentwarning, 'altnext' => $altnext]);
	
	//continue if post successfully added
	if($stmt->rowCount() > 0){
	
		//get the id of the post
		$postid = $cc->lastInsertId();
	
		//set comment ID based on post id
		$commentid = $ccpage->slug . '-' . $postid;
		$stmt = $cc->prepare("UPDATE cc_" . $tableprefix . "comics SET commentid=:commentid WHERE id=:postid LIMIT 1");
		$stmt->execute(['commentid' => $commentid, 'postid' => $postid]);
		
		//insert tags based on post id
		$tags = str_replace(", ",",",$_POST['comic-tags']);
		$tags = explode(",",$tags);
		$stmt = $cc->prepare("INSERT INTO cc_" . $tableprefix . "comics_tags(comic,comicid,tag,publishtime) VALUES(:moduleid,:postid,:tag,:publishtime)");
		foreach($tags as $tag){
			$tag = trim($tag);
			if($tag != ""){
				$stmt->execute(['moduleid' => $comic, 'postid' => $postid, 'tag' => $tag, 'publishtime' => $publishtime]);
			}
		}
		
		//give success message
		?>
		<div class="msg success f-c"><?=str_replace('%s',$title,$lang['%s has been successfully added.'])?></div>
		<?		
		
		//give preview and edit buttons for the new post
		echo '<div class="cc-btn-row">';
		buildButton(
			"light-bg",
			$ccurl . $navslug . '/' . $ccpage->slug . '/edit-post/' . $slugfinal,
			str_replace('%s',htmlentities($title),$lang['Edit %s'])
		);
		buildButton(
			"light-bg",
			$ccsite->root . $ccsite->relativepath . $ccpage->slug . '/' . $slugfinal,
			str_replace('%s',htmlentities($title),$lang['Preview %s'])
		);
		echo '</div>';
		
	}
		
	//output error message if failed
	else{
		?>
		<div class="msg error f-c"><?=$lang['There was an error adding your comic post.  Please try again.']?></div>
		<?
		echo '<div class="cc-btn-row">';
		buildButton(
			"dark-bg",
			$navslug . '/' . $ccpage->slug . '/add-post',
			$lang['Add a new comic post']
		);
		buildButton(
			"dark-bg",
			$navslug . '/' . $ccpage->slug . '/manage-posts',
			$lang['Edit a different comic post']
		);
		buildButton(
			"dark-bg",
			$navslug . '/' . $ccpage->slug . '/',
			str_replace('%s',$ccpage->title,$lang['Return to managing %s'])
		);
		echo '</div>';
	}
	
}else{

	//start the form ?>

	<form action="" method="post" enctype="multipart/form-data">
		
		<? // comic uploader area ?>
		<? buildImageInput($lang['Choose file...'],true); ?>
			
		<? //build the comic info form ?>
		<h2 class="formheader"><?=$lang['Comic info']?></h2>
		<div class="formcontain">
			<?
				//check storyline is set
				$storyline = 0;
				if(filter_var($_POST['storyline'], FILTER_VALIDATE_INT)) $storyline = $_POST['storyline'];

				//build array of form info
				$forminputs = array();
				array_push($forminputs,
					array(
						array(
							'type' => "text",
							'label' => $lang['Comic title'],
							'tooltip' => $lang['tooltip-comictitle'],
							'name' => "comic-title",
							'regex' => "normal-text"
						)
					),array(
						array(
							'type' => "date",
							'label' => $lang['Publish date'],
							'tooltip' => $lang['tooltip-publishtime'],
							'name' => "comic-date",
							'regex' => "date"
						),array(
							'type' => "time",
							'label' => $lang['Publish time'],
							'tooltip' => $lang['tooltip-publishtime'],
							'name' => "comic-time",
							'regex' => "time"
						)
					),array(
						array(
							'type' => "text",
							'label' => $lang['Hovertext'],
							'tooltip' => $lang['tooltip-hovertext'],
							'name' => "comic-hovertext",
							'regex' => false
						)
					),array(
						array(
							'type' => "text",
							'label' => $lang['Alternative link'],
							'tooltip' => $lang['tooltip-alternativelink'],
							'name' => "comic-alternative-link",
							'regex' => false
						)
					),array(
						array(
							'type' => "storylines",
							'label' => $lang['Storyline'],
							'tooltip' => $lang['tooltip-storyline'],
							'name' => "comic-storyline",
							'regex' => "storyline",
							'current' => $storyline
						)
					)
				);
				if(getModuleOption('contentwarnings') == "on") array_push($forminputs,
					array(
						array(
							'type' => "text",
							'label' => $lang['Content warning'],
							'tooltip' => $lang['tooltip-contentwarning'],
							'name' => "comic-content-warning",
							'regex' => false
						)
					)
				);
				
				//build the form
				buildForm($forminputs); 
			?>
		</div>
		
		<? //build the news post form ?>
		<h2 class="formheader">News post</h2>
		<div class="formcontain">
		<?
			//build array of form info
			$forminputs = array();
			array_push($forminputs,
				array(
					array(
						'type' => "text",
						'label' => $lang['News title'],
						'tooltip' => $lang['tooltip-newstitle'],
						'name' => "news-title",
						'regex' => false
					)
				)
			);
			if(getModuleOption('displaytags') == "on") 
				array_push($forminputs,
					array(
						array(
							'type' => "text",
							'label' => $lang['Tags'],
							'tooltip' => $lang['tooltip-tags'],
							'name' => "comic-tags",
							'regex' => false
						)
					)
				);
			
			//build the form
			buildForm($forminputs);
			buildTextEditor($lang['News content'],"news-content",$lang['tooltip-newscontent']);
			if(getModuleOption('displaytranscript') == "on") buildTextEditor($lang['Comic transcript'],"comic-transcript",$lang['tooltip-transcript']);
		?>	
		</div>
		<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Submit comic post']?></button>
	</form>
	<script>
	function showError(container,msg){
		var $errordiv = $('<div class="formerror"></div>');
		$errordiv.html(msg);
		container.css('height','auto');
		container.append($errordiv);
		$errordiv.slideDown();
	}
	</script>
	<? 
	//include relevant javascript
	$imgfolder = "comics/";
	include('includes/form-submit-js.php');
	include('includes/img-upload-js.php');
	include('includes/content-editor-js.php');
}
?>

</main>