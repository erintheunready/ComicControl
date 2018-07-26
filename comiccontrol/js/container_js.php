class Container {
	constructor(moduleid=1,getPages=true,type="storyline",searchid=0){
		this.moduleid = moduleid;
		this.getPages = getPages;
		this.type = type;
		this.searchid = searchid;
		this.name = "Top Level";
	}
	
	change(dir,searchid){
		let postdata = {
			searchid:this.searchid,
			name:
		}
		$.post("../ajax/get-page.php",
	}
}