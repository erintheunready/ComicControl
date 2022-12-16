<?php

//classes.php - base classes - called in initialize.php

//CC_Site - holds overall site options
class CC_Site{
	
	public $timezone; //site timezone
	public $sitetitle; //overall site title
	public $commentuser; //username for comments service
	public $root; //the root URL without any extra paths
	public $relativepath; //a relative path is the CC install is not at in base directory
	public $ccroot; //the path that CC is at, usually comiccontrol/
	public $dateformat; //format string for dates
	public $timeformat; //format string for times
	public $version; //version number for CC
	public $user_language; //overall language for site
	public $jquery; //URL for jquery inclusion
	public $hammerjs; //URL for hammer.js inclusion
	public $description; //default meta description
	public $updatechecked; //last time checked for updates
	public $newestversion; //newest version
	public $comments; //comment provider
	
	public function __construct(){
		global $cc;
		global $tableprefix;
		
		$this->tableprefix = $tableprefix;
		$this->timezone = self::fetchoption("timezone");
		$this->sitetitle = self::fetchoption("sitetitle");
		$this->commentname = self::fetchoption("commentname");
		$this->root = self::setProtocol(self::fetchoption("root"));
		$this->relativepath = self::fetchoption("relativepath");
		$this->ccroot = self::fetchoption("ccroot");
		$this->dateformat = self::fetchoption("dateformat");
		$this->timeformat = self::fetchoption("timeformat");
		$this->version = self::fetchoption("version");
		$this->language = self::fetchoption("language");
		$this->jquery = self::fetchoption("jquery");
		$this->hammerjs = self::fetchoption("hammerjs");
		$this->description = self::fetchoption("description");
		$this->updatechecked = self::fetchoption("updatechecked");
		$this->newestversion = self::fetchoption("newestversion");
		$this->comments = self::fetchoption("comments");

		if($this->timezone == '') $this->timezone = "America/Chicago";
	}
	private function fetchoption($option){
		global $cc;
		
		$stmt = $cc->prepare("SELECT * FROM cc_" . $this->tableprefix . "options WHERE optionname=:option LIMIT 1");
		$stmt->execute(['option' => $option]);
		$row = $stmt->fetch();
		return $row['optionvalue'];
	}
	private function setProtocol($url){
		$baseurl = substr($url,strpos($url,'/'));
		$newurl = "";

		if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
			$newurl = "https:" . $baseurl;
		}else{
			$newurl = "http:" . $baseurl;
		}
		return $newurl;
	}
	
}

//CC_User - holds onto user information, especially authorization level
class CC_User{
	
	//everything is set to void to start.  If authorization fails at any point, user will remain with base authlevel.
	public $id = 0; //user id
	public $username = ""; //username
	public $loginhash = ""; //the hash contained in user cookie if they're logged in; changes on every page load
	public $authlevel = 0; //authorization level; 0 = no auth, 1 = admin auth
	public $language = "en";
	public $avatar = "";
	
	//construct function checks if user is logged in/will be logged in and sets info if so
	public function __construct(){
		
		global $cc;
		global $tableprefix;
		
		//handle case if user is trying to log in from comiccontrol/login.php
		if(isset($_POST['username']))
		{
			
			//assign inputs
			$password = $_POST['password'];
			$username = $_POST['username'];
			
			//get user info associated with submitted username
			$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "users WHERE username=:username LIMIT 1");
			$stmt->execute(['username' => $username]);
			$userinfo = $stmt->fetch();
			
			if($userinfo['username'] != ""){
				
				//if username is found, check the user's password
				$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "users WHERE username=:username AND password=:password LIMIT 1");
				$stmt->execute(['username' => $username,'password' => md5($password . $userinfo['salt'])]);
				$userinfo = $stmt->fetch();
				
				//if password checks out, log in the user
				if($userinfo['username'] != ""){
					
					$this->loginuser($userinfo);
					
				}
			}
		}
		
		//if the user has a login cookie, check it
		else if(isset($_COOKIE['username']) && isset($_COOKIE['loginhash']) && isset($_COOKIE['hashtime'])){
			
			
			//assign inputs
			$loginhash = $_COOKIE['loginhash'];
			$username = $_COOKIE['username'];
			
			//check to see if the username is in the database
			$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "users WHERE username=:username LIMIT 1");
			$stmt->execute(['username' => $username]);
			$userinfo = $stmt->fetch();
			
			if($userinfo['username'] != ""){
				
				//if user is found, check the loginhash against the database
				$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "sessions WHERE userid=:id AND loginhash=:loginhash LIMIT 1");
				$sessionhash = (sha1($userinfo['username'] . $userinfo['salt'] . $loginhash));
				$stmt->execute(['id' => $userinfo['id'], 'loginhash' => $sessionhash]);
				$sessioninfo = $stmt->fetch();
				
				if($sessioninfo['loginhash'] != ""){
					
					//if info checks out, log them in
					$this->loginuser($userinfo,$loginhash,$sessionhash);
					
				}
			}
		}
	}
	
	//log in user if they're authorized
	private function loginuser($userinfo,$loginhash = 0,$sessionhash = 0){
		
		global $cc;
		global $tableprefix;
		
		//create login if not logged in
		if($loginhash == 0){
		
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
		
		}
		
		//update login if still logged in
		else{
			$stmt = $cc->prepare("UPDATE cc_" . $tableprefix . "sessions SET loginexpire=:expire WHERE userid=:userid AND loginhash=:loginhash LIMIT 1");
			$stmt->execute(['userid' => $userinfo['id'], 'loginhash' => $sessionhash, 'expire' => time() + (432000) ]);
		}
		
		//set the user cookie
		setcookie('loginhash', $loginhash, time() + (432000), "/", $_SERVER['HTTP_HOST']);
		setcookie('username', $userinfo['username'], time() + (432000), "/", $_SERVER['HTTP_HOST']);
		setcookie('hashtime', time(), time() + (432000), "/", $_SERVER['HTTP_HOST']);
		
		//set that info within the object
		$this->id = $userinfo['id'];
		$this->username = $userinfo['username'];
		$this->loginhash = $loginhash;
		$this->authlevel = $userinfo['authlevel'];
		$this->avatar = $userinfo['avatar'];
	}
	public function checkUser(){
		echo $this->id . '<br />' . $this->username . '<br />' . $this->loginhash . '<br />' . $this->authlevel . '<br />' . $this->language;
	}
	public function showAvatar(){
		
		global $ccsite;
		
		$avatar = $this->avatar;
		if($avatar == "") $avatar = "default.png";
		echo '<img src="' . $ccsite->root . $ccsite->ccroot . 'avatars/' . $avatar . '" />';
		
	}
}

//CC_Page - parses URL and holds on to specific page information based on that
class CC_Page{
	
	public $id;
	public $title = ""; //page title
	public $moduletype = ""; //module type; custom if not just one module
	public $template = ""; //template file location (within templates/)
	public $user_language = ""; //language for this page
	public $slug = ""; //slug for the page
	public $subslug = ""; //sub-slug; usually comic page title, archive, or search
	public $searchterm = ""; //search term if a tag is selected
	public $pagenum = ""; //page number for searching and archives
	public $isindex = false; //boolean for whether or not this is the index page
	public $moduleid = 0; //id of module if just one module
	public $module = null; //module object; usually instantiated in initialize.php
	public $galleryloaded = false; //tracks if gallery has been loaded on page so lightgallery doesn't get called twice
	public $slugarr = ""; //array of slugs parsed from URL
	public $description = ""; //meta description for page
	
