<? 

//index.php - handles the whole site.  Builds page based on the URL slugs.

//start output buffering so we can set cookies whenever we feel like it
ob_start();
error_reporting(E_ALL & ~E_NOTICE);
require_once('includes/formfunctions.php');

//go through installation checks
if(!file_exists('includes/dbconfig.php') && !isset($_POST['install-dbname'])){
	require_once('parts/install-database.php');
}else{
	
	if(isset($_POST['install-dbname']) && $_POST['install-dbname'] != ""){
		require_once('parts/install-database-build.php');
		if($failed){
			require_once('parts/install-database.php');
		}else{
			require_once('parts/install-site.php');
		}
	}else{
		
		//initialize database and classes
		require_once('includes/dbconfig.php');
		
		if(isset($_POST['install-sitetitle']) && $_POST['install-sitetitle'] != ""){
			require_once('parts/install-site-build.php');
		}
		
		require_once('includes/initialize.php');
		
		//install if no site title
		if($ccsite->sitetitle == ""){
			
			require_once('parts/install-site.php');
			
		}else{
			
			if(isset($_POST['install-username']) && $_POST['install-username'] != ""){
				require_once('parts/install-user-build.php');
			}
			
			//check users
			$query = "SELECT * FROM cc_" . $tableprefix . "users LIMIT 1";
			$stmt = $cc->prepare($query);
			$stmt->execute();
			if($stmt->rowCount() < 1){
				require_once('parts/install-user.php');
			}
			else{

				//include the user's language file; default is English
				require_once('languages/' . $ccuser->language . '.php');
			
				if(isset($_POST['install-pagetitle']) && $_POST['install-pagetitle'] != ""){
					require_once('parts/install-module-build.php');
				}

				//check modules
				$query = "SELECT * FROM cc_" . $tableprefix . "modules LIMIT 1";
				$stmt = $cc->prepare($query);
				$stmt->execute();
				if($stmt->rowCount() < 1){
					require_once('parts/install-module.php');
				}else{
					
					//if there's a post variable indicating the installation is complete, give install complete message
					if(isset($installed) && $installed = "complete"){
						require_once('parts/install-complete.php');
					}else{
					
						//build the page
						$ccpage = new CC_Page("$_SERVER[REQUEST_URI]","admin");

						//delete cookies and session if logout requested
						if($ccpage->slugarr[1] == "logout"){
							$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "users WHERE username=:username LIMIT 1");
							$stmt->execute(['username' => $ccuser->username]);
							$userinfo = $stmt->fetch();
							$loginhash = sha1($userinfo['username'] . $userinfo['salt'] . $ccuser->loginhash);
							setcookie('username','hi',time()-3600, "/", $_SERVER['HTTP_HOST']);
							setcookie('loginhash','hi',time()-3600, "/", $_SERVER['HTTP_HOST']);
							setcookie('hashtime','hi',time()-3600, "/", $_SERVER['HTTP_HOST']);
							$stmt = $cc->prepare("DELETE FROM cc_" . $tableprefix . "sessions WHERE userid=:userid AND loginhash=:loginhash");
							$stmt->execute(["userid" => $userinfo['id'], "loginhash" => $loginhash]);
							echo '<script>window.location.href="' . $ccurl . '";</script>';
							exit();
						}

						//get navigation selection slug
						$navslug = getSlug(1);

						//create quick links array
						$links = array();

						//include page header
						require_once('includes/header.php');

						//include login or password reset for non-authorized user
						if($navslug == "password-reset"){
							require_once('parts/password-reset.php');
						}else if($ccuser->authlevel < 1){ 
							require_once('parts/login.php');
						}

						//build the sidebar and the top bar if authorized
						else{	
							require_once('includes/sidebar.php');
							require_once('includes/breadcrumbs.php'); ?>
							<section id="rightside">
								<? 
									switch($navslug){
									case "modules":
										require_once('parts/module.php');
										break;
									case "image-library":
										require_once('parts/image-library.php');
										break;
									case "site-options":
										require_once('parts/site-options.php');
										break;
									case "manage-modules":
										require_once('parts/manage-modules.php');
										break;
									case "users":
										require_once('parts/users.php');
										break;
									case "templates":
										require_once('parts/templates.php');
										break;
									case "update-check":
										require_once('parts/update-check.php');
										break;
									case "upgrade":
										require_once('parts/upgrade.php');
										break;
									case "plugins":
										require_once('parts/plugins.php');
										break;
									default:
										require_once('parts/home.php');
										break;
								}	?>	
							</section>
							<? 
						}
					}
				}
			}
		}
	} 

	//include the page footer
	require_once('includes/footer.php'); 
}
ob_end_flush();
?>