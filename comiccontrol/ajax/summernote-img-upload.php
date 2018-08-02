<?php
//img-uploader.php - handles in-page uploading of images for various functions

//include scripts required for database and classes
require_once('../includes/dbconfig.php');
require_once('../includes/initialize.php');

//up the memory limit so images can be resized
ini_set('memory_limit', '128M' );

//only allow the script to be used if the user is authorized
if($ccuser->authlevel > 0){
    
    //image uploader script
	function uploadImage($tmpimage,$uploadsDirectory,$filename,$returnData,$returnkey){
		
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
			
            copy($tmpimage,$uploadFilename);
            $returnData['copied'] = "true";
            $returnData[$returnkey] = $finalfile;
				
		}
		return $returnData;
		
	}	

	//create return data array
	$returnData = array();
	$returnData['error'] = 0;
	
	//get the image and check that it's an image
	$fieldname = 'file';
	$tmpimage = $_FILES[$fieldname]['tmp_name'];
    
    //set upload directory
    $filename = $_FILES[$fieldname]['name'];
    $uploadsDirectory = '../../uploads/';
        
    $returnData = uploadImage($tmpimage,$uploadsDirectory, $filename, $returnData,'final');
    
    echo '/' . $ccsite->relativepath . 'uploads/' . $returnData['final'];
}
?>