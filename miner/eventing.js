



// Event type constants
var EVENT_TYPE_REFRESH = "REFRESH";
var EVENT_TYPE_REFRESH_SHORT = "REFRESH_SHORT";
var EVENT_TYPE_HEARTBEAT = "HEARTBEAT";
var EVENT_TYPE_LOGOFF = "LOGOFF";
var EVENT_TYPE_RECYCLE_USEREVENTMANAGER = "RECYCLE_USEREVENTMANAGER";
var EVENT_TYPE_REFRESH_ALL = "REFRESH_ALL";
var EVENT_TYPE_RELOAD_ALL = "RELOAD_ALL";
var EVENT_TYPE_MMI_START_FAILOVER = "MMI_START_FAILOVER";
var EVENT_TYPE_MMI_REQUERY_DISPLAY = "MMI_REQUERY_DISPLAY";
var EVENT_TYPE_CHANGE_OBJECT = "CHANGE_OBJECT";
var EVENT_TYPE_WINDOW_CLOSE = "WINDOW_CLOSE";
var EVENT_TYPE_DLM_WINDOW_OPEN = "DLM_WINDOW_OPEN";
var EVENT_TYPE_DLM_WINDOW_CLOSE = "DLM_WINDOW_CLOSE";
var EVENT_TYPE_DLM_SWITCH_CONTEXT = "DLM_SWITCH_CONTEXT";
var EVENT_TYPE_DLM_SEND_WORKSPACE = "DLM_SEND_WORKSPACE";
var EVENT_TYPE_DLM_OPEN_WORKSPACE = "DLM_OPEN_WORKSPACE";
var EVENT_TYPE_ACTIVE_DISPLAY_CHECK = "ACTIVE_DISPLAY_CHECK";
var EVENT_TYPE_EXECUTE = "EXECUTE";
var EVENT_TYPE_LOGIN_PUSHLET_CLOSE = "LOGIN_PUSHLET_CLOSE";

// local variable used by eventing debug popup
var updatedPageIds = "";

// Using the top window's log object for the pushlet frame
if (!window.log) {
	log = window.top.log;
	log.trace('No log.  Using window.top.log');
}

// get an return the open window list from the parent or top window
function getOpenWindowList() {
	if (window.top.thisIsTheTopWindow) {
		return window.top.openWindowList;
	} else {
		return window.top.topWindow.openWindowList;
	}
}

// refresh the window identified by the page id passed in
function refreshWindow(pageId) {
}

// refresh the window identified by the page id passed in
function refreshShortWindow(pageId) {
}

// refresh all windows associated with this session
function refreshAllWindows(reason) {

   reason = (reason == null) ? "UNKNOWN" : reason;

   try {

      log.info("Start refresh all windows  reason: " + reason );
      // get list of open windows
	  var openWindows = getOpenWindowList();
	  log.info("openWindows is : " + openWindows);

	  if (openWindows != null) {
	     log.info("refreshing all windows  : " + openWindows.length);
	     // removing all old deltas
	     if (top.topWindow)
	        try {
	           top.topWindow.strayDeltas = {};
	        } catch (e) {
	           log.error('Error clearing "stray" deltas: ' + top.exString(e));
	        }

		 // loop thru all open windows
		 for (var i = 0; i < openWindows.length; i++) {
			// skip closed windows
			if (!openWindows[i] || openWindows[i].closed) {
				continue;
			}

	        try {
	           log.info("refreshing currentPage window index: " + i);
			   openWindows[i].refreshCurrentPage(reason);
			   openWindows[i].refreshWireframeDisplay(reason);
			} catch (e) {
			  log.error("i:" + i + ", exception:", e);
			}
		 }
	  } else {
	     log.info("open window list is empty, refreshing self");
	     top.refreshCurrentPage(reason);
	     top.refreshWireframeDisplay(reason);
	     log.info("window refreshed");
	  }
	  log.info("all windows refreshed");
   } 
   catch(e) {
      log.error("exception - " + e.description);
   }

}

