// status checker
var page = require('webpage').create();
var system = require('system');
var os = system.os;
var moment = require('../node_modules/moment');
var args = system.args; // [SERVER URL]
var script_loc = os.name == 'windows' ? 'C:/var/miner/' : '/var/miner/';
var svr_url = args[1]
var fs = require('fs'); // NODE FILESYSTEM

///url/{participant}/{bid_id}/{resource}/{ip}//

setTimeout(function(){
	phantom.exit();
},300000)

var retrieve_data = function(){
	var path = script_loc+'offers/failed_retrieve/';
	var files = fs.list(path).toString();
	var matches = files.match(/failed\w+.json/g);
	// console.log(path+file);
	if(matches){
		if(fs.isFile(path+matches[0])){
			var file_contents = fs.read(path+matches[0]);
			p = file_contents.split(',');
			// var running = matches[0].replace('failed','running');

			// if(fs.exists(path+running)){
			// 	console.log("ALREADY RUNNING");
			// }else{
			page.open(svr_url+'/pubsub/retrieve_status/'+p[0]+'/'+p[1]+'/'+p[2],function(status){
				console.log(status);
				// page.close();
			})
			// }
			setTimeout(retrieve_data,5000)
			
		}
	}else{
		setTimeout(retrieve_data,3000)
		console.log("NO FAILED FILES")
	}
}
retrieve_data();
	
	



