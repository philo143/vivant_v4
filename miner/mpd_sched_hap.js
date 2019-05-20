// MARKET PROJECTIONS DISPLAYS -> SCHEDULES (HAP)
function waitFor(testFx, onReady, timeOutMillis) {
    var maxtimeOutMillis = timeOutMillis ? timeOutMillis : 30000, //< Default Max Timout is 3s
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
    var maxtimeOutMillis = timeOutMillis ? timeOutMillis : 333333, //< Default Max Timout is 3s
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
var server_ip = '112.199.90.172';
var page = require('webpage').create();
var system = require('system');
var moment = require('../node_modules/moment');
var args = system.args; // [participant] [cert_user:cert_pass] [datefrom or “last”] [dateto]
var fs = require('fs'); // NODE FILESYSTEM
var script_loc = fs.workingDirectory+"/miner/";
var address = 'https://'+server_ip+'/mpi/logon.do';
var participant = args[1];
var credentials = args[2].split(':');  
// TO SHOW CONSOLE MESSAGES //
page.onConsoleMessage = function(msg, lineNum, sourceId) {
  console.log('CONSOLE: ' + msg + ' (from line #' + lineNum + ' in "' + sourceId + '")');
  var match = msg.match(/Initializing Pushlet - setServer:/g);
  if(match != null){
  	exitApp = setTimeout(function(){
  		phantom.exit();
  	},10000)
  	// console.log(msg+'===================================');
  	// phantom.exit();
  }
};
// INITIAL / LOGIN PAGE
page.clearMemoryCache();
page.open(address, function (status) {
	// page.settings.loadImages = false;
	page.settings.resourceTimeout = 500000;
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
	var last = fs.exists(script_loc+'MPD_SCHED_HAP_'+participant+'_LastData.txt') ? fs.read(script_loc+'MPD_SCHED_HAP_'+participant+'_LastData.txt') : fs.write(script_loc+'MPD_SCHED_HAP_'+participant+'_LastData.txt',new Date(),'w');
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
  		newPage.includeJs("http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js",function(){
  			waitFor(function(){
  				return newPage.evaluate(function(){
  					return $('#mRightBodyFrame').hasClass('wireframeContent');
  				})
  			},function(){
  				newPage.evaluate(function(){
		  			document.getElementById('Menu.xMoarDa').click(); // Market Result Menu
		  		})
		  		waitFor(function(){
		  			return newPage.evaluate(function(){
		  				if($('#NAV_CELL_TITLE tbody tr td:nth-child(5)').text() == 'Regional Summary'){
		  					return true;
		  				}
		  				return false;	  				
		  			})
		  		},function() {
		  			clearTimeout(phantomTimeout);
		  			// newPage.render('regional_summary.png');
		  			newPage.evaluate(function(){
			  			document.getElementById('DropdownMenu.xMoarDa.moarOutputDisplays.xMoarScheds').click(); // <-- Market Projections Displays -> Schedules
			  		})
		  			waitFor(function(){
			  			return newPage.evaluate(function(){
			  				if($('#progressBar').css('opacity') == '0' && $('#NAV_CELL_TITLE tbody tr td:nth-child(5)').text() == 'Schedules'){
			  					return true;
			  				}
			  				return false;
			  			})
			  		},function(){
			  			
			  			// var d_file = fs.read('RTD_ResSpec_LastData.txt') // file for last date and time of captured data
						// if(d_file.length == 0){
						// 	var d = new Date();
						// }else{
						// 	var d = new Date(d_file);
						// }
							var last_d = start_date ? new Date(start_date) : new Date();	
							var e_date = end_date ? new Date(end_date) : '';
							last = new Date(last);
							// last.setMinutes(last.getMinutes() + 5);
							var retry = 1;
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
				  				console.log(output);
			  					newPage.evaluate(function(output){
			  						$('#PFC_TradeDateStart input').val(output);
						  				console.log('INSERT VALUE FOR PFC DATE') // WRITE DATE ON INPUT
						  			document.getElementById('ApplyButton').click();
						  				console.log('CLICK APPLY BUTTON'); // CLICK APPLY BUTTON
			  					},output)
		  						waitFor(function(){				
			  						return newPage.evaluate(function(output,last_d){
						  				if($('#progressBar').css('opacity') == '0' && $("#eventingIcon").hasClass("WFConnectedIcon") && $('.rowSelectionMode tbody tr:first-child td:first-child span').text() != ''){
						  					return true;
						  				}
						  				latest_d = new Date();
						  				if($('#progressBar').css('opacity') == '0' && $("#eventingIcon").hasClass("WFConnectedIcon") && (latest_d > last_d)){
								  			return true;	
								  		}
						  				return false;
						  			},output,last_d)
			  					},function(){
			  						
						  			// newPage.render('mpd-lmps.png');
			  						var xls_data = null;
			  						var dl_status = null;	
			  						var result = newPage.evaluate(function(server_ip) {
			  							console.log('RESULT STARTED!')
			  							if($('div[id*="EXPORT_ICON"]').hasClass('exportEnabled')){
			  								var display_id = $('#mRightBodyFrame input[id="pageId"]').val();
				  							var match = $('div[id*="EXPORT_ICON"]').attr('id').match(/GRID_(.*?)_EXPORT_ICON/);
				  							var dbcID = match[1];
				  							var date = new Date().getTime()/1000;
											date = date.toString().replace('.','');
				  							var uid = $('#'+display_id+' input[id="uid"]').val();
				  							var url_path = 'https://'+server_ip+'/mpi/displayExport.do?displayID='+display_id+'&type=excelPopup&dbcID=GRID_'+dbcID+'&exportAll=all&feedbackType=FOOTER_PROGRESS_BAR&feedbackId=FOOTER_PROGRESS_BAR_'+date+'&uid='+uid;		  							
										    var out = null;
										    $.ajax({
										        'async' : true,
										        'url' : url_path,
										        xhrFields: {
											    	withCredentials: true
											  	},
										        'success' : function(data, status, xhr) {									        									        
										        	window.callPhantom([data,status]);
										        }									        
										    });
										    // return out;
			  							}else{
			  								return false;
			  							}		  															    
									},server_ip);
									newPage.onCallback = function(data){
										xls_data = data[0];
										dl_status = data[1];
									}
									latest_d = new Date();
									if(result != false){
										waitForData(function(){										
											if(xls_data != null){
												return true;
											}
										},function(){		
											if(xls_data && dl_status == 'success'){// SAVE AND CONTINUE
												retry = 1;
					  							fs.write(script_loc+'mms_mpd/MPD_SCHED_HAP_'+participant+'_'+last_output+'.xls', xls_data,'w');
					  							if(last < last_d){
								  					fs.write(script_loc+'MPD_SCHED_HAP_'+participant+'_LastData.txt',last_d,'w');
								  				}
								  				last_d.setMinutes(last_d.getMinutes() + 5);	
								  				setTimeout(data_interval,3000);
											}else{ // RETRY
												if(retry != 5){
													console.log("Redownloading File");
													retry++;			
													setTimeout(data_interval,5000);					
												}else{ // NO DATA OR ERROR DOWNLOADING FILE
													retry = 1;		  							
									  				if(latest_d > last_d){
							  							fs.write(script_loc+'mms_mpd/no_data/no_data/MPD_SCHED_HAP_'+participant+'_'+last_output+'_NO_DATA.json','NO DATA','w');						  							
											  			last_d.setMinutes(last_d.getMinutes() + 5);	
											  		}	
											  		setTimeout(data_interval,15000);	
												}
											}										
										},333333)
									}else{ // HAS NO DATA result == false
										retry = 1;		  							
						  				if(latest_d > last_d){
				  							fs.write(script_loc+'mms_mpd/no_data/MPD_SCHED_HAP_'+participant+'_'+last_output+'_NO_DATA.json','NO DATA','w');				  							
								  			last_d.setMinutes(last_d.getMinutes() + 5);	
								  		}	
								  		setTimeout(data_interval,15000);
									}
									if(e_date != '' && last_d >= e_date){
						  				console.log('END')
						  				phantom.exit();
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





