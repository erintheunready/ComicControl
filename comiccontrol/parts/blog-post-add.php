<?
//blog-post-add.php
//handles adding new blog posts.
?>

<? //include necessary libraries ?>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js" type="text/javascript" /></script>

<?
//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug . '/' . $ccpage->module->slug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to managing %s'])
	),
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->module->slug.'/',
		'text' => $lang['Edit another blog post']
	),
	array(
		'link' => $ccurl . $navslug.'/'.$ccpage->module->slug.'/add-post',
		'text' => $lang['Add another blog post']
	)
);
quickLinks($links);
?>

<main id="content">

<? 

//submit post if submitted
if(isset($_POST) && $_POST['post-title'] != ""){
	
	//set values for the query 
	$blog = $ccpage->module->id;
	$timestring = $_POST['post-date'] . ' ' . sprintf('%02d',$_POST['hour']) . ':' . sprintf('%02d',$_POST['minute']) . ':' . sprintf('%02d',$_POST['second']);
	$publishtime = strtotime($timestring);
	$title = $_POST['post-title'];
	$content = $_POST['post-content'];
	
	//find available slug
	$slug = toSlug($title);
	while(strpos($slug, '--') !== false){
		$slug = str_replace('--','-',$slug);
	}
	$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "blogs WHERE blog=:blog AND slug=:slug LIMIT 1");
	$stmt->execute(['slug' => $slug, 'blog' => $blog]);
	$count = 2;
	$slugfinal = $slug;
	while($stmt->fetch()){
		$slugfinal = $slug . '-' . $count;
		$stmt->execute(['slug' => $slugfinal, 'blog' => $blog]);
		$count++;
	}
	
	//execute query
	$query = "INSERT INTO cc_" . $tableprefix . "blogs(blog,title,content,publishtime,slug) VALUES(:blog,:title,:content,:publishtime,:slug)";
	$stmt = $cc->prepare($query);
	$stmt->execute(['blog' => $blog, 'title' => $title, 'content' => $content, 'publishtime' => $publishtime, 'slug' => $slugfinal]);
	
	//continue if post successfully added
	if($stmt->rowCount() > 0){
	
		//get post id
		$postid = $cc->lastInsertId();
	
		//set comment ID based on post id
		$commentid = $ccpage->module->slug . '-' . $postid;
		$stmt = $cc->prepare("UPDATE cc_" . $tableprefix . "blogs SET commentid=:commentid WHERE id=:postid LIMIT 1");
		$stmt->execute(['commentid' => $commentid, 'postid' => $postid]);
		
		//insert tags based on post id
		$tags = str_replace(", ",",",$_POST['post-tags']);
		$tags = explode(",",$tags);
		$stmt = $cc->prepare("INSERT INTO cc_" . $tableprefix . "blogs_tags(blog,blogid,tag,publishtime) VALUES(:moduleid,:postid,:tag,:publishtime)");
		foreach($tags as $tag){
			$tag = trim($tag);
			if($tag != ""){
				$stmt->execute(['moduleid' => $blog, 'postid' => $postid, 'tag' => $tag, 'publishtime' => $publishtime]);
			}
		}
		
		//output success message
		?>
		<div class="msg success f-c"><?=str_replace('%s',$title,$lang['%s has been successfully added.'])?></div>
		<?		
		
		//give action buttons for this post
		echo '<div class="cc-btn-row">';
		buildButton(
			"light-bg",
			$ccurl . $navslug . '/' . $ccpage->module->slug . '/edit-post/' . $slugfinal,
			str_replace('%s',htmlentities($title),$lang['Edit %s'])
		);
		buildButton(
			"light-bg",
			$ccsite->root . $ccpage->module->slug . '/' . $slugfinal,
			str_replace('%s',htmlentities($title),$lang['Preview %s'])
		);
		echo '</div>';
	}
		
	//output error message if failed
	else{
		?>
		<div class="msg error f-c"><?=$lang['There was an error adding your blog post.  Please try again.']?></div>
		<?
	}
	
}else{

	//start the form ?>

	<form action="" method="post">
			
		<? //build the blog info form ?>
		<div class="formcontain">
			<?
			
				//build array of form info
				$forminputs = array();
				array_push($forminputs,
					array(
						array(
							'type' => "text",
							'label' => $lang['Post title'],
							'tooltip' => $lang['tooltip-posttitle'],
							'name' => "post-title",
							'regex' => "normal-text"
						)
					),array(
						array(
							'type' => "date",
							'label' => $lang['Publish date'],
							'tooltip' => $lang['tooltip-blogpublishtime'],
							'name' => "post-date",
							'regex' => "date"
						),array(
							'type' => "time",
							'label' => $lang['Publish time'],
							'tooltip' => $lang['tooltip-blogpublishtime'],
							'name' => "post-time",
							'regex' => "time"
						)
					)
				);
				if(getModuleOption('displaytags') == "on") 
					array_push($forminputs,
						array(
							array(
								'type' => "text",
								'label' => $lang['Tags'],
								'tooltip' => $lang['tooltip-blogtags'],
								'name' => "post-tags",
								'regex' => false
							)
						)
					);
				
				//build the form
				buildForm($forminputs); 
				
				//output the text editor for the post
				buildTextEditor($lang['Post content'],"post-content",$lang['tooltip-postcontent']);
				
			?>
		</div>
		
		<? // close the form ?>
		<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Submit blog post']?></button>
	</form>
	
	<? 
	//include relevant javascript
	include('includes/form-submit-js.php');
	include('includes/content-editor-js.php');
}
?>

</main>