// reload all windows associated with this session
function reloadAllWindows() {
	var activePageIds = window.top.topWindow.getActivePageIdsWithFramesAllDisplays(true);
	for (var i = 0; i < activePageIds.length; ++i) {
		var pageId = activePageIds[i].pageId;
		var frame = activePageIds[i].window;
		if (frame && !frame.closed) {
			try {
				log.debug("Reloading display " + pageId);
				frame.nondeltaDisplayUpdate(frame, pageId);
			}
			catch (e) {
				log.error("Error reloading display " + pageId + ": " + e.description);
			}
		}
	}
}

// update the control (identified by pageId, controlId) with the values passed in (value, properties)
function changeObject(pageId, controlId, value, properties) {
	var eventPage = window.top.getWinForDisplay(pageId);
	if (eventPage == null) {
		// send notification to server to clean up dead page
		// SPC400030715 - Stop accidental removal of an active display
		// removeDeadPage(pageId);
	}
	//Check whether this page can handle the event right now	
	else if (isSafeToRefresh(eventPage) == true) {
		// find control and update innerHTML of control
		var control = eventPage.document.getElementById(controlId);
		if (control != null) {
			control.innerHTML = value;
			//clearUpdatedPageIds();
			//alert('about to updateids');
			//updatePageIds(pageId);
		}
	}
}

// function gets and sends to the server all active display pages ids associated with this user session 
function activeDisplayCheck(displays) {
	// TODO: This possibly isn't really the right place to check the delta sequences
	try {
		if (displays != null && displays.length > 0) {
			var displayMap = new Object();
			if (typeof displays == 'string') {
				displays = eval(displays);
			}
			for (var i = 0; i < displays.length; ++i) {
				var displayObject = displays[i];
				log.debug('Server display ' + displayObject.pageId + " has delta sequence " + displayObject.sequence);
				displayMap[displayObject.pageId] = displayObject.sequence;
			}
			if (displays.length == 0) {
				log.info('No active displays on the server!');
			}

			var activePageIds = window.top.topWindow.getActivePageIdsWithFramesAllDisplays(true);
			for (var i = 0; i < activePageIds.length; ++i) {
				log.info(activePageIds[i]);
				var pageId = activePageIds[i].pageId;
				var frame = activePageIds[i].window;
				var serverSequence = displayMap[pageId];
				if (frame && !frame.closed) {
					try {
						var clientSequence = window.top.getDeltaSequence ? window.top.getDeltaSequence(frame, pageId) : frame.sequence;
						if (clientSequence < serverSequence) {
							if (frame.pendingRefresh != null) {
								log.info("Refresh already pending for display " + pageId + " with actual client sequence of " + clientSequence + " and expected sequence of " + serverSequence);
							} else {
								log.warning("Adding refresh countdown for display " + pageId + " with actual client sequence of " + clientSequence + " and expected sequence of " + serverSequence);
								(function(pageId) {
									// Using an immeidately executed function to capture the current value of pageId.
									// Otherwise, every setTimeout will end up using the last value of pageId.
									frame.pendingRefresh = frame.setTimeout(function() {
										frame.nondeltaDisplayUpdate(frame, pageId);
										log.trace('Waiting for expected sequence of ' + pageId + ' + timed out.  Display ' + pageId + ' refreshed.');
									}, frame.OUT_OF_ORDER_TIMEOUT);
								})(pageId);
							}
						}
					} catch (e) {
						log.error("Error while verifying delta sequence numbers: " + e);
					}
				}
			}
			if (top.topWindow && top.topWindow.strayDeltas) {
				for (var pageId in top.topWindow.strayDeltas) {
					if (!displayMap[pageId]) {
						log.info('Removing ' + top.topWindow.strayDeltas[pageId].length + ' "stray" deltas for display ' + pageId);
						delete top.topWindow.strayDeltas[pageId];
					}
				}
			}
		}
		var activePageIds = window.top.topWindow.getActivePageIdsAllDisplays(true);
        if (activePageIds == null)
        {
           log.error("Active Display Check returned NO active displays");
        }
        else
        {      	
           log.trace("List of active displays : " + activePageIds.toString());
           var params = "ACTIVE_PAGE_IDS=" + activePageIds.toString();
	   params += "&KEEP_ALIVE=false";
           top.display_sendAction('ACTIVE_DISPLAYS', '', params);
        }
        
        
	}
	catch(e) {
	   log.error("Unable to process active display check: " + e);
	}

}