	//contructor parses the URL and sets all the base class variables
	public function __construct($urlstr,$end = "user"){
		
		global $ccsite;
		global $cc;
		
		//pull apart URL and fill slugarr
		$slug = substr($urlstr,(strlen($ccsite->relativepath)+1));
		if(strpos($slug,'?')) $slug = substr($slug, 0, strpos($slug,'?'));
		$slug = preg_replace('/[^a-zA-Z0-9\-\/%\.\?=\' ]/', '', $slug);
		$this->slugarr = array();
		$this->slugarr = explode("/",$slug);
		foreach($this->slugarr as $key => $slug){
			if($slug == ""){
				unset($this->slugarr[$key]);
			}
		}
		
		//check if it's the index page
		$this->isindex = (count($this->slugarr)==0) ? true : false;
		if($this->slugarr[0] == "index.php") $this->isindex = true;
		if((count($this->slugarr)==1) && $this->slugarr[0] == "") $this->isindex = true;
		
		//fill in variables based on what's in slugarr and who we're showing it to (admin or user)
		if(!$this->isindex){
			if($end == "admin"){
				$this->slug = toSlug($this->slugarr[2]);
				$this->subslug = toSlug($this->slugarr[3]);
			}else{
				$this->slug = toSlug($this->slugarr[0]);
				$this->subslug = toSlug($this->slugarr[1]);
				if($this->subslug == "page") $this->pagenum = (ctype_digit($this->slugarr[2])) ? $this->slugarr[2] : 0;
				if($this->subslug == "search"){ 
					$this->searchterm = $this->slugarr[2];
					$this->pagenum = (ctype_digit($this->slugarr[3])) ? $this->slugarr[3] : 0;
				}
			}
		}
		
		//get page information from the database
		if($this->isindex){
			$stmt = $cc->prepare("SELECT * FROM cc_" . $ccsite->tableprefix . "options WHERE optionname='homepage' LIMIT 1");
			$stmt->execute();
			$mainpage = $stmt->fetch();
			$stmt = $cc->prepare("SELECT * FROM cc_" . $ccsite->tableprefix . "modules WHERE id=:id LIMIT 1");
			$stmt->execute(['id' => $mainpage['optionvalue']]);
			$page = $stmt->fetch();
		}else{
			$stmt = $cc->prepare("SELECT * FROM cc_" . $ccsite->tableprefix . "modules WHERE slug=:slug LIMIT 1");
			$stmt->execute(['slug' => $this->slug]);
			$page = $stmt->fetch();
		}
		
		//if the page wasn't found, just get the main page
		if($page['title'] == ""){
			$stmt = $cc->prepare("SELECT * FROM cc_" . $ccsite->tableprefix . "options WHERE optionname='homepage' LIMIT 1");
			$stmt->execute();
			$mainpage = $stmt->fetch();
			$stmt = $cc->prepare("SELECT * FROM cc_" . $ccsite->tableprefix . "modules WHERE id=:id LIMIT 1");
			$stmt->execute(['id' => $mainpage['optionvalue']]);
			$page = $stmt->fetch();
		}
		
		//set variables based on info pulled from the database
		$this->id = $page['id'];
		$this->title = $page['title'];
		$this->moduletype = $page['moduletype'];
		$this->language = $page['language'];
		$this->template = $page['template'];
		$this->description = $page['description'];
		if($this->moduletype != "custom") $this->module = $this->buildModule($page['slug']);
	}
	
	//debug function to check if the right info got pulled...
	public function checkvars(){
		
		print_r($this->slugarr);
		echo '<br />';
		echo $this->title . '<br />';
		echo $this->moduletype . '<br />';
		echo $this->template . '<br />';
		echo $this->language . '<br />';
		echo $this->slug . '<br />';
		echo $this->subslug . '<br />';
		echo $this->searchterm . '<br />';
		echo $this->pagenum . '<br />';
		echo ($this->isindex) ? 'true' : 'false' . '<br />';
	}
	
	//build module if one specific module is set; not called if custom page
	public function buildModule($slug){
		
		global $cc;
		global $tableprefix;
		
		//get the module from the database
		$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE slug=:slug LIMIT 1";
		$stmt = $cc->prepare($query);
		$stmt->execute(['slug' => $slug]);
		$moduleinfo = $stmt->fetch();
		
		//if the module was found, build the module
		if($moduleinfo['id'] != ""){
			
			$module = null;
			switch($moduleinfo['moduletype']){
				
				case "text":
					$module = new CC_Text($moduleinfo);
					break;
				case "comic":
					$module = new CC_Comic($moduleinfo);
					break;
				case "blog":
					$module = new CC_Blog($moduleinfo,$this->subslug);
					break;
				case "gallery":
					$module = new CC_Gallery($moduleinfo);
					break;
					
			}
			return $module;
			
		}
		
		//if the module wasn't found, return null
		else{
			return null;
		}
		
	}
	
	//display the title for the page (for the <title> tag mainly)
	public function displayTitle(){
		
		global $user_lang;
		global $ccsite;
		
		//keeping track of hyphens.  You know how it is
		$hyphen = true;
		
		//comics often have the same name as the site, so we don't want to repeat it
		if($this->module->name != $ccsite->sitetitle){
			echo $this->module->name;
			$hyphen = false;
		}
		
		//if there's extra info, add that to the title
		if($this->moduletype == "comic" || ($this->moduletype == "blog" && $this->subslug != "")){
			
			if(!$hyphen) echo ' - ';
			
			if($this->subslug == "archive"){
				echo $user_lang['Archive'];
			}
			
			else if($this->subslug == "search"){
				echo $user_lang['Search'] . ' - ' . urldecode($this->searchterm);
			}
			
			else if($this->subslug == "filter"){
				echo urldecode($this->searchterm);
			}
			
			else if($this->subslug == "page"){
				echo str_replace('%n', $this->pagenum, $user_lang['Page %n']);
			}
			
			//if slug is assigned, get comic or blog title
			else{
				if($this -> isindex){
					$post = $this->module->getSeq("last");
				}else{
					$post = $this->module->getPost($this->subslug);
				}
				echo $post['title'];
			}
		}
	}
	public function displayMeta(){
		
		global $ccsite;
		$description = $this->description;
		
		if($description == "") $description = $ccsite->description;
		echo '<meta name="description" content="' . str_replace('"','&quot;',$description) . '" />';
		echo '<meta name="twitter:title" content="' . str_replace('"','&quot;',$ccsite->sitetitle) . '" />';
		echo '<meta name="twitter:description" content="' . str_replace('"','&quot;',$description) . '" />';

	}
	
}

//CC_Module - a basic class for real modules to be built on.  Not meant for use by itself.
class CC_Module{
	
	//a function to fetch the options for the given module
	public function getOptions($moduleinfo){
		
		global $cc;
		global $tableprefix;
		
		$options = array();
		
		//get all the options from the database associated with the module
		$query = "SELECT * FROM cc_" . $tableprefix . "modules_options WHERE moduleid=:moduleid";
		$stmt = $cc->prepare($query);
		$stmt->execute(['moduleid' => $moduleinfo['id']]);
		$moduleoptions = $stmt->fetchAll();
		if(is_array($moduleoptions)){
			foreach($moduleoptions as $optionarr){
				$options[$optionarr['optionname']] = $optionarr['value'];
			}
		}

		return $options;
		
	}
	
