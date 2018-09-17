<? //blog-main.php - the main page for any blog module. ?>

<main id="content">

<?
//get file contents function
function get_info($url){
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($curl);
	curl_close($curl);
	
	return $output;
}

$lastmidnight = strtotime('today midnight');
$updateneeded = false;
if($ccsite->updatechecked < $lastmidnight){
	$version = get_info("http://www.comicctrl.com/version-control/getversion.php");
	$ccsite->newestversion = $version;
	$query = "UPDATE cc_" . $tableprefix . "options SET optionvalue=:value WHERE optionname=:option";
	$stmt = $cc->prepare($query);
	$stmt->execute(['value' => $ccsite->newestversion, 'option' => 'newestversion']);
	$stmt->execute(['value' => time(), 'option' => 'updatechecked']);
}
if($ccsite->version != $ccsite->newestversion){
	?>
	<div class="msg alert"><a href="<?=$ccurl?>update-check"><?=$lang['Your version of ComicControl needs updating! Click here to update your site!']?></a></div>
	<?
}

?>

<div class="home-box">
	<div class="home-exampleimg">
		<img src="<?=$ccurl?>images/blankpage.png" />
	</div>
	<div class="home-exampletext">
		<?=$lang['homemessage']?>
	</div>
	<div style="clear:both;"></div>
</div>
<div class="home-twocol">
	<div class="home-leftcol">
		<div class="home-box">
			<div class="home-tableheader"><?=$lang['Updates from @ComicCtrl']?></div>
			<a class="twitter-timeline" href="https://twitter.com/comicctrl" data-chrome="noheader nofooter transparent"></a> <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
		</div>
	</div>
	
	<div class="home-rightcol">
		<div class="home-box">
			<div class="home-table">
				<div class="home-tableheader">
					<?=$lang['Scheduled post calendar']?>
				</div>
				<?
					$query = "SELECT slug,publishtime,title,comic as moduleid FROM cc_" . $tableprefix . "comics WHERE publishtime >= " . time() . " UNION SELECT slug,publishtime,title,blog as moduleid FROM cc_" . $tableprefix . "blogs WHERE publishtime >= " . time() . " ORDER BY publishtime ASC LIMIT 5";
					$stmt = $cc->prepare($query);
					$stmt->execute();
					$futurecount = $stmt->rowCount();
					$pastcount = 5;
					if($futurecount < 5) $pastcount = 10 - $futurecount;
					$future = $stmt->fetchAll();
					$query = "SELECT slug,publishtime,title,comic as moduleid FROM cc_" . $tableprefix . "comics WHERE publishtime < " . time() . " UNION SELECT slug,publishtime,title,blog as moduleid FROM cc_" . $tableprefix . "blogs WHERE publishtime < " . time() . " ORDER BY publishtime DESC LIMIT " . $pastcount;$stmt = $cc->prepare($query);
					$stmt = $cc->prepare($query);
					$stmt->execute();
					$past = $stmt->fetchAll();
					$past = array_reverse($past);
					$postlist = array_merge($past,$future);
					foreach($postlist as $post){
						$query = "SELECT slug FROM cc_" . $tableprefix . "modules WHERE id=:id";
						$stmt = $cc->prepare($query);
						$stmt->execute(['id' => $post['moduleid']]);
						$module = $stmt->fetch();
						$moduleslug = $module['slug'];
						$status = $lang['Posted'];
						if($post['publishtime'] >= time()) $status = $lang['Scheduled'];
						echo '<div class="home-table-row"><div class="home-table-col1"><a href="' . $ccurl . 'modules/' . $moduleslug . '/edit-post/' . $post['slug'] . '">' . $post['title'] . '</a></div><div class="home-table-col2">' . $status . '</div><div class="home-table-col3">' . date($ccsite->dateformat,$post['publishtime']) . '</div></div>';
					}
				?>
			</div>
		</div>
	</div>
</div>

</main>