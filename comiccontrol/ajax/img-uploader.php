<?php
//img-uploader.php - handles in-page uploading of images for various functions

//include scripts required for database and classes
require_once('../includes/dbconfig.php');
require_once('../includes/initialize.php');

//up the memory limit so images can be resized
ini_set('memory_limit', '128M' );

//only allow the script to be used if the user is authorized
if($ccuser->authlevel > 0){
	
	//figure out what function is using the script
	$iscomic = false;
	$isgallery = false;
	$isavatar = false;
	$serveruri = $_POST['serveruri'];
	
	$ccpage = new CC_Page($serveruri,"admin");
	
	if(getSlug(1) == "modules"){
		if($ccpage->module->type == "comic") $iscomic = true;
		if($ccpage->module->type == "gallery") $isgallery = true;
	}
	if(getSlug(1) == "users") $isavatar = true;
		
	//generic function for uploading images and returning info
	function uploadImage($tmpimage,$uploadsDirectory,$filename,$returnData,$maxw,$maxh,$returnkey){

		if(!($source = imagecreatefromstring(file_get_contents($tmpimage)))){
			$returnData['error'] = 1;
		}else{
				
			//get image type
			$type = strtolower(substr(strrchr($filename,"."),1));
			
			//find an available filename
			$now = time();
			while(file_exists($uploadFilename = $uploadsDirectory.$now.'-'.$filename))
			{
				$now++;
			}
			$finalfile = $now.'-'.$filename;
			
			//get current file sizes
			$x = imagesx($source);
			$y = imagesy($source);
			
			//determine new file sizes
			if($x <= $maxw && $y <= $maxh){
				$w = $x;
				$h = $y;
			}else{
				if(($x/$maxw) >= ($y/$maxh)){
					$w = $maxw;
					$h = ($y/$x) * $w;
				}else{
					$h = $maxh;
					$w = ($x/$y) * $h;
				}
			}
			
			//if not bigger than restraints, just copy
			if($x == $w && $h == $y){
				copy($tmpimage,$uploadFilename);
				$returnData['copied'] = "true";
				$returnData[$returnkey] = $finalfile;
			}else{
			
				//resize image and move file to new location
				if(!($slate = imagecreatetruecolor($w, $h))) $returnData['error'] = 1;
				else{
					if($type == "gif" or $type == "png"){
						imagecolortransparent($slate, imagecolorallocatealpha($slate, 0, 0, 0, 127));
						imagealphablending($slate, false);
						imagesavealpha($slate, true);
					}
					imagecopyresampled($slate, $source, 0, 0, 0, 0, $w, $h, $x, $y);
					switch($type){
						case 'bmp': imagewbmp($slate, $uploadFilename); break;
						case 'gif': imagegif($slate, $uploadFilename); break;
						case 'jpg': imagejpeg($slate, $uploadFilename, 100); break;
						case 'png': imagepng($slate, $uploadFilename, 9); break;
					}
					$returnData[$returnkey] = $finalfile;
					imagedestroy($slate);
				}
			}
				
		}
		return $returnData;
		
	}	
	
	//create return data array
	$returnData = array();
	$returnData['error'] = 0;
	
	//get the image and check that it's an image
	$fieldname = $_POST['fieldname'];
	$tmpimage = $_FILES[$fieldname]['tmp_name'];
		
		
		//set upload directory
		$filename = $_FILES[$fieldname]['name'];
		$uploadsDirectory = '../../uploads/';
			
		//get max file sizes
		$maxw = 10000;
		$maxh = 50000;
		if($iscomic){
			$maxw = $ccpage->module->options['comicwidth'];
			$uploadsDirectory = '../../comics/';
		}else if($isavatar){
			$uploadsDirectory = '../avatars/';
			$maxw = 230;
			$maxh = 230;
		}
		
		$returnData = uploadImage($tmpimage,$uploadsDirectory, $filename, $returnData, $maxw, $maxh, 'final');
		
		//if a comic, upload the high res image and thumbnail
		if($iscomic){
			
			//upload high res image
			
			//set upload directory
			$uploadsDirectory = '../../comicshighres/';
				
			//get max file sizes
			$maxw = 10000;	
			
			//upload image
			$returnData = uploadImage($tmpimage,$uploadsDirectory, $filename, $returnData, $maxw, $maxh, 'highres');
			
			//upload thumbnail
			
			//set upload directory
			$uploadsDirectory = '../../comicsthumbs/';
				
			//get max file sizes
			$maxw = $ccpage->module->options['thumbwidth'];
			$maxh = $ccpage->module->options['thumbheight'];
				
			//upload image
			$returnData = uploadImage($tmpimage,$uploadsDirectory, $filename, $returnData, $maxw, $maxh, 'thumb');
		}else if($isgallery){
			
			//upload thumbnail
				
			//get max file sizes
			$maxw = $ccpage->module->options['thumbwidth'];
			$maxh = $ccpage->module->options['thumbheight'];
				
			//upload image
			$returnData = uploadImage($tmpimage,$uploadsDirectory, $filename, $returnData, $maxw, $maxh, 'thumb');
			
		}else{
			$maxw = 120;
			$maxh = 120;
				
			//upload image
			$returnData = uploadImage($tmpimage,$uploadsDirectory, $filename, $returnData, $maxw, $maxh, 'thumb');
		}
			
		
	
	//encode and echo the return data
	header('Content-Type: application/json');
	echo json_encode($returnData);
}
?>