	//function to build navigation text at the bottom of search/archive pages
	public function getPageNav($numpages){
		
		global $ccsite;
		global $ccpage;
		global $user_lang;
		
		$page = $ccpage->pagenum;
		
		//if there's no page set, make it page 1
		if($page == "" || $page == 0) $page = 1;
		
		//building part of the URL that will be in the buttons; differs depending on the page that it's in
		$pagedir = "page";
		if($ccpage->subslug == "search"){
			$pagedir = "search/" . $ccpage->searchterm;
		}
		
		//output previous/next buttons for flipping through pages
		echo '<div class="cc-prevnext">';
		if($page > 1){
			echo '<a href="' . $ccsite->root . $ccsite->relativeroot . $this->slug . '/' . $pagedir . '/' . ($page-1) . '">' . $user_lang['navprev'] . '</a>';
		}
		if($page < $numpages){
			echo '<a href="' . $ccsite->root . $ccsite->relativeroot . $this->slug . '/' . $pagedir . '/' . ($page+1) . '">' . $user_lang['navnext'] . '</a>';
		}
		echo '</div>';
		
		//output page number string for jumping through pages
		$ellipsis1 = false;
		$ellipsis2 = false;
		echo '<div class="cc-pagelist">' . $user_lang["Page"] . ' ';
		for($i=1; $i<=$numpages; $i++){
			if($i < $page-4 && $ellipsis1 == false){
				echo '<a href="' . $ccsite->root . $this->slug . '/' . $pagedir . '/1">1 ...</a> ';
				$ellipsis1 = true;
			}
			if($page != $i && ($i >= $page-4 && $i < $page+4)){
				echo '<a href="' . $ccsite->root . $this->slug . '/' . $pagedir .'/' . $i . '">' . $i . '</a> ';
			}
			if($page == $i){
				echo $i . ' ';
			}
			if($i >= $page+4 && $ellipsis2 == false){
				echo '<a href="' . $ccsite->root . $this->slug . '/' . $pagedir . '/' . $numpages . '">... ' . $numpages . '</a>';
				$ellipsis2 = true;
			}
		}
		echo '</div>';
	}
	
}

//CC_Text - basic text module. Basically holds just a title and a content area.
class CC_Text extends CC_Module{
	
	public $id; //module id
	public $name; //module name
	public $type = "text"; //module type
	public $slug = "slug"; //module slug
	
	public $content = ""; //text content
	public $options; //module options
	
	//constructor gets any relevant options and the title and content.
	public function __construct($moduleinfo){
		
		global $cc;
		global $tableprefix;
		
		//set basic variables from given info
		$this->id = $moduleinfo['id'];
		$this->name = $moduleinfo['title'];
		$this->slug = $moduleinfo['slug'];
		
		//get text content and title from database
		$stmt = $cc->prepare("SELECT * FROM cc_" . $tableprefix . "text WHERE id=:id LIMIT 1");
		$stmt->execute(['id' => $moduleinfo['id']]);
		$text = $stmt->fetch();
		if(is_array($text)){
			$this->content = $text['content'];
		}
		
		//get the options
		$this->options = $this->getOptions($moduleinfo);
		
	}
	
	//display the text.
	public function display(){
		if($this->options['showTitle'] == "on") echo '<h1 class="cc-title">' . $this->name . '</h1>';
		echo $this->content;
	}
	
}

//CC_Comic - contains all the information for a given comic and also functions for displaying and navigating through a comic
class CC_Comic extends CC_Module{
	
	public $id; //module id
	public $name; //comic name
	public $type = "comic"; //module type
	public $options = array(); //comic options array
	
	//constructor sets basic comic info
	public function __construct($moduleinfo){
		
		global $cc;
		global $tableprefix;
		
		$this->id = $moduleinfo['id'];
		$this->name = $moduleinfo['title'];		
		$this->slug = $moduleinfo['slug'];		
		$this->options = $this->getOptions($moduleinfo);
		
		
	}
	
	public function display(){
		
		global $ccsite;
		global $ccpage;
		global $user_lang;
		
		//get the current comic
		$comic = $this->getComic();
		
		echo '<div id="cc-comicbody">';
		
		if($comic['title'] != ""){
		
			//handle displaying swf comics
			if($comic['mime'] == "application/x-shockwave-flash"){
				echo '<div id="cc-comic" style="height:' . $comic['height'] . 'px; width:' . $comic['width'] . 'px; display:inline-block;">';
				echo '<embed height="' . $comic['height'] . '" width="' . $comic['width'] . '" src="' . $ccsite->root . 'comics/' . $comic['imgname'] . '" />';
				echo '</div>';
			}
			
			//handle case for everything else and display comic
			else{
				$tagadd = "";
				if($ccpage->slugarr[2] == "read-tag") $tagadd = "/read-tag/" . $ccpage->slugarr[3];
				
				//link to the next page if available
				if($comic != $this->getSeq("last") && $comic['altnext'] == ""){
					$nextcomic = $this->getSeq("next");
					echo '<a href="' . $ccsite->root . $this->slug . '/' . $nextcomic['slug'] . $tagadd . '">';
				}
				
				//if there's an alternative link given, link to that
				else if($comic['altnext'] != ""){
					echo '<a href="' . $comic['altnext'] . '">';
				}
				
				//output the comic image and close the link
				$hovertext = $comic['hovertext'];
				if($hovertext == "") $hovertext = $comic['title'];
				echo '<img title="' . str_replace('"','&quot;',$hovertext) . '" src="' . $ccsite->root . 'comics/' . $comic['imgname'] . '" id="cc-comic" />';
				if($comic != $this->getSeq("last") || $comic['altnext'] != ""){
					echo '</a>';
				}
				
				if($this->options['contentwarnings'] == "on" && trim($comic['contentwarning']) != ""){
					echo '<script>var contentwarningtext = "' . str_replace('"','&quot;',$comic['contentwarning']) . $user_lang['<br />Click to view this page'] . '";</script>';
					?>
					<script>
					$('#cc-comicbody img').addClass('cc-blur');
					$('#cc-comicbody').append('<div class="cc-contentwarning">' + contentwarningtext + '</div>');
					$('#cc-comicbody a').click(function(event){
						if($('#cc-comicbody img').hasClass('cc-blur')){
							event.preventDefault();
							$('#cc-comicbody img').removeClass('cc-blur');
							$('#cc-comicbody .cc-contentwarning').remove();
						}
					});
					$('.cc-contentwarning').click(function(){
							$('#cc-comicbody img').removeClass('cc-blur');
							$('#cc-comicbody .cc-contentwarning').remove();
					});
					</script>
					<?php
				}
				
				//check if fullscreen will be displayed
				$isfullscreen = false;
				if($this->options['clickaction'] == "fullscreen") $isfullscreen = true;
				if($this->options['clickaction'] == "fullscreenbig" && $comic['width'] > $this->options['comicwidth']) $isfullscreen = true;
				
				//if option is set to display full size when comic is clicked, put comic in lightbox context
				if($isfullscreen){
					?>
						<script>
						$('document').ready(function(){
							var htmlheight = $('html').css('height');
							var bodyheight = $('body').css('height');
							
							$('body').append('<div class="cc-fullscreen-overlay"><img src="<?=$ccsite->root . "comicshighres/" . $comic['comichighres']?>" /></div>');
								
							$('#cc-comicbody').on('click',function(e){
								e.preventDefault();
								$('body, html').css({'height':'100%','overflow':'hidden'});
								$('.cc-fullscreen-overlay').fadeIn();
							});
							$('.cc-fullscreen-overlay').on('click',function(){
								$('.cc-fullscreen-overlay').fadeOut(function(){
									$('body').css({'height':bodyheight});
									$('html').css({'height':htmlheight});
									$('body, html').css({'overflow':'auto'});
								});
							});
						});
						</script>
							
					<?php
				}
			
				//display hovertext div for mobile if that option is set
				if($this->options['touchaction'] == "hovertext" && trim($comic['hovertext']) != "" && !$isfullscreen && $comic['altnext'] == ""){
					?>
					<script>
						var touchOn = document.getElementById("cc-comic");
						delete Hammer.defaults.cssProps.userSelect;
						delete Hammer.defaults.cssProps.userDrag;
						delete Hammer.defaults.cssProps.contentZooming;
						var comicTouchOn = new Hammer(touchOn,{
  inputClass: Hammer.SUPPORT_POINTER_EVENTS ? Hammer.PointerEventInput : Hammer.TouchInput, touchAction : 'auto'
});
						
						if ('ontouchstart' in window) {
							$("#cc-comicbody a").click(function (e) {
								e.preventDefault();
							});
							comicTouchOn.on("tap", function(){
								$('body').append('<div class="cc-fullscreen-overlay"><div class="cc-fullscreen-center"><div id="cc-hoverdiv"><?=str_replace("'","\'",$comic['hovertext'])?></div></div></div>');
								$(".cc-fullscreen-overlay").fadeIn('fast');
								var overlay = document.getElementsByClassName("cc-fullscreen-overlay");
								var touchOff = overlay[0];
								var comicTouchOff = new Hammer(touchOff,{
  inputClass: Hammer.SUPPORT_POINTER_EVENTS ? Hammer.PointerEventInput : Hammer.TouchInput, touchAction : 'auto'
});
								comicTouchOff.on("tap", function(){
									$(".cc-fullscreen-overlay").fadeOut('fast',function(){
										$(this).remove();
									});
								});
							});
						}
					</script>
					<?php
				}
					
			}

			//insert arrow key navigation if included

			if($this->options['arrowkey'] == "on"){

				//check to add tag reading portion of URL
				$tagadd = "";
				if($ccpage->slugarr[2] == "read-tag"){
					$tagadd = "/read-tag/" . $ccpage->slugarr[3];
				}

				//get the first and last comic
				$firstcomic = $this->getSeq("first");
				$lastcomic = $this->getSeq("last");

				if($firstcomic['id'] == $comic['id']){
					$prevslug = $comic['slug'];
				}else{
					$prevslug = $this->getSeq("prev")['slug'];
				}

				if($lastcomic['id'] == $comic['id']){
					$nextslug = $comic['slug'];
				}else{
					$nextslug = $this->getSeq("next")['slug'];
				}

				
				?>
				<script>
				function leftArrowPressed() {
					var prev = "<?=$ccsite->root . $this->slug . '/' . $prevslug . $tagadd?>";
					window.location = prev;
				}

				function rightArrowPressed() {
					var next = "<?=$ccsite->root . $this->slug . '/' . $nextslug . $tagadd?>";
					window.location = next;
				}

				document.onkeydown = function(evt) {
					evt = evt || window.event;
					switch (evt.keyCode) {
						case 37:
							leftArrowPressed();
							break;
						case 39:
							rightArrowPressed();
							break;
					}
				};
				</script>
				<?php
			}
				
		}
		//if current comic wasn't found, deliver error message
		else{
			echo '<div class="cc-errormsg">' . $user_lang['There is no comic with this ID.'] . '</div>';	
		}
		
		echo '</div>';
		
	}
	
