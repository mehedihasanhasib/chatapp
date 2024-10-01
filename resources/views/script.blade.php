<script>
    const socket = new WebSocket('ws://127.0.0.1:2801');
    const messagesDiv = document.getElementById('messages');
    const userList = document.getElementById('userList');
    const messageArea = document.getElementById('message-area');
    const toggleUserListButton = document.getElementById('toggleUserList');
    let imageInput = document.getElementById('attachment');
    let images = new DataTransfer();
    let receiver_id;
    let sender_id = @json(Auth::user()->id);
    const user_name = @json(Auth::user()->name);

    function scrollToTop() {
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function toggelUserList() {
        if (window.innerWidth < 1280) {
            userList.classList.toggle('w-full');
            userList.classList.toggle('w-0');
            messageArea.classList.toggle('w-0');
            messageArea.classList.toggle('w-full');
            messageArea.classList.toggle('hidden');
            messageArea.classList.toggle('flex');
            toggleUserListButton.classList.toggle('hidden')
        } else {
            messageArea.classList.add('w-full');
            messageArea.classList.remove('w-0');
            messageArea.classList.remove('hidden');
            messageArea.classList.add('flex');
        }
    }

    function fileType(extension, imagePath) {
        // let extension = fileName.split('.').pop();
        switch (extension) {
            case "image/jpeg":
            case "image/jpg":
            case "image/png":
            case "image/gif":
            case "image/svg+xml":
            case "image/webp":
            case "jpg":
            case "jpeg":
            case "png":
            case "gif":
            case "webp":
            case "svg":
                return imagePath;
            case "application/pdf":
            case "pdf":
                return '/js/icons/pdf.png';
            case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
            case "doc":
            case "docx":
                return '/js/icons/doc.png';
            case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
            case "xlsx":
            case "xlsm":
            case "xlsb":
                return '/js/icons/excel.png';
            case "application/vnd.openxmlformats-officedocument.presentationml.presentation":
            case "ppt":
                return '/js/icons/ppt.png';
            case "text/plain":
            case "txt":
                return '/js/icons/txt.png';
            case "application/zip":
            case 'application/x-zip-compressed':
            case "application/gzip":
            case "tar":
            case "xz":
            case "rar":
            case "7z":
            case "zip":
            case "gzip":
            case "tar":
            case "xz":
            case "rar":
            case "7z":
                return '/js/icons/zip.png';
            default:
                return 'js/icons/unknown.png'
        }
    }

    function appendMessage(receivedData) {
        /*const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${receivedData.sender_id == sender_id ? 'justify-end' : 'justify-start'}`;
        messageDiv.innerHTML = `<div class="${receivedData.sender_id == sender_id ? 'bg-[#285c48] text-white' : 'bg-[#444a58] text-white'} rounded-sm px-2 py-2 max-w-[80%]">
                                    <div class="flex items-center justify-start flex-wrap">
                                        ${receivedData.attachment && receivedData.attachment.map(attac => `<img src="chat_attachments/${attac}" class="${receivedData.attachment.length < 2 ? 'w-[calc(100%/${receivedData.attachment.length})]' : 'w-20'} xl:w-[7.6rem] lg:w-28 h-20 lg:h-28 xl:h-[7rem] m-2 transition-all">`).join('')}
                                    </div>
                                    <p class="text-wrap break-words ${receivedData.attachment.length > 0 ? 'ml-2' : ''}">${receivedData.message || ''}</p>
                                </div>`;
        messagesDiv.appendChild(messageDiv);*/

        const messageDiv = document.createElement('div');
        messageDiv.className = `flex items-end ${receivedData.sender_id == sender_id ? 'justify-end' : 'justify-start'} mb-4`;

        // Avatar image element
        const avatar = document.createElement('img');
        avatar.src = receivedData.sender_id == sender_id ? 'https://i.pravatar.cc/40?img=1' : 'https://i.pravatar.cc/40?img=2';
        avatar.alt = 'avatar';
        avatar.className = `w-5 h-5 rounded-full object-cover ${receivedData.sender_id == sender_id ? 'ml-2' : 'mr-2'} shadow-lg`;

        // Message content wrapper
        const messageContent = document.createElement('div');
        messageContent.className = `${receivedData.sender_id == sender_id ? 'bg-gradient-to-r from-[#136a3b] to-[#444a58] text-white' : 'bg-gradient-to-r from-[#444a58] to-[#136a3b] text-white'} rounded-md p-3 shadow-sm max-w-[80%] transition-all`;

        // Attachments (if any)
        if (receivedData.attachment && receivedData.attachment.length > 0) {
            const attachmentContainer = document.createElement('div');
            attachmentContainer.className = 'flex items-center justify-start flex-wrap';

            receivedData.attachment.forEach(fileName => {
                const img = document.createElement('img');
                img.src = fileType(fileName.split('.').pop().toLowerCase());
                img.className = `${receivedData.attachment.length < 2 ? 'w-[calc(100%/${receivedData.attachment.length})]' : 'w-24'} xl:w-[7.6rem] lg:w-28 h-24 lg:h-28 xl:h-[7rem] m-1 rounded-sm object-cover shadow-md transition-all hover:opacity-90`;
                attachmentContainer.appendChild(img);
            });

            messageContent.appendChild(attachmentContainer);
        }

        // Message text
        const messageText = document.createElement('p');
        messageText.className = `text-wrap break-words font-medium`;
        messageText.textContent = receivedData.message || '';

        // Append text to the message content
        messageContent.appendChild(messageText);

        // Append avatar and message content to the message div
        if (receivedData.sender_id != sender_id) {
            messageDiv.appendChild(avatar);
        }

        messageDiv.appendChild(messageContent);
        if (receivedData.sender_id == sender_id) {
            messageDiv.appendChild(avatar);
        }

        messagesDiv.appendChild(messageDiv);
    }

    // Function to clear attachments and reset the DataTransfer object
    function restForm() {
        messageInput.value = '';
        imageInput.value = '';
        images = new DataTransfer();
        const imagePreviewContainer = document.getElementById('image-preview');
        while (imagePreviewContainer.firstChild) {
            imagePreviewContainer.removeChild(imagePreviewContainer.firstChild);
        }
    }

    // ------------------------------------- //

    socket.onopen = function(e) {
        console.log("Connection established");
        const userData = {
            userData: {
                userId: sender_id,
                userName: user_name
            }
        };
        socket.send(JSON.stringify(userData));
    };

    socket.onmessage = function(event) {
        try {
            const receivedData = JSON.parse(event.data);
            console.log(receivedData)

            if (receivedData.type === 'userDataResponse') {
                console.log(receivedData.message);
            } else if (receivedData.userId) {
                // Update user status
                document.querySelectorAll('.user').forEach(user => {
                    if (user.getAttribute('data-id') == receivedData.userId) {
                        user.querySelector('p').className = 'text-[green]';
                    }
                });
            } else {
                // Handle received message
                if (receivedData.receiver_id == sender_id && receivedData.sender_id == receiver_id) {
                    // console.log('Received message:', receivedData.message);
                    appendMessage(receivedData);
                    scrollToTop();
                }
            }
        } catch (error) {
            console.error("Error parsing message:", error);
            console.log("Raw message:", event.data);
        }
    };

    socket.onclose = function(event) {
        if (event.wasClean) {
            console.log(`Connection closed cleanly, code=${event.code} reason=${event.reason}`);
        } else {
            console.log('Connection died');
        }
    };

    socket.onerror = function(error) {
        console.log(`WebSocket error: ${error}`);
    };

    document.querySelectorAll('.user').forEach(user => {
        user.addEventListener('click', function() {
            receiver_id = this.getAttribute('data-id');
            let users = document.querySelectorAll('.user');
            users.forEach(user => {
                user.querySelector('p').classList.remove('text-[white]');
                user.querySelector('p').classList.add('text-gray-900');
                user.classList.remove('bg-[#444a58]');
            });

            let user = this;

            user.querySelector('p').classList.add('text-[white]');
            user.querySelector('p').classList.remove('text-gray-900');
            user.classList.add('bg-[#444a58]');

            const userName = this.querySelector('p').textContent;
            const userAvatar = this.querySelector('img').getAttribute('src');
            document.getElementById('recipientAvatar').src = userAvatar;
            document.getElementById('recipientName').textContent = userName;

            toggelUserList();
            messagesDiv.innerHTML = '';
            fetch("{{ route('messages.index') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF_TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        sender_id,
                        receiver_id
                    }),
                }).then(response => response.json())
                .then(response => {
                    const messages = response.messages;
                    messages.forEach((message) => {
                        appendMessage(message);
                    })
                    scrollToTop();
                })
        });
    });

    function sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value;

        if ((message.trim() == '' && imageInput.files.length < 1) || !receiver_id || !sender_id) {
            alert('Please enter a message and select a recipient');
            return;
        }

        const data = new FormData(); // Create a new FormData object
        data.append('message', message || ""); // Add the message text
        data.append('sender_id', sender_id); // Add the sender ID
        data.append('receiver_id', receiver_id); // Add the receiver ID

        for (let i = 0; i < imageInput.files.length; i++) {
            data.append('attachment[]', imageInput.files[i]); // Attach the files one by one
        }
        fetch("{{ route('message.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF_TOKEN': "{{ csrf_token() }}"
                },
                body: data
            })
            .then(response => response.json())
            .then(response => {
                // console.log(response)
                // WebSocket message with attachment URLs and metadata
                const socketData = {
                    sender_id: sender_id,
                    receiver_id: receiver_id,
                    message: message,
                    attachment: response.attachment // Attach the file URLs/paths received from server
                };

                socket.send(JSON.stringify(socketData)); // Send metadata over WebSocket
                restForm();
                appendMessage(response);
                scrollToTop();
            })
    }

    document.getElementById('message-form').addEventListener('submit', function(event) {
        event.preventDefault();
        sendMessage();
    })



    document.addEventListener('DOMContentLoaded', function() {
        toggleUserListButton.addEventListener('click', function() {
            toggelUserList();
        })
    })
