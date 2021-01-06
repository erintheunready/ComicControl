<?

$dbhost = $_POST['install-dbhost'];
$dbname = $_POST['install-dbname'];
$dbuser = $_POST['install-dbuser'];
$dbpass = $_POST['install-dbpass'];
$tableprefix = $_POST['install-tableprefix'];

$charset = "utf8";

//CONNECT TO DATABASE
$dsn = "mysql:host=$dbhost;dbname=$dbname;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
	PDO::MYSQL_ATTR_FOUND_ROWS => true
];
$failed = false;
try {
    $pdotest = new PDO($dsn, $dbuser, $dbpass, $opt);
}
catch( PDOException $error ) {
	print_r($error);
    $failed = true;
}

if(!$failed){
	
	$dbconfigtxt = '<?php
	//dbconfig.php - connects to database

	//DATABASE INFO
	$dbhost = "' . $dbhost . '";
	$dbname = "' . $dbname . '";
	$dblogin = "' . $dbuser . '";
	$dbpass = "' . $dbpass . '";
	$charset = "utf8mb4";

	//CONNECT TO DATABASE
	$dsn = "mysql:host=$dbhost;dbname=$dbname;charset=$charset";
	$opt = [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES   => false,
		PDO::MYSQL_ATTR_FOUND_ROWS => true
	];
	$cc = new PDO($dsn, $dblogin, $dbpass, $opt);
	$tableprefix = "' . $tableprefix . '";

	?>';

	file_put_contents('includes/dbconfig.php',$dbconfigtxt);
	include('includes/dbconfig.php');
	
	$sqlquery = file_get_contents("install.sql","r");
	$sqlquery = str_replace("_temp_","_" . $tableprefix, $sqlquery);
	
	$cc->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);

	try {
		$cc->exec($sqlquery);
	}
	catch (PDOException $e)
	{
		echo $e->getMessage();
		die();
	}
	
	unlink('install.sql');

}

?>