	//get specific comic based on a slug
	public function getPost($slug){
		
		global $cc;
		global $tableprefix;
		global $ccuser;
		
		$comic = array();
		
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE slug=:slug AND comic=:comicid";
		if($ccuser->authlevel == 0){
			$query .= ' AND publishtime < ' . time();
		}
		$query .= " LIMIT 1";
		$stmt = $cc->prepare($query);
		$stmt->execute(['slug' => $slug, 'comicid' => $this->id]);
		$comic = $stmt->fetch();
		
		return $comic;
		
	}
	
	//get specific comic based on navigation direction
	public function getSeq($dir){
		
		global $cc;
		global $tableprefix;
		global $ccuser;
		global $ccpage;  
		
		$comic = "";
		$queryadd = "";
		
		//extra string to add to check authlevel
		if($ccuser->authlevel == 0){
			$queryadd = " AND publishtime < " . time();
		}

		if($ccpage->slugarr[2] == "read-tag"){

			//get all entries of comics with that tag
			$query = "SELECT comicid FROM cc_" . $tableprefix . "comics_tags WHERE comic=:comic AND tag=:tag";
			if($ccuser->authlevel == 0) $query .= " AND publishtime < " . time();
			$query .= " ORDER BY publishtime ASC";
			$stmt = $cc->prepare($query);
			$stmt->execute(['comic' => $this->id, 'tag' => urldecode($ccpage->slugarr[3])]);
			$taggedposts = $stmt->fetchAll();
			$allposts = array();
			
			switch($dir){
				case "first":	
					$query = "SELECT comicid FROM cc_" . $tableprefix . "comics_tags WHERE comic=:comic AND tag=:tag" . $queryadd . " ORDER BY publishtime ASC LIMIT 1";
					$stmt = $cc->prepare($query);
					$stmt->execute(['comic' => $this->id, 'tag' => urldecode($ccpage->slugarr[3])]);
					break;
				case "prev":
					$currentcomic = $this->getComic();
					$query = "SELECT comicid FROM cc_" . $tableprefix . "comics_tags WHERE comic=:comic AND tag=:tag AND publishtime < " . $currentcomic['publishtime'] . $queryadd . " ORDER BY publishtime DESC LIMIT 1";
					$stmt = $cc->prepare($query);
					$stmt->execute(['comic' => $this->id, 'tag' => urldecode($ccpage->slugarr[3])]);
					break;
				case "next":
					$currentcomic = $this->getComic();
					$query = "SELECT comicid FROM cc_" . $tableprefix . "comics_tags WHERE comic=:comic AND tag=:tag AND publishtime > " . $currentcomic['publishtime'] . $queryadd . " ORDER BY publishtime ASC LIMIT 1";
					$stmt = $cc->prepare($query);
					$stmt->execute(['comic' => $this->id, 'tag' => urldecode($ccpage->slugarr[3])]);
					break;
				case "last":
					$query = "SELECT comicid FROM cc_" . $tableprefix . "comics_tags WHERE comic=:comic AND tag=:tag" . $queryadd . " ORDER BY publishtime DESC LIMIT 1";
					$stmt = $cc->prepare($query);
					$stmt->execute(['comic' => $this->id, 'tag' => urldecode($ccpage->slugarr[3])]);
					break;
			}
			$comic = $stmt->fetch();
			
			//create array of comics based on those results
			$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE id=:id";
			$stmt = $cc->prepare($query);
			$stmt->execute(['id' => $comic['comicid']]);

		}
		
		else{
			//get comic relative to current comic based on given direction
			switch($dir){
				case "first":	
					$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic=:comicid" . $queryadd . " ORDER BY publishtime ASC LIMIT 1";
					$stmt = $cc->prepare($query);
					$stmt->execute(['comicid' => $this->id]);
					break;
				case "prev":
					$currentcomic = $this->getComic();
					$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic=:comicid AND publishtime < " . $currentcomic['publishtime'] . $queryadd . " ORDER BY publishtime DESC LIMIT 1";
					$stmt = $cc->prepare($query);
					$stmt->execute(['comicid' => $this->id]);
					break;
				case "next":
					$currentcomic = $this->getComic();
					$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic=:comicid AND publishtime > " . $currentcomic['publishtime'] . $queryadd . " ORDER BY publishtime ASC LIMIT 1";
					$stmt = $cc->prepare($query);
					$stmt->execute(['comicid' => $this->id]);
					break;
				case "last":
					$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic=:comicid" . $queryadd . " ORDER BY publishtime DESC LIMIT 1";
					$stmt = $cc->prepare($query);
					$stmt->execute(['comicid' => $this->id]);
					break;
			}
		}
		$comic = $stmt->fetch();
		
		return $comic;
		
	}
	
	//get current comic (latest if index, based on slug if not)
	public function getComic(){
		
		global $ccpage;
		
		if($ccpage->subslug == ""){
			$comic = $this->getSeq("last");
		}else{
			$comic = $this->getPost($ccpage->subslug);
		}
		
		return $comic;
		
	}
	
