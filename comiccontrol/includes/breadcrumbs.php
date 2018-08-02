<? //breadcrumbs.php - outputs location header at top of backend pages ?>

<header id="navheader" class="dark-bg no-arrow">
	<?
		$currentpage = getSlug(1);
		
		//header adding for modules since they have a special title format and extra slug
		if($currentpage == "modules"){
			
			//output the module title and type
			echo '<div class="header-block header-page has-subtitle"><span class="title">';
			echo '<a href="' . $ccurl . $navslug . '/' . getSlug(2) . '">' . $ccpage->title . '</a>';
			echo '</span><br /><span class="subtitle">';
			echo str_replace('%s',ucwords($ccpage->moduletype),$lang['%s Module']);
			echo '</span>';
			
			//if an action is selected, output a caret and the action
			if(getSlug(3) != ""){
				echo '</div><div style="display:inline-block; line-height:50px;"><i class="fa fa-caret-right"></i></div><div class="header-block">';
			}
			switch(getSlug(3)){
				case "add-post":
					echo $lang['Add post'];
					break;
				case "edit-post":
					echo $lang['Edit post'];
					break;
				case "manage-posts":
					echo $lang['Manage posts'];
					break;
				case "delete-post":
					echo $lang['Delete post'];
					break;
				case "manage-storylines":
					echo $lang['Manage storylines'];
					break;
				case "add-storyline":
					echo $lang['Add storyline'];
					break;
				case "edit-storyline":
					echo $lang['Edit storyline'];
					break;
				case "rearrange-storylines":
					echo $lang['Rearrange storylines'];
					break;
				case "delete-storyline":
					echo $lang['Delete storyline'];
					break;
				case "manage-options":
					echo $lang['Manage module options'];
					break;
				case "add-image":
					echo $lang['Add an image'];
					break;
				case "edit-image":
					echo $lang['Edit image'];
					break;
				case "delete-image":
					echo $lang['Delete image'];
					break;
				case "rearrange-images":
					echo $lang['Rearrange images'];
					break;
			}
			echo '</div>';
		}
		
		//if not a module, echo the title and action
		else{
		?>
		<div class="header-block">
		<?
			//output the main title for the page
			echo '';
			switch($currentpage){
				case "image-library":
					echo '<a href="' . $ccurl . $navslug . '/">' . $lang['Image Library'] . '</a>';
					break;
				case "site-options":
					echo '<a href="' . $ccurl . $navslug . '/">' . $lang['Site Options'] . '</a>';
					break;
				case "users":
					echo '<a href="' . $ccurl . $navslug . '/">' . $lang['Users'] . '</a>';
					break;
				case "update-check":
					echo '<a href="' . $ccurl . $navslug . '/">' . $lang['Check for Updates'] . '</a>';
					break;
				case "upgrade":
					echo '<a href="' . $ccurl . 'update-check/">' . $lang['Check for Updates'] . '</a>';
					break;
				case "templates":
					echo '<a href="' . $ccurl . $navslug . '/">' . $lang['Templates'] . '</a>';
					break;
				case "manage-modules":
					echo '<a href="' . $ccurl . $navslug . '/">' . $lang['Manage modules'] . '</a>';
					break;
				case "plugins":
					echo '<a href="' . $ccurl . $navslug . '/">' . $lang['Plugins'] . '</a>';
					break;
				case "":
					echo '<a href="' . $ccurl . $navslug . '/">' . $lang['Home'] . '</a>';
					break;
			}
			
			//output the action title if one is selected
			if(getSlug(2) != "" && getSlug(1) != "plugins"){
				if(getSlug(4) != "confirmed") echo '</div><div style="display:inline-block; line-height:50px;"><i class="fa fa-caret-right"></i></div><div class="header-block">';
				switch(getSlug(2)){
					case "add-user":
						echo $lang['Add a user'];
						break;
					case "edit-user":
						echo $lang['Edit user'];
						break;
					case "delete-user":
						echo $lang['Delete user'];
						break;
					case "add-module":
						if(!isset($_POST) || $_POST['title'] == "") echo $lang['Add a module'];
						break;
					case "delete-module":
						if(getSlug(4) != "confirmed") echo $lang['Delete module'];
						break;
					case "delete-image":
						echo $lang['Delete image'];
						break;
				}
			}
			echo '</div>';
		}
	?>
	
	<? //output logout button for desktop version ?>
	<div class="header-block dark-bg" id="logout"><a href="<?=$ccurl?>logout"><?=$lang['Logout']?></a></div>
</header>