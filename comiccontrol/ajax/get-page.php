<?php

//import db and initialize
require_once('../includes/dbconfig.php');
require_once('../includes/initialize.php');

//check user authorization
if($ccuser->authlevel > 0){

	//get action type
		//action types: comic, blog, gallery, media
	$action = $_POST['action'];
	$numpages = 1;
	$returnData = array();

	//sanitize all inputs
		//sanitize expected parts of received object that will be put into PHP query (module id, page number, storyline id)
	if($action == "storyline"){
		$searchid = filter_var($_POST['storyline'], FILTER_SANITIZE_NUMBER_INT);	
	}else{
		$searchid = filter_var($_POST['pagenum'], FILTER_SANITIZE_NUMBER_INT);	
		$lowerlimit = 20 * ($searchid-1);
	}

	$moduleid = 0;
	if($action != "media"){
		$moduleid = filter_var($_POST['moduleid'], FILTER_SANITIZE_NUMBER_INT);	
	}


	//build query based on action type and get auxiliary info
	switch($_POST['action']){
		case "media":
			$query = $cc->prepare("SELECT id FROM cc_" . $tableprefix . "images");
			$query->execute();
			$numpages = ceil(($query->rowCount())/20);
			$returnData['numpages'] = $numpages;
			$query = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "images ORDER BY id DESC LIMIT " . $lowerlimit . ", 20");
			$query->execute();
		break;
		case "gallery":
			$query = $cc->prepare("SELECT id FROM cc_" . $tableprefix . "galleries WHERE gallery=:moduleid");
			$query->execute(['moduleid' => $moduleid]);
			$numpages = ceil(($query->rowCount())/20);
			$returnData['numpages'] = $numpages;
			$query = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "galleries WHERE gallery=:moduleid ORDER BY porder ASC LIMIT " . $lowerlimit . ", 20");
			$query->execute(['moduleid' => $moduleid]);
		break;
		case "blog":
			$query = $cc->prepare("SELECT id FROM cc_" . $tableprefix . "blogs WHERE blog=:moduleid");
			$query->execute(['moduleid' => $moduleid]);
			$numpages = ceil(($query->rowCount())/20);
			$returnData['numpages'] = $numpages;
			$query = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "blogs WHERE blog=:moduleid ORDER BY publishtime ASC LIMIT " . $lowerlimit . ", 20");
			$query->execute(['moduleid' => $moduleid]);
		break;
		case "storyline":
			$query = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE parent=:searchid AND comic=:moduleid ORDER BY sorder ASC");
			$query->execute(['searchid' => $searchid,'moduleid' => $moduleid]);
			$returnData['storylineRows'] = $query->fetchAll();
			$query = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE id=:searchid");
			$query->execute(['searchid'=>$searchid]);
			$thisstoryline = $query->fetch();
			if($thisstoryline['parent'] > 0)  $returnData['parent'] = $thisstoryline['parent'];
			else $returnData['parent'] = 0;
			if($thisstoryline['id'] > 0) $returnData['heading'] = $thisstoryline['name'];
			else $returnData['heading'] = '';
			$query = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "comics WHERE storyline=:searchid AND comic=:moduleid ORDER BY publishtime ASC");
			$query->execute(['searchid' => $searchid, 'moduleid' => $moduleid]);
		break;
	}

	//update object with current rows
	$returnData['rows'] = $query->fetchAll();
	//return object

	echo json_encode($returnData);

}

?>