	//display the navigation
	public function navDisplay($tag = ""){
		
		global $cc;
		global $tableprefix;
		global $ccuser;
		global $ccpage;
		global $ccsite;
		
		//determine what buttons are shown based on navorder option
		$navarray = explode("|",$this->options['navorder']);
		
		//get the current comic
		$currentcomic = $this->getComic();
		$tagadd = "";
		if($ccpage->slugarr[2] == "read-tag"){
			$tagadd = "/read-tag/" . $ccpage->slugarr[3];
		}
		
		if($currentcomic['title'] != ""){
		
			//get the first and last comic
			$firstcomic = $this->getSeq("first");
			$lastcomic = $this->getSeq("last");
			
			//if this the first comic, then the first and previous buttons will be disabled
			if($firstcomic == $currentcomic){
				$firstbutton = '<div class="cc-first-dis">' . $this->options['firsttext'] . '</div>';
				$prevbutton = '<div class="cc-prev-dis">' . $this->options['prevtext'] . '</div>';
			}
			//if it's not, generate the first and previous comic buttons
			else{
				$firstbutton = '<a class="cc-first" rel="first" href="' . $ccsite->root . $this->slug . '/' . $firstcomic['slug'] . $tagadd . '">' . $this->options['firsttext'] . '</a>';
			
				$prevcomic = $this->getSeq("prev");
				$prevbutton = '<a class="cc-prev" rel="prev" href="' . $ccsite->root . $this->slug . '/' . $prevcomic['slug'] . $tagadd . '">' . $this->options['prevtext'] . '</a>';
			}
			
			//if this the last comic, then the last and next buttons will be disabled
			if($lastcomic == $currentcomic){
				$nextbutton = '<div class="cc-next-dis">' . $this->options['nexttext'] . '</div>';
				$lastbutton = '<div class="cc-last-dis">' . $this->options['lasttext'] . '</div>';
			}
			//if it's not, generate the last and next comic buttons
			else{
				$lastbutton = '<a class="cc-last" rel="last" href="' . $ccsite->root . $this->slug . '/' . $lastcomic['slug'] . $tagadd . '">' . $this->options['lasttext'] . '</a>';
			
				$nextcomic = $this->getSeq("next");
				$nextbutton = '<a class="cc-next" rel="next" href="' . $ccsite->root . $this->slug . '/' . $nextcomic['slug'] . $tagadd . '">' . $this->options['nexttext'] . '</a>';
			}
			
			//generate auxiliary button
			$auxbutton = '<a class="cc-navaux" href="' . $ccsite->root . $this->options['navaux'] . '">' . $this->options['auxtext'] . '</a>';
			
			$buttonstring = "";
			
			//fill out nav based on navorder
			foreach($navarray as $button){
				switch($button){
					case "first":
						$buttonstring .= $firstbutton;
						break;
					case "prev":
						$buttonstring .= $prevbutton;
						break;
					case "aux":
						$buttonstring .= $auxbutton;
						break;
					case "next":
						$buttonstring .= $nextbutton;
						break;
					case "last":
						$buttonstring .= $lastbutton;
						break;
				}
			}
				
		}
		//if the comic wasn't found, deliver an empty nav
		else{
			
			$buttonstring = "";
			
		}
		
		//output the nav
		echo '<nav class="cc-nav" role="navigation">' . $buttonstring . '</nav>';
		
	}
	
	//COMIC TEXT FUNCTIONS
	//these functions below all have to do with the text that can be displayed with the comic, namely the news post and comments along with other tidbits.
	
	//disply everything based on the user's options
	public function displayAll(){
		
		$this->displayNews();
		if($this->options['displaytranscript'] == "on") $this->displayTranscript();
		if($this->options['displaytags'] == "on") $this->displayTags();
		if($this->options['displaycomments'] == "on") $this->displayComments();
		
	}
	
	//display just the news
	public function displayNews(){
		
		global $cc;
		global $ccuser;
		global $ccpage;
		global $ccsite;
		global $tableprefix;
		global $user_lang;
		
		//get current comic row
		$news = $this->getComic();
		
		if($news['title'] != ""){
		
			//if they only want the latest relevant news, go back and get most recent filled out news post
			if($this->options['newsmode'] == "latestnews"){
				$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE trim(newscontent)!='' AND publishtime<=:publishtime";
				$query .= " ORDER BY publishtime DESC LIMIT 1";
				$stmt = $cc->prepare($query);
				$stmt->execute(['publishtime' => $news['publishtime']]);
				$news = $stmt->fetch();
			}
			
			//output news post based on whatever news is selected
			echo '<div class="cc-newsarea">';
			echo '<div class="cc-newsheader">';
			if($ccpage->isindex) echo '<a href="' . $ccsite->root . $ccplug->slug . '/' . $news['slug'] . '">';
			echo $news['newstitle'];
			if($ccpage->isindex) echo '</a>';
			echo '</div>';
			echo '<div class="cc-publishtime">' . str_replace('%t',date($ccsite->timeformat,$news['publishtime']),str_replace('%d',date($ccsite->dateformat,$news['publishtime']),$user_lang['Posted %d at %t'])) . '</div>';
			echo '<div class="cc-newsbody">';
			echo $news['newscontent'] . '';
			echo '</div></div>';
			
		}
		
		//if the comic wasn't found, deliver an error message
		else{
			echo '<div class="cc-errormsg">' . $user_lang['There is no news post with this ID.'] . '</div>';
		}
		
	}
	
	//display the comic's tags
	public function displayTags(){
		
		global $cc;
		global $ccuser;
		global $ccpage;
		global $ccsite;
		global $tableprefix;
		global $user_lang;
		
		//get the current comic
		$comic = $this->getComic();
		
		if($comic['title'] != ""){
			
			//find all the associated tags in the database
			$query = "SELECT DISTINCT tag FROM cc_" . $tableprefix . "comics_tags WHERE comicid=:comicid";
			if($ccuser->authlevel == 0) $query .= " AND publishtime <= " . time();
			$stmt = $cc->prepare($query);
			$stmt->execute(['comicid' => $comic['id']]);
			$tags = $stmt->fetchAll();
			$divided = false;
			
			//display those tags in a row
			if(count($tags) > 0){
				echo '<div class="cc-tagline">' . $user_lang['Tags: '];
				foreach($tags as $tag){
					if($divided) echo ", ";
					$divided = true;
					echo '<a href="' . $ccsite->root . $this->slug . "/search/" . $tag['tag'] . '">' . $tag['tag'] . '</a>';
				}
				echo '</div>';
			}
			
		}
		
	}
	
	//display the comic transcript
	public function displayTranscript(){
		
		global $user_lang;
		
		//get current comic
		$comic = $this->getComic();
		
		//only display any transcript info if there's a transcript actually inputted
		if($comic['transcript'] != ""){
			
			//put the transcript behind a button if the user wants that
			if($this->options['transcriptclick'] == "on") echo '<div id="cc-transcriptclick"><button type="button" id="cc-transcriptbutton">' . $user_lang['Click to view comic transcript'] . '</button>';
			echo '<div class="cc-transcript"';
			if($this->options['transcriptclick'] == "on") echo ' id="cc-transcripttogglediv" style="display:none"';
			echo '>' . $comic['transcript'] . '</div>';
			if($this->options['transcriptclick'] == "on") echo '</div>';
			if($this->options['transcriptclick']){
				?>
				<script>
					$("#cc-transcriptbutton").on("click", function(){
						$("#cc-transcripttogglediv").slideToggle();
					});
				</script>
				<?php
			}
		}
	}
	
