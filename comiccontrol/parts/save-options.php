<?
	
//save-options.php - generic option saving for module options

//save the options that go in the modules table
$query = "UPDATE cc_" . $tableprefix . "modules SET title=:title, template=:template, language=:language, description=:description WHERE id=:moduleid";
$stmt = $cc->prepare($query);
$stmt->execute(['title' => $_POST['page-title'], 'template' => $_POST['page-template'], 'language' => $_POST['page-language'], 'description' => $_POST['page-description'], 'moduleid' => $ccpage->id]);

//save the options that go in the modules_options table
$query = "SELECT * FROM cc_" . $tableprefix . "modules_options WHERE moduleid=:moduleid";
$stmt = $cc->prepare($query);
$stmt->execute(['moduleid' => $ccpage->module->id]);
$options = $stmt->fetchAll();
$query = "UPDATE cc_" . $tableprefix . "modules_options SET value=:value WHERE optionname=:optionname AND moduleid=:moduleid";
$stmt = $cc->prepare($query);

foreach($options as $option){
	if(array_key_exists($option['optionname'],$_POST)){
		$stmt->execute(['value' => $_POST[$option['optionname']], 'optionname' => $option['optionname'], 'moduleid' => $ccpage->module->id]);
	}
}
	
?>