<? //form-submit-js.php - outputs javascript for form submission functions ?>

<script>

//function for showing errors below inputs
function showError(container,msg){
	var $errordiv = $('<div class="formerror"></div>');
	$errordiv.html(msg);
	container.css('height','auto');
	container.append($errordiv);
	$errordiv.slideDown();
}

//validation script for submitted form
$("#submitform").on('click', function(){
	
	var error = false;
	
	//go through each input and check it
	$("input").each(function(){
		
		//only check it if it has a specific validation tied to it
		if($(this).attr('data-validate')) {
			
			//remove old errors
			var $forminput = $(this).closest('.forminput');
			$forminput.find('.formerror').remove();
			
			//different validations depending on the type of input
			switch($(this).attr('data-validate')){
				case 'normal-text':
					if($(this).val().length < 1){
						showError($forminput,'<?=$lang['error-normaltext']?>');
						error = true;
					}
					break;
				case 'date':
					var dateregex = /^\d{2}\/\d{2}\/\d{4}$/;
					if(!dateregex.test($(this).val())){
						showError($forminput,'<?=$lang['error-date']?>');
						error = true;
					}
					break;
				case 'file-upload':
					var $forminput = $(this).closest('.fileinputcontainer');
					if(!($(this).closest('.fileinputcontainer').find('.finalfile').val())){
						showError($forminput,'<?=$lang['error-file']?>');
						error = true;
					}
					break;
				case 'int':
					if(!(($(this).val()) % 1 === 0) || $(this).val() < 1){
						showError($forminput,'<?=$lang['error-int']?>');
						error = true;
					}
					break;
				case 'prefix':
					var regexprefix = /^[a-z0-9_]{1,7}$/i;
					if(!regexprefix.test($(this).val())){
						showError($forminput,'<?=$ilang['error-prefix']?>');
						error = true;
					}
					break;
					
			}
		}
		
	});
	
	//if no error, submit the form
	if(!error){
		
		//manage text editors - turn their content into textarea info
		$('.texteditor').each( function(){
			if($(this).find('.editor').html() != ""){
				$(this).find('.htmleditor').val($(this).find('.editor').html());
			}
		});
		
		//remove all the file inputs so the form doesn't try to upload images again
		$('input[type=file]').each( function() {
			$(this).remove();
		});
		
		//submit the form
		$('#submitform').closest('form').submit();
		
	}
	
	//push to the top of the form if there were errors
	else{
		$('html, body').animate({scrollTop: '0px'}, 300);
	}
	
});
</script>