	//display the comments
	public function displayComments(){
		
		global $ccpage;
		global $ccsite;
		global $user_lang;
		
		//get the current comic
		$comic = $this->getComic();
		
		if($comic['title'] != ""){
			
			//if it's the index, don't display the comments, just display the comments link
			if($ccpage->isindex){
				switch($ccsite->comments){
					case "commento":
						echo '<script src="https://cdn.commento.io/js/count.js"></script>';
						echo '<div class="cc-commentlink"><a href="' . $ccsite->root . $this->slug . '/' . $comic['slug'] . '#commento">' . $user_lang['View/Post Comments'] . '</a></div>';
					break;
					case "disqus":
						echo '<div class="cc-commentlink"><a href="' . $ccsite->root . $this->slug . '/' . $comic['slug'] . '#disqus_thread" data-disqus-identifier="' . $comic['commentid'] . '">' . $user_lang['View/Post Comments'] . '</a></div>';
						echo '<script id="dsq-count-scr" src="//' . $ccsite->commentname . '.disqus.com/count.js" async></script>';
						break;


				}
			}
			
			//if it's not the index, display the comment thread
			else{
				echo '<div class="cc-commentheader">' . $user_lang['Comments'] . '</div><div class="cc-commentbody">';
				switch($ccsite->comments){
					case "commento":
						?><div id="commento"></div>
						<script src="https://cdn.commento.io/js/commento.js"></script><?php
						break;
					case "disqus":
						?>
						<div id="disqus_thread"></div>
						<script>
							var disqus_config = function () {
								this.page.url = '<?=$ccsite->root . $this->slug?>/<?=$comic['slug']?>';
								this.page.identifier = '<?=$comic['commentid']?>';
							};
							(function() {  
								var d = document, s = d.createElement('script');
								
								s.src = '//<?=$ccsite->commentname?>.disqus.com/embed.js';  
								
								s.setAttribute('data-timestamp', +new Date());
								(d.head || d.body).appendChild(s);
							})();
						</script>
						<noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript" rel="nofollow">comments powered by Disqus.</a></noscript>
						<?php
					break;
				}
				
				echo '</div>';
			}
		}
		
	}
	
	//display a dropdown list of pages for the archive
	public function displayDropdown(){
		
		global $cc;
		global $tableprefix;
		global $ccuser;
		global $ccsite;
		global $ccpage;
		global $user_lang;
		
		//get a list of comics from the database
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic=:comicid";
		if($ccuser->authlevel == 0) $query .= " AND publishtime < " . time();
		$query .= " ORDER BY publishtime ASC";
		$stmt = $cc->prepare($query);
		$stmt->execute(['comicid' => $this->id]);
		$comiclist = $stmt->fetchAll();
		
		//display the dropdown list along with a function to automatically flip pages on select
		?>
		<script>
		function changePage(slug){
			window.location.href='<?=$ccsite->root?>'+slug;
		}
		</script>
		<select name="comic" onChange="changePage(this.value)"><option value=""><?=$user_lang['Select a comic...']?></option>
		<?php
		foreach($comiclist as $comic){
			echo '<option value="' . $this->slug . '/' . $comic['slug'] . '">' . date($ccsite->dateformat,$comic['publishtime']) . ' - ' . $comic['title'] . '</option>';
		}
		?>
		</select>
		<?php
	}
	
	//display a list of chapters in the archive
	public function displayChapters($dropdown = false,$current = false, $parent = false){
		global $cc;
		global $tableprefix;
		global $lang;
		
		$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE parent=0 AND comic=:comic ORDER BY sorder ASC";
		$stmt = $cc->prepare($query);
		$stmt->execute(['comic' => $this->id]);
		$thislevel = $stmt->fetchAll();
		
		
		//start recursing through the levels of chapters
		if($dropdown) echo '<select name="comic-storyline">';
		
		//give top level option if looking to assign parent storyline
		if($parent) echo '<option value="0">' . $lang['No parent storyline'] . '</option>';
		
		if(is_array($thislevel)) $this->recurseChapters($thislevel,$dropdown,$current);
		if(!$dropdown) echo '<div style="clear:both;"></div>';
		else echo '</select>';
	}
	
	//recursive function to traverse the chapter tree
	private function recurseChapters($tree,$dropdown,$current){
		global $cc;
		global $tableprefix;
		global $ccuser;
		global $ccsite;
		global $ccpage;
		
		//recursive magic
		foreach($tree as $arr){
			
			if(!$dropdown) echo '<div class="cc-storyline-contain" style="margin-left:' . ($arr['level'] * 50) . 'px">';
			
			//get the pages in this storyline
			$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE storyline=:storyline AND comic=:comic";
			if($ccuser->authlevel == 0) $query .= " AND publishtime < " . time();
			$query .= " ORDER BY publishtime ASC";
			$stmt = $cc->prepare($query);
			$stmt->execute(['storyline' => $arr['id'],'comic' => $this->id]);
			$firstpage = $stmt->fetch();
			
			//get the children of this storyline
			$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE parent=:parent AND comic=:comic ORDER BY sorder ASC";
			$stmtchildren = $cc->prepare($query);
			$stmtchildren->execute(['parent' => $arr['id'], 'comic' => $this->id]);
			$children = $stmtchildren->fetchAll();
			
			if(!($dropdown)){
				//anchor for some sites to click quickly to chapters
				echo '<a name="' . $arr['id'] . '"></a>';
				if(!empty($firstpage)){
					
					//display the chapter thumbnail if options are set just so
					if($this->options['pagethumbs'] == "off" && $this->options['chapterthumbs'] == "on"){
						$thumbnail = $firstpage['comicthumb'];
						if($arr['thumbnail'] != "") $thumbnail = $arr['thumbnail'];
						echo '<div class="cc-storyline-thumb"><a href="' . $ccsite->root . $this->slug . '/' . $firstpage['slug'] . '"><img src="' . $ccsite->root . 'comicsthumbs/' . $thumbnail . '" /></a></div>';
					}
				
					//display the chapter name
					$stmt->execute(['storyline' => $arr['id'],'comic' => $this->id]);
					$pages = $stmt->fetchAll();
					echo '<div class="cc-storyline-text"><div class="cc-storyline-header"><a href="' . $ccsite->root . $this->slug . '/' . $firstpage['slug'] . '">' . 
					$arr['name'] . '</a></div>';
					if($arr['caption'] != "") echo '<div class="cc-storyline-caption">' . $arr['caption'] . '</div>';
					if($ccpage->module->options['pagetitles'] == "on" && $ccpage->module->options['pagethumbs'] == "off"){
						echo '<div class="cc-storyline-pagetitles">';
						foreach($pages as $page){
							echo '<div class="cc-pagerow"><a href="' . $ccsite->root . $this->slug . '/' . $page['slug'] . '">' . $page['title']  . '</a></div>';
						}
						echo '</div>';
					}
					echo '</div>';
					
					//display page thumbnails if option set
					if($this->options['pagethumbs'] == "on"){
						echo '<div class="cc-storyline-thumbwrapper">';
						foreach($pages as $page){
							echo '<div class="cc-storyline-pagethumb"><a href="' . $ccsite->root . $this->slug . '/' . $page['slug'] . '"><img src="' . $ccsite->root . 'comicsthumbs/' . $page['comicthumb'] . '" />';
							if($this->options['pagetitles'] == "on") echo '<br /><br />' . $page['title'];
							echo '</a></div>';
						}
						echo '</div>';
						
					}
				}else if(!empty($children)){
				
					//display the chapter name without a link if there are no pages
					echo '<div class="cc-storyline-header">' . $arr['name'] . '</div>';
				}
				echo '</div>';
					
			}
			else{ 
				echo '<option value="' . $arr['id'] . '"';
				echo $current;
				if($current == $arr['id']) echo ' SELECTED';
				echo '>';
				for($i = 0; $i < $arr['level']; $i++) echo '&nbsp;&nbsp;';
				echo $arr['name'] . '</option>';
			}
			
			
			//go further down the rabbit hole
			$this->recurseChapters($children,$dropdown,$current);
		}
		
	}
	
