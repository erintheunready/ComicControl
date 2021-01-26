<?php //password-reset.php - handles all parts of the password reset process ?>

<div class="password-reset-wrap">

<?php

//set variables based on URL
$action = getSlug(2);
$userid = getSlug(3);
$validate = getSlug(4);

//if validation key is given, ask user to reset their password
if($action == "validate"){
	
	//get the user information from the database
	$query = "SELECT * FROM cc_" . $tableprefix . "users WHERE id=:id LIMIT 1";
	$stmt = $cc->prepare($query);
	$stmt->execute(['id' => $userid]);
	
	//if user not found, throw an error
	if($stmt->rowCount() < 1){
		?>
		<div class="msg error"><?=$lang['There is no user with this information.']?></div>
		<?php
	}
	
	//if the user was found, proceed
	else{
		
		//check the database again to make sure the validation key matches the user info
		$thisuser = $stmt->fetch();
		$resethash = md5($validate . $thisuser['resetsalt']);
		$query = "SELECT * FROM cc_" . $tableprefix . "users WHERE id=:id AND resethash=:resethash";
		$stmt = $cc->prepare($query);
		$stmt->execute(['id' => $thisuser['id'], 'resethash' => $resethash]);
		
		//if the validate didn't match, throw a generic error
		if($stmt->rowCount() < 1){
			?>
			<div class="msg error"><?=$lang['No user was found with this information.']?></div>
			<?php
		}
		
		//if the validate matched, proceed
		else{
			
			//if the form was submitted, reset the password
			if(isset($_POST) && $_POST['user-password'] != ""){
				
				//create the password hash
				$password = md5($_POST['user-password'].$thisuser['salt']);
				
				//reset the password in the database
				$query = "UPDATE cc_" . $tableprefix . "users SET password=:password WHERE id=:id";
				$stmt = $cc->prepare($query);
				$stmt->execute(['password' => $password, 'id' => $thisuser['id']]);
				
				//output success message
				if($stmt->rowCount() > 0){
					?>
					<div class="msg success"><?=$lang['Your password has successfully been changed.  Click here to return to the login page.']?></div>
					<?php
				}
				
				//throw error if the password wasn't changed
				else{
					?>
					<div class="msg error"><?=$lang['There was an error changing your password.  Please go back and try again.']?></div>
					<?php
				}
				
			}
			
			//if the form wasn't submitted yet, output the password reset form
			else{
				?>
				<div class="msg prompt"><?=$lang['To reset your password, please type in a new password and confirm it below.']?></div>
				<form action="" method="post" enctype="multipart/form-data">
					<?php
			
					//build the password reset form
					$forminputs = array();
					array_push($forminputs,
						array(
							array(
								'type' => "password",
								'label' => $lang['Password'],
								'tooltip' => $lang['tooltip-password'],
								'name' => "user-password",
								'regex' => 'password'
							),
							array(
								'type' => "password",
								'label' => $lang['Confirm password'],
								'tooltip' => $lang['tooltip-passwordconfirm'],
								'name' => "user-password-confirm",
								'regex' => 'confirm'
							)
						)
					);
					
					//output the form
					buildForm($forminputs); 
					
					//close out the form
					?>
					<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Reset password']?></button>
				</form>
				<script>
					//form submission/validation
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
						var regexpassword = /^[A-Za-z\d!@#%&;:\'\"\^\$\*\+\?\.\(\)\|\{\}\[\]]{8,32}$/;
						$("input").each(function(){
							input = $(this).val();
							if($(this).attr('data-validate')) {
								var $forminput = $(this).closest('.forminput');
								$forminput.find('.formerror').remove();
								switch($(this).attr('data-validate')){
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
							$('#submitform').closest('form').submit();
						}else{
							$('html, body').animate({scrollTop: '0px'}, 300);
						}
						
					});
				</script>
				<?php
				
			}
		}
	}
	
}

//if no validation key given, give e-mail submit form
else{
	
	//if the e-mail form was submitted, create validation key and send e-mail to user
	if(isset($_POST) && $_POST['user-email']){
		
		//check to make sure the e-mail exists
		$query = "SELECT * FROM cc_" . $tableprefix . "users WHERE email=:email LIMIT 1";
		$stmt = $cc->prepare($query);
		$stmt->execute(['email' => $_POST['user-email']]);
		
		//if the user was found, proceed
		if($stmt->rowCount() > 0){
			
			//get the user info
			$thisuser = $stmt->fetch();
			
			//create a salt and validation key
			$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			$resetstring = '';
			$salt = '';
			for ($i = 0; $i < 16; $i++) {
			  $salt .= $characters[rand(0, strlen($characters) - 1)];
			}
			for ($i = 0; $i < 32; $i++) {
			  $resetstring .= $characters[rand(0, strlen($characters) - 1)];
			}
			$hash = md5($resetstring.$salt);
			
			//put the salt and hash in the database
			$query = "UPDATE cc_" . $tableprefix . "users SET resethash=:resethash, resetsalt=:resetsalt WHERE email=:email";
			$stmt = $cc->prepare($query);
			$stmt->execute(['resethash' => $hash, 'resetsalt' => $salt, 'email' => $thisuser['email']]);
			
			//send an e-mail to the user with their validation key
			$to = $thisuser['email'];
			$subject = $lang['ComicControl - Password Reset Request'];
			$message = $lang['passwordresetmessage1'] . '<p><a href="' . $ccurl . 'password-reset/validate/' . $thisuser['id'] . '/' . $resetstring . '">' . $ccurl . 'password-reset/validate/' . $thisuser['id'] . '/' . $resetstring . '</a></p>' . $lang['passwordresetmessage2'];
			$parse = parse_url($ccsite->root);
			$domain = $parse['host'];
			$header = "From:comiccontrol@" . $domain . " \r\n";
			$header .= "MIME-Version: 1.0\r\n";
			$header .= "Content-type: text/html\r\n";
			$mailsend = mail($to,$subject,$message,$header);
			
			//if the mail sent, give a success message
			if($mailsend){
				echo '<div class="msg success">' . $lang['A password reset e-mail has been sent to your address.  Please follow the instructions in the e-mail to reset your password.'] . '</div>';
			}
			
			//otherwise, throw an error
			else{
				echo '<div class="msg error">' . $lang['There was an error sending an e-mail to this address. Please try again.'] . '</div>';
			}
		}
		
		//if the user wasn't found, throw an error
		else{
			echo '<div class="msg error">' . $lang['No user was found with this information.'] . '</div>';
		}
		
	}
	
	//output the e-mail submission form if not yet postedd
	else{
		?>
		<div class="msg prompt"><?=$lang['To reset your password, please submit your e-mail address.  An e-mail will be sent to your account with a link to reset your password.']?></div>
		<form action="" method="post" enctype="multipart/form-data">
			<?php
			
			//build the e-mail submission form
			$forminputs = array();
			array_push($forminputs,
				array(
					array(
						'type' => "text",
						'label' => $lang['E-mail'],
						'tooltip' => $lang['tooltip-email'],
						'name' => "user-email",
						'regex' => "email"
					)
				)
			);
			
			//output the form
			buildForm($forminputs); 
			
			//close out the form
			?>
			<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$lang['Reset password']?></button>
		</form>
		<script>
		
			//form validation scripts
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
				var regexemail = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
				$("input").each(function(){
					input = $(this).val();
					if($(this).attr('data-validate')) {
						var $forminput = $(this).closest('.forminput');
						$forminput.find('.formerror').remove();
						switch($(this).attr('data-validate')){
							case 'email':
								if(!regexemail.test(input)){
									showError($forminput,'<?=$lang['error-email']?>');
									error = true;
								}
								break;
						}
					}
					
				});
				
				if(!error){
					$('#submitform').closest('form').submit();
				}else{
					$('html, body').animate({scrollTop: '0px'}, 300);
				}
				
			});
		</script>
		<?php
	}
	
}

?>
</div>