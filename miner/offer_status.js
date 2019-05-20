var page = require('webpage').create();
var system = require('system');
var moment = require('../node_modules/moment');
var args = system.args; // [IP] [participant] [cert_user:cert_pass] [transaction id] [RESOURCE] [SERVER_URL] /SNAP/2411405/01MAGAT_U01/112.199.90.171
var fs = require('fs'); // NODE FILESYSTEM
var os = system.os; // CHECK PLATFORM/
var script_loc = os.name == 'windows' ? 'C:/var/miner/' : '/var/miner/';
var address = 'https://'+args[1]+'/mpi/logon.do';
var participant = args[2];
var credentials = args[3].split(':');
var trans_id = args[4];
var resource_id = args[5];
var svr_url = args[6];

if(fs.exists(script_loc+'offers/failed_retrieve/failed_'+participant+'_'+trans_id+'.json')){
	setTimeout(
		fs.remove(script_loc+'offers/failed_retrieve/failed_'+participant+'_'+trans_id+'.json')
	,3000); // REMOVE FAILED
}

fs.write(script_loc+'offers/failed_retrieve/running_'+participant+'_'+trans_id+'.json',participant+','+trans_id+','+resource_id,'w'); // TO CHECK IF SCRIPT IS RUNNING


function waitFor(testFx, onReady, timeOutMillis) {
    var maxtimeOutMillis = timeOutMillis ? timeOutMillis : 120000, //< Default Max Timout is 3s
        start = new Date().getTime(),
        condition = false,
        interval = setInterval(function() {
            if ( (new Date().getTime() - start < maxtimeOutMillis) && !condition ) {
                // If not time-out yet and condition not yet fulfilled
                condition = (typeof(testFx) === "string" ? eval(testFx) : testFx()); //< defensive code
            } else {
                if(!condition) {
                    // If condition still not fulfilled (timeout but condition is 'false')
                    fs.write(script_loc+'offers/failed_retrieve/failed_'+participant+'_'+trans_id+'.json',participant+','+trans_id+','+resource_id,'w');
                    fs.remove(script_loc+'offers/failed_retrieve/running_'+participant+'_'+trans_id+'.json');
                    setTimeout(function(){
                	  console.log("'waitFor()' timeout");
                	  phantom.exit(1);
                    },3000)
                  
                } else {
                    // Condition fulfilled (timeout and/or condition is 'true')
                    console.log("'waitFor()' finished in " + (new Date().getTime() - start) + "ms.");
                    clearInterval(interval);
                    typeof(onReady) === "string" ? eval(onReady) : onReady(); //< Do what it's supposed to do once the condition is fulfilled
                     //< Stop this interval
                }
            }
        }, 4000); //< repeat check every 1s
};
function waitForData(testFx, onReady, timeOutMillis) {
    var maxtimeOutMillis = timeOutMillis ? timeOutMillis : 120000, //< Default Max Timout is 1min
        start = new Date().getTime(),
        condition = false,
        interval = setInterval(function() {
            if ( (new Date().getTime() - start < maxtimeOutMillis) && !condition ) {
                // If not time-out yet and condition not yet fulfilled
                condition = (typeof(testFx) === "string" ? eval(testFx) : testFx()); //< defensive code
            } else {
                if(!condition) {
                    // If condition still not fulfilled (timeout but condition is 'false')
                    fs.write(script_loc+'offers/failed_retrieve/failed_'+participant+'_'+trans_id+'.json',participant+','+trans_id+','+resource_id,'w');
                    fs.remove(script_loc+'offers/failed_retrieve/running_'+participant+'_'+trans_id+'.json');
                    setTimeout(function(){
                	  console.log("'waitForData()' timeout");
                	  phantom.exit(1);
                    },3000)
                } else {
                    // Condition fulfilled (timeout and/or condition is 'true')
                    console.log("'waitForData()' finished in " + (new Date().getTime() - start) + "ms.");
                    clearInterval(interval);
                    typeof(onReady) === "string" ? eval(onReady) : onReady(); //< Do what it's supposed to do once the condition is fulfilled
                     //< Stop this interval
                }
            }
        }, 5000); //< repeat check every 5s
};
var phantomTimeout = setTimeout(function(){
	phantom.exit();
},240000);
var exitApp = '';

