<?php //blog-post-edit.php - Handles editing of already created blog posts. ?>

<?php //include necessary libraries ?>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js" type="text/javascript" /></script>

<?php

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

<?php 

//get selected post
$post = $ccpage->module->getPost(getSlug(4));

//output error if that's not a valid post
if(empty($post)){
	echo '<div class="msg error">' . $lang['No blog post was found with this information.'] . '</div>';
}

//if found, proceed
else{

	//submit page if posted
	if(isset($_POST) && $_POST['post-title'] != ""){
		
		//set values for the query 
		$blog = $ccpage->module->id;
		$timestring = $_POST['post-date'] . ' ' . sprintf('%02d',$_POST['hour']) . ':' . sprintf('%02d',$_POST['minute']) . ':' . sprintf('%02d',$_POST['second']);
		$publishtime = strtotime($timestring);
		$title = $_POST['post-title'];
		$content = $_POST['post-content'];
		
		//execute query
		$query = "UPDATE cc_" . $tableprefix . "blogs SET blog=:blog,title=:title,content=:content,publishtime=:publishtime WHERE id=:id";
		$stmt = $cc->prepare($query);
		$stmt->execute(['blog' => $blog, 'title' => $title, 'content' => $content, 'publishtime' => $publishtime, 'id' => $post['id']]);
		
		//continue if post successfully added
		if($stmt->rowCount() > 0){
				
			//remove current tags
			$stmt = $cc->prepare("DELETE FROM cc_" . $tableprefix . "blogs_tags WHERE blog=:blog AND blogid=:blogid");
			$stmt->execute(['blog' => $blog,'blogid' => $post['id']]);
			
			//insert new tags based on post id
			$tags = str_replace(", ",",",$_POST['post-tags']);
			$tags = explode(",",$tags);
			$stmt = $cc->prepare("INSERT INTO cc_" . $tableprefix . "blogs_tags(blog,blogid,tag,publishtime) VALUES(:moduleid,:postid,:tag,:publishtime)");
			foreach($tags as $tag){
				$tag = trim($tag);
				if($tag != ""){
					$stmt->execute(['moduleid' => $blog, 'postid' => $post['id'], 'tag' => $tag, 'publishtime' => $publishtime]);
				}
			}
			
			//output success message
			?>
			<div class="msg success f-c"><?=str_replace('%s',$title,$lang['%s has been successfully edited.'])?></div>
			<?php		
			echo '<div class="cc-btn-row">';
			buildButton(
				"light-bg",
				$ccurl . $navslug . '/' . $ccpage->module->slug . '/edit-post/' . $post['slug'],
				str_replace('%s',htmlentities($title),$lang['Edit %s again'])
			);
			buildButton(
				"light-bg",
				$ccsite->root . $ccpage->module->slug . '/' . $post['slug'],
				str_replace('%s',htmlentities($title),$lang['Preview %s'])
			);
			echo '</div>';
			
		}
			
		//output error message if failed
		else{
			
			?>
			<div class="msg error f-c"><?=$lang['There was an error editing your blog post.  Please try again.']?></div>
			<?php
			
		}
		
	}else{
		
		//get tags
		$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "blogs_tags WHERE blog=:blog AND blogid=:blogid");
		$stmt->execute(['blog' => $post['blog'],'blogid' => $post['id']]);
		$tagarr = $stmt->fetchAll();
		$tags = "";
		foreach($tagarr as $tag){
			$tags .= $tag['tag'] . ', ';
		}

		//start the form ?>

		<form action="" method="post">
				
			<?php //build the blog info form ?>
			<div class="formcontain">
				<?php
				
					//build array of form info
					$forminputs = array();
					array_push($forminputs,
						array(
							array(
								'type' => "text",
								'label' => $lang['Post title'],
								'tooltip' => $lang['tooltip-posttitle'],
								'name' => "post-title",
								'regex' => "normal-text",
								'current' => $post['title']
							)
						),array(
							array(
								'type' => "date",
								'label' => $lang['Publish date'],
								'tooltip' => $lang['tooltip-blogpublishtime'],
								'name' => "post-date",
								'regex' => "date",
								'current' => date("m/d/Y",$post['publishtime'])
							),array(
								'type' => "time",
								'label' => $lang['Publish time'],
								'tooltip' => $lang['tooltip-blogpublishtime'],
								'name' => "post-time",
								'regex' => "time",
								'current' => $post['publishtime']
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
									'regex' => false,
									'current' => $tags
								)
							)
						);
					
					//output the form
					buildForm($forminputs); 
					
					//output the text editor for the blog post
					buildTextEditor($lang['Post content'],"post-content",$lang['tooltip-postcontent'],$post['content']);
				?>
			</div>
			
			<?php // close the form ?>
			<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Submit changes']?></button>
		</form>
		
		<?php 
		//include relevant javascript
		include('includes/form-submit-js.php');
		include('includes/content-editor-js.php');
	}
	
}
?>

</main>