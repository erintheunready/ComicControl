class Container {
	constructor(moduleid=1,getPages=true,type="comic",searchid=0){
		this.moduleid = moduleid;
		this.getPages = getPages;
		this.type = type;
		this.searchid = searchid;
		this.name = "Top Level";
		this.pageroot = 'http://www.metacarpolis.com/';
		this.ccroot = 'comiccontrol/';
		this.navslug = 'modules';
		this.pageslug = 'comic';
		
		this.rowBuilder;
		switch(type){
			case "gallery":
				this.rowBuilder = galleryRows;
				break;
			case "images":
				this.rowBuilder = imagesRows;
				break;
			case "blog":
				this.rowBuilder = blogRows;
				break;
			default:
				this.rowBuilder = comicRows;
				break;
		}
	}
	
	call(searchid){
		let postdata = {
			moduleid:this.moduleid,
			searchid:this.searchid,
			name:this.name,
			getPages:this.getPages,
			type:this.type
		};
		$.post("../ajax/get-page.php",postdata,this.change(data));
	}
	change(data){
		let $newDiv = $('<div class="row-container"></div>');
		
		//format the rows
		$newDiv = this.getRows($newDiv,data);
		//add in navigation arrows
		$newDiv = this.addArrows($newDiv,data);
		
	}
	getRows($div,data){
		let $newDiv = $div.clone();
		let rows = data.results.map(this.rowBuilder);
		let gray = false;
		rows.forEach( function($row){
			if(gray){
				$row.addClass('gray-bg');
				gray = false;
			}else{
				gray = true;
			}
			$newDiv = $newDiv.append($row);
		});
		return $newDiv;
	}
	addArrows($div,data){
		
	}
	galleryRows(entry){
		let $rowDiv = $('<div class="zebra-row"></div>');
		$rowDiv.append(`<div class="row-img"><img src="${this.pageroot}uploads/${entry.thumbname}" /></div>`);
		$rowDiv.append(`<div class="row-caption">${entry.caption}</div>`);
		$rowDiv.append(`<a href="${this.pageroot}${this.ccroot}${this.navslug}/${this.pageslug}/delete-image/${entry.id}">Delete</a>`);
		$rowDiv.append(`<a href="${this.pageroot}${this.ccroot}${this.navslug}/${this.pageslug}/edit-image/${entry.id}">Edit</a>`);
		return $rowDiv;
	}
	imagesRows(entry){
		
	}
}