var lastHeartbeat = new Date();
if (window != window.top) {
	var eventWin = window.top.frames[window.top.EventingID];
	if (eventWin == window) {
		top.topWindow.ignoreHeartbeat = false;
	}
}
// process an incoming event
// this is the primary method called by the pushlet
function processEvent(eventSeqId, eventType, pageId, controlId, value, properties) {

	lastHeartbeat = new Date(); // Updates last heartbeat timestamp
	top.topWindow.ignoreHeartbeat=true; // Pause hartbeat timeout while events are being processed
	top.topWindow.eventingActive=true; // Guarantes that an isEventingActive timeout doesn't cause problems

	try {
		if (eventSeqId >= 0) {
			parent.setCurrentEventSeqId(eventSeqId);
		}

		if (eventType == EVENT_TYPE_HEARTBEAT) {
			var footerSystemTimeElement = window.top.document.getElementById("WFFooterSystemTime");
			if (footerSystemTimeElement) {
				top.display_setInnerText(footerSystemTimeElement, pageId);
			}
			for (var i = 1; i < window.top.openWindowList.length; i++) {
				var openWindow = window.top.openWindowList[i];
				if (openWindow && !openWindow.closed) {
					footerSystemTimeElement = openWindow.document.getElementById("WFFooterSystemTime");
					if (footerSystemTimeElement) {
						top.display_setInnerText(footerSystemTimeElement, pageId);
					}
				}
			}
		}

		else if (eventType == EVENT_TYPE_CHANGE_OBJECT) {
			changeObject(pageId, controlId, value, properties);
		} else if (eventType == EVENT_TYPE_WINDOW_CLOSE) {
			//alert('about to close');
			parent.shutDownOpenWindows(window);
			parent.window.close();
		} else if (eventType == EVENT_TYPE_DLM_WINDOW_CLOSE) {
			//alert('about to close');
			parent.shutDownOpenWindows(window);
			parent.window.close();
		} else if (eventType == EVENT_TYPE_DLM_SWITCH_CONTEXT) {
			//alert(parent.window.location);
		} else if (eventType == EVENT_TYPE_DLM_WINDOW_OPEN) {
			var code = URLDecode(properties);
			//alert('' + 'parent.' + code + '');
			eval('parent.' + code);
			//parent.shutDownOpenWindows(window);
			//parent.window.open("http://www.google.com");
		} else if (eventType == EVENT_TYPE_DLM_SEND_WORKSPACE) {
			if (!pageId) {
				var activePageIds = parent.getActivePageIdsAllDisplays();
				var ids = activePageIds.split(',');
				if (ids != null) {
					//alert(activePageIds);
					//alert(ids.length);
					pageId = ids[0];
				}
			}
			sendGeometry(pageId);
		} else if (eventType == EVENT_TYPE_MMI_START_FAILOVER) {
			mmiStartFailover();
		} else if (eventType == EVENT_TYPE_MMI_REQUERY_DISPLAY) {
			window.top.deleteMessageById('SPECTRUM_DP_ERROR');
			mmiRequeryDisplays(controlId);
		} else if (eventType == EVENT_TYPE_REFRESH) {
			// refresh specified window
			refreshWindow(pageId);
		} else if (eventType == EVENT_TYPE_REFRESH_SHORT) {
			// refresh specified window
			var t = setTimeout("refreshShortWindow(" + pageId + " )", 1000);
		} else if (eventType == EVENT_TYPE_LOGOFF) {
			// log off user
			parent.logoff();
		} else if (eventType == EVENT_TYPE_RECYCLE_USEREVENTMANAGER) {
			log.info("Pushlet recycling notification.");
			// Mark eventing process to restart to recycle memory
			top.recyclePushlet = true;
			top.eventingCurrentRetry = 0;
			
		} else if (eventType == EVENT_TYPE_REFRESH_ALL) {
			// refresh all open windows
			refreshAllWindows();
		} else if (eventType == EVENT_TYPE_RELOAD_ALL) {
			// reload all open windows
			reloadAllWindows();
		} else if (eventType == EVENT_TYPE_ACTIVE_DISPLAY_CHECK) {
			log.info("active display check");
		    activeDisplayCheck(value);
		} else if (eventType == EVENT_TYPE_EXECUTE) {
			//alert( "Value = " + value + "\nProperties = " + properties );
		} else if (eventType == EVENT_TYPE_LOGIN_PUSHLET_CLOSE ) {
			// tell pushlet to not reconnect
			stopRetry = true;
			top.submitLoginForm();
		}
		

		// update last updated date on eventing popup
		//updateLastUpdated();
	} catch (e) {
		//updatePageIds('ERROR PROCESSING EVENT TYPE ' + eventType + ': ' + e.message);
	}
	lastHeartbeat = new Date(); // Updates last heartbeat timestamp
	top.topWindow.ignoreHeartbeat = false; // Enable heart beat timeout
}

