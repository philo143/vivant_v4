/* start block _extraPreContent_ */

/* end block */

//methods removed from sdk.js and added to handle new window open both for dropdowm and page menu.
var topWindow;
var thisIsTheTopWindow = false;
var openWindowList = null;
var currentEventSeqId = -1;
var uid = "";

var reuseWindow = false;

// Failover
var failoverInitialized = false;
var recyclePushlet = false;
var isEventingConnected = false;

// Current server connection variables
var serverProtocol = "";
var serverAddress = "";
var serverName = "";
var serverPort = "";
var applicationContext = "";

// Failover retry variables
var eventingMaxRetry = 100;
var eventingCurrentRetry = 0;

// Failover server list variables
var failoverServerList = null;
var failoverServerIndex = 0;
var failoverServerRetries = 3;
var failoverCurrentServerRetry = -1;

// HeartBeat tiemout check variables
var heartbeatTimeoutEnabled = false;
var heartbeatTimeout = 7; // Heartbeat Timeout (seconds)
var heartbeatTimestamp = null;
var ignoreHeartbeat = false; // Heartbeat timeout will be enabled after when the first event is received
var eventingActive = true; // isEventing active

// ODV Failover message control
var disconnectMessageSent = true;

var stopRetry = false;
var disconnected = false;

var checkWindowsInterval=10000;
var checkWindowsID = null;
var checkWindowFailCnt = 0;
var maxCheckWindowFails = 3;
var checkWindowErrorMsgAfterCnt = 2;
var failoverRefreshReason = "FAILOVER";

/** 
 * Connectivity globals
 */
var connections = new Array();
var hasDisconnections = false;
var disconnectionWarning = false;
var connectionStatusChange = false;

connections[0] = "-,-,-,-";

/**
 Array for storing an history of pushlet connection events
 */
var eventingHistory = [];

// If outer window then set interval to monitor bad window references....
// for both the child and the top window
// if popup window don't start timer
if (window.top == window && !window.top.isUnmanagedPopup) 
{
     checkWindowsID = window.setInterval(checkWindowReferencesInterval, checkWindowsInterval);
     
     // add event handler for outerwindow closing 
     document.ignoreLogoff = false;   
     if(msie) {  
    	window.addEventListener("unload", displayUnLoad, false); 
     }else{
     	window.addEventListener("beforeunload", displayUnLoad, false); 
     }
}

/**
 * Object that represents an history entry
 */
function EventingHistoryEntry(timestamp, retryCount, serverRetryCount, eventType, URL) {
	this.timestamp = timestamp;
	this.eventType = eventType;
	this.retryCount = retryCount;
	this.serverRetryCount = serverRetryCount;
	this.URL = URL;
}

/**
 * Function for adding history entry
 */
function addEventingHistoryEntry(timestamp, retryCount, serverRetryCount, eventType, URL) {
	try {
		eventingHistory.push(new EventingHistoryEntry(timestamp, retryCount, serverRetryCount, eventType, URL));
	} catch (e) {
		log.error("Unable to addEventingHistoryEntry: " + e.description);
	}
}

/**
 * Eventing history functions
 * 
 * @param page - page nr, or "last"
 * @param rowsPerPage - Nr of rows per page
 */
function showEventingSummary() {
	var page = (arguments[0]? (arguments[0]=="last"?arguments[0]:(arguments[0] - 1)) : 0);
	var rowsPerPage = (arguments[1] ? arguments[1] : 10);
	var formatted = (arguments[2] ? arguments[2] : false);

	var initialIndex = 0;
	var totalPages = eventingHistory.length / rowsPerPage;
	
	if(page=="last") {
		page = Math.floor(totalPages);
	}
	
	if(eventingHistory.length > rowsPerPage && totalPages >= page) {
		initialIndex = rowsPerPage * page;
	} else {
		page = 0;
	}
	
	var eventingSummary = "<table width='100%' cellspacing='4px'><tr><td id='popup-text'>" +
	"<b>Eventing Summary - Total: </b>" + eventingHistory.length + ", <b>Page: </b>" + (page + 1) + ", <b>RowsPerPage: </b>" + rowsPerPage + ", <b>TotalPages: </b>" + Math.ceil(totalPages) + "<br />" +
	"----------------" + "<br /><br />" +
	"<b>" + serverProtocol + "://" + serverName + ":" + serverPort + "/" + applicationContext + "</b> - [" + serverAddress + "]" + " - <b>isConnected:</b> " + isEventingConnected + "<br /><br />" +
	"<b>Max Connection Retries: </b>" + eventingMaxRetry + "<br />" +
	"<b>Max Same Server Retries: </b>" + failoverServerRetries + "<br /><br />" + 
	"<b>Server List: </b>" + failoverServerList + "<br /><br />" +
	"<b>Reconnection Events History:</b>" +
	"</td></tr></table><br />" +
	"<table width='100%' cellspacing='4px'><tr><td>" +
	"<b>Timestamp</b></td><td><b>Retry</b></td><td><b>ServerRetry</b></td><td><b>EventType</b></td><td><b>URL</b>" + "</td></tr>";
	for(var i=initialIndex ; i < eventingHistory.length && i < (rowsPerPage*(page+1)); i++) {
		eventingSummary += "<tr><td nowrap><i>" + eventingHistory[i].timestamp + "</i></td><td>" + eventingHistory[i].retryCount + "</td><td>" + eventingHistory[i].serverRetryCount + "</td><td><i>" + eventingHistory[i].eventType + "</i></td><td nowrap>" + eventingHistory[i].URL.replace(/\?.*$/ig,"") + "</td></tr>";
	}
	eventingSummary += "</table>";
	
	if(formatted) {
		display_WebSDKAlert(eventingSummary);
	} else {
		eventingSummary = eventingSummary.replace(/<\/td><td[^>]+>/ig," | ");
		eventingSummary = eventingSummary.replace(/<\/td><td>/ig," | ");
		eventingSummary = eventingSummary.replace(/<\/td><\/tr>/ig,"<br />");
		eventingSummary = eventingSummary.replace(/<br \/>/ig,"\n");
		eventingSummary = eventingSummary.replace(/<[^>]+>/ig,"");
		alert(eventingSummary);
	}
}

