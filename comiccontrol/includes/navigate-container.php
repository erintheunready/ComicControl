<? //navigate-container.php - javascript for managing page-to-page navigation on various pages ?>

<script>

//change content of the container based on postdata and direction
function changeContainer(postdata){
	
	//send ajax request
	$.post("/<?=$ccsite->relativepath.$ccsite->ccroot;?>ajax/get-page.php", postdata, function( data ){
		
		//parse the data received from get-level.php
		data = JSON.parse(data);
		
		//start creating new content for the contaienr
		var newhtml = '<div class="col-header blue-bg">';
		
		if(postdata.type == "storyline"){
			//if not the top level, create arrow to go up a level
			if(postdata.searchid != 0) newhtml += '<div class="back-arrow" data-parent="' + data.parent.id + '" data-parentname="' + data.parent.name + '"><i class="fa fa-caret-left"></i></div><div class="arrow-bump"></div>';
		}
		
		//put the name of the current page name
		if(postdata.type == "storyline"){
			newhtml += postdata.storylinename;
		}
		else newhtml += '<?=$lang['Page %s']?>'.replace('%s',postdata.page);
		
		//back/forth arrows for page navigation
		if(postdata.type != "storyline"){
			if(data.prev != 0) newhtml += '<div class="prev-page" data-page="' + data.prev + '"><i class="fa fa-caret-left"></i></div><div class="arrow-bump"></div>';
			if(data.next != 0) newhtml += '<div class="next-page" data-page="' + data.next + '"><i class="fa fa-caret-right"></i></div>';
		}
		
		//loop through storylines and output rows
		var gray = false;
		newhtml += '</div><div class="row-container">';
		if(postdata.getPages) newhtml += '<div class="col-header blue-bg"><?=$lang['Storylines']?></div>';
		for(var i = 0; i < data.results.length; i++){
			newhtml += '<div class="zebra-row ';
			newhtml += postdata.type;
			if(gray == true){ 
				newhtml += ' gray-bg';
				gray = false;
			}
			else gray = true;
			
			//display rows different depending on the page
			switch(postdata.type){
				case "storyline":
					newhtml += '" data-storyline="' + data.results[i].id + '" data-name="' + data.results[i].name + '"><div class="row-title">' + data.results[i].name + '</div>';
					if(!postdata.getPages){
						newhtml += '<a href="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/delete-storyline/'?>' + data.results[i].id + '"><?=$lang['Delete']?></a><a href="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/edit-storyline/'?>' + data.results[i].id + '"><?=$lang['Edit']?></a>';
					}
					newhtml += '<div class="next-arrow"><i class="fa fa-caret-right"></i></div>';
					break;
				case "gallery":
					newhtml += '"><div class="row-img"><img src="<?=$ccsite->root?>uploads/' + data.results[i].thumbname + '" /></div>';
					newhtml += '<div class="row-caption">' + data.results[i].caption + '</div>';
					newhtml += '<a href="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/delete-image/'?>' + data.results[i].id + '"><?=$lang['Delete']?></a><a href="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/edit-image/'?>' + data.results[i].id + '"><?=$lang['Edit']?></a>';
					break;
				case "images":
					newhtml += '"><div class="row-img"><img src="<?=$ccsite->root?>uploads/' + data.results[i].thumbname + '" /></div>';
					newhtml += '<div class="row-caption"><a href="<?=$ccsite->root?>uploads/' + data.results[i].imgname + '" target="_blank" style="float:left; text-transform:none;"><?=$ccsite->root?>uploads/' + data.results[i].imgname + '</a></div>';
					newhtml += '<a href="<?=$ccurl . $navslug . '/delete-image/'?>' + data.results[i].id + '"><?=$lang['Delete']?></a>';
					break;
				case "blog":
					newhtml += '"><div class="row-title">' + data.results[i].title + '</div>';
					newhtml += '<a href="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/delete-post/'?>' + data.results[i].slug + '"><?=$lang['Delete']?></a><a href="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/edit-post/'?>' + data.results[i].slug + '"><?=$lang['Edit']?></a>';
					break;
			}
			newhtml += '<div style="clear:both"></div></div>';
		}
		
		if(postdata.type == "storyline"){
			//if there are pages delivered for this storyline, loop through them and give options
			if(postdata.getPages && data.pages.length > 0) newhtml += '</div><div class="row-container"><div class="col-header blue-bg"><?=$lang['Pages']?></div>';
			else newhtml += '</div>';
			gray = false;
			
			//if in managing comic pages, output the pages
			if(postdata.getPages){
				for(var i = 0; i < data.pages.length; i++){
					newhtml += '<div class="zebra-row';
					if(gray == true){ 
						newhtml += ' gray-bg';
						gray = false;
					}
					else gray = true;
					newhtml += '"><div class="row-title">' + data.pages[i].title + '</div><a href="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/delete-post/'?>' + data.pages[i].slug + '"><?=$lang['Delete']?></a><a href="<?=$ccurl . $navslug . '/' . $ccpage->slug . '/edit-post/'?>' + data.pages[i].slug + '"><?=$lang['Edit']?></a><div style="clear:both"></div></div>';
				}
			}
		}
		
		//close out new html for the container
		newhtml += '</div><div style="height:1px">&nbsp;</div>';
		
		//create div that can slide for the new information
		var $newdiv = $('<div class="slideover"></div>');
		$newdiv.html(newhtml);
		
		//if the container was empty, just append the new content
		if($('.manage-container').html() == ""){
			$('.manage-container').append($newdiv);
		}
		
		//otherwise, append/prepend it and slide left or right depending on direction
		else{
			
			//get the current data and set the height of the container equal to it so no weirdness happens when making it absolute positioned
			var $olddiv = $('.manage-container').find('.slideover');
			$('.manage-container').css('height',$('.manage-container').height());
			$olddiv.css('position','absolute');
			
			//set css for new data based on which way we're sliding
			$newdiv.css('position','absolute');
			if(postdata.dir == 'down') $newdiv.css('left','100%');
			else $newdiv.css('left','-100%');
			
			//append or prepend based on slide direction
			if(postdata.dir == 'down') $('.manage-container').append($newdiv);
			else $('.manage-container').prepend($newdiv);
			
			//animate the container size changing
			$('.manage-container').animate({
				height:$newdiv.height()
			}, { duration:200, queue: true });
			var oldleft = "-100%";
			if(postdata.dir == 'up'){
				oldleft = "100%";
			}
			
			//animate the slide
			$(function () {
				$olddiv.animate({
				   left:oldleft
				}, { duration: 200, queue: false });

				$newdiv.animate({
				   left:0
				}, { duration: 200, queue: false });
			});
			
			if(postdata.type == "storyline"){
				//set button to new storyline
				$('input[name=storyline]').val(postdata.searchid);
				if(postdata.getPages) $('.addstoryline').html('<?=$lang['Add a comic to %s']?>'.replace('%s',postdata.storylinename));
				else $('.addstoryline').html('<?=$lang['Add a storyline to %s']?>'.replace('%s',postdata.storylinename));
			}
			
			//get rid of the old information
			$olddiv.remove();
			
		}
		
		//trigger changeContainer if a storyline is clicked
		$('.storyline').on("click", function(){
			var newdata = {
				searchid: $(this).data('storyline'),
				storylinename: $(this).data('name'),
				getPages:postdata.getPages,
				type: postdata.type,
				dir: "down",
				moduleid:postdata.moduleid
			}
			changeContainer(newdata);
		});
		
		//trigger changeContainer if arrow is clicked
		$('.back-arrow').on("click",function(){
			var newdata = {
				searchid: $(this).data('parent'),
				moduleid: <?=$ccpage->module->id?>,
				storylinename: $(this).data('parentname'),
				getPages:postdata.getPages,
				type: postdata.type,
				dir: "up",
				moduleid:postdata.moduleid
			}
			changeContainer(newdata);
		})
		
		<?
		
		$searchid = 0;
		if($ccpage->module->id != "") $searchid = $ccpage->module->id;
		
		?>
		
		//trigger changeContainer if arrow is clicked
		$('.next-page').on("click", function(){
			var newdata = {
				page: $(this).data('page'),
				searchid: <?=$searchid?>,
				type: postdata.type,
				dir: "down"
			}
			changeContainer(newdata);
		});
		
		//trigger changeContainer if arrow is clicked
		$('.prev-page').on("click",function(){
			var newdata = {
				page: $(this).data('page'),
				searchid: <?=$searchid?>,
				type: postdata.type,
				dir: "up"
			}
			changeContainer(newdata);
		})
		
	});
}

</script>