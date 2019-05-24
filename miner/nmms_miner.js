function waitFor(testFx, onReady, timeOutMillis) {
    var maxtimeOutMillis = timeOutMillis ? timeOutMillis : 60000, //< Default Max Timout is 3s
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
    var maxtimeOutMillis = timeOutMillis ? timeOutMillis : 60000, //< Default Max Timout is 1min
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
var phantomTimeout = '';
var exitApp = '';
setTimeout(function(){ // phantom exit (for memory leak issue)
		phantom.exit();
},3600000)

function hasClass( elem, klass ) {
     return (" " + elem.className + " " ).indexOf( " "+klass+" " ) > -1;
}

var page = require('webpage').create();
var system = require('system');
var moment = require('../node_modules/moment');
var args = system.args; // [IP] [participant] [cert_user:cert_pass]
var fs = require('fs'); // NODE FILESYSTEM
var nmms_ip = args[1];
var script_loc = fs.workingDirectory+"/miner/";
var jquery_loc = fs.workingDirectory+"/jquery/jquery.js";
var address = 'https://'+nmms_ip+'/mpi/logon.do'; // [IP] TO FOLLOW NEED DYNAMIC IP
var participant = args[2];
var credentials = args[3].split(':');
// TO SHOW CONSOLE MESSAGES //
page.onConsoleMessage = function(msg, lineNum, sourceId) {
  console.log('CONSOLE: ' + msg + ' (from line #' + lineNum + ' in "' + sourceId + '")');
  var match = msg.match(/Initializing Pushlet - setServer:/g);
  if(match != null){
  	exitApp = setTimeout(function(){
  		phantom.exit();
  	},15000)
  }
};

page.clearMemoryCache();
// INITIAL / LOGIN PAGE
page.open(address, function (status) {
	page.settings.loadImages = true;
	// page.settings.resourceTimeout = 500000;
	page.onConsoleMessage = function(msg, lineNum, sourceId) {
		  system.stderr.writeLine( 'console: ' + msg );
	};
	if(page.injectJs(script_loc+'eventing.js')){
		console.log("EVENTING IS INCLUDED");
	};
	if(page.injectJs(script_loc+'setTopWindow.js')){ 
		console.log("setTopWindow IS INCLUDED");
	};
	page.includeJs("http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js", function() {
		waitFor(function() {
    		return page.evaluate(function() { 	
        		return $("#eventingIcon").hasClass("WFConnectedIcon");
    		});
        },function(){
        		page.injectJs(jquery_loc);
        		// page.render(script_loc+'1st-page-rtd.png');
        		phantomTimeout = setTimeout(function(){
					phantom.exit();
				},10000);
	        	var p = page.evaluate(function(credentials) {  
	        			document.getElementById('username').value = credentials[0];
	        			document.getElementById('password').value = credentials[1];
	        			$('input[value="Submit"]').click();					
	        	},credentials);
        	}
        );
    });
});
page.onPageCreated = function(mainPage) { // NMMS DATA MAIN POPUP/PAGE
	// mainPage.settings.loadImages = true; 
	mainPage.settings.resourceTimeout = 500000;
	clearTimeout(exitApp);
	clearTimeout(phantomTimeout);
	mainPage.onResourceError = function(resourceError) {
		var match = msg.match(/"Host pum1pemc.177.47.91 not found"/g);
	  	if(match != null){
	  		console.log("UNABLE TO RE-CONNECT");
	  		phantom.exit();	  
	  	}
	};

	// CHECK IF POPUP WAS CREATED
  	console.log('main page was created! Its requested URL is not yet available, though.');
  	// page.close();
  	mainPage.viewportSize = {
	  width: 1280,
	  height: 800
	};
	if(mainPage.injectJs(jquery_loc)){
		waitFor(function(){
			mainPage.injectJs(jquery_loc)
			return mainPage.evaluate(function(){
				return $('#mRightBodyFrame').hasClass('wireframeContent');
			})
		},function(){
			mainPage.evaluate(function(){
	  			document.getElementById('Menu.xMoarDa').click(); // Market Data Menu
	  		})
	  		waitFor(function(){
	  			return mainPage.evaluate(function(){
	  				if($('#NAV_CELL_TITLE tbody tr td:nth-child(5)').text() == 'Regional Summary'){
	  					console.log('IT IS RESOURCE SPECIFIC')
	  					return true;
	  				}
	  				console.log('Waiting for Resource Specific Text');
	  				return false;	  				
	  			})
	  		},function() {
	  			mainPage.evaluate(function(){
	  				top.displayCallUp('xMoarDa.moarOutputDisplays.xMoarScheds',1,'',{},'',null); // FOR HAP SCHEDULES
	  			})
	  			mainPage.evaluate(function(){
	  				top.displayCallUp('xMoarDa.moarRtdOutputDisplays.xRtdLMPs',1,'',{},'',null); // MOD LMP
	  			})
	  			mainPage.evaluate(function(){
	  				top.displayCallUp('xMoarDa.moarRtdOutputDisplays.xRtdResourceSpecific',1,'',{},'',null); // MOD RTD
	  			})
	  			mod_rtd_status = false;
	  			mod_lmp_status = false;
	  			mpd_sched_status = false;
	  			// OPEN PAGES FOR MINING

  				modRtdPage = mainPage.pages[0];
  				modLmpsPage = mainPage.pages[1];
  				mpdSchedPage = mainPage.pages[2];
  				
  				modRtdPage.onLoadFinished = function(status){
  					// modRtdPage.render('screenshots/mod_rtd_page.png');
						mod_rtd_status = true;
  				}
  				modLmpsPage.onLoadFinished = function(status){
  					// modLmpsPage.render('screenshots/mod_lmp_page.png');
  					mod_lmp_status = true;
  				}
  				mpdSchedPage.onLoadFinished = function(status){
					// mpdSchedPage.render('screenshots/mpd_sched_hap_page.png');
					mpd_sched_status = true;
  				}
	  			waitFor(function(){
	  				if(mod_rtd_status == true && mod_lmp_status == true && mpd_sched_status == true){
	  					return true;
	  				}
	  			},function(){
	  				modRtdPage.viewportSize = {
					  width: 1280,
					  height: 800
					};
					modLmpsPage.viewportSize = {
					  width: 1280,
					  height: 800
					};
					mpdSchedPage.viewportSize = {
					  width: 1280,
					  height: 800
					};
	  				// DATA MINING START errorMsg
	  				var latest_rtd_date = moment().minute(Math.ceil(moment().format('mm')/5)*5).subtract(5,'m');
	  				var latest_lmp_date = moment().minute(Math.ceil(moment().format('mm')/5)*5).subtract(5,'m');
	  				var latest_sched_hap_date = moment().minute(Math.ceil(moment().format('mm')/5)*5).subtract(5,'m');
	  				var latest_sched_dap_date = moment().minute(0).subtract(1,'h');

					var modRtd = function(){ // RTD MINER
				  		var rtd_latest_d = moment(new Date()).subtract(2,'m').format('YYYYMMDDHHmm'); // DATE TIME CHECKER WITH ALLOWANCE FOR LATE DATA
						var rtd_date = latest_rtd_date.format('MM/DD/YYYY HH:mm');
						var rtd_last_date = latest_rtd_date.format('YYYYMMDDHHmm');
						modRtdPage.injectJs(jquery_loc)
						modRtdPage.evaluate(function(rtd_date){
	  						$('#PFC_TradeDateStart input').val(rtd_date);
				  			document.getElementById('ApplyButton').click();
				  				console.log('CLICK APPLY BUTTON'); // CLICK APPLY BUTTON
	  					},rtd_date)
	  					waitForData(function(){
	  						modRtdPage.injectJs(jquery_loc)
	  						return modRtdPage.evaluate(function(){
				  				if($('#progressBar').css('opacity') == 0){
				  					return true;
				  				}
				  				return false;
				  			})
	  					},function(){
	  						modRtdPage.injectJs(jquery_loc)
				  			var new_data = modRtdPage.evaluate(function(){
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
				  			console.log("RTD LATEST OUTPUT IS : "+rtd_date) // LAST DATE INPUT
				  			if(new_data.length > 2){
				  				fs.write(script_loc+'mms_mod/RTD_ResSpec_'+participant+'_'+rtd_last_date+'.json',new_data,'w');
				  				console.log('FILE HAS BEEN SAVED '+rtd_last_date);
				  				latest_rtd_date.add(5,'m');
				  				setTimeout(modRtd,1000); // rerun waitForData after it is finished -- changed setInterval to this to prevent multiple executions							  				
				  			}else{
				  				console.log('==== GREATER THAN '+rtd_latest_d+' > '+rtd_last_date)
				  				if(rtd_latest_d > rtd_last_date){
				  					fs.write(script_loc+'mms_mod/no_data/RTD_ResSpec_'+participant+'_'+rtd_last_date+'_NO_DATA.json','NO DATA','w');
					  				latest_rtd_date.add(5,'m');
					  				console.log(latest_rtd_date);
				  				}
				  				console.log('RTD NO DATA FOUND 30 SECS RERUN');
				  				setTimeout(modRtd,30000); // rerun waitForData after it is finished -- changed setInterval to this to prevent multiple executions
				  			}
				  			localStorage.clear();
	  					})
					}
					var modLmp = function(){ // LMP MINER
						var lmp_latest_d = moment(new Date()).subtract(2,'m').format('YYYYMMDDHHmm'); // DATE TIME CHECKER WITH ALLOWANCE FOR LATE DATA
						var lmp_date = latest_lmp_date.format('MM/DD/YYYY HH:mm');
						var lmp_last_date = latest_lmp_date.format('YYYYMMDDHHmm');
						// last.setMinutes(last.getMinutes() + 5);
						var lmp_retry = 1;		  		
						modLmpsPage.injectJs(jquery_loc)		
	  					modLmpsPage.evaluate(function(lmp_date){
	  						$('#PFC_TradeDateStart input').val(lmp_date);
				  				console.log('INSERT VALUE FOR PFC DATE') // WRITE DATE ON INPUT
				  				document.getElementById('ApplyButton').click();
				  				console.log('CLICK APPLY BUTTON'); // CLICK APPLY BUTTON
	  					},lmp_date)
	  					
	  					waitFor(function(){
	  						modLmpsPage.injectJs(jquery_loc)
	  						return modLmpsPage.evaluate(function(){ 
				  				if($('#progressBar').css('opacity') == '0'){
				  					return true;
				  				}
				  				return false;
				  			})
	  					},function(){	  							  						
	  						var lmp_xls_data = null;
	  						var lmp_dl_status = null;	
	  						modLmpsPage.injectJs(jquery_loc)
	  						var lmp_result = modLmpsPage.evaluate(function(nmms_ip) {
	  							console.log('RESULT STARTED!')
	  							if($('div[id*="EXPORT_ICON"]').hasClass('exportEnabled')){
	  								var lmp_display_id = $('#mRightBodyFrame input[id="pageId"]').val();
		  							var lmp_match = $('div[id*="EXPORT_ICON"]').attr('id').match(/GRID_(.*?)_EXPORT_ICON/);
		  							var lmp_dbcID = lmp_match[1];
		  							var lmp_date = new Date().getTime()/1000;
									lmp_date = lmp_date.toString().replace('.','');
		  							var lmp_uid = $('#'+lmp_display_id+' input[id="uid"]').val();
		  							var lmp_url_path = 'https://'+nmms_ip+'/mpi/displayExport.do?displayID='+lmp_display_id+'&type=excelPopup&dbcID=GRID_'+lmp_dbcID+'&exportAll=all&feedbackType=FOOTER_PROGRESS_BAR&feedbackId=FOOTER_PROGRESS_BAR_'+lmp_date+'&uid='+lmp_uid;		  							
								    var lmp_out = null;
								    $.ajax({
								        'async' : true,
								        'url' : lmp_url_path,
								        xhrFields: {
									    	withCredentials: true
									  	},
								        'success' : function(lmp_data, lmp_status, xhr) {									        									        
								        	window.callPhantom([lmp_data,lmp_status]);
								        }									        
								    });
								    // return out;
	  							}else{
	  								return false;
	  							}		  															    
							},nmms_ip);
							modLmpsPage.onCallback = function(lmp_data){
								lmp_xls_data = lmp_data[0];
								lmp_dl_status = lmp_data[1];
							}
							if(lmp_result != false){
								waitForData(function(){										
									if(lmp_xls_data != null){
										return true;
									}
								},function(){		
									if(lmp_xls_data && lmp_dl_status == 'success'){// SAVE AND CONTINUE
										lmp_retry = 1;
			  							fs.write(script_loc+'mms_mod/RTD_LMP_'+lmp_last_date+'.xls', lmp_xls_data,'w');
						  				latest_lmp_date.add(5,'m');
						  				setTimeout(modLmp,3000);
									}else{ // RETRY
										if(lmp_retry != 5){
											console.log("Redownloading File");
											lmp_retry++;			
											setTimeout(data_interval,5000);					
										}else{ // NO DATA OR ERROR DOWNLOADING FILE
											lmp_retry = 1;		  							
							  				if(lmp_latest_d > lmp_last_date){
					  							fs.write(script_loc+'mms_mod/no_data/RTD_LMP_'+lmp_last_date+'_NO_DATA.json','NO DATA','w');						  							
									  			latest_lmp_date.add(5,'m');
									  		}	
									  		setTimeout(modLmp,15000);	
										}
									}										
								},240000)
							}else{ // HAS NO DATA result == false
								lmp_retry = 1;
								console.log('==== GREATER THAN '+lmp_latest_d+' > '+lmp_last_date)
				  				if(lmp_latest_d > lmp_last_date){
		  							fs.write(script_loc+'mms_mod/no_data/RTD_LMP_'+lmp_last_date+'_NO_DATA.json','NO DATA','w');				  							
						  			latest_lmp_date.add(5,'m');
						  		}	
						  		console.log('LMP WAITING FOR DATA 60 SECS RERUN');
						  		setTimeout(modLmp,60000);
							}
							localStorage.clear();	
	  					})
					}
					mpdSchedPage.onConsoleMessage = function(msg, lineNum, sourceId) {
						  system.stderr.writeLine( '-====== SCHED CONSOLE ======-: ' + msg );
					};
					var mpdSched = function(){ // HAP SCHED MINER
						// SCHED HAP DATES
						var sched_hap_latest_d = moment(new Date()).subtract(2,'m').format('YYYYMMDDHHmm'); // DATE TIME CHECKER WITH ALLOWANCE FOR LATE DATA
						var sched_hap_date = latest_sched_hap_date.format('MM/DD/YYYY HH:mm');
						var sched_hap_datetime_check = moment(latest_sched_hap_date).add(1,'h').format('MM/DD/YYYY HH:mm');
						var sched_hap_last_date = latest_sched_hap_date.format('YYYYMMDDHHmm');

						// SCHED DAP DATES
						var sched_dap_latest_d = moment(new Date()).subtract(40,'m').format('YYYYMMDDHHmm'); // DATE TIME CHECKER WITH ALLOWANCE FOR LATE DATA
						var sched_dap_date = latest_sched_dap_date.format('MM/DD/YYYY HH:mm');
						var sched_dap_datetime_check = moment(latest_sched_dap_date).add(1,'h').format('MM/DD/YYYY HH:mm');
						var sched_dap_last_date = latest_sched_dap_date.format('YYYYMMDDHHmm');

						var sched_retry = 1;
						var dap_continue = false;
						var hap_continue = false;
						
	  					// FOR DAP INSERT DATE AND CLICK APPLY
	  					mpdSchedPage.injectJs(jquery_loc)
	  					mpdSchedPage.evaluate(function(sched_dap_date){
	  						$('#PFC_xMarket').val('DAP');
	  						$('#PFC_TradeDateStart input').val(sched_dap_date);
				  				console.log('INSERT VALUE FOR PFC DATE') // WRITE DATE ON INPUT
				  				document.getElementById('ApplyButton').click();
	  					},sched_dap_date)

	  					waitFor(function(){
	  						mpdSchedPage.injectJs(jquery_loc)
	  						// mpdSchedPage.render('screenshots/dap_'+sched_dap_last_date+'_first.png')
	  						return mpdSchedPage.evaluate(function(sched_dap_datetime_check,dap_continue){
	  							if($('.rowSelectionMode tbody tr:first-child td:first-child span').text() != ''){
			  						dap_continue = true;
			  					}
				  				if($('#progressBar').css('opacity') == '0'){
				  					return true;
				  				}
				  				return false;
				  			},sched_dap_datetime_check,dap_continue)
	  					},function(){
	  						// mpdSchedPage.render('screenshots/dap_'+sched_dap_last_date+'_next.png')
  							waitForData(function(){
	  							mpdSchedPage.injectJs(jquery_loc)
		  						return mpdSchedPage.evaluate(function(sched_dap_datetime_check){ // remove +1 hour on rowSelectionMode if nmms fixed dap +1 hour issue on export
					  				if($('#progressBar').css('opacity') == '0'){
					  					return true; 
					  				}
					  				return false;
					  			},sched_dap_datetime_check)
		  					},function(){
		  						var sched_dap_xls_data = null;
		  						var sched_dap_dl_status = null;
		  						// newPage.render('mpd-lmps_dap.png'); 
		  						mpdSchedPage.injectJs(jquery_loc)
		  						// mpdSchedPage.render('screenshots/dap_'+sched_dap_last_date+'.png')
		  						var sched_dap_result = mpdSchedPage.evaluate(function(nmms_ip) {
		  							if($('div[id*="EXPORT_ICON"]').hasClass('exportEnabled')){
		  								var sched_dap_display_id = $('#mRightBodyFrame input[id="pageId"]').val();
			  							var sched_dap_match = $('div[id*="EXPORT_ICON"]').attr('id').match(/GRID_(.*?)_EXPORT_ICON/);
			  							var sched_dap_dbcID = sched_dap_match[1];
			  							var sched_dap_date = new Date().getTime()/1000;
										sched_dap_date = sched_dap_date.toString().replace('.','');
			  							var sched_dap_uid = $('#'+sched_dap_display_id+' input[id="uid"]').val();
			  							var sched_dap_url_path = 'https://'+nmms_ip+'/mpi/displayExport.do?displayID='+sched_dap_display_id+'&type=excelPopup&dbcID=GRID_'+sched_dap_dbcID+'&exportAll=all&feedbackType=FOOTER_PROGRESS_BAR&feedbackId=FOOTER_PROGRESS_BAR_'+sched_dap_date+'&uid='+sched_dap_uid;		  							
									    var sched_dap_out = null;
									    $.ajax({
									        'async' : true,
									        'url' : sched_dap_url_path,
									        xhrFields: {
										    	withCredentials: true
										  	},
									        'success' : function(sched_dap_data, sched_dap_status, xhr) {									        									        
									        	window.callPhantom([sched_dap_data,sched_dap_status]);
									        }									        
									    });
									    // return out;
		  							}else{
		  								return false;
		  							}		  															    
								},nmms_ip);
								mpdSchedPage.onCallback = function(sched_dap_data){
									sched_dap_xls_data = sched_dap_data[0];
									sched_dap_dl_status = sched_dap_data[1];
								}
								if(sched_dap_result != false){
									waitForData(function(){
										if(sched_dap_xls_data != null){
											return true;
										}
									},function(){
										if(sched_dap_xls_data && sched_dap_dl_status == 'success' || sched_dap_xls_data.search('No Data') == false){// SAVE AND CONTINUE
											console.log('SCHED DAP : SAVING FILE')
											sched_dap_retry = 1;
				  							fs.write(script_loc+'mms_mpd/MPD_SCHED_DAP_'+participant+'_'+sched_dap_last_date+'.xls', sched_dap_xls_data,'w');
							  				latest_sched_dap_date.add(1,'h');
							  				hap_continue = true;
							  				// setTimeout(mpdSchedDap,3000);
										}else{ // RETRY
											if(sched_dap_retry != 5){
												console.log("SCHED DAP : Redownloading File");
												sched_dap_retry++;
												hap_continue = true;
												// setTimeout(mpdSchedDap,5000);
											}else{ // NO DATA OR ERROR DOWNLOADING FILE
												sched_dap_retry = 1;
								  				if(sched_dap_latest_d > sched_dap_last_date){
						  							fs.write(script_loc+'mms_mpd/no_data/MPD_SCHED_DAP_'+participant+'_'+sched_dap_last_date+'_NO_DATA.json','NO DATA','w');						  							
										  			latest_sched_dap_date.add(1,'h');
										  		}
										  		hap_continue = true;
										  		console.log('SCHED DAP : MAX RETRIES EXCEEDED')
										  		// setTimeout(mpdSchedDap,15000);
											}
										}
									},240000)
								}else{ // HAS NO DATA result == false
									sched_dap_retry = 1;
					  				if(sched_dap_latest_d > sched_dap_last_date){
			  							fs.write(script_loc+'mms_mpd/no_data/MPD_SCHED_DAP_'+participant+'_'+sched_dap_last_date+'_NO_DATA.json','NO DATA','w');				  							
							  			latest_sched_dap_date.add(1,'h');
							  		}	
							  		console.log('SCHED DAP : RE-INITIATE')
							  		hap_continue = true;
							  		// setTimeout(mpdSchedDap,60000);
								}
							})
	  						waitFor(function(){
	  							return hap_continue;
	  						},function(){
	  							//INITIATE SCHED HAP
			  					mpdSchedPage.evaluate(function(sched_hap_date){
			  						$('#PFC_xMarket').val('HAP');
			  						$('#PFC_TradeDateStart input').val(sched_hap_date);
						  				console.log('INSERT VALUE FOR PFC DATE') // WRITE DATE ON INPUT
						  				document.getElementById('ApplyButton').click();
			  					},sched_hap_date)
		  						waitFor(function(){
		  							mpdSchedPage.injectJs(jquery_loc)
		  							// mpdSchedPage.render('screenshots/hap_'+sched_hap_last_date+'.png')
			  						return mpdSchedPage.evaluate(function(sched_hap_datetime_check,hap_continue){
						  				if($('#progressBar').css('opacity') == '0'){
						  					return true;
						  				}
						  				return false;
						  			},sched_hap_datetime_check,hap_continue)
			  					},function(){
			  						var sched_hap_xls_data = null;
			  						var sched_hap_dl_status = null;	
			  						mpdSchedPage.injectJs(jquery_loc)
			  						// mpdSchedPage.render('screenshots/hap_'+sched_hap_last_date+'.png')
			  						var sched_hap_result = mpdSchedPage.evaluate(function(nmms_ip) {
			  							console.log('RESULT STARTED!')
			  							if($('div[id*="EXPORT_ICON"]').hasClass('exportEnabled')){
			  								var sched_hap_display_id = $('#mRightBodyFrame input[id="pageId"]').val();
				  							var sched_hap_match = $('div[id*="EXPORT_ICON"]').attr('id').match(/GRID_(.*?)_EXPORT_ICON/);
				  							var sched_hap_dbcID = sched_hap_match[1];
				  							var sched_hap_date = new Date().getTime()/1000;
											sched_hap_date = sched_hap_date.toString().replace('.','');
				  							var sched_hap_uid = $('#'+sched_hap_display_id+' input[id="uid"]').val();
				  							var sched_hap_url_path = 'https://'+nmms_ip+'/mpi/displayExport.do?displayID='+sched_hap_display_id+'&type=excelPopup&dbcID=GRID_'+sched_hap_dbcID+'&exportAll=all&feedbackType=FOOTER_PROGRESS_BAR&feedbackId=FOOTER_PROGRESS_BAR_'+sched_hap_date+'&uid='+sched_hap_uid;		  							
										    var sched_hap_out = null;
										    $.ajax({
										        'async' : true,
										        'url' : sched_hap_url_path,
										        xhrFields: {
											    	withCredentials: true
											  	},
										        'success' : function(sched_hap_data, sched_hap_status, xhr) {									        									        
										        	window.callPhantom([sched_hap_data,sched_hap_status]);
										        }									        
										    });
										    // return out;
			  							}else{
			  								return false;
			  							}		  															    
									},nmms_ip);
									mpdSchedPage.onCallback = function(sched_hap_data){
										sched_hap_xls_data = sched_hap_data[0];
										sched_hap_dl_status = sched_hap_data[1];
									}
									if(sched_hap_result != false){
										waitForData(function(){
											if(sched_hap_xls_data != null){
												return true;
											}
										},function(){
											if(sched_hap_xls_data && sched_hap_dl_status == 'success'){// SAVE AND CONTINUE
												sched_retry = 1;
					  							fs.write(script_loc+'mms_mpd/MPD_SCHED_HAP_'+participant+'_'+sched_hap_last_date+'.xls', sched_hap_xls_data,'w');
								  				latest_sched_hap_date.add(5,'m');
								  				setTimeout(mpdSched,3000);
											}else{ // RETRY
												if(sched_retry != 5){
													console.log("SCHED : Redownloading File");
													sched_retry++;
													setTimeout(mpdSched,5000);
												}else{ // NO DATA OR ERROR DOWNLOADING FILE
													sched_retry = 1;		  							
									  				if(sched_hap_latest_d > sched_hap_last_date){
							  							fs.write(script_loc+'mms_mpd/no_data/no_data/MPD_SCHED_HAP_'+participant+'_'+sched_hap_last_date+'_NO_DATA.json','NO DATA','w');						  							
											  			latest_sched_hap_date.add(5,'m');
											  		}
											  		console.log('SCHED : MAX RETRIES EXCEEDED')
											  		setTimeout(mpdSched,15000);
												}
											}
										},240000)
									}else{ // HAS NO DATA result == false
										sched_retry = 1;		  							
						  				if(sched_hap_latest_d > sched_hap_last_date){
				  							fs.write(script_loc+'mms_mpd/no_data/MPD_SCHED_HAP_'+participant+'_'+sched_hap_last_date+'_NO_DATA.json','NO DATA','w');				  							
								  			latest_sched_hap_date.add(5,'m');
								  		}	
								  		console.log('SCHED : RE-INITIATE')
								  		setTimeout(mpdSched,60000);
									}
									localStorage.clear();												
			  					})  
	  						})
	  					})										  			
					}
					modRtd();
					modLmp();
					mpdSched();
					// FOR CONFIRMATION : ADD NON LINKED PAGES TO APP LIKE LMP-HAP/DAP/WAP ,ALL WAP PAGES , RESOURCE SUMMARY PAGES
	  			})
	  		})
		})
	}
};