<? //comic-post-edit.php - handles editing existing comic posts ?>

<? //include necessary libraries ?>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js" type="text/javascript" /></script>

<?

//get the requested post
$thiscomic = $ccpage->module->getPost(getSlug(4));

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
	),
	array(
		'link' => $ccsite->root . $ccsite->relativepath . $ccpage->slug . '/' . $thiscomic['slug'],
		'text' => str_replace('%s',htmlentities($thiscomic['title']),$lang['Preview %s'])
	)
);
quickLinks($links);

?>

<main id="content">

<? 

//if post not found, return error
if(empty($thiscomic)){
	echo '<div class="msg error f-c">' . $lang['No comic was found with this information.'] . '</div>';
}
else{

	//submit page if posted
	if(isset($_POST) && $_POST['comic-title'] != ""){
		
		//set values for the query 
		$comic = $ccpage->module->id;
		if(isset($_POST['image-finalfile']) && $_POST['image-finalfile'] != ""){
			$comichighres = $_POST['image-highres'];
			$comicthumb = $_POST['image-thumbnail'];
			$imgname = $_POST['image-finalfile'];
		}else{
			$comichighres = $thiscomic['comichighres'];
			$comicthumb = $thiscomic['comicthumb'];
			$imgname = $thiscomic['imgname'];
		}
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
		$slugfinal = $thiscomic['slug'];
		
		//execute query
		$query = "UPDATE cc_" . $tableprefix . "comics SET comic=:comic,comichighres=:comichighres,comicthumb=:comicthumb,imgname=:imgname,publishtime=:publishtime,title=:title,newstitle=:newstitle,newscontent=:newscontent,transcript=:transcript,storyline=:storyline,hovertext=:hovertext,width=:width,height=:height,mime=:mime,contentwarning=:contentwarning,altnext=:altnext WHERE id=:id";
		$stmt = $cc->prepare($query);
		$stmt->execute(['comic' => $comic, 'comichighres' => $comichighres, 'comicthumb' => $comicthumb, 'imgname' => $imgname, 'publishtime' => $publishtime, 'title' => $title, 'newstitle' => $newstitle, 'newscontent' => $newscontent, 'transcript' => $transcript, 'storyline' => $storyline, 'hovertext' => $hovertext, 'width' => $width, 'height' => $height, 'mime' => $mime, 'contentwarning' => $contentwarning, 'altnext' => $altnext, 'id' => $thiscomic['id']]);
		
		//continue if post successfully edited
		if($stmt->rowCount() > 0){
			
			//reset tags
			$stmt = $cc->prepare("DELETE FROM cc_" . $tableprefix . "comics_tags WHERE comic=:comic AND comicid=:comicid");
			$stmt->execute(['comic' => $comic,'comicid' => $thiscomic['id']]);
			
			$tags = str_replace(", ",",",$_POST['comic-tags']);
			$tags = explode(",",$tags);
			$stmt = $cc->prepare("INSERT INTO cc_" . $tableprefix . "comics_tags(comic,comicid,tag,publishtime) VALUES(:moduleid,:postid,:tag,:publishtime)");
			foreach($tags as $tag){
				$tag = trim($tag);
				if($tag != ""){
					$stmt->execute(['moduleid' => $comic, 'postid' => $thiscomic['id'], 'tag' => $tag, 'publishtime' => $publishtime]);
				}
			}
			
			?>
			<div class="msg success f-c"><?=str_replace('%s',$title,$lang['%s has been successfully edited.'])?></div>
			<?		
			echo '<div class="cc-btn-row">';
			buildButton(
				"light-bg",
				$ccurl . $navslug . '/' . $ccpage->slug . '/edit-post/' . $slugfinal,
				str_replace('%s',htmlentities($title),$lang['Edit %s again'])
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
			<div class="msg error f-c"><?=$lang['There was an error editing your comic post.  Please try again.']?></div>
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
			<div class="currentfileholder"><button class="full-width dark-bg toggle-current-file"><span class="current-file-text"><?=$lang['View current file']?></span> <i class="fa fa-angle-down"></i></button>
				<div class="currentfile"><img src="<?=$ccsite->root . $ccsite->relativepath . 'comics/' . $thiscomic['imgname']?>" /></div>
			</div>
			<? buildImageInput($lang['Change file...'],false); ?>
				
			<? //build the comic info form ?>
			<h2 class="formheader"><?=$lang['Comic info']?></h2>
			<div class="formcontain">
				<?
					//build array of form info
					$forminputs = array();
					array_push($forminputs,
						array(
							array(
								'type' => "text",
								'label' => $lang['Comic title'],
								'tooltip' => $lang['tooltip-comictitle'],
								'name' => "comic-title",
								'regex' => "normal-text",
								'current' => $thiscomic['title']
							)
						),array(
							array(
								'type' => "date",
								'label' => $lang['Publish date'],
								'tooltip' => $lang['tooltip-publishtime'],
								'name' => "comic-date",
								'regex' => "date",
								'current' => date("m/d/Y",$thiscomic['publishtime'])
							),array(
								'type' => "time",
								'label' => $lang['Publish time'],
								'tooltip' => $lang['tooltip-publishtime'],
								'name' => "comic-time",
								'regex' => "time",
								'current' => $thiscomic['publishtime']
							)
						),array(
							array(
								'type' => "text",
								'label' => $lang['Hovertext'],
								'tooltip' => $lang['tooltip-hovertext'],
								'name' => "comic-hovertext",
								'regex' => false,
								'current' => $thiscomic['hovertext']
							)
						),array(
							array(
								'type' => "text",
								'label' => $lang['Alternative link'],
								'tooltip' => $lang['tooltip-alternativelink'],
								'name' => "comic-alternative-link",
								'regex' => false,
								'current' => $thiscomic['altnext']
							)
						),array(
							array(
								'type' => "storylines",
								'label' => $lang['Storyline'],
								'tooltip' => $lang['tooltip-storyline'],
								'name' => "comic-storyline",
								'regex' => "storyline",
								'current' => $thiscomic['storyline']
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
								'regex' => false,
								'current' => $thiscomic['contentwarning']
							)
						)
					);
					
					//build the form
					buildForm($forminputs) 
				?>
			</div>
			
			<? //build the news post form ?>
			<h2 class="formheader">News post</h2>
			<div class="formcontain">
			<?
				//get tags
				$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "comics_tags WHERE comic=:comic AND comicid=:comicid");
				$stmt->execute(['comic' => $thiscomic['comic'],'comicid' => $thiscomic['id']]);
				$tagarr = $stmt->fetchAll();
				$tags = "";
				foreach($tagarr as $tag){
					$tags .= $tag['tag'] . ', ';
				}
				
			
				//build array of form info
				$forminputs = array();
				array_push($forminputs,
					array(
						array(
							'type' => "text",
							'label' => $lang['News title'],
							'tooltip' => $lang['tooltip-newstitle'],
							'name' => "news-title",
							'regex' => false,
							'current' => $thiscomic['newstitle']
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
								'regex' => false,
								'current' => $tags
							)
						)
					);
				
				//build the form
				buildForm($forminputs);
				buildTextEditor($lang['News content'],"news-content",$lang['tooltip-newscontent'],$thiscomic['newscontent']);
				if(getModuleOption('displaytranscript') == "on") buildTextEditor($lang['Comic transcript'],"comic-transcript",$lang['tooltip-transcript'],$thiscomic['transcript']);
			?>	
			</div>
			<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Submit changes']?></button>
		</form>
		<script>
		$('.toggle-current-file').on('click',function(e){
			e.preventDefault();
			$(this).parent().find('.currentfile').slideToggle();
			$(this).find('.current-file-text').text(function(i, text){
				  return text === 'View current file' ? 'Hide current file' : 'View current file';
			});
			$(this).find('i').toggleClass('fa-angle-down fa-angle-up');
		});
		</script>
		<? 
		//include relevant javascript
		$imgfolder = "comics/";
		include('includes/form-submit-js.php');
		include('includes/img-upload-js.php');
		include('includes/content-editor-js.php');
	}
}
?>

</main>