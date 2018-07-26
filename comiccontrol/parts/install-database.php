<? include('includes/install-header.php');  ?>

<?=$ilang['firststep']?>

<? if($failed) echo '<div class="msg error">' . $ilang['dbbuilderror'] . '</div>'; ?>
<form action="" method="post">
<?

$forminputs = array();
array_push($forminputs,
	array(
		array(
			'type' => "text",
			'label' => $ilang['Database host'],
			'tooltip' => $ilang['dbhost-tooltip'],
			'name' => "install-dbhost",
			'regex' => "normal-text"
		)
	),array(
		array(
			'type' => "text",
			'label' => $ilang['Database name'],
			'tooltip' => $ilang['dbname-tooltip'],
			'name' => "install-dbname",
			'regex' => "normal-text"
		)
	),array(
		array(
			'type' => "text",
			'label' => $ilang['Database user'],
			'tooltip' => $ilang['dbuser-tooltip'],
			'name' => "install-dbuser",
			'regex' => "normal-text"
		)
	),array(
		array(
			'type' => "password",
			'label' => $ilang['Database password'],
			'tooltip' => $ilang['dbpassword-tooltip'],
			'name' => "install-dbpass",
			'regex' => "normal-text"
		)
	),array(
		array(
			'type' => "text",
			'label' => $ilang['Prefix for tables'],
			'tooltip' => $ilang['tableprefix-tooltip'],
			'name' => "install-tableprefix",
			'regex' => "prefix"
		)
	)
);

//build the form
buildForm($forminputs); 

?>

<? // close the form ?>
<button class="full-width light-bg" style="margin-top:20px;" type="button" id="submitform"><?=$ilang['next']?></button>

</form>
<?

//include relevant javascript
include('includes/form-submit-js.php'); 

?>

<? include('includes/install-footer.php'); ?>