<? include('includes/install-header.php');  ?>

<?=$ilang['thirdstep']?>

<form action="" method="post">
<?

$forminputs = array();
array_push($forminputs,
	array(
		array(
			'type' => "text",
			'label' => $ilang['Username'],
			'tooltip' => $ilang['username-tooltip'],
			'name' => "install-username",
			'regex' => "username"
		)
	),
	array(
		array(
			'type' => "text",
			'label' => $ilang['E-mail'],
			'tooltip' => $ilang['email-tooltip'],
			'name' => "install-email",
			'regex' => "email"
		)
	),array(
		array(
			'type' => "password",
			'label' => $ilang['Password'],
			'tooltip' => $ilang['password-tooltip'],
			'name' => "install-password",
			'regex' => "password"
		)
	),array(
		array(
			'type' => "password",
			'label' => $ilang['Confirm your password'],
			'tooltip' => $ilang['confirm-tooltip'],
			'name' => "install-password-confirm",
			'regex' => "confirm"
		)
	)
);

//build the form
buildForm($forminputs); 

?>

<? // close the form ?>
<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$ilang['next']?></button>

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
<? include('includes/install-footer.php'); ?>