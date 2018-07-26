<? //user-edit.php - form for editing user info 

if($ccuser->authlevel == 2){
	//create and output quick links if top level user
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
}

?>

<main id="content">

<? //include necessary libraries ?>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js" type="text/javascript" /></script>

<? 
//get selected user
$query = "SELECT * FROM cc_" . $tableprefix . "users WHERE id=:id LIMIT 1";
$stmt = $cc->prepare($query);
$stmt->execute(['id' => $userid]);

//throw error if the user wasn't found
if($stmt->rowCount() < 1){
	echo '<div class="msg error">' . $lang['There is no user with this ID.'] . '</div>';
}

//if user found, proceed
else{
	
	$thisuser = $stmt->fetch();

	//submit page if posted
	if(isset($_POST) && $_POST['user-username'] != ""){
		
		//set values for the query 
		$avatar = "";
		$avatar = $_POST['image-finalfile'];
		if($avatar == "") $avatar = $thisuser['avatar'];
		$username = $_POST['user-username'];
		$email = $_POST['user-email'];
		$password = $_POST['user-password'];
		$authlevel = $_POST['user-type'];
		
		//don't reset password if no new password set
		if($password == ""){
			$password = $thisuser['password'];
			$salt = $thisuser['salt'];
		}
		
		//recreate salt and reset password if set
		else{
			
			$salt = "";
			$charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			for($i = 0; $i < 16; $i++){
				$salt .= $charset[rand(0, strlen($charset)-1)];
			}
			
			$password = md5($password.$salt);
		
		}
		
			
		//create query
		$query = "UPDATE cc_" . $tableprefix . "users SET username=:username, password=:password, email=:email, salt=:salt, authlevel=:authlevel,avatar=:avatar WHERE id=:id";
		$stmt = $cc->prepare($query);
		$stmt->execute(['username' => $username, 'password' => $password, 'email' => $email, 'salt' => $salt, 'authlevel' => $authlevel, 'avatar' => $avatar, 'id' => $thisuser['id']]);
		
		//continue if user successfully added
		if($stmt->rowCount() > 0){
		
			//reset user info in cookies and server if the edited user was the logged in user
			if($ccuser->id == $thisuser['id']){
				
				//create the user-end hash
				$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
				$loginhash = '';
				for ($i = 0; $i < 32; $i++) {
				  $loginhash .= $characters[rand(0, strlen($characters) - 1)];
				}
				$sessionhash =  sha1($username . $salt . $loginhash);
				
				//create the server-end hash and put it in the database if not already logged in
				$stmt = $cc->prepare("INSERT INTO cc_" . $tableprefix . "sessions(userid, loginhash, loginexpire) VALUES(:userid,:loginhash,:expire)");
				$stmt->execute(['userid' => $thisuser['id'], 'loginhash' => $sessionhash, 'expire' => time() + (432000) ]);
				
				setcookie('loginhash', $loginhash, time() + (432000), "/", $_SERVER['HTTP_HOST']);
				setcookie('username', $username, time() + (432000), "/", $_SERVER['HTTP_HOST']);
				
			}
			
			//give success message
			?>
			<div class="msg success f-c"><?=$lang['Your changes were successfully saved.']?></div>
			<?
		}
			
		//output error message if failed
		else{
			?>
			<div class="msg error f-c"><?=$lang['There was an error editing this user.  Please try again.']?></div>
			<?
		}
			
		
	}else{

		//start the form ?>

		<form action="" method="post" enctype="multipart/form-data">
			
			<? // image uploader area for avatar
			if($thisuser['avatar'] != ""){
			?>
			<div class="currentfileholder"><button class="full-width dark-bg toggle-current-file"><span class="current-file-text"><?=$lang['View current avatar']?></span> <i class="fa fa-angle-down"></i></button>
				<div class="currentfile"><img src="<?=$ccurl . 'avatars/' . $thisuser['avatar']?>" /></div>
			</div>
			<?
			}
			
			buildImageInput($lang['Choose avatar image...'],false,$lang['tooltip-avatarimage']); ?>
			<?
				
				//build array of form info
				$forminputs = array();
				array_push($forminputs,
					array(
						array(
							'type' => "text",
							'label' => $lang['Username'],
							'tooltip' => $lang['tooltip-username'],
							'name' => "user-username",
							'regex' => "username",
							'current' => $thisuser['username']
						)
					),
					array(
						array(
							'type' => "text",
							'label' => $lang['E-mail'],
							'tooltip' => $lang['tooltip-email'],
							'name' => "user-email",
							'regex' => "email",
							'current' => $thisuser['email']
						)
					),array(
						array(
							'type' => "password",
							'label' => $lang['Password'],
							'tooltip' => $lang['tooltip-password-change'],
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
				
				//allow user to change authorization level if an administrator
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
								),
								'current' => $thisuser['authlevel']
							)
						)
					);
				}
				
				//build the form
				buildForm($forminputs); 
			?>
			<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Submit changes']?></button>
		</form>
		
		<script>
		//custom validation scripts for user info
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
							if(input != ""){
								if(!regexpassword.test(input)){
									showError($forminput,'<?=$lang['error-password']?>');
									error = true;
								}
							}
							break;
						case 'confirm':
							var pwtest = $('input[name=' + $(this).attr('name').slice(0,-8) + ']').val();
							if(pwtest != ""){
								if($(this).val() != pwtest){
									showError($forminput,'<?=$lang['error-confirm']?>');
									error = true;
								}
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
		
		//manages avatar image pull down area
		$('.toggle-current-file').on('click',function(e){
			e.preventDefault();
			$(this).parent().find('.currentfile').slideToggle();
			$(this).find('.current-file-text').text(function(i, text){
				  return text === '<?=$lang['View current avatar']?>' ? '<?=$lang['Hide current avatar']?>' : '<?=$lang['View current avatar']?>';
			});
			$(this).find('i').toggleClass('fa-angle-down fa-angle-up');
		});
		</script>

		<? 
		//include relevant javascript
		$imgfolder = "comiccontrol/avatars/";
		include('includes/img-upload-js.php');
		include('includes/content-editor-js.php');
	}
		
}
?>

</main>