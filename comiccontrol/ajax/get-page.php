<?
//get-page.php - get page of results and navigation when navigating through pages (blog, gallery, images, storylines, etc.)

//include scripts required for database and classes
require_once('../includes/dbconfig.php');
require_once('../includes/initialize.php');

//only allow script to be used if user has authorization
if($ccuser->authlevel > 0){
	
	//set variables for the query (either defaults or from post data)
	$searchid = 1;
	$page = 1;
	$searchid = filter_var($_POST['searchid'], FILTER_SANITIZE_NUMBER_INT);
	$moduleid = filter_var($_POST['moduleid'], FILTER_SANITIZE_NUMBER_INT);
	$page = filter_var($_POST['page'], FILTER_SANITIZE_NUMBER_INT);
	$type = $_POST['type'];
	
	$data = array();
	
	//set limits for pages
	$lowerlimit = 20 * ($page-1);
	$upperlimit = $lowerlimit + 20;
	
	//build query based on what script is being called for
	$query = "SELECT * FROM cc_" . $tableprefix;
	if($type == "storyline") $query .=  "comics_storyline WHERE parent=:searchid AND comic=:comicid ORDER BY sorder ASC";
	if($type == "blog") $query .= "blogs WHERE blog=:searchid ORDER BY publishtime DESC LIMIT " . $lowerlimit . ",20";
	if($type == "gallery") $query .= "galleries WHERE gallery=:searchid ORDER BY porder ASC LIMIT " . $lowerlimit . ",20";
	if($type == "images") $query .= "images ORDER BY id DESC LIMIT " . $lowerlimit . ",20";
	$stmt = $cc->prepare($query);
	
	//execute query based on the module
	if($type == "images") $stmt->execute();
	else if($type == "storyline") $stmt->execute(['searchid' => $searchid, 'comicid' => $moduleid]);
	else  $stmt->execute(['searchid' => $searchid]);
	$data['results'] = $stmt->fetchAll();
	
	//handle special case for storyline navigation
	if($type == "storyline"){
		
		$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE id=:id AND comic=:comicid LIMIT 1";
		$stmt = $cc->prepare($query);
		$stmt->execute(['id' => $searchid, 'comicid' => $moduleid]);
		$thisstoryline = $stmt->fetch();
		
		//get the storyline parent
		if($thisstoryline['parent'] != 0){
			$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE id=:id AND comic=:comicid LIMIT 1";
			$stmt = $cc->prepare($query);
			$stmt->execute(['id' => $thisstoryline['parent'], 'comicid' => $moduleid]);
			$data['parent'] = $stmt->fetch();
		}else{
			$data['parent'] = array();
			$data['parent']['id'] = 0;
			$data['parent']['name'] = "Top level";
		}
	}
	
	//if doing comic page navigation, get the pages in the storyline
	if($_POST['getPages'] == true){
		$query = "SELECT slug,title FROM cc_" . $tableprefix . "comics WHERE storyline=:searchid ORDER BY publishtime ASC";
		$stmt = $cc->prepare($query);
		$stmt->execute(['searchid' => $searchid]);
		$data['pages'] = $stmt->fetchAll();
	}
	
	//do regular page navigation for non-storylines
	if($type != "storyline"){
		
		//check if there are previous and next pages
		$data['prev'] = 0;
		$data['next'] = 0;
		
		if($page > 1) $data['prev'] = $page - 1;
		
		$query = "SELECT * FROM cc_" . $tableprefix;
		if($type == "blog") $query .= "blogs WHERE blog=:searchid ORDER BY publishtime ASC LIMIT " . $upperlimit . ",20";
		if($type == "gallery") $query .= "galleries WHERE gallery=:searchid ORDER BY porder ASC LIMIT " . $upperlimit . ",20";
		if($type == "images") $query .= "images ORDER BY id ASC LIMIT " . $upperlimit . ",20";
		$stmt = $cc->prepare($query);
		
		if($type == "images") $stmt->execute();
		else $stmt->execute(['searchid' => $searchid]);
		$result = $stmt->fetchAll();
		if(!empty($result)) $data['next'] = $page + 1;
		
		
	}
	
	//encode and echo the results
	echo json_encode($data);
	
}

?>