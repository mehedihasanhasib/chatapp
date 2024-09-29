<style>
    #image-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        overflow-y: scroll;
    }

    .image-container {
        position: relative;
        display: inline-block;
        left: 0.7vw;
    }

    .image-container img {
        width: 150px;
        height: 150px;
        border: 2px solid #ddd;
        border-radius: 4px;
        padding: 5px;
        margin: 5px;
    }

    .close-button {
        position: absolute;
        top: calc(100% - 95%);
        right: calc(100% - 95%);
        ;
        background: rgba(255, 0, 0, 0.7);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-size: 12px;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
<x-app-layout>
    <div class="bg-gradient-to-br from-indigo-100 to-purple-100 min-h-screen py-4">
        <div class="max-w-[90vw] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded shadow-2xl overflow-hidden">
                <div class="flex flex-col justify-center h-[96vh]">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-[#136a3b] to-[#444a58] text-white p-3 flex justify-between items-center">
                        <div>
                            <button id="toggleUserList" class="hidden mr-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex justify-between items-center space-x-[18.5vw]">
                            {{-- <h1 class="text-2xl font-bold">Chatter</h1> --}}
                            <x-dropdown width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-transparent focus:outline-none transition ease-in-out duration-150">
                                        <div class="flex items-center flex-1">
                                            <img class="h-12 w-12 rounded-full object-cover border-2 border-indigo-500" src="https://randomuser.me/api/portraits/thumb/men/{{ rand(1, 100) }}.jpg" alt="{{ Auth::user()->name }}">
                                            {{-- {{ Auth::user()->name }} --}}
                                        </div>
                                        {{-- <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div> --}}
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <!-- Authentication -->
                                    <x-dropdown-link>
                                        {{ Auth::user()->name }}
                                    </x-dropdown-link>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>

                    <!-- Chat area -->
                    <div class="flex flex-1 overflow-hidden max-w-full">
                        <!-- User list -->
                        <div id="userList" class="md:w-[full] lg:w-[full] xl:w-[22%] w-full bg-gray-50 border-r border-gray-200 overflow-y-auto transition-all">
                            <ul class="divide-y divide-gray-200">
                                @foreach ($users as $key => $user)
                                    @if (Auth::user()->id != $user->id)
                                        <li class="user cursor-pointer transition-colors duration-100 border-none m-2" data-id="{{ $user->id }}">
                                            <div class="flex items-center px-6 py-3">
                                                <img class="h-12 w-12 rounded-full object-cover border-2 border-indigo-500" src="https://randomuser.me/api/portraits/thumb/men/{{ rand(1, 100) }}.jpg" alt="{{ $user->name }}">
                                                <div class="ml-4">
                                                    <p class="text-sm font-semibold text-gray-900 text-nowrap">{{ $user->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $user->status }}</p>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>

                        <!-- Message area -->

                        <div class="flex-1 flex-col bg-white hidden w-0 transition-all" id="message-area">
                            <!-- Current chat recipient -->
                            <div id="currentRecipient" class="bg-gray-100 px-4 py-2 sm:px-6 sm:py-3 border-b border-gray-200 flex items-center gap-2">
                                <img id="recipientAvatar" class="h-10 w-10 sm:h-12 sm:w-12 rounded-full object-cover border-2 border-indigo-500" src="https://picsum.photos/100/200" alt="{{ $user->name }}">
                                <span id="recipientName" class="font-bold"></span>
                            </div>

                            <div id="messages" class="flex-1 overflow-y-auto p-6 space-y-6 bg-gradient-to-tr from-slate-200 to-slate-300 transition-all scroll-smooth">
                                <!-- Messages will be dynamically inserted here -->
                            </div>

                            <!-- Message input -->
                            <div id="image-preview"></div>
                            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 overflow-y-scroll">
                                <!-- Attachment Preview Section -->
                                <form id="message-form" class="flex items-center justify-center mb-0" enctype="multipart/form-data">
                                    <label class="button px-3 py-3 mr-3 rounded-full bg-slate-300 hover:bg-[#e0e0e0]" id="attachment-button" title="Add Attachment">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" stroke="#369e7b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </label>
                                    <input type="file" class="hidden" id="attachment" multiple>
                                    <input type="text" id="messageInput" placeholder="Type your message..." class="flex-1 appearance-none border border-gray-300 rounded-full w-full py-3 px-6 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-300 ease-in-out">
                                    <button type="submit" class="ml-4 bg-[#136a3b] hover:bg-[#0f4428] text-white font-bold py-3 px-6 rounded-full transition-colors duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#136a3b]">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<script>
    const socket = new WebSocket('ws://127.0.0.1:2823');
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

    function appendMessage(receivedData) {
        console.log(receivedData)
        // const message = document.createElement('div');
        // message.className = `max-w-[70%] ${receivedData.sender_id === sender_id ? 'justify-end' : 'justify-start'}`;
        // message.innerHTML = `<div class="${receivedData.sender_id === sender_id ? 'bg-[#136a3b] text-white' : 'bg-[#444a58] text-white'} rounded-sm px-2 py-2 max-w-[80%]">
        //                         <p class="text-wrap" style="word-break: break-word; white-space: pre-wrap">${receivedData.message}</p>
        //                     </div>`;
        // messagesDiv.appendChild(message);

        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${receivedData.sender_id == sender_id ? 'justify-end' : 'justify-start'}`;
        messageDiv.innerHTML = `<div class="${receivedData.sender_id == sender_id ? 'bg-[#285c48] text-white' : 'bg-[#444a58] text-white'} rounded-sm px-2 py-2 max-w-[80%]">
                                    <div class="flex items-center justify-start flex-wrap">
                                        ${receivedData.attachment && receivedData.attachment.map(attac => `<img src="chat_attachments/${attac}" class="w-20 xl:w-[7.6rem] lg:w-28 h-20 lg:h-28 xl:h-[7rem] m-2 transition-all">`).join('')}
                                    </div>
                                    <p class="text-wrap break-words ${receivedData.attachment.length > 0 ? 'ml-2' : ''}">${receivedData.message || ''}</p>
                                </div>`;
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
        messagesDiv.innerHTML += `<p>Error: ${error}</p>`;
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


            // console.log('Selected user ID:', receiver_id);
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

                const closeButton = document.createElement(
                    'button'); // Create a button to close/remove the image
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
