
var socket = new WebSocket("ws://192.168.99.100:32769");
var types = ['registration', 'server_to_user', 'user_to_server', 'user-to-user', 'error', 'user_list', 'getMyMessages'];
var my_id = null;
var userlistIntervalHandle, chatboxIntervalHandle;

socket.onopen = function() {
	addMesFromServer('Connection established.');
    userlistIntervalHandle = setInterval(requestUserlist, 1000);
	chatboxIntervalHandle = setInterval(requestMyMessages, 1000);
};

socket.onclose = function(event) {
    if (event.wasClean) {
	    addMesFromServer('Connection ended clean.');
     
    } else {
        console.log(addMesFromServer('Connection unexpectedly died.')); // например, "убит" процесс сервера
    }
    console.log('Code: ' + event.code + ' reason: ' + event.reason);
    clearInterval(userlistIntervalHandle);
	clearInterval(chatboxIntervalHandle);
};


socket.onmessage = function(event) {
    try {
        var data = JSON.parse(event.data);
      
        if (data.type === types[0]) {
            //registration
        } else if(data.type === types[1]) {
	        //remember id
	        my_id = data.to;
            //message from server
	        //console.log("Message from server: " + data.text);
	        addMesFromServer(data.text);
	        
        } else if(data.type === types[4]) {
	        //message about error from server
	        socket.close(4000, 'Error from server: ' + data.text);
	        addMesFromServer('Error from server: ' + data.text);
	        
        } else if(data.type === types[5]) {
	        //userlist update
	        var userList = JSON.parse(data.text);
	        //clear options
	        var select = document.getElementById('sendTo');
	        select.innerHTML = '';
	        //add each user as <option> to <select>
	        for (var i = 0; i < userList.length; i++) {
	        	if (userList[i].id !== my_id) {
			        var opt = document.createElement('option');
			        opt.value = userList[i].id;
			        opt.innerHTML = userList[i].name;
			        select.appendChild(opt);
		        }
	        }
	        
        } else if(data.type === types[6]) {
	        //user's message box's update
	        var messages = JSON.parse(data.text);
	        var chatbox = document.getElementById('chatbox');
	        //clear text
	        chatbox.innerHTML = '';
	        //add each message to message box
	        for (var i = 0; i < messages.length; i++) {
		        var span = document.createElement('span');
		        var p = document.createElement('p');
		        p.innerHTML = '<span style="color:#4f9fff">' + messages[i].from + '</span> to '
			        + '<span style="color:#4f9fff">' + messages[i].to + '</span>: ' + messages[i].text;
		        chatbox.appendChild(p);
	        }
        }
    
    } catch(err) {
        console.log(err);
        if (err instanceof SyntaxError) {
            console.log("Can't parse message from server. Disconnecting");
        } else {
            console.log("Unrecognized error: " + err.message + " Disconnecting");
        }
        socket.close(4000, 'Failed to decode data from server as json');
    }
};

socket.onerror = function(error) {
    console.log("Ошибка " + error.message);
};

function changeName() {
	var usernameElement = document.getElementById('username');
	if (usernameElement !== null) {
		var message = {};
		message.text = usernameElement.value;
		message.from = my_id;
		message.to = 0; //0 indicates server
		message.type = types[0];
		socket.send(JSON.stringify(message));
	} else {
		throw new Error("Couldn't find element 'username'");
	}
}

function addMesFromServer(text) {
	var serverbox = document.getElementById('serverbox');
	var p = document.createElement('p');
	//text is already escaped by server
	p.innerHTML = text;
	serverbox.appendChild(p);
}
function requestUserlist() {
	var message = {};
	message.text = '';
	message.from = my_id;
	message.to = 0; //0 indicates server
	message.type = types[5];
	socket.send(JSON.stringify(message));
}

function requestMyMessages() {
	var message = {};
	message.text = '';
	message.from = my_id;
	message.to = 0; //0 indicates server
	message.type = types[6];
	socket.send(JSON.stringify(message));
}

function sendMessage() {
	var select = document.getElementById("sendTo");
	var selectedUserId = select.value;
	var userMessageElement = document.getElementById("user_message");
	var userMessage = userMessageElement.value;

	if (selectedUserId > 0 && userMessage.length > 0) {
		var message = {};
		message.text = userMessage;
		message.from = my_id;
		message.to = selectedUserId;
		message.type = types[3];
		socket.send(JSON.stringify(message));
		userMessageElement.value = '';
	}
}