// update last updated field on eventing display
//function updateLastUpdated()
//{
//	document.getElementById('lastUpdated').innerHTML = new Date();
//}

// update Page Ids list on event debug popup
//function updatePageIds(pageId)
//{
//	if (pageId != null && pageId != 'null')
//	{
//		if(updatedPageIds =="")
//			updatedPageIds = pageId;
//		else
//			updatedPageIds = updatedPageIds + ","+pageId;
//	
//		document.getElementById('updatedPages').innerHTML="Updated Page ID(s): " +updatedPageIds;
//	}
//}

// clear Page Ids list on event debug popup
//function clearUpdatedPageIds()
//{
//	updatedPageIds = "";
//	document.getElementById('updatedPages').innerHTML="Updated Page ID(s):";
//}

// report dead page
// if an event is received for an inaccessable page, it will be reported back to the app server (and removed)
function removeDeadPage(deadPageId) {
	// 	alert('report dead page ' + deadPageId);
	var xmlhttp = createAJAXRequest();
	var sendEventUrl = 'sendEvent.do?eventAction=removeDeadPage&deadPageId=' + deadPageId;
	xmlhttp.open('GET', sendEventUrl, true);
	xmlhttp.send(null);
}

// report dead page
// if an event is received for an inaccessable page, it will be reported back to the app server (and removed)
//This is the function currently being called 10-13-2009 Jerry

function sendGeometry(pageId) {
	//alert('sending geometry ' + pageId);
	sendGeometryAsXML(null, null, null, pageId);
	//var page = findPage(pageId);

	//if(page==null || page =='null') 
	//{
	// alert('Cannot find page id: ' + pageId + ' because page not found in list of open windows');
	// return;
	//  }

	//alert('w:' + page.pageWidth() + ' h:' + page.pageHeight());
	//alert('screenX:' + page.screenX + ' screenY:' + page.screenY);
	//alert('l:' + page.posLeft()+ ' t:' + page.posTop());
	//alert('r:' + page.posRight() + ' b:' + page.posBottom());
	//dump(page.);
	//var xmlhttp = createAJAXRequest();
	//var sendEventUrl = 'sendEvent.do?eventAction=geometryUpdate&pageId=' + pageId + '&screenX='+page.posLeft() + '&screenY='+page.posTop()+ '&width=' + page.pageWidth() + '&height=' + page.pageHeight() ;
	//xmlhttp.open('GET', sendEventUrl , true);
	//xmlhttp.send(null);
}

/**
 * Index variables for setting/getting the window information from the array
 */
var WIN_TOP = "winTop";
var WIN_LEFT = "winLeft";
var WIN_WIDTH = "winWidth";
var WIN_HEIGHT = "winHeight";

/**
 * Return and Array containing the window size and position information
 *
 * Array[WIN_LEFT] = Left
 * Array[WIN_TOP] = Top
 * Array[WIN_WIDTH] = Width
 * Array[WIN_HEIGHT] = Height
 */
