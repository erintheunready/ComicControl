<?

//set values for the query 
$username = $_POST['install-username'];
$email = $_POST['install-email'];
$password = $_POST['install-password'];
$authlevel = 2;

//build the password salt and hash
$salt = "";
$charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
for($i = 0; $i < 16; $i++){
	$salt .= $charset[rand(0, strlen($charset)-1)];
}

$password = md5($password.$salt);

//create query
$query = "INSERT INTO cc_" . $tableprefix . "users(username,password,email,salt,authlevel,avatar) VALUES(:username,:password,:email,:salt,:authlevel,:avatar)";
$stmt = $cc->prepare($query);
$stmt->execute(['username' => $username, 'password' => $password, 'email' => $email, 'salt' => $salt, 'authlevel' => $authlevel, 'avatar' => '']);
$userid = $cc->lastInsertId();

//get the user and log them in
$query = "SELECT * FROM cc_" . $tableprefix . "users WHERE id=:id LIMIT 1";
$stmt = $cc->prepare($query);
$stmt->execute(['id' => $userid]);
$userinfo = $stmt->fetch();
$loginhash = 0;

//create the user-end hash
$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
$loginhash = '';
for ($i = 0; $i < 32; $i++) {
  $loginhash .= $characters[rand(0, strlen($characters) - 1)];
}

//create the server-end hash and put it in the database if not already logged in
$sessionhash =  sha1($userinfo['username'] . $userinfo['salt'] . $loginhash);
$stmt = $cc->prepare("INSERT INTO cc_" . $tableprefix . "sessions(userid, loginhash, loginexpire) VALUES(:userid,:loginhash,:expire)");
$stmt->execute(['userid' => $userinfo['id'], 'loginhash' => $sessionhash, 'expire' => time() + (432000) ]);
		
//set the user cookie
setcookie('loginhash', $loginhash, time() + (432000), "/", $_SERVER['HTTP_HOST']);
setcookie('username', $userinfo['username'], time() + (432000), "/", $_SERVER['HTTP_HOST']);
setcookie('hashtime', time(), time() + (432000), "/", $_SERVER['HTTP_HOST']);

$ccuser = new CC_User();

?>