function setupHeartbeatTimeoutCheck(enabled, timeout) {
	heartbeatTimeoutEnabled = enabled;
	heartbeatTimeout = timeout;
	log.debug('Heartbeat timeout enabled: ' + enabled + ' timeout: ' + timeout + 'sec(s)');
}

/**
 * Function for verifying if the heartbeat is on time
 */
function isHeartbeatOntime() {
	if (!window.frames[EventingID]) {
		log.error("Heartbeat timed out: no pushlet");
		return false;
	}

	heartbeatTimestamp = window.frames[EventingID].lastHeartbeat;
	var now = new Date();
	if(heartbeatTimestamp == null) {
		// not received heartbeat yet
		heartbeatTimestamp = now;
		window.frames[EventingID].lastHeartbeat = heartbeatTimestamp;
		log.debug('HeartBeat check, not received yet, setting HeartBeat = Now = ' + heartbeatTimestamp);
	}
	var heartbeatDiff = (now - heartbeatTimestamp) / 1000;
	if (heartbeatTimeoutEnabled && (heartbeatDiff >= heartbeatTimeout)) {
		log.error('Heartbeat timed out, now: ' + now + ' - lastHeartBeat: ' + heartbeatTimestamp + ' - difference: ' + heartbeatDiff + ' sec(s)');
		return false;
	} else {
		return true;
	}
}

/**
 * Function for verifying if eventing is active on the server side, in order to prevent false failure notification
 */
function isEventingActive() {
	log.info("isEventingActive() ? " + eventingActive);
	try {
		if (eventingActive) {
			log.info("Eventing is active, requestig activity state to the server.");
			var params = "checkEventingActivity=true&uid=" + evt_uid + "&";
			var xmlhttp = createAJAXRequest();
			xmlhttp.open("POST", getFullURL(EventingURL), true);
			xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=UTF-8");
			xmlhttp.onreadystatechange=function() {
				if (xmlhttp.readyState==4) {
					var result = xmlhttp.responseText;
					if(xmlhttp.status != 200 || result!="true") {
						eventingActive = false;
					}
					log.debug("Eventing activity request http status: " + xmlhttp.status + " result: '" + result + "'");
				}
			};
			xmlhttp.send(params);
			window.setTimeout(function() { 
				if(xmlhttp.readyState!=4) {
					try {
						log.debug("Aborting request");
						xmlhttp.abort();
					} catch(e) {
						log.error("Unable to abort: " + e.description);
					}
					// 
					log.info("Check eventing activity state timed out, marking as inactive");
					eventingActive = false;
				} else {
					var result = xmlhttp.responseText;
					if(result!="true") {
						log.debug("Eventing not active: " + result);
						eventingActive = false;
					}
				}
			}, 5000);
		}
		return eventingActive;
	} catch(e) {
		log.error("Unable to check eventing activity: " + e.description);
		log.debug("Returning result as inactive");
	}
	return false;
}



// function sets flag to indicate unload should NOT trigger logoff action
function ignoreUnloadForLogoff() {
   document.ignoreLogoff= true;
   if (window.checkWindowsID) {
	   window.clearInterval(checkWindowsID);
   }
}

// function is invoked when outer display is unloaded
//  - triggers logoff upon if window is last TOP window 
//  - send synchronous action to end the users session
function displayUnLoad() {
   if (document.ignoreLogoff == true) {
      return;
   }
   
	window.closing = true;
	unloadAllDisplays();

   var win = findFirstRemainingOpenWindow(window);
	if (win == null && window.closing) {
      log.info("Last remaining top window, invoking logoff");
      if(!logoffCalled) {
	      logoff();
	  }
   }
}

/** 
 * Function used to verify if the eventing frame is disconnected
 */
function isEventingFrameDisconnected() {
	return (window.frames[EventingID].document.readyState == 'complete');
}

/**
 * Function used to verify if failover is being attempted
 */
function isAttemptingFailover() {
	// (recyclePushlet == false), if pushlet is recycling it's not attempting failover
	return ((isEventingConnected == false || isEventingFrameDisconnected()) && failoverInitialized == true && recyclePushlet == false);
}

/**
 * Function used to verify if WSDK is configured for handling the failover on the clientside
 */
function isClientSideFailover() {
	return (failoverServerList != null);
}

function getServerURL() {
	var serverURL = "";
	if(failoverServerList != null && serverProtocol != "" && serverAddress != "" && serverPort != "" && applicationContext != "")  {
		serverURL = serverProtocol + "://" + serverName + ":" + serverPort + "/" + applicationContext + "/";
	}
	return serverURL;
}

function setServer(protocol, newAddress, serverNm, port, context, footerServerAndPort, dojoUrl)
{
	try {
		if (footerServerAndPort == null){
			footerServerAndPort = serverNm + ":" + port;
		}
		setFooterServerAndPort(footerServerAndPort);
	} catch(e) {
		log.error('Unable to setFooterServerAndPort: ' + e.description);
	}
	var oldServer = serverName;
	
	connectEventing();
	disconnectMessageSent = false;
	serverName = serverNm;
	serverProtocol = protocol;
	serverPort = port;
	context = context.replace(new RegExp("^/"),"");
	applicationContext = context.replace(new RegExp("/$"),"");

	eventingCurrentRetry = 0;
	window.top.topWindow.frames[EventingID].frameElement.style.visibility='visible';

	updateRequireJSModuleLocations("//" + serverName + ":" + serverPort, dojoUrl, oldServer, serverName);
	if(serverAddress != "" && serverAddress != newAddress)
	{	
		// The server name has changed, execute fail over actions
		if(window.top.topWindow.frames[EventingID] != null)
		{
			var msg = globalNLSEntries['failover.connected.start'] + serverName + globalNLSEntries['failover.connected.end.refresh'];
			addMessage(msg, MSG_TYPE_Message, 'eventingConnect', MSG_STD_TIMEOUT, true);
			window.top.topWindow.frames[EventingID].refreshAllWindows(failoverRefreshReason);
		}
		// For ODV we don't show that the display is being refreshed
		var msg = globalNLSEntries['failover.connected.start'] + serverName + globalNLSEntries['failover.connected.end'];
		sendODVMessage(msg);
		handleJBossFailoverEvent(newAddress, serverAddress);
	} else {
	    if (serverAddress != "")
	    {
	        if (recyclePushlet)
			{
			    var msg = globalNLSEntries['recycle.connected.start'] + serverName + globalNLSEntries['recycle.connected.end'];
			    log.info(msg);
			} else if (failoverInitialized) {
			   var msg = globalNLSEntries['failover.connected.start'] + serverName + globalNLSEntries['failover.connected.end'];
			   addMessage(msg, MSG_TYPE_Message, 'eventingConnect', MSG_STD_TIMEOUT, true);
			   sendODVMessage(msg);
			}
	    }
	}
	
	deleteMessageById('connectionFailure', true);
	serverAddress = newAddress;
	showEventingDiv();
	failoverInitialized = true;
    // mark pushlet as not being recycled
	recyclePushlet = false;
	ignoreHeartbeat = true; // Heartbeat timeout will be enabled after when the first event is received
	eventingActive = true; // Eventing is active

	addEventingHistoryEntry(new Date(), "-", "-", "PushletConnected", serverProtocol + "://" + serverName + ":" + serverPort + "/" + applicationContext + "/");

	log.info("Initializing Pushlet - setServer: " + serverProtocol + "://" + serverName + ":" + serverPort + "/" + applicationContext + "/");
}

