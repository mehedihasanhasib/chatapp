const socket = new WebSocket('ws://127.0.0.1:6001');

        socket.onopen = function() {
            console.log('WebSocket connection established.');
        };

        socket.onmessage = function(event) {
            const message = event.data;
            console.log('New message:', message);

            // Update your UI to display the new message
        };

        socket.onclose = function() {
            console.log('WebSocket connection closed.');
        };


        document.getElementById('sendButton').addEventListener('click', function(event) {
            event.preventDefault();
            const messageInput = document.getElementById('message');
            const message = messageInput.value;

            if (socket.readyState === WebSocket.OPEN) {
                socket.send(message);  // Send the message to the WebSocket server
                messageInput.value = '';  // Clear the input field
            } else {
                console.log('WebSocket connection is not open.');
            }
        });