function hasClass( elem, klass ) {
     return (" " + elem.className + " " ).indexOf( " "+klass+" " ) > -1;
}

// setTimeout(exitApp,120000) // 2 MINS TIMEOUT 
// var redis = require('ioredis');

// TO SHOW CONSOLE MESSAGES //
page.onConsoleMessage = function(msg, lineNum, sourceId) {
  console.log('CONSOLE: ' + msg + ' (from line #' + lineNum + ' in "' + sourceId + '")');
  var match = msg.match(/Initializing Pushlet - setServer:/g);
  if(match != null){
  	exitApp = setTimeout(function(){
  		fs.write(script_loc+'offers/failed_retrieve/failed_'+participant+'_'+trans_id+'.json',participant+','+trans_id+','+resource_id,'w');
  		fs.remove(script_loc+'offers/failed_retrieve/running_'+participant+'_'+trans_id+'.json');
  		setTimeout(function(){
    	  phantom.exit(1);
        },3000)
  	},10000)
  	// console.log(msg+'===================================');
  	// phantom.exit();
  }
};
// Resource request error: QNetworkReply::NetworkError(HostNotFoundError) ( "Host pum1pemc.177.47.91 not found" ) URL: "https://pum1pemc.177.47.91/mpi/receiveEvents.do?
// INITIAL / LOGIN PAGE
page.open(address, function (status) {
	page.onConsoleMessage = function(msg, lineNum, sourceId) {
		  system.stderr.writeLine( 'console: ' + msg );
	};
	if(page.injectJs(script_loc+'eventing.js')){
		console.log("EVENTING IS INCLUDED");
	};
	if(page.injectJs(script_loc+'setTopWindow.js')){
		console.log("setTopWindow IS INCLUDED");
	};
	page.settings.userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36';
    if (status !== 'success') {
    	fs.write(script_loc+'offers/failed_retrieve/failed_'+participant+'_'+trans_id+'.json',participant+','+trans_id+','+resource_id,'w');
  		fs.remove(script_loc+'offers/failed_retrieve/running_'+participant+'_'+trans_id+'.json');
        console.log('Unable to access page');
        setTimeout(function(){
    	  phantom.exit(1);
        },3000)
    } else {
    	page.includeJs("https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js", function() {
    		waitFor(function() {
        		return page.evaluate(function() { 	
            		return $("#eventingIcon").hasClass("WFConnectedIcon");
        		});
	        },function(){
	        		
	        		// page.render(script_loc+'1st-page-rtd.png');
		        	var p = page.evaluate(function(credentials) {  
		        			document.getElementById('username').value = credentials[0];
		        			document.getElementById('password').value = credentials[1];
		        			$('input[value="Submit"]').click();					
		        	},credentials);
	        	}
	        );
	    });
    }
});
var status_arr = "";
page.onPageCreated = function(newPage) {
	clearTimeout(exitApp);
	newPage.onResourceError = function(resourceError) {
		var match = msg.match(/"Host pum1pemc.177.47.91 not found"/g);
	  	if(match != null){
	  		fs.write(script_loc+'offers/failed_retrieve/failed_'+participant+'_'+trans_id+'.json',participant+','+trans_id+','+resource_id,'w');
	  		fs.remove(script_loc+'offers/failed_retrieve/running_'+participant+'_'+trans_id+'.json');
	  		setTimeout(function(){
        	  console.log("'RESOURCE ERROR");
        	  phantom.exit(1);
            },3000)  
	  	}
	};
	newPage.onConsoleMessage = function(msg, lineNum, sourceId) {
  		console.log('CONSOLE: ' + msg + ' (from line #' + lineNum + ' in "' + sourceId + '")');
	};
	newPage.settings.userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36';

	// CHECK IF POPUP WAS CREATED
  	console.log('A new child page was created! Its requested URL is not yet available, though.');
  	page.stop();
  	newPage.viewportSize = {
	  width: 3000,
	  height: 800
	};
  	setTimeout(function(){
  		newPage.includeJs("https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js",function(){
  			waitFor(function() {
        		return newPage.evaluate(function() {
        			var table_id = $('span:contains("Bid Status Audit Log")').attr('id').match(/GRID_(.*?)_/);
        			table_id = table_id[1];
		  			$('#GRID_'+table_id+'_FILTER_ICON').click();
		  			if(document.getElementById("progressBar").style.opacity == 0 && table_id){
		  				return true;
		  			}else{
		  				return false;
		  			}
		  			console.log(table_id);
        		})
	        },function(){
	        	
	        	waitForData(function(){
	        		return newPage.evaluate(function(trans_id,resource_id) {
	        			var table_id = $('span:contains("Bid Status Audit Log")').attr('id').match(/GRID_(.*?)_/);
        				table_id = table_id[1];
	        			$('#GRID_'+table_id+'_INLINE_FILTER_log_bid_id').val(trans_id);
	        			// var input = document.querySelector('#NewMaster1_INLINE_FILTER_bs_bid_id')
	        			var ev = document.createEvent('Event');
	        			ev.initEvent('keypress');
						ev.which = ev.keyCode = 13;

						document.getElementById('GRID_'+table_id+'_INLINE_FILTER_log_bid_id').dispatchEvent(ev);
						// if($('#NewMaster1_INLINE_FILTER_bs_bid_id').val() == '2413125' && document.getElementById("progressBar").style.opacity == 0){
	        			var tr_id = $('#GRID_'+table_id+'_DATA_TABLE_DATA_BODY tbody tr.CVTaRow td:nth-child(5):contains("Submitted")').closest('tr').attr('id');
	        			if($('#'+tr_id+' td:nth-child(4)').text() == trans_id 
	        				&& ($('#'+tr_id+' td:nth-child(3)').text() == resource_id) 
	        				&& ($('#'+tr_id+' td:nth-child(6)').text() == 'Valid') 
	        				|| $('#'+tr_id+' td:nth-child(6)').text() == 'Invalid'){
        					return true;
	        			}else{
	        				return false;
	        			}
	        		},trans_id,resource_id)
	        	},function(){
	        		var data = newPage.evaluate(function(participant){
	        			var table_id = $('span:contains("Bid Status Audit Log")').attr('id').match(/GRID_(.*?)_/);
        				table_id = table_id[1];
	        			var tr_id = $('#GRID_'+table_id+'_DATA_TABLE_DATA_BODY tbody tr.CVTaRow td:nth-child(5):contains("Submitted")').closest('tr').attr('id');
	        			var status = $('#'+tr_id+' td:nth-child(6)').text();
        				var date = $('#'+tr_id+' td:nth-child(1)').text();
        				// var type = $('#'+tr_id+' td:nth-child(3)').text();
        				var resource = $('#'+tr_id+' td:nth-child(3)').text();
        				var bid_id = $('#'+tr_id+' td:nth-child(4)').text();
        				// var m_participant = $('#'+tr_id+' td:nth-child(17)').text();
        				// var t_participant = $('#'+tr_id+' td:nth-child(18)').text();
        				// var submitted = $('#'+tr_id+' td:nth-child(19)').text();
        				// var source = $('#'+tr_id+' td:nth-child(20)').text();
    					status_arr ={"status": status,
    								 "date": date,
    								// {"type": type},
    								 "bid_id": bid_id,
    								 "resource": resource,
    								// {"m_participant": m_participant},
    								 "participant": participant};
    								// {"submitted": submitted},
    								// {"source": source}];
    								
    					return JSON.stringify(status_arr)
	        		},participant)
	        		waitFor(function(){
	        			fs.write(script_loc+'offers/status_results/'+participant+'_'+trans_id+'.json',data,'w');
	        			return true;
	        		},function(){
                var success = null;
                newPage.evaluate(function(trans_id,svr_url,participant){
                    $.get(svr_url+'/pubsub/offer_status/'+participant+'/'+trans_id,function(){
                        window.callPhantom('Success!')
                      })                    
                },trans_id,svr_url,participant)
                newPage.onCallback = function(data){
                  success = data;
                }
	        			waitForData(function(){
	        				if(success != null){
                     return true;
                  }else{
                     return false;
                  }
	        			},function(){
	        				setTimeout(function(){
	        					// fs.write('../miner/offers/status_results/'+participant+'_'+trans_id+'.json',data,'w');
	        					fs.remove(script_loc+'offers/failed_retrieve/running_'+participant+'_'+trans_id+'.json');
	        					setTimeout(function(){
						    	  phantom.exit(1);
						        },3000)
	        				},4000)
	        				
	        			})
	        		})
	        		
	        	})
	        })

  		})
  	},30000)
}; 