	//search for comics with a given tag
	public function search(){
		
		global $cc;
		global $ccpage;
		global $ccsite;
		global $tableprefix;
		global $ccuser;
		global $user_lang;
		
		//set page number
		$page = $ccpage->pagenum;
		if($page < 1) $page = 1;
		
		//set the minimum post number for this page
		$lowerlimit = ($page - 1) * $this->options['perpage'];
		if($lowerlimit < 0) $lowerlimit = 0;
		
		//get all entries of comics with that tag
		$query = "SELECT comicid FROM cc_" . $tableprefix . "comics_tags WHERE comic=:comic AND tag=:tag";
		if($ccuser->authlevel == 0) $query .= " AND publishtime < " . time();
		$query .= " ORDER BY publishtime ASC";
		$stmt = $cc->prepare($query);
		$stmt->execute(['comic' => $this->id, 'tag' => urldecode($ccpage->searchterm)]);
		$taggedposts = $stmt->fetchAll();
		$allposts = array();
		
		//create array of comics based on those results
		foreach($taggedposts as $post){
			$query = "SELECT slug,title,comicthumb FROM cc_" . $tableprefix . "comics WHERE id=:comicid";
			if($ccuser->authlevel == 0) $query .= " AND publishtime < " . time();
			$query .= " LIMIT 1";
			$stmt = $cc->prepare($query);
			$stmt->execute(['comicid' => $post['comicid']]);
			array_push($allposts,$stmt->fetch());
		}
		$numposts = count($allposts);
		$numpages = ceil($numposts / $this->options['perpage']);
		
		//get the subset of that array that will be on this page
		$posts = array_slice($allposts,$lowerlimit,$this->options['perpage']);
		
		//display the results
		echo '<div class="cc-searchheader">' . str_replace('%s',urldecode($ccpage->searchterm), $user_lang['Comics tagged with "%s"']) . ' - ' . str_replace('%n', $page, $user_lang['Page %n']) . '</div>';
		echo '<div class="cc-searchbody">';
		if($numposts > 0){
	
			foreach($posts as $post){
				echo '<div class="cc-searchbox"><a href="' . $ccsite->root . $this->slug . '/' . $post['slug'] . '"><div class="cc-searchcomicname">' . $post['title'] . '</div><div class="cc-searchcomicimgbox"><img class="cc-searchcomicimage" src="' . $ccsite->root . 'comicsthumbs/' . $post['comicthumb'] . '" /></div></a></div>';
			}
			
		}
		
		//deliver error message if no results found
		else{
			
			echo '<div class="cc-errormsg">' . $user_lang['No results found.'] . '</div>';
			
		}
		echo '</div><div style="clear:both;"></div>';
		
		//display page navigation at the bottom
		if($numposts > 0 ) $this->getPageNav($numpages);
	}
	
	public function checkComic(){
		
		echo $this->id . '<br />'; //module id
		echo $this->name . '<br />'; //comic name
		echo $this->type . '<br />'; //module type
		print_r($this->options); //comic options array
		
	}
	
	
}

//CC_Gallery - module for creating a gallery of images
class CC_Gallery extends CC_Module{
	
	public $id; //module id
	public $name; //module name
	public $type = "gallery"; //module type
	public $description;
	
	//fill in the basic information
	public function __construct($moduleinfo){
		
		global $cc;
		global $tableprefix;
		
		$this->id = $moduleinfo['id'];
		$this->name = $moduleinfo['title'];
		$this->slug = $moduleinfo['slug'];
		$this->options = $this->getOptions($moduleinfo);
		
		$query = "SELECT * FROM cc_" . $tableprefix . "text WHERE id=:id LIMIT 1";
		$stmt = $cc->prepare($query);
		$stmt->execute(['id' => $this->id]);
		$desc = $stmt->fetch();
		
		$this->description = $desc['content'];
		
	}
	
	//display the gallery
	public function display(){
		
		global $cc;
		global $tableprefix;
		global $ccsite;
		global $ccpage;
		global $user_lang;
		
		//display the title if requested
		if($this->options['showTitle'] == "on") echo '<h1 class="cc-title">' . $this->name . '</h1>';
		if($this->options['showDescription'] == "on") echo '<div class="cc-gallery-description">' . $this->description . '</div>';
		
		//if the gallery scripts aren't loaded, load them
		if(!$ccpage->galleryloaded){
			?>
			<script type="text/javascript" src="<?=$ccsite->root?>lightbox/js/lightbox.js"></script>
			<link rel="stylesheet" href="<?=$ccsite->root?>lightbox/css/lightbox.css" type="text/css" media="screen" />
			<?php
			$ccpage->galleryloaded = true;
		}
		
		//get the images from the database
		$query = "SELECT * FROM cc_" . $tableprefix . "galleries WHERE gallery=:moduleid ORDER BY porder ASC";
		$stmt = $cc->prepare($query);
		$stmt->execute(['moduleid' => $this->id]);
		echo '<div class="cc-gallery">';
		$images = $stmt->fetchAll();
		
		//output the images
		if($images[0]['imgname'] != ""){
			foreach($images as $image){
				echo '<a href="' . $ccsite->root . 'uploads/' . $image['imgname'] . '" data-lightbox="' . $ccpage->title . '" data-title="<div class=\'customHtml\'>' . str_replace('"','&quot;',$image['caption']) . '</div>"><img src="' . $ccsite->root . 'uploads/' . $image['thumbname'] . '" /></a>';
			}
		}
		
		//deliver error message if no images found
		else{
			echo '<div class="cc-errormsg">' . $user_lang['There are no images in this gallery.'] . '</div>';
		}
		echo '</div>';
		echo '<div style="clear:both"></div>';
		
	}
	
}

//CC_Blog - contains basic info for a blog and display/navigation functions for a blog
class CC_Blog extends CC_Module{
	
	public $id; //module id
	public $name; //module title
	public $type = "blog"; //module type
	public $options = array(); //options array
	public $browsing = false; //boolean for keeping track if the user is looking at a list of posts or one post
	
	//assign basic variables
	public function __construct($moduleinfo,$subslug){
		
		global $ccsite;
		
		$this->id = $moduleinfo['id'];
		$this->name = $moduleinfo['title'];
		$this->slug = $moduleinfo['slug'];
		$this->options = $this->getOptions($moduleinfo);
		if($subslug == "" || $subslug == "page" || $subslug == "search") $this->browsing = true;
		
	}
	
