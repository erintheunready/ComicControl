<? //sidebar.php - display layout sidebar ?>
<?
	//determine if admin, if not fetch modules permitted
	$admin = false;
	$permMods = array();
	if($ccuser->authlevel == 2) $admin = true;
	else{
		$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "users_permissions WHERE userid=:userid");
		$stmt->execute(['userid' => $ccuser->id]);
		$perms = $stmt->fetchAll();
		foreach($perms as $perm){
			array_push($permMods,$perm['moduleid']);
		}
	}
?>
<nav id="sidebar" class="dark-bg">
	<? //show the avatar ?>
	<div id="sidebar-avatar">
		<? $ccuser->showAvatar(); ?>
		<div id="sidebar-avatar-bottom"></div>
	</div>
	
	<? //display site title ?>
	<a id="sidebar-header" href="<?=$ccurl?>"><div class="f-c"><div><i class="fa fa-home"></i> <?=$ccsite->sitetitle?></div></div></a>
	<a id="menu-expand"><div class="f-c"><i class="fa fa-bars"></i></div></a>
	
	<? //show main menu ?>
	<ul id="sidebar-menu">
		<li class="dropdown">
			<? //display list of modules ?>
			<a><span class="li-contain"><i class="fa fa-puzzle-piece"></i><?=$lang['Modules']?><i class="fa fa-angle-right angle"></i></span></a>
			<ul>
				<?
					$query = "SELECT * FROM cc_" . $tableprefix . "modules";
					$stmt = $cc->prepare($query);
					$stmt->execute();
					$modules = $stmt->fetchAll();
					foreach($modules as $module){
						if($module['moduletype'] != "custom" && ($admin || in_array($module['id'],$permMods))){
							echo '<li><a href="' . $ccurl . 'modules/' . $module['slug'] . '"><i class="fa fa-';
							switch($module['moduletype']){
								case "comic":
									echo 'th-large';
									break;
								case "blog":
									echo 'newspaper-o';
									break;
								case "text":
									echo 'file';
									break;
								case "gallery":
									echo 'image';
									break;
								default:
									echo 'file';
									break;
							}
							echo '"></i>' . $module['title'] . '</a></li>';
						}
					}
					if($admin){
				?>
				<li><a href="<?=$ccurl?>manage-modules"><i class="fa fa-cog"></i><?=$lang['Manage modules...']?></a></li>
					<? } ?>
			</ul>
		</li>
		<? //restrict these options if not admin 
		if($admin){ ?>
		<li class="dropdown">
			<? //display list of plugins ?>
			<a><span class="li-contain"><i class="fa fa-plug"></i><?=$lang['Plugins']?><i class="fa fa-angle-right angle"></i></span></a>
			<ul>
				<?
					$query = "SELECT * FROM cc_" . $tableprefix . "plugins";
					$stmt = $cc->prepare($query);
					$stmt->execute();
					$plugins = $stmt->fetchAll();
					foreach($plugins as $plugin){
						echo '<li><a href="' . $ccurl . 'plugins/' . $plugin['slug'] . '"><i class="fa fa-plug"></i>' . $plugin['name'] . '</a></li>';
					}
					
				?>
				<li><a href="<?=$ccurl?>plugins"><i class="fa fa-cog"></i><?=$lang['Manage plugins...']?></a></li>
			</ul>
		</li>
		<? //give rest of actions ?>
		<li>
			<a href="<?=$ccurl?>image-library"><span class="li-contain"><i class="fa fa-image"></i><?=$lang['Image Library']?></span></a>
		</li>
		<li>
			<a href="<?=$ccurl?>templates"><span class="li-contain"><i class="fa fa-map-o"></i><?=$lang['Templates']?></span></a>
		</li>
		<li>
			<a href="<?=$ccurl?>site-options"><span class="li-contain"><i class="fa fa-cog"></i><?=$lang['Site Options']?></span></a>
		</li>
		<li>
			<a href="<?=$ccurl?>update-check"><span class="li-contain"><i class="fa fa-refresh"></i><?=$lang['Check for Updates']?></span></a>
		</li>
				<? } ?>
		<li>
			<a href="<?=$ccurl?>users"><span class="li-contain"><i class="fa fa-user"></i>
		<? //give user management if top-level user; if not, only allow self-management ?>	
		<? if($ccuser->authlevel == 2){ ?><?=$lang['Users']?>
		<? }else{ ?><?=$lang['Profile']?><? } ?></span></a></a>
		</li>
		<li>
			<a href="http://www.comicctrl.com/"><span class="li-contain"><i class="fa fa-support"></i><?=$lang['Support']?></span></a>
		</li>
		
		<? //logout button to display if on mobile ?>
		<li id="left-logout">
			<a href="<?=$ccurl?>logout"><span class="li-contain light-bg"><i class="fa fa-sign-out"></i><?=$lang['Logout']?></span></a>
		</li>
	</ul>
	<div id="cclogo"><a href="http://comicctrl.com" target="_blank"><img src="<?=$ccurl?>images/ccsmall.png" /></a></div>
</nav>