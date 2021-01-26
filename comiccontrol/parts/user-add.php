<?php //user-add.php - handles adding new users

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

<?php //include necessary libraries ?>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js" type="text/javascript" /></script>

<?php 

//submit the new user if posted
if(isset($_POST) && $_POST['user-username'] != "" && $ccuser->authlevel == 2){
	
	//set values for the query 
	$avatar = "";
	$avatar = $_POST['image-finalfile'];
	$username = $_POST['user-username'];
	$email = $_POST['user-email'];
	$password = $_POST['user-password'];
	$authlevel = $_POST['user-type'];
	
	//build the password salt and hash
	$salt = "";
	$charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	for($i = 0; $i < 16; $i++){
		$salt .= $charset[rand(0, strlen($charset)-1)];
	}
	
	$password = md5($password.$salt);
	
	//check that username doesn't already exist
	$query = "SELECT id FROM cc_" . $tableprefix . "users WHERE username=:username";
	$stmt = $cc->prepare($query);
	$stmt->execute(['username' => $username]);
	
	//throw an error if the username was found
	if($stmt->rowCount() > 0){
		?>
		<div class="msg error f-c"><?=$lang['Sorry, there is already a user with this username.  Please go back and select a different username.']?></div>
		<?php
	}else{
		
		//check that the e-mail isn't already taken
		$query = "SELECT id FROM cc_" . $tableprefix . "users WHERE email=:email";
		$stmt = $cc->prepare($query);
		$stmt->execute(['email' => $email]);
		
		//throw an error if the e-mail is already in use
		if($stmt->rowCount() > 0){
			?>
			<div class="msg error f-c"><?=$lang['Sorry, there is already a user with this e-mail address.  Please go back and user a different e-mail address.']?></div>
			<?php
		}
		
		//if all is well, submit the user
		else{
			
			//create query
			$query = "INSERT INTO cc_" . $tableprefix . "users(username,password,email,salt,authlevel,avatar) VALUES(:username,:password,:email,:salt,:authlevel,:avatar)";
			$stmt = $cc->prepare($query);
			$stmt->execute(['username' => $username, 'password' => $password, 'email' => $email, 'salt' => $salt, 'authlevel' => $authlevel, 'avatar' => $avatar]);
			$userid = $cc->lastInsertId();
			
			//success message if the user was added
			if($stmt->rowCount() > 0){
				?>
				<div class="msg success f-c"><?=$lang['This user was successfully added.']?></div>
				<?php
			}
				
			//output error message if failed
			else{
				?>
				<div class="msg error f-c"><?=$lang['There was an error added this user.  Please try again.']?></div>
				<?php
			}
		
		}
		
	}
	
}
//if not posted, give form for adding a user
else{

	//start the form ?>

	<form action="" method="post" enctype="multipart/form-data">
		
		<?php // image uploader area for avatar ?>
		<?php buildImageInput($lang['Choose avatar image...'],false,$lang['tooltip-avatarimage']); ?>
		<?php
		
			//build array of form info
			$forminputs = array();
			array_push($forminputs,
				array(
					array(
						'type' => "text",
						'label' => $lang['Username'],
						'tooltip' => $lang['tooltip-username'],
						'name' => "user-username",
						'regex' => "username"
					)
				),
				array(
					array(
						'type' => "text",
						'label' => $lang['E-mail'],
						'tooltip' => $lang['tooltip-email'],
						'name' => "user-email",
						'regex' => "email"
					)
				),array(
					array(
						'type' => "password",
						'label' => $lang['Password'],
						'tooltip' => $lang['tooltip-password'],
						'name' => "user-password",
						'regex' => "password"
					),
					array(
						'type' => "password",
						'label' => $lang['Confirm password'],
						'tooltip' => $lang['tooltip-passwordconfirm'],
						'name' => "user-password-confirm",
						'regex' => "confirm"
					)
				)
			);
			
			if($ccuser->authlevel == 2){
				array_push($forminputs,
					array(
						array(
							'type' => "select",
							'label' => $lang['User type'],
							'tooltip' => $lang['tooltip-usertype'],
							'name' => "user-type",
							'regex' => "select",
							'options' => array(
								'2' => $lang['Administrator'],
								'1' => $lang['User']
							)
						)
					)
				);
			}
			
			//build the form
			buildForm($forminputs); 
			
		//close out the form
		?>
		<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Create user']?></button>
	</form>
	
	<script>
	//special user information validation
	function showError(container,msg){
		var $errordiv = $('<div class="formerror"></div>');
		$errordiv.html(msg);
		container.css('height','auto');
		container.append($errordiv);
		$errordiv.slideDown();
	}
	$("#submitform").on('click', function(){
		var error = false;
		var input = "";
		var regexusername = /^[a-zA-Z0-9\_]{4,32}$/;
		var regexemail = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
		var regexpassword = /^[A-Za-z\d!@#%&;:\'\"\^\$\*\+\?\.\(\)\|\{\}\[\]]{8,32}$/;
		$("input").each(function(){
			input = $(this).val();
			if($(this).attr('data-validate')) {
				var $forminput = $(this).closest('.forminput');
				$forminput.find('.formerror').remove();
				switch($(this).attr('data-validate')){
					case 'username':
						if(!regexusername.test(input)){
							showError($forminput,'<?=$lang['error-username']?>');
							error = true;
						}
						break;
					case 'email':
						if(!regexemail.test(input)){
							showError($forminput,'<?=$lang['error-email']?>');
							error = true;
						}
						break;
					case 'password':
						if(!regexpassword.test(input)){
							showError($forminput,'<?=$lang['error-password']?>');
							error = true;
						}
						break;
					case 'confirm':
						var pwtest = $('input[name=' + $(this).attr('name').slice(0,-8) + ']').val();
						console.log(pwtest);
						if($(this).val() != pwtest){
							showError($forminput,'<?=$lang['error-confirm']?>');
							error = true;
						}
						break;
						
						
				}
			}
			
		});
		
		if(!error){
			$('input[type=file]').each( function() {
				$(this).remove();
			});
			$('#submitform').closest('form').submit();
		}else{
			$('html, body').animate({scrollTop: '0px'}, 300);
		}
		
	});
	</script>

	<?php 
	//include relevant javascript
	$imgfolder = "comiccontrol/avatars/";
	include('includes/img-upload-js.php');
	include('includes/content-editor-js.php');
}
?>

</main>