function getWindowSizeAndPosition(win) {
	var winSP = new Array();
	if(win.screenX) {
		winSP[WIN_LEFT] = win.screenX;
		winSP[WIN_TOP] = win.screenY;
		winSP[WIN_WIDTH] = win.outerWidth;
		winSP[WIN_HEIGHT] = win.outerHeight;
	} else {
		/**
		 * OFFSET values top prevent the 'window jump' to the user, during the calculation
		 * those values were calculated to a default WebSDK application (no toolbars, no menus, no statusbar, no location)
		 *
		 * EXPECTED_VALUE - IE_RETURNED_VALUE = OFFSET
		 *
		 * The values bellow where obtained during tests with IE maximized window on the 2nd monitor.
		 *
		 * LEFT: 1276 - 1280 = -4
		 * TOP: -4 - 19 = -23
		 * WIDTH: 1288 - 1280 = 8
		 * HEIGHT: 1032 - 1005 = 27
		 */
		var LEFT_OFFSET = -4;
		var TOP_OFFSET = -23;
		var WIDTH_OFFSET = 8;
		var HEIGHT_OFFSET = 27;
		
		// Gets the actual size and position information and adds the expected offset to prevent the window "jump" to the user
		var left0 = win.screenLeft + LEFT_OFFSET;
		var top0 = win.screenTop + TOP_OFFSET;
		var width0 = win.document.body.offsetWidth + WIDTH_OFFSET;
		var heigth0 = win.document.body.offsetHeight + HEIGHT_OFFSET;

		// Resizes and moves the window according to the size and position values returned by IE + their previous calculated OFFSET
		win.moveTo(left0, top0);
		win.resizeTo(width0, heigth0);

		// Calculates the differences between the previously obtained size/position values and the size/position values obtained after the resize and move
		var leftDifference = left0 - (win.screenLeft + LEFT_OFFSET);
		var topDifference = top0 - (win.screenTop + TOP_OFFSET);
		var widthDifference = width0 - (win.document.body.offsetWidth + WIDTH_OFFSET);
		var heightDifference = heigth0 - (win.document.body.offsetHeight + HEIGHT_OFFSET);

		// Uses the differences to calculate the correct value
		winSP[WIN_LEFT] = left0 + leftDifference;
		winSP[WIN_TOP] = top0 + topDifference;
		winSP[WIN_WIDTH] = width0 + widthDifference;
		winSP[WIN_HEIGHT] = heigth0 + heightDifference;

		// Restores the window positioning
		win.moveTo(winSP[WIN_LEFT], winSP[WIN_TOP]);
		win.resizeTo(winSP[WIN_WIDTH], winSP[WIN_HEIGHT]);
	}

	// Return top/left as being at the initial coordinate if the window was minimized when the function was executed
	// (-4, -4 is what's returned when the window is localized at the initial coordinates)
	var isMinimized = false;
	if(winSP[WIN_LEFT] < -31000 && winSP[WIN_TOP] < -31000) {
		isMinimized = true;
		winSP[WIN_LEFT] = -4;
		winSP[WIN_TOP] = -4;
	}

	log.debug("Desktop Layout Save" + (isMinimized ? " MINIMIZED" : "") + " [ top: " + winSP[WIN_TOP] + ", left: " + winSP[WIN_LEFT] + ", width: " + winSP[WIN_WIDTH] + ", height: " + winSP[WIN_HEIGHT] + " ]");
	
	return winSP; 
}

