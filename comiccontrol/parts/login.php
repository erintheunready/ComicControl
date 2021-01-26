<?php //content of the login form ?>

<form action="<?=$ccurl?>" method="post" id="loginwrap">
	<div id="loginbox">
		<div id="loginlogo"><img src="images/loginlogo.png" /></div>
		<?php if(isset($_POST['username'])) echo '<div class="msg error" style="margin-top:30px;">' . $lang['Incorrect login information.  Please check your input and try again.'] . '</div>'; ?>
		<div class="formline"><label><div class="v-c"><?=$lang['Username']?>: *</div></label><input type="text" name="username" /></div>
		<div class="formline"><label><div class="v-c"><?=$lang['Password']?>: *</div></label><input type="password" name="password" /></div>
		<button class="full-width light-bg" type="submit"><?=$lang['Login']?></button>
		<button class="full-width dark-bg" type="button" onclick="location.href='<?=$ccurl?>password-reset'"><?=$lang['Lost your password?']?></a>
	</div>
</form>