</script>
<script>
    const attachmentButton = document.getElementById('attachment-button');

    attachmentButton.addEventListener('click', function(event) {
        event.preventDefault();
        imageInput.click();
    })

    imageInput.addEventListener('change', function(event) {
        const imagePreviewContainer = document.getElementById('image-preview'); // Get the container for image previews
        const files = event.target.files; // Get the files from the input event
        const currentSelectedImages =
            new DataTransfer(); // Create a new DataTransfer object for current selected images

        const array = Array.from(files); // Convert the FileList to an array

        array.forEach((file, index) => {
            currentSelectedImages.items.add(file); // Add each file to currentSelectedImages

            const reader = new FileReader(); // Create a FileReader to read the file
            reader.onload = function(e) {
                const imageContainer = document.createElement('div'); // Create a container div for the image
                imageContainer.classList.add('image-container'); // Add class to the image container

                const img = document.createElement('img'); // Create an img element
                img.src = e.target.result; // Set the src of the img element to the file data
                img.alt = file.name; // Set the alt attribute of the img element

                const closeButton = document.createElement('button'); // Create a button to close/remove the image
                closeButton.innerHTML = '&times;'; // Set the button's inner HTML to a times symbol
                closeButton.classList.add('close-button'); // Add class to the close button
                closeButton.addEventListener('click', function() {
                    imageContainer.remove(); // Remove the image container from the DOM
                    removeFile(file); // Call removeFile to remove the file from images
                });

                imageContainer.appendChild(img); // Add the img element to the image container
                imageContainer.appendChild(
                    closeButton); // Add the close button to the image container

                imagePreviewContainer.appendChild(
                    imageContainer); // Add the image container to the preview container
            };
            reader.readAsDataURL(file); // Read the file as a data URL
        });

        for (let i = 0; i < currentSelectedImages.files.length; i++) {
            images.items.add(currentSelectedImages.files[i]); // Add the current selected images to images
        }

        function removeFile(file) {
            let updatedImages = new DataTransfer(); // Create a new DataTransfer object for updated images
            const filesArray = Array.from(imageInput.files); // Convert the input files to an array
            const selectedToRemoveFileName = file.name; // Get the name of the file to remove

            for (let i = 0; i < images.files.length; i++) { // Loop through images
                if (selectedToRemoveFileName !== images.files[i]
                    .name) { // Check if the current file is not the one to remove
                    updatedImages.items.add(images.files[i]); // Add the file to updatedImages
                }
            }

            images = updatedImages; // Reassign images to updatedImages
            imageInput.files = images.files; // Update the input files with the new DataTransfer object
            // console.log(imageInput.files); // Log the updated files
        }

        imageInput.files = images.files; // Update the input files with the updated images
    });
</script>