function updateRequireJSModuleLocations(serverPath, dojoUrl, oldServer, newServer) {

	if(oldServer == null || oldServer === '') {
		return;
	}
	
	for (var key in require.packs) {
		var pack = require.packs[key];
        var location = require.packs[key].location;
    	if(typeof location == "string") {
	        if (location.search(/^http[s]?\:\/\//) === 0) { // absolute paths
	        	updateAbsoluteRequirePath(pack, oldServer, newServer, location);
	        } else if (location.indexOf("//") === 0) { // expected for everything after a previous failover
	            updateProtocolRelativeRequirePath(pack, oldServer, newServer, location);
	        } else if (location.indexOf("/") === 0) { // Siemens custom modules
	        	updateRootRequirePath(pack, serverPath, location);
	        } else { // dojo initial module paths (relative)
	        	updateRelativeRequirePath(pack, serverPath + dojoUrl + key); 	
	        }
    	}
	}
}

function updateRelativeRequirePath(pack, dojoUrl) {
	pack.location = dojoUrl;
}

function updateProtocolRelativeRequirePath(pack, oldServer, newServer, location) {
	pack.location = location.replace("//" + oldServer + ":", "//" + newServer + ":");
}

function updateRootRequirePath(pack, serverPath, location) {
	pack.location = serverPath + location;
}

function updateAbsoluteRequirePath(pack, oldServer, newServer, location) {
	location = location.replace(/^http[s]?\:\/\//, '//');
	updateProtocolRelativeRequirePath(pack, oldServer, newServer, location);
}

/**
 * Function responsible for notifying the backend that a failover has happened, 
 * application eventualy need to be notified of a jboss failover in order to recover 
 * things that can't be recovered through display refresh (e.g. Standalone OLM)
 */
function handleJBossFailoverEvent(newServer, oldServer) {
	window.parent.display_sendAction('HANDLE_JBOSS_FAILOVER', '', 'newServer=' + newServer + "&oldServer=" + oldServer);
}

function setFailoverServerList(retries,list)
{
	failoverServerIndex = 0;
	failoverCurrentServerRetry = -1;
	failoverServerRetries = retries;
	failoverServerList = list;
}

function hideEventingDiv()
{
	var evImg = document.getElementById('eventingIcon');
	if(evImg!=null)
	{
		evImg.style.visibility='hidden';
	}
	
	var evText = document.getElementById('eventingText');
	if(evText!=null)
	{
		evText.style.visibility='hidden';
	}
}

function hideEventingIFrame()
{
	var evIF = document.getElementById(EventingID);
	if(evIF!=null)
	{
		evIF.style.visibility='hidden';
	}
	var evText = document.getElementById('eventingText');
	if(evText!=null)
	{
		evText.style.visibility='hidden';
	}
}

function showEventingDiv()
{
	var evImg = document.getElementById('eventingIcon');
	if(evImg!=null)
	{
		evImg.style.visibility='visible';
	}
	var evText = document.getElementById('eventingText');
	if(evText!=null)
	{
		evText.style.visibility='visible';
	}
}

function setFooterServerAndPort(footerServerAndPort) {
    var openWindows = getOpenWindowList();
	if (openWindows != null) {
		// loop thru all open windows
		for (var i = 0; i < openWindows.length; i++) {
			// skip closed windows
			if (!openWindows[i] || openWindows[i].closed) {
				continue;
			}

			try {
				var element = openWindows[i].document.getElementById('footerServerAndPort');
				if (element) {
					element.innerText = footerServerAndPort;
				}
			} catch (e) {
				log.error('Failed to update footer: ', e);
			}
		}
	} else {
		log.warning('Failed to find openWindowList!');
		try {
			var element = window.top.document.getElementById('footerServerAndPort');
			if (element) {
				element.innerText = footerServerAndPort;
			}
		} catch (e) {
			log.error('Failed to update footer: ', e);
		}
	}
}

function connectEventing(isJboss)
{
	if(isJboss == undefined || isJboss){
		isEventingConnected = true;
	}
	
	var evIcon = document.getElementById('eventingIcon');
	if(evIcon!=null)
	{
		evIcon.classList.remove('WFNoConnectionIcon');
		evIcon.classList.add('WFConnectedIcon');
	}
	var evText = document.getElementById('eventingText');
	if(evText!=null)
	{
		evText.innerHTML=globalNLSEntries['connectivity.connected'];
	}
}

function disconnectEventing(isJboss)
{
	if(isJboss == undefined || isJboss){
		isEventingConnected = false;
	}
	
	var evIcon = document.getElementById('eventingIcon');
	if(evIcon!=null)
	{
		evIcon.classList.remove('WFConnectedIcon');
		evIcon.classList.add('WFNoConnectionIcon');
	}
	var evText = document.getElementById('eventingText');
	if(evText!=null)
	{
		evText.style.visibility="";
		evText.innerHTML=globalNLSEntries['connectivity.disconnected'];
	}
}

function setWindowAsTopWindow(inOpenWindowList)
{
    var isNewTopWin = ( window != top.topWindow);
    
    // if window is same as the current top window then stop processing....
    if (!isNewTopWin) {
        return;
    }
    
    if(inOpenWindowList == null) { inOpenWindowList = []; }
	
    // Creating new window list array  
    openWindowList = [];
    openWindowList[0] = window;

    if(inOpenWindowList != null) {
    	// Update window list array with input list contents
        var nCounter = 1;
        for (var inCounter = 0; inCounter < inOpenWindowList.length; inCounter++) {
            if(inOpenWindowList[inCounter]==null || inOpenWindowList[inCounter]==window) {
                continue;
            }
            openWindowList[nCounter] = inOpenWindowList[inCounter];

            // the window has moved into slot nCounter, so update the number at the end of the title
            setWindowTitle(nCounter);
            
            nCounter++;
        }
    }

    // if Singleton logger and initial setting of topWindow mark window as window 0
    if (window.LOGGER_SINGLETON && (window.winName == null || window.winName == logger.LOG_ALL_WINDOWS))
    {
        log.updateWindowName("0");
    }
     
    // If setting top window is NOT initial callup and Logger Singleton is specified
    // then recreate logger instance and update all references of logger in the 
    // other windows opened
    if (window.LOGGER_SINGLETON)
    {
        // create new CLONED instance of logger
        // when new top window occurs.   Browser / JS doesn't let you access
        // object instance created in another window after that window is closed
        try
        {
            var newLogger = new LOGGER();
            newLogger.COPY_LOGGER(logger);
            logger = newLogger;
            log.updateLogger(logger);
        }
        catch(ex) {
            alert("exception creating logger : " + ex.toString());
        }

        // for all other opened windows, update reference to logger and log data	   
        for (var i = 0; i < openWindowList.length; i++)
        {
            var inWin = openWindowList[i];
			
            //only update window if window is open and not the NEW top window
            if (inWin != null &&  inWin != window && !inWin.closed)
            {
                // update the log reference for all open windows...
                if (inWin.updateLogger != null)
                {
                    openWindowList[i].updateLogger(logger);
                }	
            }
        }
    }

    strayDeltas = {};

    // Set this window as top window
    top.topWindow = window;
    
    thisIsTheTopWindow = true;

	log.debug('topWindow updated');

	// the window has moved into slot 0, so update the number at the end of the title
	setWindowTitle(0);

	// add handler for the unload event, this function is on sdk.js
	if (window.attachEvent) //ie
	{
	   window.attachEvent("onunload", handleTopWindowShutDown);
	}
	else // netscape
	{
	   window.addEventListener("beforeunload", handleTopWindowShutDown, false);
	}

	//reset variable for topWindow for existing windows	
	for (var i = 1; i < openWindowList.length; i++)
	{
		try 
		{
			if (openWindowList[i] != null && (openWindowList[i].document))
			{
				openWindowList[i].topWindow = topWindow;
				openWindowList[i].hideEventingDiv();
			}
		}
		catch (e)
		{
			log.error('Failure at SetWindowAsTopWindow: ' + e.description);
		}
	}	

	if(window.pushletEnabled == true) {
		require(['dojo/domReady!'], function(){
			// Note: we could load the pushlet on domReady!, but then document.readyState
			// will not reach 'complete' until if and when the pushlet connection is ended.
			initEventing();
			showEventingDiv();
		});
	}
}


function transferOpenWindowList(inOpenWindowList) {

	// Creating new window list array  
    openWindowsRef = [];
    
    if(inOpenWindowList != null) {
    	// Update window list array with input list contents
        var nCounter = 0;
        for (var inCounter = 0; inCounter < inOpenWindowList.length; inCounter++) {
            if(inOpenWindowList[inCounter]==null) {
                continue;
            }
            
            openWindowsRef[nCounter] = inOpenWindowList[inCounter];
            
            nCounter++;
        }
    }
}

function transferTopWindowInformation(inCurrentEventSeqId, inFailoverServerList, inServerProtocol, inServerAddress, inServerName, inServerPort, inApplicationContext, inStrayDeltas) {
	
	// Update sequence id
	currentEventSeqId = inCurrentEventSeqId;

	// Set previous server variables to force pushlet to be reconnected to the same server it was connected to
	if(inFailoverServerList != null && inServerProtocol != "" && inServerAddress != "" && inServerPort != "" && inApplicationContext != "")  {
		failoverServerList = inFailoverServerList;
		serverProtocol = inServerProtocol;
		serverAddress = inServerAddress;
		serverName = inServerName;
		serverPort = inServerPort;
		applicationContext = inApplicationContext;
		if (inStrayDeltas) {
			strayDeltas = {};
			for (var key in inStrayDeltas) {
				var deltas = inStrayDeltas[key];
				if (deltas && deltas.length > 0) {
					// efficiently copy the array to the new window
					strayDeltas[key] = Array.prototype.slice.call(deltas, 0);
					log.trace('Transfered ' + deltas.length + ' "stray" deltas to the new topWindow');
				}
			}
		}
		log.debug("Saving top window server: " + serverProtocol + "://" + serverName + "[" + serverAddress +  "]:" + serverPort + "/" + applicationContext);
	}
}

var EventingID = "eventing";
var EventingURL = "receiveEvents.do";
var EventingInterval = 5000;
var eventingRunning = false;
var evt_uid = ""; 

function getEventingURL() {
	var url = EventingURL + '?currentEventSeqId=' + currentEventSeqId +'&uid='+evt_uid+'&eventingReconnection=true&currentServerAddr='+serverAddress ;
	if (top.window.logonPage == true) {
		url += "&logonPage=true";
	}
	return url;
}

function createPushletFrame(event)
{
	if(window.top.topWindow.frames[EventingID] != null)
	{
		return true;
	}
	
	if (top.document == null || top.document.body == null) {
		return false;
	}
	
	var ifr = window.top.document.createElement('IFRAME');
	ifr.id = EventingID;
	ifr.name = EventingID;
	var url = getEventingURL();
	
	ifr.src = url;
	ifr.style.width='23px';
	ifr.style.height='23px';
	ifr.style.border=0;
	ifr.style.visibility='hidden';
	ifr.style.position='absolute';
	ifr.style.bottom='0px';
	ifr.style.right='0px';
	
	try {
		top.document.body.appendChild(ifr);
	}
	catch(e) {
		log.error("error adding element ex: " + e);
		return false;
	}
	
	// NOTE: load event is not fired until pushlet is FINISHED processing content
	ifr.addEventListener("load",reloadFrameFunction,true);
	
	return true;
}

// create and start new eventing process in new eventing window
function initEventing()
{
	try
	{
		//Don't make duplicate frames
		if(window.top.topWindow != null && window.top.topWindow.frames[EventingID] != null)
		{
		    //addMessage('Eventing Running', MSG_TYPE_Message);
			return;
		}

		// check window state
		if (document.readyState !== 'complete')
		{
			var loadInitEventing = function() {
				document.onreadystatechange = null;
				initEventing();
				if (window.removeEventListener)
					window.removeEventListener('load', initEventing, false);
			};

			// not ready, wait
			document.onreadystatechange = loadInitEventing;

			// fallback to onload
			if (window.addEventListener) {
				window.addEventListener('load', loadInitEventing, false);
			}
			return;
		}

		check window state  
		if (msie)
		{
			var url = getEventingURL();
			// start eventing
			document.body.insertAdjacentHTML('beforeEnd','<IFRAME id="' + EventingID+ '" SRC="' + getFullURL(url) + '" onError="hideEventingIFrame()" frameborder=\'0\' scrolling=\'no\' Allowtransparency=\'true\' height=\'25px\' width=\'25px\' marginheight=\'25px\' marginwidth=\'25px\' style="z-index:100; display:block; position:absolute; bottom:0; right:0; margin: 0 0 0 0; padding: 0 0 0 0; overflow: hidden"><\/IFRAME>');
			
	   		eventingRunning = true;
		}
		else
		{
			// Mozilla
			
			
			reloadFrameFunction=function(event) {
				top.window.initPushletTimeoutId = setTimeout("hideEventingIFrame();"+
					"log.info('restarting pushlet from setTimeout...'); "+
					"if( !stopRetry )"+
					"{" +
					"	if(eventingCurrentRetry < eventingMaxRetry)"+
					"	{"+
					"		disconnectEventing();"+
					"		var msg = globalNLSEntries['failover.disconnect.start'] + serverName + globalNLSEntries['failover.disconnect.end'];" +
					// NOTE: on firefox reload function is what triggers pushlet recycle, not monitoring frame
					// when recycling do not show error message on first retry
					"		if (!(recyclePushlet && eventingCurrentRetry == 0)) { " +
					"			addMessage(msg, MSG_TYPE_ERROR, 'connectionFailure', 0, true);"+
					"			addMessage(globalNLSEntries['failover.attempt'] + (++eventingCurrentRetry) + '. . .', MSG_TYPE_WARNING, 'failoverAttempt', MSG_STD_TIMEOUT, true);"+
					" 		}" +
					"		if(!disconnectMessageSent) {" +
					"			disconnectMessageSent = true;" +
					" 			sendODVMessage(msg);" +
					"		}" +
					"		recycleEventing();"+
					"	} else {" +
					"		sendODVMessage(globalNLSEntries['failover.reconnection.failure.odv'], true);" +
					"		parent.applicationSessionHandler(true);" +
					"	}" + 
					"}", 360000);
			};
			eventingRunning = createPushletFrame();
		}

	}
	catch (e) {addMessage('Failed to initialize eventing frame: ', e);}
	
	// start process to monitor eventing process
	if (eventingRunning)
	{
		log.debug('eventing frame initialized');
		monitorEventing();
	}
}

function getFailoverServer() {
	var failoverServer = null;
	if(failoverServerList != null && failoverServerList.length > 0) {
		if(failoverCurrentServerRetry < (failoverServerRetries - 1)) {
			failoverCurrentServerRetry++;
		} else {
			failoverCurrentServerRetry = 0;
			failoverServerIndex++;
			if(failoverServerIndex > failoverServerList.length) {
				failoverServerIndex = 0;
			}
		}
		
		if(failoverServerIndex == 0) {
			failoverServer = getServerURL();
		} else {
			failoverServer = failoverServerList[failoverServerIndex - 1] + "/" + applicationContext + "/";
		}
	}
	log.info("Getting failover server: " + failoverServer + " retries: " + failoverCurrentServerRetry);
	return failoverServer;
}

// restart new eventing process in existing eventing window
function recycleEventing()
{
	// get reference to eventing iframe
	var eventingFrameRef = window.frames[EventingID];
	try
	{
		var failoverUrl = getEventingURL();
		var failoverServer = getFailoverServer();
		if(failoverServer != null) {
			failoverUrl = failoverServer + failoverUrl;
		}

		
		// recycle may be caught by timeout or monitor code.. 
		// prevent timeout code from executing if it hasn't been triggered yet
		// which will prevent recycle from happening twice
		if (top.window.initPushletTimeoutId && top.window.initPushletTimeoutId > 0) {
			window.clearTimeout(top.window.initPushletTimeoutId);
			top.window.initPushletTimeoutId = -1;
		}
	
		var recycleReason = ((recyclePushlet && eventingCurrentRetry <= 1)?"PushletRecycle":"PushletReconnectionTry");
		addEventingHistoryEntry(new Date(), eventingCurrentRetry, failoverCurrentServerRetry, recycleReason, failoverUrl);
//		// Debug messages
//		addMessage('Cookie : ' + document.cookie);
//		addMessage('URL : ' + failoverUrl);
		eventingFrameRef.location = failoverUrl;
	}
	catch(e)
	{
		log.error("Unable to restart eventing: ", e);
		addMessage('Unable to restart eventing.', MSG_TYPE_ERROR);
	}	
}

// forces a failover to occur.  This function is only used for debugging
function forceFailover()
{
	if(failoverServerList != null && failoverServerList.length > 0) {
		failoverServerIndex++;
		if(failoverServerIndex > failoverServerList.length) {
			failoverServerIndex = 0;
		}
		recycleEventing();
	}
	else {
		refreshAllWindows(failoverRefreshReason);
	}
}

// monitorEventing is an iterative process that montitors the eventing process
// and restarts it if it dies
var monitorEventingTimer;
function monitorEventing() {
	try {
		var eventingIFrame = window.top.topWindow.frames[EventingID];
		if (eventingIFrame) {
			var restartPushlet=false;
			try {
				if (eventingIFrame.document.readyState == 'complete') {
						eventingIFrame.frameElement.style.visibility='hidden';
						restartPushlet= true;
					hideEventingIFrame();

					// if recycling the pushlet failed to re-establish then
					// start failover...
					if (recyclePushlet && eventingCurrentRetry > 0) {
						recyclePushlet = false;
						failoverInitialized = true;
						log.error('Eventing retry > 0, pushlet recycle failure. Initializing failover !');
					}
				} else if (heartbeatTimeoutEnabled && isEventingConnected) {
					var reloadPushlet = false;
					
					// Pushlet will set pageState variable on loading of frame
					// - first JS in head will set state to INIT
					// - last JS cmd in body will set state to LOADED
					
					var pageState = eventingIFrame.pageState;
					if (pageState != null) {
						var heartBeatOK = isHeartbeatOntime();
						
						if (!heartBeatOK) {
							// if Pushlet has just started loading content then give approprirate time to transition
							// from INIT to LOADED
							if (pageState == "INIT") {
								if (eventingIFrame.loadingTS == null) {
									log.info("Setting timestamp to allow page to transition from INIT to LOADED");
									eventingIFrame.loadingTS = new Date();
								} else {
									var threshold = 30000; // threshold > monitor interval
									var delta = (new Date()).getTime() - eventingIFrame.loadingTS.getTime();
									if (delta > threshold) {
										log.error("Reloading pushlet due to pageState did not transition from INIT to LOADED in time(ms): " + threshold);
										reloadPushlet = true;
										eventingIFrame.loadingTS = null;
									}
								}
							}
							// if page has loaded ALL of the initial content
							else if (pageState = "LOADED") {
								log.error("Eventing frame is loaded but heartbeat is behind, reload pushlet");
								reloadPushlet = true;
							}

							log.info("heartbeat is behind..check if eventing is active");
							var eventActive = isEventingActive();
							if (eventActive) {
								// NOTE this case was hit on CAISO
								// Server had pushlet connection and able to
								// write events
								// Client had pushlet but was not processing
								// events
								log.error("Server reporting eventing as active but heartbeat is behind!!");
							}
						}
						// TODO: ?? should we also check when (heartbeat == OK) that isEventingActive() returns true
						// In my opinion this would detect that the JBoss server is responsive
						// or not for NEW requests.. It doesn't mean the current pushlet is bad if it fails

					}
					// if JS pageState at head of page has not loaded then check if frame is in process of loading
					else {
						// check if eventing frame is being loaded but has
						// NOT yet loaded JS for Eventing Frame's pageState variable
						var readyState = eventingIFrame.document.readyState;
						if (readyState == "interactive" || readyState == "loading ") {
							// put TS to determine if frame loads JS from
							// pushlet in some threshold
							// if not mark page as dead
							if (eventingIFrame.initLoadingTS == null) {
								eventingIFrame.initLoadingTS = new Date();
							} else {
								var threshold = 30000; // threshold >
								// monitor interval
								var delta = (new Date()).getTime() - eventingIFrame.initLoadingTS.getTime();
								if (delta > threshold) {
									log.error("Reloading pushlet due to pageState NOT set for pushlet in time(ms): "
											+ threshold);
									reloadPushlet = true;
									eventingIFrame.initLoadingTS = null;
								}

							}
						}
					}

					if (reloadPushlet) {
						log.info("Marking pushlet to be reloaded");
						restartPushlet = true;
						recyclePushlet = true;
						hideEventingIFrame();
					}
				}

			} catch (e) {
				restartPushlet=true;
				hideEventingIFrame();
			}

			// if pushlet is trying to be restarted due to recycle event then
			// display appropriate messages
			// and reconnect the pushlet
			if (restartPushlet && recyclePushlet && eventingCurrentRetry == 0) {
				eventingCurrentRetry++;

				disconnectEventing();

				var msg = globalNLSEntries['recycle.disconnect.start'] + serverName
						+ globalNLSEntries['recycle.disconnect.end'];

				log.info("restarting pushlet from monitor for first attempt...");
				log.info(msg);
				recycleEventing();

			} else if (restartPushlet && failoverInitialized && !stopRetry) {
				updateConnectivityStatus(connections);
				if (eventingCurrentRetry < eventingMaxRetry) {
					disconnectEventing();
					var msg = globalNLSEntries['failover.disconnect.start'] + serverName
							+ globalNLSEntries['failover.disconnect.end'];
					// Do not show for the first attempt to reconnect
					if (eventingCurrentRetry > 1) {
						addMessage(msg, MSG_TYPE_ERROR, 'connectionFailure', 0, true);
						addMessage(globalNLSEntries['failover.attempt'] + (++eventingCurrentRetry) + '. . .',
								MSG_TYPE_WARNING, 'failoverAttempt', MSG_STD_TIMEOUT, true);
					}else{
						++eventingCurrentRetry;
					}

					if(!disconnectMessageSent) {
						disconnectMessageSent = true;
						sendODVMessage(msg);
					}
					recycleEventing();
				} else {
					addEventingHistoryEntry(new Date(), eventingCurrentRetry, "-", "MaxRetryReached", "-");
					sendODVMessage(globalNLSEntries['failover.reconnection.failure.odv'], true);
					closeWindowsOnSessionChange(true);
					return;
				}
			}
		}
	} catch (e) {
		log.error('Failure at monitorEventing: ' + e.description);
	}

	// if monitorEventing not running, start the process
	try {
		if (!(monitorEventingTimer)) {
			monitorEventingTimer = setInterval(monitorEventing, EventingInterval);
		}
	} catch (e) {
		log.error('Failure at monitorEveting - setTimer: ', e);
	}
}

function sendODVMessage(message, closeWindow) {
	if(closeWindow == null) {
		closeWindow = false;
	}
	try {
		// passMsg only exists under ODVWebUI application
		if(window.passMsg) {
			passMsg(message, closeWindow);
		}
	} catch(e) {}
}

function checkWindowReferencesInterval() 
{
   log.trace("start");
   try
   {
      // if popup window cancel timer, NOTE it is possible interval started first time
      // before property was set by calling window...hence the second check
      if (window.top.isUnmanagedPopup) 
      {
         clearInterval(checkWindowsID);
         return;
      }
   
      var openWindowList = [];
   
      // if top window.. check for any window references that have gone away
      if (thisIsTheTopWindow) 
      {
      	 log.trace("is topWindow");
         if (topWindow == null ) {
         	log.error("isTopWindow but topWindow is null");
         	window.topWindow = window;
         }
         
         openWindowList = topWindow.openWindowList; 
         
      }
      else
      { 
         log.trace("NON topWindow");   	

         try {
         	if (topWindow != null) {
         	   // noOp.. all is good
         	}
            // if no top window..but opener knows about its top window then re-assign
            else if (window.opener != null && !window.opener.closed  && window.opener.top.topWindow != null) 
            {
            	log.debug("assigned topWindow from opener");   	
                topWindow = window.opener.top.topWindow;
            }
            // check to see if one of the registered windows in window list can be used to get topWindow
            else
            {
               var openWindowsRef = window.openWindowsRef;
               if (openWindowsRef != null) {
                  for (var i=0; i < openWindowsRef.length; i++) {
                     var win = openWindowsRef[i];
                     if (win != null && !win.closed && win.topWindow != null) {
                        log.info("assigned topWindow from another registered window");
                        topWindow = win.topWindow;
                        break;
                     }
                  }
               }
               if (topWindow == null) {
                  // NOTE: if we are hitting this case ( fall back code) we may need to force this list to be up to date
                  // by either copying it each poll cycle (when top window is found) or whenever windows are added / closed
                  log.debug("unable to find TopWindow from local copy of open window list");
               }
            }
         }
         catch(e) 
         {
            log.error("caught exception accessing opener.top to find top window");     
         }
        
         
         // if STILL NO top window defined...How did reference get lost??
         if (topWindow == null ) 
         {
             checkWindowFailCnt++;
             
             // add error message after N failures
             if (checkWindowFailCnt >= checkWindowErrorMsgAfterCnt) {
                var msg = globalNLSEntries['window.topwindow.reference.disconnected'];
             	addMessage(msg,MSG_TYPE_ERROR, msg);
             }
             
             
             if (checkWindowFailCnt >= maxCheckWindowFails) 
             {
             	log.error("No top window defined for child window..closing dead window!!!");
             	if (top.showErrorDisplay) {
             	   top.showErrorDisplay( 'window.disconnected', 'window.disconnected.unloaded',window.top);
                }
                else {
                   // close window;
                   top.window.close();
                }
                clearInterval(checkWindowsID);
             }
             else
             {
                log.info("No top window defined for child window count:" + checkWindowFailCnt + " !!..try again next interval");
             }
             return;   
         }
         else {
            if (checkWindowFailCnt > 0 ) {
               log.info("window reconnected to top window");
               deleteMessageById("window.disconnected");
               checkWindowFailCnt = 0;
            }
         }

         try {
            log.trace("getting OpenWindow list from topWindow");   
		    openWindowList = topWindow.openWindowList;
         }
		 catch(e) {	
		    log.debug("Exception getting OpenWindow list from topWindow and using local copy");   
		    var openWindowsRef = window.openWindowsRef;
            if ( openWindowsRef != null) {
			   for(var i = 0; i < window.openWindowsRef.length; i++) {
			      if (openWindowsRef[i] && openWindowsRef[i] != null && !openWindowsRef[i].closed) {
				     if (openWindowsRef[i] == window) {
					    try {
						   log.info("setting NEW top window...");
						   window.setWindowAsTopWindow(openWindowsRef);
						}
						catch(e) {
						   log.error('Error setting new top window. Error:' + e.name + ' - ' + e.description);
						}
													
						break;	
                     }		
				  }		
			   }
			   log.debug("assigning window list from ref copy");   
			   openWindowList = openWindowsRef;
            }
         }
      }	
      
      log.trace("check if window registered");  
      
      // make sure the window is registered
      var found = false;
      if (openWindowList == null) {
         log.error("window not registered and topWindow not found");
      }
      else {
	     for (var i = 0; i < openWindowList.length; i++) {
		    if (openWindowList[i] != null && openWindowList[i] == window) {
			   found = true;
			   break;
			}
	     }
	  }
	  
	  log.trace("window found: " + found);  
         
      // if server is not found then try to re-register window... probably should
      // refresh the display too
      if (!found) 
      {
         if (topWindow == null) {
            log.error("window not registered and topWindow not found");
         }
         else {
         	log.info("re-registring display and refreshing content");
         	topWindow.addWindowToOpenWindowList(window);     
            reloadContentDisplay();
         }
      }   			 		 	
   }
   catch(e) 
   {
      log.error("Error processing exception: ", e);
   }
   
}

// Used by logger window to simulate a window reference being lost
function removeWindowFromList() {
   var openWindowList = top.topWindow.openWindowList;
   for (var i = 0; i < top.topWindow.openWindowList.length; i++) {
      if (openWindowList[i] != null && openWindowList[i] == window) {
	     openWindowList[i] = null;    
		 break;
	  }
   }
}

function reloadContentDisplay() 
{

   log.info("reloading content frame");
   var contentFrame = getContentFrame();
   if (contentFrame != null && contentFrame.document != null) 
   {
      var contentDoc = contentFrame.document;
      var pageId = contentDoc.getElementById(PAGE_ID_FIELD_NAME);
      if (pageId != null && pageId.value != "" && contentFrame.nondeltaDisplayUpdate)
      {
         contentFrame.nondeltaDisplayUpdate( contentFrame, pageId.value);
      }
      else
      {
         addMessage("Unable to reload content frame upon reconnecting display");
      }
   }
}


/*
  Methods are only for testing purposes.... 
*/
function removeFromParent()
{
  addMessage("test removeFromParent");
  
   if (!thisIsTheTopWindow) 
   {
       if (topWindow != null ) 
       {
           var openWindowList = topWindow.openWindowList;
           for (var i = 1; i < openWindowList.length; i++)
	       {
		     if (openWindowList[i] != null && openWindowList[i] == window)
			 {
			     openWindowList[i] = null;
			 }
		   }
       } 
       topWindow = null;
   }
      
}

function checkChildWindows() 
{
   addMessage("test checkChildWindows");

   if (!thisIsTheTopWindow || topWindow == null)  { return; }
   
   var openWindowList = topWindow.openWindowList;
   for (var i = 1; i < openWindowList.length; i++)
   {
      if (openWindowList[i] != null && !(openWindowList[i].closed))
	  {
	     openWindowList[i].addMessage("child window!!");
	  }
   }
}

/** 
 * Function used to update the menu content on all windows through a delta replacement and reload the content frame
 */
function reloadDisplayOnAllWindows() {
    log.info("replacing displays");
	if (openWindowList != null) {
		// loop thru all open windows
		for ( var i = 0; i < openWindowList.length; i++) {
			try {
		        log.info("calling reload content display");
				openWindowList[i].refreshCurrentPage();
			} catch (e) {
				log.error('Failed to reload content display: ', e);
				}
		}
	} else {
		try {
			log.info("reloading content, windowlist is null");
			refreshCurrentPage();
		} catch (e) {
			log.error('Failed to reload content display: ', e);
		}
	}
}

/**
 * Function used to highlight the menu item
 */
function selectMenuItem(menuId) {
		var topMenuId = menuId.split('.',1);

		// Highlight tab menu
		if (window.document.getElementById(topMenuId) != null) {
			handleTabMenuClick(window.document.getElementById(topMenuId), '\'' + topMenuId + '\'');
		}
   	 	
		// Change 2nd tier menu
		change_menu(topMenuId, menuId);
}

/**
 * Function used to replace the menu with the new delta
 */
function replaceMenuContent(menuDelta) {
	try {
		cleanMenuBuild();
		var updateElement = document.getElementById(menuDelta.componentID);
		if(updateElement) {
			processDeltaReplace(updateElement, menuDelta);
		} else {
			log.trace("DropDown menu NOT found, ignoring replacement");
		}
	} catch(e) {
		log.error('Failed to replace MenuContent: ', e);
	}
}

function updateConnectivityInfo(server, newConnections) {
	updateServerInfo(server);
	updateConnectivityStatus(newConnections);
}

/**
 * Update the jboss connection info shown in the info box
 * 
 */
function updateServerInfo(server) {
	var infoBox = document.getElementById('debugInfo');
	if (infoBox) {
		var visibility = infoBox.style.visibility;
		if (!visibility  || visibility == 'visible') {
			document.getElementById("serverProp").innerHTML = server;
		}
	}
}

/**
 * Update the connection status list with the new info from the pushlet (ConnectivityMonitor)
 */
function updateConnectivityStatus(newConnections) {
	
	defineConnectionStatus(newConnections);
	
	// if there are any changes on the connections, perform the determined actions
	if(connectionStatusChange) {	
		if(hasDisconnections == true) {
			disconnectEventing(false);
		} else {
			connectEventing(false);
		}
	
		var windowList = top.openWindowList;
		if(windowList != null) {	
			var popup =	windowList[0].document.getElementById('conMonitor');
			if(popup.style.visibility == '' || disconnectionWarning) windowList[0].display_openConnectionMonitor(false);
			
			// go through all windows and hide/display the disconnection watermark
			for(var i = 0; i < windowList.length; i++) {
				var contentFrame = windowList[i].getContentDom();
				var visibility = (disconnectionWarning == true) ? '' : 'none';
				
				var waterMark = contentFrame.getElementById('disconWaterMark');
				if(waterMark){
					waterMark.style.display = visibility;
				}
				var waterMarkImg = contentFrame.getElementById('disconWaterMarkImg');
				if(waterMarkImg){
					waterMarkImg.style.display = visibility;
				}
			}
		}
	}
}

/**
 * Updates the connection list with the new info provided by the connectivity 
 * monitor task and determine if an action should occur (e.g. bring up the 
 * connection monitor, show disconnection watermark, etc.)
 */
function defineConnectionStatus(newConnections) {
	
	var newConLength = newConnections.length, conLength = connections.length;
	connectionStatusChange = false;
	hasDisconnections = false;
	
	// check if the jboss connection is down and react accordingly
	if(!isEventingConnected) {
		updateJbossConnectionDetails();
		return;
	}

	// go through the new connection list and determine what has changed
	for(var i = 0; i < newConLength; i++) {
		var newConInfo = newConnections[i].split(","), foundEqual = false;
		
		for(var j = 0; j < conLength; j++) {
			var conInfo = connections[j].split(",");
			if(newConInfo[1] == conInfo[1]) {
				foundEqual = true;
		
				// if the state of a connection has changed, mark for update
				if(newConInfo[3] != conInfo[3]) {
					connectionStatusChange = true;	
					
					// if the new status is disconnected, mark to show the popup and display disconnection warning
					if(newConInfo[3] == '0') {
						disconnectionWarning = true;
						hasDisconnections = disconnectionWarning;
					} 
				} 
				break;
			}
			if(newConInfo[3] == '0') {
				hasDisconnections = true;
			}
		}
		// in case the connection is new, mark it for update and check if it requires a disconnection warning
		if(!foundEqual) {
			connectionStatusChange = true;
			if(newConInfo[3] == '0') {
				disconnectionWarning = true;
			}
		}
	}

	if(!hasDisconnections) {
		disconnectionWarning = hasDisconnections;
	}
	if(disconnectionWarning) {
		top.openWindowList[0].document.getElementById('acknowledgeButton').disabled = false;
	}
	connections = newConnections;
}

/**
 * When the jboss server goes down, update the jboss connections to disconnected
 * on the monitor popup
 */
function updateJbossConnectionDetails() {
	
	// go through the connection array and change the status of jboss connections
	connectionStatusChange = false;		
	for(var i = 0; i < connections.length; i++) {
		var conInfo = connections[i].split(",");
		
		if(conInfo[0] == globalNLSEntries['connectivity.name.server'] && conInfo[3] == '1') {
			connectionStatusChange = true;
			disconnectionWarning = true;
			connections[i] = conInfo[0] + "," + conInfo[1] + "," + conInfo[2] + ",0";
		}
	}
	hasDisconnections = true;
}

/**
 * This functions makes the disconnection watermark to all windows.
 */
function acknowledgeDisconnection() {
	// resets the disconnection warning that triggers the watermark
	top.disconnectionWarning = false;
	
	// go through all windows and hide the disconnection watermark
	var windowList = top.openWindowList;
	if(windowList != null) {
	
		windowList[0].document.getElementById('acknowledgeButton').disabled = 'disabled';
		for(var i = 0; i < windowList.length; i++) {
			var contentFrame = windowList[i].getContentDom();
			var waterMark = contentFrame.getElementById('disconWaterMark');
			if (waterMark) {
				waterMark.style.display = 'none';
			}
			var waterMarkImg = contentFrame.getElementById('disconWaterMarkImg');
			if (waterMarkImg) {
				waterMarkImg.style.display = 'none';
			}
		}
	}
}

/* start block _extraContent_ */

/* end block */