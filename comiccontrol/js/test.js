class TestIt{
	constructor(){
		this.name = "Erin";
		name = "whatchamacallit";
		this.templatestring = `My name is ${this.name}`;
	}
	tester(newname){
		let name = newname;
		let newtemplate = this.templatestring;
		console.log(newtemplate);
	}
}

let thistest = new TestIt();
thistest.tester("Alvina");