<? //content-editor-js.php - outputs all the javascript for the rich text editor ?>

<script>
var uploadFile = function(file,el){
	var formdata = new FormData();
	formdata.append('file',file);
	console.log(formdata.get('file'));
	$.ajax({
		url: "/<?=$ccsite->relativepath.$ccsite->ccroot;?>ajax/summernote-img-upload.php",
		data: formdata,
		type: "POST",
		cache:false,
		contentType: false,
		processData: false,
		success:function(data){
			$(el).summernote('editor.insertImage',data);
		}
	});
}
</script>