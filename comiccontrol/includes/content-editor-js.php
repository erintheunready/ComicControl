<? //content-editor-js.php - outputs all the javascript for the rich text editor ?>

<script>
var uploadFile = function(file,el){
	var form = $("<form method='POST' action='/<?=$ccsite->relativepath.$ccsite->ccroot;?>ajax/summernote-img-upload.php' enctype='multipart/form-data'></form>");
	var formdata = new FormData();
	formdata.append('file',file);
	var $progressDiv = $('<div class="summernote-overlay"><div class="summernote-overlay-box"><?=$lang['Uploading image']?>: <span class="summernote-percent">0</span>%</div></div>');
	$(el).parent('.bootstrap-iso').append($progressDiv);
	var $percentage = $progressDiv.children('.summernote-overlay-box').children('.summernote-percent');
	form.ajaxSubmit({
		formData: formdata,
		beforeSend: function(){
			var percentVal = 0;
			$percentage.html(percentVal);
		},
		uploadProgress: function(event, position,total,percentComplete) {
			percentVal = percentComplete;
			$percentage.html(percentVal);
		},
		success:function(data){
			$progressDiv.html('<div class="summernote-overlay-box"><?=$lang['Processing image...']?></div>');
		},
		complete: function(xhr) {
			var data = xhr.responseText;
			$(el).summernote('editor.insertImage',data);
			$progressDiv.remove();
		}
	});
}
</script>