function debugWindowSpecs(hwnd) {
	if (hwnd.screen) {

		myWidth = hwnd.document.body.clientWidth;
		myHeight = hwnd.document.body.clientHeight;

		var specs = "hwnd.document.body.clientWidth: " + myWidth + "\n";
		specs += "hwnd.document.body.clientHeight: " + myHeight + "\n";

		specs += "document.body.offsetWidth: " + hwnd.document.body.clientWidth
				+ "\n";
		specs += "document.body.offsetHeight: " + hwnd.document.body.clientHeight
				+ "\n";

		specs += "document.body.scrollWidth : " + hwnd.document.body.scrollWidth
				+ "\n";
		specs += "document.body.scrollHeight: " + hwnd.document.body.scrollHeight
				+ "\n";
		specs += "document.body.scrollLeft: " + hwnd.document.body.scrollLeft + "\n";
		specs += "document.body.scrollTop: " + hwnd.document.body.scrollTop + "\n";

		specs += "document.documentElement.clientWidth: "
				+ hwnd.document.documentElement.clientWidth + "\n";
		specs += "document.documentElement.clientHeight: "
				+ hwnd.document.documentElement.clientHeight + "\n";

		specs += "document.documentElement.offsetWidth: "
				+ hwnd.document.documentElement.clientWidth + "\n";
		specs += "document.documentElement.offsetHeight: "
				+ hwnd.document.documentElement.clientHeight + "\n";

		specs += "document.documentElement.scrollWidth : "
				+ hwnd.document.body.scrollWidth + "\n";
		specs += "document.documentElement.scrollHeight: "
				+ hwnd.document.body.scrollHeight + "\n";
		specs += "document.documentElement.scrollLeft: "
				+ hwnd.document.body.scrollLeft + "\n";
		specs += "document.documentElement.scrollTop: "
				+ hwnd.document.body.scrollTop + "\n";

		specs += "document.body.clientLeft: " + hwnd.document.body.clientLeft + "\n";
		specs += "document.body.clientTop: " + hwnd.document.body.clientTop + "\n";
		specs += "document.body.topMargin: " + hwnd.document.body.topMargin + "\n";
		specs += "document.body.bottomMargin: " + hwnd.document.body.bottomMargin
				+ "\n";

		specs += "screenWidth: " + screen.width + "\n";
		specs += "screenHeight: " + screen.height + "\n";
		specs += "screenAvlWidth: " + screen.availWidth + "\n";
		specs += "screenAvlHeight: " + screen.availHeight + "\n";

		specs += "window.screenLeft: " + hwnd.screenLeft + "\n";
		specs += "window.screenTop: " + hwnd.screenTop + "\n";

		specs += "window.screenX: " + hwnd.screenX + "\n";
		specs += "window.screenY: " + hwnd.screenY + "\n";

		alert(specs);

	}
}

/****  end window positioning and size capture function for DLTOOL ****/

function sendGeometryAsXML(action, command, params, ownerPageId) {
	var activePageIds = window.top.getActivePageIdsAllDisplays();
	var ids = activePageIds.split(',');

	//string constants for building xml message strings
	var winTypeHeadPre = '<Window type=\"';
	var winTypeHeadPost = '\">';
	var winTypeTail = '</Window>';
	
	var propertiesHead = '<Properties>';
	var propertiesTail = '</Properties>';
	
	var entryKeyHeadPre = '<entry key=\"';
	var entryKeyHeadPost = '\">';
	var entryKeyTail = '</entry>';
	var type;
	var sendEventXmlUrl = '<Application type=\"WebUI\">';

	if (!action) {
		action = 'DESKTOP_LAYOUT';

		if (!command) {
			command = 'geometryForAllPages';

			if (!params) {
				params = '';
			}
		}
	}
	if (params) {
		params += '&';
	}

	//alert('sendGeometryAsXML ' + ids.length);
	if (ids != null) {
		for ( var idx = 0; idx < ids.length; idx++) {
			if (ids[idx] != null
					&& ids[idx].replace(/^\s+|\s+$/g, "").length > 0) {
				try {
					var pageId = ids[idx];
					var page = window.top.getWinForDisplay(pageId);
					if (page == null || page == 'null') {
						log.info('Cannot send geometry for page id: ' + pageId + ' because page not found in list of open windows');
						return;
					}

					//We need to get outer window coordinates, not iframe inner cordinates
					//if page == parent then page==mainwindow else page = child
					//Format xml as near to final format as possible to reuse the parsing all ready available in jDLM
					var hwnd = page;
					if (page.parent != null) {
						hwnd = page.parent;
					}

					//If this page is the "main window"
					
					if(idx == 0) { //This logic may be the wrong way to id the main window
						type = 'Main';
					} else {
						type = 'Child';
					}
					
					var loc = hwnd.top.location;
					log.trace("Window Location " + loc);
					
					var wireFrameType = 1;
					if((new RegExp("\/popupWithNoMenu\.do","g")).test(loc)) {
						wireFrameType = 2;
					} else if((new RegExp("\/displayView\.do","g")).test(loc)) {
						wireFrameType = 3;
					}
					
					// Obtain window size and position information
					var wSizePos = getWindowSizeAndPosition(hwnd);
					log.debug("Window geometry Size and Position " + wSizePos);
	
					sendEventXmlUrl += winTypeHeadPre + type + winTypeHeadPost
					
					+ propertiesHead
						
					//Create entry key wireFrameType
					+ entryKeyHeadPre + 'wireFrameType' + entryKeyHeadPost + wireFrameType + entryKeyTail
					
					//Create entry key pageId
					+ entryKeyHeadPre + 'pageId' + entryKeyHeadPost + pageId + entryKeyTail
					
					//Create entry key left
					+ entryKeyHeadPre + 'x' + entryKeyHeadPost + wSizePos[WIN_LEFT] + entryKeyTail
					
					//Create entry key y or top
					+ entryKeyHeadPre + 'y' + entryKeyHeadPost + wSizePos[WIN_TOP] + entryKeyTail
					
					//Create entry key w or width
					+ entryKeyHeadPre + 'w' + entryKeyHeadPost + wSizePos[WIN_WIDTH] + entryKeyTail
					
					//Create entry key h or height
					+ entryKeyHeadPre + 'h' + entryKeyHeadPost + wSizePos[WIN_HEIGHT] + entryKeyTail
					
					//terminate properties tag
					+ propertiesTail

					//terminate window tag
					+ winTypeTail;
				} catch(e) {
					log.error('geometryForAllPages failed: ' + top.exString(e));
				}
			} else {
				log.error('geometryForAllPages failed, ids[idx] ' + ids[idx]);
			}
		}
	} else {
		log.error('geometryForAllPages failed, ids null - ' + activePageIds);
	}

	sendEventXmlUrl += '</Application>'; //Terminate application tag

	//alert('found: ' + sendEventXmlUrl);
	//alert('sending: ' + escape(sendEventXmlUrl));

	try {
		window.top.display_sendAction(action, command, params + 'encodedXMLStr=' + sendEventXmlUrl, ownerPageId);
	} catch(e) {
		log.error('geometryForAllPages failed during sendAction: ' + top.exString(e));
	}
}

