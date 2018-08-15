<?
//formfunctions.php - builds php functions for building forms

//build individual form input based on info
function buildFormInput($options){
	
	global $ccpage;
	
	extract($options);
	
	//display input label
	echo '<div class="forminput"><label><div class="v-c">' . $label . ':';
	if($regex) echo ' *'; //output asterisk if required field
	echo '</div></label>';
	
	//put in a text input element if a date or text input
	if($type == "text" || $type == "date" || $type == "password"){
		echo '<input type="';
		if($type == "password") echo 'password'; else echo 'text';
		echo '" name="' . $name . '"';
		if($regex) echo ' data-validate="' . $regex . '"';
		if(htmlspecialchars($current) != "") echo ' value="' . htmlspecialchars($current) . '"';
		else if($type == "date") echo ' value="' . date('m/d/Y',time()) . '"';
		echo ' />';
		
		//put in datepicker code if a date input
		if($type == "date"){
			echo '<script>$(\'input[name="' . $name . '"\').datepicker({
    beforeShow: function (input, inst) {
        setTimeout(function () {
            inst.dpDiv.css("left","-=" + ($("html").width() - $("body").width())/2 + "px");
        }, 0);
    }
});</script>';
		}
	}else if($type == "time"){
		
		//get time
		$hour = "";
		$minute = "";
		$second = "";
		$time = time();
		if(htmlspecialchars($current) != "") $time = htmlspecialchars($current);
		$hour = date("H",$time);
		$minute = date("i",$time);
		$second = date("s",$time);
		
		//output hour select
		echo '<div class="timeselect"><select name="hour">';
		for($i = 0; $i < 24; $i++){
			$formatted = sprintf("%02d", $i);
			echo '<option value="' . $i . '"';
			if($formatted == $hour) echo ' SELECTED';
			echo '>' . $formatted . '</option>';
		}
		echo '</select><div class="f-c timecolon">:</div><select name="minute">';
		for($i = 0; $i < 60; $i++){
			$formatted = sprintf("%02d", $i);
			echo '<option value="' . $i . '"';
			if($formatted == $minute) echo ' SELECTED';
			echo '>' . $formatted . '</option>';
		}
		echo '</select><div class="f-c timecolon">:</div><select name="second">';
		for($i = 0; $i < 60; $i++){
			$formatted = sprintf("%02d", $i);
			echo '<option value="' . $i . '"';
			if($formatted == $second) echo ' SELECTED';
			echo '>' . $formatted . '</option>';
		}
		echo '</select></div>';
		
	}
	else if($type=="storylines"){
		$ccpage->module->displayChapters(true,htmlspecialchars($current),$needsparent);
	}else if($type=="editor"){
		buildTextEditor($name);
	}else if($type=="select"){
		echo '<select name="' . $name . '">';
		foreach($options as $key => $value){
			echo '<option value="' . $key . '"';
			if($key == htmlspecialchars($current)) echo ' SELECTED';
			echo '>' . $value . '</option>';
		}
		echo '</select>';
	}
	
	//put in a tooltip and close out the div
	echo '<div class="tooltip"><a class="f-c">?</a><div class="tooltip-help"><div class="tooltip-triangle"></div>' . $tooltip . '</div></div></div>';
}

//build out whole form
function buildForm($forminputs){
	
	//loop through form inputs and generate lines
	foreach($forminputs as $formline){
		echo '<div class="formline">';
		foreach($formline as $input){
			buildFormInput($input);
		}
		echo '</div>';
	}
}

function buildTextArea($label,$name,$tooltip,$current = ""){
	//create header for content editor ?>
	<div class="formtext"><label><div class="v-c"><?=$label?>:</div><div class="tooltip"><a class="f-c">?</a><div class="tooltip-help"><div class="tooltip-triangle"></div><?=$tooltip?></div></div></label>
	
	<? //create html editor area ?>
	<div class="texteditor">
		
		<? //place html editor area ?>
		<textarea name="<?=$name?>"><?=htmlspecialchars($current)?></textarea>
		
	</div>
	<?
}

//output a text editor
function buildTextEditor($label,$name,$tooltip,$current = ""){

	//create header for content editor ?>
	<div class="formtext"><label><div class="v-c"><?=$label?>:</div><div class="tooltip"><a class="f-c">?</a><div class="tooltip-help"><div class="tooltip-triangle"></div><?=$tooltip?></div></div></label>
	</div>
	<div class="bootstrap-iso">
	<textarea id="<?=$name?>" name="<?=$name?>"><?=$current?></textarea>
	<script>
	$(document).ready(function() {
		$('#<?=$name?>').summernote({
			height: 300,
			callbacks: {
				onImageUpload: function(files, editor, welEditable) {
					for(var i = 0; i < files.length; i++){
						uploadFile(files[i],this);
					}
				}
			},
			toolbar: [
				['style', ['style']],
				['font',['bold','underline','italic','clear','fontname','fontsize']],
				['color', ['color']],
				['para', ['ul', 'ol', 'paragraph']],
				['insert',['table','link','picture','video']],
				['view',['fullscreen','codeview','help']]
			]
		});
	});
	</script>
	<?

}

//output image input for in-page image uploading
function buildImageInput($buttontext,$validate,$tooltip = ""){
	echo '<div class="fileinputcontainer"><input name="imagefile" type="file" id="imagefile" class="hidefileinput"';
	if($validate) echo ' data-validate="file-upload"';
	echo ' /><label for="imagefile" class="fileselect light-bg">' . $buttontext;
	if ($tooltip != "") echo '<div class="tooltip"><a class="f-c">?</a><div class="tooltip-help"><div class="tooltip-triangle"></div>' . $tooltip . '</div></div>';
	echo '</label>
	<div id="image-upload-bar" class="upload-progress">
		<div class="bar">&nbsp;</div >
		<div class="percent"></div>
	</div>
	<div class="filenameholder"></div>
	</div>';
}

?>