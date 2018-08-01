<? //user-delete.php - handles deletion of existing users

//create and output quick links
$links = array(
	array(
		'link' => $ccurl . $navslug,
		'text' => str_replace('%s',$ccpage->title,$lang['Return to managing users'])
	),
	array(
		'link' => $ccurl . $navslug . '/add-user',
		'text' => $lang['Add another user']
	)
);
quickLinks($links);

?>

<main id="content">

<?

//get selected user
$query = "SELECT * FROM cc_" . $tableprefix . "users WHERE id=:id";
$stmt = $cc->prepare($query);
$stmt->execute(['id' => $userid]);
$thisuser = $stmt->fetch();

//throw error if the user wasn't found
if(empty($thisuser)){
	echo '<div class="msg error f-c">' . $lang['No user was found with this information.'] . '</div>';
}

//otherwise, proceed
else{
	
	//don't allow a user to delete themselves
	if($thisuser['id'] == $ccuser->id){
		?>
		<div class="msg error f-c"><?=$lang['The logged in user cannot delete their own account.']?></div>
		<?
	}
	else{
		
		//if confirmed, delete the user
		if(getSlug(4) == "confirmed"){
			
			$stmt = $cc->prepare("DELETE FROM cc_" . $tableprefix . "users WHERE id=:id");
			$stmt->execute(['id' => $thisuser['id']]);
			?>
			
			<div class="msg success f-c"><?=$lang['This user has been deleted.']?></div>
			
			<?
			
			
		}else{
		
			//give a prompt to confirm user confirmation ?>

			<div class="msg prompt f-c"><?=$lang['Are you sure you want to delete this user? This action cannot be undone.']?></div>
			<?
			
			echo '<div class="cc-btn-row">';
			buildButton(
				"light-bg",
				$ccurl . $navslug.'/delete-user/' . $thisuser['id'] . '/confirmed',
				$lang['Yes']
			);
			buildButton(
				"dark-bg",
				$ccurl . $navslug.'/'.$ccpage->slug."/",
				$lang['No']
			);
			echo '</div>';
			
		}
	}
}
?>

</main>