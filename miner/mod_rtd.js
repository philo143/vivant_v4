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
                    console.log("'waitFor()' timeout");
                    phantom.exit(1);
                } else {
                    // Condition fulfilled (timeout and/or condition is 'true')
                    console.log("'waitFor()' finished in " + (new Date().getTime() - start) + "ms.");
                    clearInterval(interval);
                    typeof(onReady) === "string" ? eval(onReady) : onReady(); //< Do what it's supposed to do once the condition is fulfilled
                     //< Stop this interval
                }
            }
        }, 1000); //< repeat check every 250ms
};
function waitForData(testFx, onReady, timeOutMillis) {
    var maxtimeOutMillis = timeOutMillis ? timeOutMillis : 333333, //< Default Max Timout is 1min
        start = new Date().getTime(),
        condition = false,
        interval = setInterval(function() {
            if ( (new Date().getTime() - start < maxtimeOutMillis) && !condition ) {
                // If not time-out yet and condition not yet fulfilled
                condition = (typeof(testFx) === "string" ? eval(testFx) : testFx()); //< defensive code
            } else {
                if(!condition) {
                    // If condition still not fulfilled (timeout but condition is 'false')
                    console.log("'waitFor()' timeout");
                    phantom.exit(1);
                } else {
                    // Condition fulfilled (timeout and/or condition is 'true')
                    console.log("'waitFor()' finished in " + (new Date().getTime() - start) + "ms.");
                    clearInterval(interval);
                    typeof(onReady) === "string" ? eval(onReady) : onReady(); //< Do what it's supposed to do once the condition is fulfilled
                     //< Stop this interval
                }
            }
        }, 3000); //< repeat check every 250ms
};
var phantomTimeout = setTimeout(function(){
	phantom.exit();
},240000);
var exitApp = '';

function hasClass( elem, klass ) {
     return (" " + elem.className + " " ).indexOf( " "+klass+" " ) > -1;
}

// setTimeout(exitApp,120000) // 2 MINS TIMEOUT 

