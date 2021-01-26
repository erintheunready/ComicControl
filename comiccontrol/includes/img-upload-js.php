<?php //img-upload-js.php - outputs javascript for making AJAX requests for in-page image uploads ?>

<script>

//add server URI to the form so that the AJAX knows what page made the request
$('form').append('<input type="hidden" name="serveruri" value="<?=$_SERVER['REQUEST_URI']?>" />');

//initiate request if file input is changed
$('.hidefileinput').on('change',function(){
	
	var filearr = ($(this).val()).split("\\");
	var lastbit = filearr[filearr.length-1];
	filearr = lastbit.split("/");
	lastbit = filearr[filearr.length-1];
	
	//only run the request if a file was actually submitted
	if(lastbit){
		
		//set variables for specific divs
		$fileholder = $(this).parent();
		$imagearea = $fileholder.find('.filenameholder');

		var progress = $('#image-upload-bar');
		var bar = progress.find('.bar');
		var percent = progress.find('.percent');
		progress.css('display','block');
		
		//submit the ajax request
		$('form').ajaxSubmit({
			
			//set progress bar before the request happens
			beforeSend: function() {
				var percentVal = '0%';
				bar.width(percentVal);
				percent.html(percentVal);
			},
			
			//change progress as the file is uplaoded
			uploadProgress: function(event, position, total, percentComplete) {
				var percentVal = percentComplete + '%';
				bar.css('width',percentVal);
				percent.html('<?=$lang['Uploading image']?>: ' + percentVal);
				console.log(percentVal, position, total);
			},
			
			//show image as processing until the image is done resizing
			success: function(data) {
				percent.html('<?=$lang['Processing image...']?>');
			},
			
			//on finish, put return data in proper place
			complete: function(xhr) {
				
				//get response text and parse it
				var data = JSON.parse(xhr.responseText);
				
				//put the final filename in the form as a hidden input
				var $finalfile = $('<input type="hidden" class="finalfile" name="image-finalfile" value="' + data.final + '" />');
				$imagearea.html('');
				$imagearea.append($finalfile);
				
				//if a comic, put the high res filename in the form
				<?php if(getSlug(1) != "users" && getSlug(1) != "image-library"){ ?>$('.filenameholder').append('<input type="hidden" class="finalfile" name="image-highres" value="' + data.highres + '" />'); <?php } ?>
				
				//if not an avatar, also put the thumbnail filename in the form as a hidden input
				<?php if(getSlug(1) != "users"){ ?>$('.filenameholder').append('<input type="hidden" class="finalfile" name="image-thumbnail" value="' + data.thumb + '" />'); <?php } ?>
				
				//if not the image library, put the image in the page
				<?php if(getSlug(1) != "image-library"){ ?>
				$imagearea.append('<img src="<?=$ccsite->root?><?=$imgfolder?>' + data.final + '" />');
				$imagearea.find('img').on('load', function(){
					$fileholder.find('.fileselect').html('<?=$lang['Change File']?>');
					progress.css('display','none');
					$imagearea.slideDown('slow');
				});
				<?php }else{ ?>
				progress.css('display','none');
				$('form').submit();
				<?php } ?>
				
				//remove old image if there
				$('.currentfileholder').remove();
			},
			
			//set the data that's going to be set to the ajax script
			data: {
				fieldname:'imagefile',
				moduleslug: '<?=$ccpage->module->slug?>',
				serveruri: '<?=$_SERVER['REQUEST_URI']?>'
			},
			url: "/<?=$ccsite->relativepath.$ccsite->ccroot;?>ajax/img-uploader.php"
			
		}); 
	}
});
</script>