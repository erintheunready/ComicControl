<? //content-editor-js.php - outputs all the javascript for the rich text editor ?>

<script>

//build image upload prompt so it can be used later if needed
var $imagePrompt = $("<div></div>");

//don't create new paragraphs on enter, just put in some line breaks
$('div[contenteditable]').keydown(function(e) {
	if (e.keyCode === 13) {
	  document.execCommand('insertHTML', false, '<br><br>');
	  return false;
	}
  });
  
 //manage standard commands for modifying the text
$('.toolbar a').on("click", function(e){
	if($(this).data('command') == 'fontsize'){
		var size = document.queryCommandValue("FontSize");
		if($(this).data('value') == 'smaller'){
			size--;
		}else{
			size++;
		}
		document.execCommand($(this).data('command'), false, size);
	}else if($(this).data('command') == 'formatBlock'){
		document.execCommand($(this).data('command'), false, $(this).data('value'));
	}else if($(this).data('command') == 'createLink'){
		$imagePrompt.html('');
	}else{
		document.execCommand($(this).data('command'), false, null);
	}
});

//swap between rich text and HTML editors
$('.toggleHTML').on("click",function(){
	var $htmleditor = $(this).closest('.texteditor').find('.htmleditor');
	var $richtext = $(this).closest('.texteditor').find('.editor');
	var $richicons = $(this).closest('.texteditor').find('.richtextoptions');
	var $inserticons = $(this).closest('.texteditor').find('.insertoptions');
	if($htmleditor.css('display') == "none"){
		$htmleditor.css('display','block');
		$richtext.css('display','none');
		$htmleditor.val($richtext.html());
		$richtext.html('');
		$richicons.css('display','none');
		$inserticons.css('display','none');
	}else{
		$htmleditor.css('display','none');
		$richtext.html($htmleditor.val());
		$htmleditor.val('');
		$richtext.css('display','block');
		$richicons.css('display','inline-block');
		$inserticons.css('display','inline-block');
	}
});

//manage header style dropdown
$('.headers').on("mouseenter mouseleave",function(){
	$(this).parent().find('.headerlist').slideToggle('fast');
});

//build image uploader if that button is clicked
$('.insertimage').on("click",function(){
	var selection=window.getSelection();
	var $fileholder = $(this).closest('.imagePrompt');
	var range = 0;
	var $richtext = $(this).closest('.texteditor').find('.editor');
	var $htmleditor = $(this).closest('.texteditor').find('.htmleditor');
	if(selection.rangeCount != 0) range = selection.getRangeAt(0);
	$imageprompt = $('<div class="overlay"><div class="prompt"><div class="closethis"><i class="fa fa-close"></i></div><h2 class="formheader"><?=$lang['Insert image']?></h2><form name="uploadimage" id="uploadimage" method="post" enctype="multipart/form-data"><input name="selectimage" type="file" id="selectimage" class="hidefileinput" /><div class="fileinputcontainer"><label for="selectimage" class="fileselect light-bg f-c"><?=$lang['Choose file...']?></label><div id="small-upload-bar" class="upload-progress"><div class="bar">&nbsp;</div ><div class="percent"></div></div><br /><input type="text" name="imageurl" class="imageurl" /><br /><button class="addthisimage f-c" type="button"><?=$lang['Add image']?></button></form></div></div>');
	
	//put the prompt on top of the page
	$('body').append($imageprompt);
	$('.closethis').on('click',function(){
		$imageprompt.remove();
	});
	$('.addthisimage').on("click",function(){
		var $image = $("#uploadimage").find('.imageurl').val();
		$('.overlay').remove();
		
		var imageNode = document.createElement("img");
		imageNode.setAttribute("src",$image);
		range.insertNode(imageNode);
	
	});
	
	//do ajax request if image is selected
	$('#selectimage').on('change',function(){
		var progress = $('#small-upload-bar');
		var bar = progress.find('.bar');
		var percent = progress.find('.percent');
		progress.css('display','block');
		
		$('#uploadimage').ajaxSubmit({
			beforeSend: function() {
				var percentVal = '0%';
				bar.width(percentVal);
				percent.html(percentVal);
			},
			uploadProgress: function(event, position, total, percentComplete) {
				var percentVal = percentComplete + '%';
				bar.css('width',percentVal);
				percent.html('<?=$lang['Uploading image']?>: ' + percentVal);
				console.log(percentVal, position, total);
			},
			success: function(data) {
				percent.html('<?=$lang['Processing image...']?>');
			},
			complete: function(xhr) {
				progress.css('display','none');
				var data = JSON.parse(xhr.responseText);
				$('.imageurl').val('<?=$ccsite->root.$ccsite->relativepath?>uploads/' + data.final);
			},
			data: {
				fieldname:'selectimage',
				moduleid: ""
			},
			url: "/<?=$ccsite->relativepath.$ccsite->ccroot;?>ajax/img-uploader.php"
		}); 
		
	});

});

//handle link adding
$('.addlink').on('click', function(){
	var selection=window.getSelection();
	if(selection.rangeCount != 0){ 
		var range = selection.getRangeAt(0);
		var cloned = range.cloneContents();
		var div = document.createElement('div');
		div.appendChild(cloned);
		var linktext = div.innerHTML;
		var $linkprompt = $('<div class="overlay"><div class="prompt"><div class="closethis"><i class="fa fa-close"></i></div><h2 class="formheader">Insert link</h2><form name="insertlink" id="insertlink" method="post"><p><input name="linktoinsert" type="text" id="linktoinsert" /></p><p><select name="linktarget" id="linktarget"><option value=""><?=$lang['Open link in same window']?></option><option value="_blank"><?=$lang['Open link in new window']?></option></select><br /><br /><button class="addthislink f-c" type="button"><?=$lang['Add link']?></button></form></div></div>');
		$('body').append($linkprompt);
	}else{
		return false;
	}
	$('.closethis').on('click',function(){
		$linkprompt.remove();
	});
	$('.addthislink').on('click',function(){
		var linkNode = document.createElement("a");
		linkNode.setAttribute("href",$('#linktoinsert').val());
		if($('#linktarget').val() != "") linkNode.setAttribute("target",$('#linktarget').val());
		linkNode.innerHTML = linktext;
		range.deleteContents();
		range.insertNode(linkNode);
		$linkprompt.remove();
	});
});
</script>