function sendAllGeometryAsXmlStr(encodedXMLStr) {
	var xmlhttp = createAJAXRequest();
	var sendEventUrl = 'sendEvent.do?eventAction=geometryForAllPages&encodedXMLStr=' + encodedXMLStr;
	xmlhttp.open('GET', sendEventUrl, true);
	xmlhttp.send(null);
}

function mmiStartFailover() {
     top.display_sendAction('MMI_START_FAILOVER', '', '', '');
}

function updateWindowsFooter(mmiServer) {
   var openWindows = getOpenWindowList();
   if (openWindows != null) {
      // loop thru all open windows
      for ( var i = 0; i < openWindows.length; i++) {
		// skip closed windows
		if (!openWindows[i] || openWindows[i].closed) {
			continue;
		}

         try {
            var footerDataServerAndPort = openWindows[i].document.getElementById('footerDataServerAndPort');
            if (footerDataServerAndPort) {
               footerDataServerAndPort.innerText = mmiServer;
            }
         } catch (e) {
            log.error('Failed to update footer: ' + top.exString(e));
         }
      }
   } else {
      log.warning('Failed to find openWindowList!');
      try {
         var footerDataServerAndPort = window.top.document.getElementById('footerDataServerAndPort');
         if (footerDataServerAndPort) {
            footerDataServerAndPort.innerText = mmiServer;
         }
      } catch (e) {
         log.error('Failed to update footer: ' + top.exString(e));
      }
   }
}