var page = require('webpage').create();
var system = require('system');
var moment = require('../node_modules/moment');
var args = system.args; // [participant] [cert_user:cert_pass] [datefrom or “last”] [dateto]
var fs = require('fs'); // NODE FILESYSTEM
var script_loc = fs.workingDirectory+"/miner/";
var address = 'https://112.199.90.171/mpi/logon.do'; // [IP] TO FOLLOW NEED DYNAMIC IP
var participant = args[1];
var credentials = args[2].split(':');
// TO SHOW CONSOLE MESSAGES //
page.onConsoleMessage = function(msg, lineNum, sourceId) {
  console.log('CONSOLE: ' + msg + ' (from line #' + lineNum + ' in "' + sourceId + '")');
  var match = msg.match(/Initializing Pushlet - setServer:/g);
  if(match != null){
  	exitApp = setTimeout(function(){
  		phantom.exit();
  	},15000)
  	// console.log(msg+'===================================');
  	// phantom.exit();
  }
};
// Resource request error: QNetworkReply::NetworkError(HostNotFoundError) ( "Host pum1pemc.177.47.91 not found" ) URL: "https://pum1pemc.177.47.91/mpi/receiveEvents.do?
// INITIAL / LOGIN PAGE
page.clearMemoryCache();
page.open(address, function (status) {
	// page.settings.loadImages = false;
	page.settings.resourceTimeout = 500000;
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
        console.log('Unable to access page');
        phantom.exit();
    } else {
    	page.includeJs("http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js", function() {
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
page.onPageCreated = function(newPage) {
	newPage.settings.loadImages = false;
	newPage.settings.resourceTimeout = 500000;
	clearTimeout(exitApp);
	newPage.onResourceError = function(resourceError) {
		var match = msg.match(/"Host pum1pemc.177.47.91 not found"/g);
	  	if(match != null){
	  		console.log("UNABLE TO RE-CONNECT");
	  		phantom.exit();	  
	  	}
	};
	newPage.settings.userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36';
	var last = fs.exists(script_loc+'RTD_ResSpec_'+participant+'_LastData.txt') ? fs.read(script_loc+'RTD_ResSpec_'+participant+'_LastData.txt') : fs.write(script_loc+'RTD_ResSpec_'+participant+'_LastData.txt',new Date(),'w');
	if(args[3] == 'last'){
		args[3] = last;
		 // file for last date and time of captured data
	}
	var start_date = args[3] ? moment(args[3]) : '' ;
	var end_date = args[4] ? moment(args[4]) : '' ;
	newPage.settings.userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36';

	// CHECK IF POPUP WAS CREATED
  	console.log('A new child page was created! Its requested URL is not yet available, though.');
  	page.stop();
  	newPage.viewportSize = {
	  width: 1280,
	  height: 800
	};
  	setTimeout(function(){
  		// newPage.render(script_loc+'regional_summary.png');
  		newPage.includeJs("http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js",function(){
  			waitFor(function(){
  				return newPage.evaluate(function(){
  					return $('#mRightBodyFrame').hasClass('wireframeContent');
  				})
  			},function(){
  				newPage.evaluate(function(){
		  			document.getElementById('Menu.xMoarDa').click(); // Market Data Menu
		  		})
		  		waitFor(function(){
		  			return newPage.evaluate(function(){
		  				if($('#NAV_CELL_TITLE tbody tr td:nth-child(5)').text() == 'Regional Summary'){
		  					console.log('IT IS RESOURCE SPECIFIC')
		  					return true;
		  				}
		  				console.log('ITS NOT!!!!!!!!!!! RESOURCE SPECIFIC')
		  				return false;	  				
		  			})
		  		},function() {
		  			clearTimeout(phantomTimeout);
		  			// newPage.render(script_loc+'regional_summary.png');
		  			newPage.evaluate(function(){
		  			// document.getElementById('DropdownMenu.xMoarDa.moarRtdOutputDisplays.xRtdRegional').click(); // <-- RTD REGIONAL SUMMARY LINK
			  			document.getElementById('DropdownMenu.xMoarDa.moarRtdOutputDisplays.xRtdResourceSpecific').click(); // <-- RTD Resource Specific LINK
			  		})
		  			waitFor(function(){
			  			return newPage.evaluate(function(){
			  				if($('#progressBar').css('opacity') == '0' && $('#NAV_CELL_TITLE tbody tr td:nth-child(5)').text() == 'Resource-Specific'){
			  					return true;
			  				}
			  				return false;
			  			})
			  		},function(){
			  			// newPage.render(script_loc+'resource_specific.png');
			  			// var d_file = fs.read('RTD_ResSpec_LastData.txt') // file for last date and time of captured data
						// if(d_file.length == 0){
						// 	var d = new Date();
						// }else{
						// 	var d = new Date(d_file);
						// }
							var last_d = start_date ? new Date(start_date) : new Date();
							var e_date = end_date ? new Date(end_date) : '';
							last = new Date(last);
							last.setMinutes(last.getMinutes() + 5);
				  			var data_interval = function(){
				  				console.log("INTERVAL STARTED");
								var month = last_d.getMonth()+1;
								var day = last_d.getDate();
								var hour = last_d.getHours();
								var minute = last_d.getMinutes();
								var min = Math.ceil(minute/5)*5-5;
								if(hour == 24){
									hour = 00;
								}
								if(min == 60){
									hour = hour+1;
									min = 0;
								}
								if(min == -5){
									min = 0;
								}

								var output =  ((''+month).length<2 ? '0' : '') + month + '/' +
								    		  ((''+day).length<2 ? '0' : '') + day + '/' 
								    		  + last_d.getFullYear()+' '+ ((''+hour).length<2 ? '0' : '') + hour + ':' + ((''+min).length<2 ? '0' : '') + min;

				  				var last_output = last_d.getFullYear()+''+month+''+day+''+((''+hour).length<2 ? '0' : '') + hour+''+((''+min).length<2 ? '0' : '')+min;

			  					newPage.evaluate(function(output){
			  						$('#PFC_TradeDateStart input').val(output);
						  				console.log('INSERT VALUE FOR PFC DATE') // WRITE DATE ON INPUT
						  			document.getElementById('ApplyButton').click();
						  				console.log('CLICK APPLY BUTTON'); // CLICK APPLY BUTTON
			  					},output)
			  					waitForData(function(){		  						
			  						return newPage.evaluate(function(output,last_d){
						  				if($('#progressBar').css('opacity') == '0' && $("#eventingIcon").hasClass("WFConnectedIcon") && $('.rowSelectionMode tbody tr:first-child td:first-child span').text() == output){
						  					return true;
						  				}
						  				latest_d = new Date();
						  				if($('#progressBar').css('opacity') == '0' && $("#eventingIcon").hasClass("WFConnectedIcon") && (latest_d > last_d)){
								  			return true;	
								  		}
						  				return false;
						  			},output,last_d)
			  					},function(){

						  			var new_data = newPage.evaluate(function(){
					  				var data_array = Array();
					  				var data_count = 0;
									$('.rowSelectionMode tbody tr').each(function(){
										var td = this.getElementsByTagName('td');
										data_array[data_count] = Array();
										$.each(td,function(tr,td){
											data_array[data_count].push($(td).text())
										})
										data_count++;
									})
									return JSON.stringify(data_array);
						  			})
						  			console.log("LATEST OUTPUT IS : "+last_output) // LAST DATE INPUT
						  			// newPage.render(script_loc+'last-data.png'); // SCREENSHOT OF PAGE
						  			if(new_data.length > 2){
						  				fs.write(script_loc+'mms_mod/RTD_ResSpec_'+participant+'_'+last_output+'.json',new_data,'w');
						  				if(last < last_d){
						  					fs.write(script_loc+'RTD_ResSpec_'+participant+'_LastData.txt',last_d,'w');
						  				}
							  			if(e_date != '' && last_d >= e_date){
							  				console.log('END')
							  				phantom.exit();
							  			}
						  				last_d.setMinutes(last_d.getMinutes() + 5);
						  				setTimeout(data_interval,1000); // rerun waitForData after it is finished -- changed setInterval to this to prevent multiple executions							  				
						  			}else{
						  				latest_d = new Date();
						  				if(latest_d > last_d){
						  					fs.write(script_loc+'mms_mod/no_data/RTD_ResSpec_'+participant+'_'+last_output+'_NO_DATA.json','NO DATA','w');
						  					if(e_date != '' && last_d >= e_date){
								  				console.log('END')
								  				phantom.exit();
								  			}
							  				last_d.setMinutes(last_d.getMinutes() + 5);
							  				console.log(last_d);
						  				}
						  				setTimeout(data_interval,15000); // rerun waitForData after it is finished -- changed setInterval to this to prevent multiple executions
						  			}
							  		
			  					})				  				
				  			}
				  			data_interval();  		
			  		});
		  		})	  		
  			})  			
  		})
  	},30000)
};