	//display blog (single post, post archive, or search)
	public function display(){
		
		global $ccsite;
		global $ccpage;
		global $ccuser;
		global $cc;
		global $tableprefix;
		global $user_lang;
		
		//assign page number
		$page = $ccpage->pagenum;
		if($page < 1) $page = 1;
		
		//display several posts if browsing
		if($this->browsing){
			
			//set lowest post number for this page
			$lowerlimit = ($page - 1) * $this->options['perpage'];
			if($lowerlimit < 0) $lowerlimit = 0;
			
			//get tagged posts if searching
			if($ccpage->subslug == "search"){
				$query = "SELECT blogid FROM cc_" . $tableprefix . "blogs_tags WHERE blog=:blog AND tag=:tag";
				if($ccuser->authlevel == 0) $query .= " AND publishtime < " . time();
				$query .= " ORDER BY publishtime " . $this->options['archiveorder'];
				$stmt = $cc->prepare($query);
				$stmt->execute(['blog' => $this->id, 'tag' => urldecode($ccpage->searchterm)]);
				$taggedposts = $stmt->fetchAll();
				$allposts = array();
				
				//build array of posts from tagged post list
				foreach($taggedposts as $post){
					$query = "SELECT slug FROM cc_" . $tableprefix . "blogs WHERE id=:postid";
					if($ccuser->authlevel == 0) $query .= " AND publishtime < " . time();
					$query .= " LIMIT 1";
					$stmt = $cc->prepare($query);
					$stmt->execute(['postid' => $post['blogid']]);
					array_push($allposts,$stmt->fetch());
				}
			}
			
			//get all posts if not searching and put them in array
			else{
				$query = "SELECT slug FROM cc_" . $tableprefix . "blogs WHERE blog=:blogid";
				if($ccuser->authlevel == 0) $query .= " AND publishtime < " . time();
				$query .= " ORDER BY publishtime " . $this->options['archiveorder'];
				
				$stmt = $cc->prepare($query);
				$stmt->execute(['blogid' => $this->id]);
				$allposts = $stmt->fetchAll();
			}
			
			//do some math
			$numposts = count($allposts);
			$posts = array_slice($allposts,$lowerlimit,$this->options['perpage']);
			$numpages = ceil($numposts / $this->options['perpage']);
			
			//display posts and nav based on the created post array
			if(is_array($posts[0])){
				if($ccpage->subslug == "search") echo '<div class="cc-searchheader">' . str_replace('%s',urldecode($ccpage->searchterm), $user_lang['Posts tagged with "%s"']) . ' - ' . str_replace('%n', $page, $user_lang['Page %n']) . '</div>';
				$this->displayPosts($posts);
				$this->getPageNav($numpages);
			}
			
			//deliver error message if no posts found
			else{
				if($ccpage->subslug == "search") echo '<div class="cc-errormsg">' . $user_lang["No posts were found with this tag."] . '</div>';
				else echo $user_lang["There are no posts in this blog."];
			}
			
		}
		
		//display single post if the user isn't browsing
		else{
			$this->displaySinglePost($ccpage->subslug);
		}
		
	}
	
	public function recentPosts($num){
		
		global $ccuser;
		global $tableprefix;
		global $cc;
		global $ccsite;
		
		$query = "SELECT slug FROM cc_" . $tableprefix . "blogs WHERE blog=:blogid";
		if($ccuser->authlevel == 0) $query .= " AND publishtime < " . time();
		$query .= " ORDER BY publishtime DESC LIMIT 0,:num";
		
		$stmt = $cc->prepare($query);
		$stmt->execute(['blogid' => $this->id, 'num' => $num]);
		$allposts = $stmt->fetchAll();
		
		$this->displayPosts($allposts);
		
		echo '<div style="clear:both;"></div><div class="cc-readmore"><a href="' . $ccsite->root . $this->slug . '">' . $user_lang['View more posts...'] . '</a></div>';
	}
	
	//display just one post
	public function displaySinglePost($slug){
		
		global $ccsite;
		global $ccpage;
		global $user_lang;
		
		//get the post
		$post = $this->getPost($slug);
		
		if($post['title'] != ""){
			
			//output the post
			echo '<article class="cc-blogpost">';
			echo '<div class="cc-blogtitle">';
			echo '<a href="' . $ccsite->root . $this->slug . '/' . $post['slug'] . '">' . $post['title'] . '</a></div>';
			echo '<div class="cc-blog-publishtime">' . str_replace('%t',date($ccsite->timeformat,$post['publishtime']),str_replace('%d',date($ccsite->dateformat,$post['publishtime']),$user_lang['Posted %d at %t'])) . '</div>';
			echo '<div class="cc-blogcontent">' . $post['content'] . '</div>';
			
			//output supplementary material if options are set
			if($this->options['displaytags'] == "on") $this->displayTags($post['id']);
			if($this->options['displaycomments'] == "on") $this->displayComments($post);
			echo '</article>';
			
		}
		
		//deliver error message if the post wasn't found
		else{
			echo '<div class="cc-errormsg">' . $user_lang['There is no post with this ID.'] . '</div>';
		}
		
	}
	
	//display a list of tags
	public function displayTags($postid){
		
		global $cc;
		global $ccuser;
		global $ccpage;
		global $ccsite;
		global $tableprefix;
		global $user_lang;
		
		//get all the relevant tags for the post
		$query = "SELECT DISTINCT tag FROM cc_" . $tableprefix . "blogs_tags WHERE blogid=:blogid";
		if($ccuser->authlevel == 0) $query .= " AND publishtime <= " . time();
		$stmt = $cc->prepare($query);
		$stmt->execute(['blogid' => $postid]);
		$tags = $stmt->fetchAll();
		$divided = false;
		if(count($tags) > 0){
			
			//display a list of tags if there are any
			echo '<div class="cc-tagline">' . $user_lang['Tags: '];
			foreach($tags as $tag){
				if($divided) echo ", ";
				$divided = true;
				echo '<a href="' . $ccsite->root . $this->slug . "/search/" . $tag['tag'] . '">' . $tag['tag'] . '</a>';
			}
			echo '</div>';
			
		}
		
	}
	
	//display the blog post's comments
	public function displayComments($post){
		
		global $ccpage;
		global $ccsite;
		global $user_lang;
		
		//if the user is browsing through a list of posts, only show the comment number
		if($this->browsing){
			switch($ccsite->comments){
				case "commento":
					echo '<script src="https://cdn.commento.io/js/count.js"></script>';
					echo '<div class="cc-commentlink"><a href="' . $ccsite->root . $this->slug . '/' . $post['slug'] . '#commento">' . $user_lang['View/Post Comments'] . '</a></div>';
				break;
				case "disqus":
					echo '<div class="cc-commentlink"><a href="' . $ccsite->root . $this->slug . '/' . $post['slug'] . '#disqus_thread" data-disqus-identifier="' . $post['commentid'] . '">' . $user_lang['View/Post Comments'] . '</a></div>';
					echo '<script id="dsq-count-scr" src="//' . $ccsite->commentname . '.disqus.com/count.js" async></script>';
					break;
			}
		}
		
		//if they're looking at just one post, output the comment thread
		else{
			echo '<div class="cc-commentheader">' . $user_lang['Comments'] . '</div><div class="cc-commentbody">';
			switch($ccsite->comments){
				case "commento":
					?><div id="commento"></div>
					<script src="https://cdn.commento.io/js/commento.js"></script><?php
					break;
				case "disqus":
					?>
					<div id="disqus_thread"></div>
					<script>
						var disqus_config = function () {
							this.page.url = '<?=$ccsite->root . $this->slug?>/<?=$post['slug']?>';
							this.page.identifier = '<?=$post['commentid']?>';
						};
						(function() {  
							var d = document, s = d.createElement('script');
							
							s.src = '//<?=$ccsite->commentname?>.disqus.com/embed.js';  
							
							s.setAttribute('data-timestamp', +new Date());
							(d.head || d.body).appendChild(s);
						})();
					</script>
					<noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript" rel="nofollow">comments powered by Disqus.</a></noscript>
					<?php
				break;
			}
			
			echo '</div>';
		}
		
	}
	
	//display multiple posts
	public function displayPosts($posts){
		
		foreach($posts as $post){
			$this->displaySinglePost($post['slug']);
		}
		
	}
	
	//get a specific post based on a slug
	public function getPost($slug){
		
		global $cc;
		global $tableprefix;
		global $ccuser;
		
		$post = array();
		
		//get the post out of the database
		$query = "SELECT * FROM cc_" . $tableprefix . "blogs WHERE slug=:slug";
		if($ccuser->authlevel == 0){
			$query .= ' AND publishtime < ' . time();
		}
		$query .= " LIMIT 1";
		$stmt = $cc->prepare($query);
		$stmt->execute(['slug' => $slug]);
		$post = $stmt->fetch();
		
		return $post;
		
	}
	
}

?>