function mmiRequeryDisplays(jsonString) {
   try {
      var jsonObj = eval(jsonString);
   } catch(e) {
      log.error('Failed to eval mmiRequeryDisplays jsonString: ' +  jsonString);
      return;
   }

   updateWindowsFooter(jsonObj.footerDataServerAndPort);
	
   var pageIds = jsonObj.displayList;
   log.info('MMI Requery displays: ' + pageIds);

   if (pageIds != null) {
      for (var i=0; i < pageIds.length; i++) {
         var page = window.top.getWinForDisplay(pageIds[i]);
         if (page != null) {
            log.debug('Display id: ' + pageIds[i] + ' found on current list, requery using pushlet');
            page.display_sendAction('MMI_FAILOVER_REQUERY_DISPLAY', '', '', pageIds[i]);
         } else {
            log.debug('Display id: ' + pageIds[i] + ' not found on current list, requery using pushlet');
            // Display was not found, send requery response as an pushlet update event
            try {
               top.display_sendAction('MMI_FAILOVER_REQUERY_DISPLAY', '', 'usePushlet=true', pageIds[i]);
            } catch(e) {
               log.error('unable to requery display ' +e.description);
            }
         }
      }
   }
}
//function sendAllPagesGeometry() 
//{
//alert('sendAllPagesGeometry');
//var t1=setTimeout("activeDisplayCheck()",3000);
//var activePageIds = parent.getActivePageIdsAllDisplays();
//var ids = activePageIds.split(',');
//if(ids!=null)
//{
//		 		for (var idx = 0; idx < ids.length; idx++)
//				{
//					try 
//					{
//						//alert(ids[idx].replace(/^\s+|\s+$/g,""));
//						if(ids[idx]!=null && ids[idx].replace(/^\s+|\s+$/g,"").length > 0)
//						{		
//						if(ids.length==1)
//							var t2=setTimeout("sendGeometry(" + ids[idx]+ ")",2000);
//						else
//							var t3=setTimeout("sendGeometry(" + ids[idx]+ ")",1000);
//						}
//}
//			catch (e) {
//			// alert(e);
//    	}
//	}	
//  }
//}

// debugging method for creating a new event via event debug popup
//function sendEvent() 
//{
//	var eventAction = document.getElementById('newEventAction').value;
//	if (eventAction == 'STOP_USEREVENTMANAGER')
//	{	
//		window.location = null;
//	}
//	else
//	{
//		var xmlhttp = createAJAXRequest();
//		var eventSystem = document.getElementById('newEventSystem').value;
//		var eventSession = document.getElementById('newEventSession').value;
//		var eventRefreshEvent = document.getElementById('newRefreshEvent').value;
//		var eventSource = document.getElementById('newEventSource').value;
//		var eventType = document.getElementById('newEventType').value;
//		var eventInstance = document.getElementById('newEventInstance').value;
//		var eventAttribute = document.getElementById('newEventAttribute').value;
//		var eventValue = document.getElementById('newEventValue').value;
//		var eventAppSuite = document.getElementById('newEventAppSuite').value;
//		var eventSubAppSuite = document.getElementById('newEventSubAppSuite').value;
//		var eventFunctionId = document.getElementById('newEventFunctionId').value;
//		var eventSlotNumber = document.getElementById('newEventSlotNumber').value;
//		
//		var sendEventUrl = 'sendEvent.do?eventAction=' + eventAction + '&eventSystem=' + eventSystem + '&eventSession=' + eventSession  + '&eventRefreshEvent=' + eventRefreshEvent + '&eventSource=' + eventSource + '&eventType=' + eventType + '&eventInstance=' + eventInstance + '&eventAttribute=' + eventAttribute + '&eventValue=' + eventValue + '&eventAppSuite=' + eventAppSuite + '&eventSubAppSuite=' + eventSubAppSuite + '&eventFunctionId=' + eventFunctionId + '&eventSlotNumber=' + eventSlotNumber;
//		//alert(sendEventUrl);
//			
//		// reset updatedPageIds for each sending event
//		clearUpdatedPageIds();
//		xmlhttp.open('GET', sendEventUrl , true);
//		xmlhttp.send(null);
//	}
//}

// Check displayStatus to determine whether event can be processed
function isSafeToRefresh(display) {
	//Staleness check
	var dispStatus = display.getDisplayStatus(display);

	if (dispStatus.hasChanges == true) {
		// display has changes, throw out event and mark display as stale
		dispStatus.setIsStale(true);
		return false;
	}
	return true;
}

// browser independent function to create an AJAX request (XMLHttp Request object)
function createAJAXRequest() {
	var xmlhttp;
	try {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	} catch (e) {
		try {
			xmlhttp = new XMLHttpRequest();
		} catch (e) {
			return;
		}
	}
	return xmlhttp;
}

function URLDecode(encodedStr) {
	encodedStr = unescape(encodedStr);
	encodedStr = encodedStr.replace(/\+/g, " ");
	return encodedStr;
}



