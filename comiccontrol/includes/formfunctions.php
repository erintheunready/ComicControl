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
	
	<? //create html editor area ?>
	<div class="texteditor">
	
		<? //create toolbar ?>
		<div class="toolbar">
			<div class="formatoptions">
				<div class="richtextoptions">
					<a href="javascript:void(0);" class="undo" data-command="undo"><i class="fa fa-undo"></i></a>
					<a href="javascript:void(0);" class="redo" data-command="redo"><i class="fa fa-repeat"></i></a> | 
					<a href="javascript:void(0);" data-command='bold'><i class='fa fa-bold'></i></a>
					<a href="javascript:void(0);" data-command='italic'><i class='fa fa-italic'></i></a>
					<a href="javascript:void(0);" data-command='underline'><i class='fa fa-underline'></i></a>
					<a href="javascript:void(0);" data-command='strikethrough'><i class='fa fa-strikethrough'></i></a> | 
					<a href="javascript:void(0);" data-command='subscript'><i class='fa fa-subscript'></i></a>
					<a href="javascript:void(0);" data-command='superscript'><i class='fa fa-superscript'></i></a> | 
					<a href="javascript:void(0);" data-command='justifyLeft'><i class='fa fa-align-left'></i></a>
					<a href="javascript:void(0);" data-command='justifyCenter'><i class='fa fa-align-center'></i></a>
					<a href="javascript:void(0);" data-command='justifyRight'><i class='fa fa-align-right'></i></a>
					<a href="javascript:void(0);" data-command='justifyFull'><i class='fa fa-align-justify'></i></a> | 
					<a href="javascript:void(0);" data-command='insertUnorderedList'><i class='fa fa-list-ul'></i></a>
					<a href="javascript:void(0);" data-command='insertOrderedList'><i class='fa fa-list-ol'></i></a>
					<a href="javascript:void(0);" data-command='indent'><i class='fa fa-indent'></i></a>
					<a href="javascript:void(0);" data-command='outdent'><i class='fa fa-outdent'></i></a> | 
					<a href="javascript:void(0);" data-command='fontsize' data-value="smaller"><i class='fa fa-font'></i><i class='fa fa-caret-down' style="font-size:.5em; vertical-align:top"></i></a>
					<a href="javascript:void(0);" data-command='fontsize' data-value="bigger"><i class='fa fa-font'></i><i class='fa fa-caret-up' style="font-size:.5em; vertical-align:top"></i></a>
					<div href="javascript:void(0);" class="headers"><a class="headericon"><i class='fa fa-header'></i><i class='fa fa-caret-down' style="font-size:.5em; vertical-align:bottom"></i></a>
						<div class="headerlist">
							<a href="javascript:void(0);" data-command='formatBlock' data-value="h1">h1</a>
							<a href="javascript:void(0);" data-command='formatBlock' data-value="h2">h2</a>
							<a href="javascript:void(0);" data-command='formatBlock' data-value="h3">h3</a>
							<a href="javascript:void(0);" data-command='formatBlock' data-value="h4">h4</a>
							<a href="javascript:void(0);" data-command='formatBlock' data-value="h5">h5</a>
						</div>
					</div>	 | 		
					<a href="javascript:void(0);" class="addlink" data-command='createLink'><i class='fa fa-link'></i></a>
					<a href="javascript:void(0);" class="unlink" data-command='unlink'><i class='fa fa-unlink'></i></a>
					<a href="javascript:void(0);" class="insertimage"><i class='fa fa-image'></i></a>	 | 		
				</div>
				<a href="javascript:void(0);" class="toggleHTML"><i class='fa fa-code'></i></a>
			</div>
			<div style="clear:both"></div>
		</div>
		
		<? //place rich text editor area ?>
		<div class="editarea">
			<div class="editor" id="<?=$name?>" contenteditable><?=$current?></div>
		</div>
		
		<? //place html editor area ?>
		<textarea class="htmleditor" name="<?=$name?>">
		</textarea>
		
	</div>
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