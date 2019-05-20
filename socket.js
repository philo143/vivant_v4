require('dotenv').config();
var app = require('express')();
var fs = require( 'fs' );
var Redis = require('ioredis');
var redis = new Redis();

PORT = typeof process.env['NODEJS_PORT'] !== "undefined" ? process.env['NODEJS_PORT'] : 3000 ;
PROTOCOL = typeof process.env['PROTOCOL'] !== 'undefined' ? process.env['PROTOCOL'] : 'http';
//PROTOCOL = 'https';
if ( PROTOCOL == 'https') {
	var https = require('https');
	var server = https.createServer({
	    key: fs.readFileSync('/etc/nginx/ssl/v4.teamacaciasoft.com/371482/server.key'),
	    cert: fs.readFileSync('/etc/nginx/ssl/v4.teamacaciasoft.com/371482/server.crt')
	},app);
}else {
	var server = require('http').Server(app);
}

var io = require('socket.io')(server);

redis.psubscribe('app.*', function(err, count) {

});

redis.on('pmessage', function(channel, pattern, message) {
    console.log('Message Recieved: ' + message);
    message = JSON.parse(message);
    io.emit(pattern +':' + message.event, message.data);
});

server.listen(PORT, function(){
    console.log('Listening on Port 3000');
});