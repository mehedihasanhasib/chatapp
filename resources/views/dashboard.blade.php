<x-app-layout>
    <div class="bg-gradient-to-br from-indigo-100 to-purple-100 min-h-screen py-4">
        <div class="max-w-[90vw] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex flex-col h-[96vh]">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-[#136a3b] to-[#444a58] text-white p-3 flex justify-between items-center">
                        <button id="toggleUserList" class="lg:hidden block mr-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-2xl font-bold">Chatter</h1>
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

                                    <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Chat area -->
                    <div class="flex flex-1 overflow-hidden">
                        <!-- User list -->
                        <div id="userList" class="lg:w-1/4 w-0 bg-gray-50 border-r border-gray-200 overflow-y-auto transition-all">
                            <ul class="divide-y divide-gray-200">
                                @foreach ($users as $key => $user)
                                    @if (Auth::user()->id != $user->id)
                                        <li class="user cursor-pointer hover:bg-indigo-50 transition-colors duration-150" data-id="{{ $user->id }}">
                                            <div class="flex items-center px-6 py-4">
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

                        <div class="flex-1 flex flex-col bg-white">
                            <!-- Current chat recipient -->
                            <div id="currentRecipient" class="bg-gray-100 px-4 py-2 sm:px-6 sm:py-3 border-b border-gray-200 hidden items-center gap-2">
                                <img id="recipientAvatar" class="h-10 w-10 sm:h-12 sm:w-12 rounded-full object-cover border-2 border-indigo-500" src="https://picsum.photos/100/200" alt="{{ $user->name }}">
                                <span id="recipientName" class="font-bold"></span>
                            </div>

                            <div id="messages" class="flex-1 overflow-y-auto p-6 space-y-6 hidden bg-gradient-to-tr from-slate-200 to-slate-300">
                                <!-- Messages will be dynamically inserted here -->
                            </div>

                            <!-- Message input -->
                            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 hidden" id="chat-section">
                                <form id="message-form" class="flex items-center justify-center">
                                    <label for="attachment">
                                        <button type="button" class="button px-3 py-3 mr-3 rounded-full bg-slate-300 hover:bg-[#e0e0e0]" id="attachment-button" title="Add Attachment">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" stroke="#369e7b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </label>
                                    <input type="file" class="hidden" id="attachment">
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
    const socket = new WebSocket('ws://127.0.0.1:2812');
    const messagesDiv = document.getElementById('messages');
    const userList = document.getElementById('userList');
    const chatSection = document.getElementById('chat-section');
    const currentRecipient = document.getElementById('currentRecipient');
    const toggleUserListButton = document.getElementById('toggleUserList');
    let receiver_id;
    let sender_id = @json(Auth::user()->id);
    const user_name = @json(Auth::user()->name);

    function scrollToTop() {
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function toggelUserList() {
        userList.classList.toggle('w-0');
        userList.classList.toggle('w-1/2');
    }

    function appendMessage(receivedData) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${receivedData.sender_id === sender_id ? 'justify-end' : 'justify-start'}`;
        messageDiv.innerHTML = `
                                <div class="${receivedData.sender_id === sender_id ? 'bg-[#136a3b] text-white' : 'bg-[#444a58] text-white'} rounded-lg px-4 py-2 max-w-[70%]">
                                    <p>${receivedData.message}</p>
                                </div>`;
        messagesDiv.appendChild(messageDiv);
    }

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
            } else if (receivedData.message) {
                // Handle received message
                if (receivedData.receiver_id == sender_id && receivedData.sender_id == receiver_id) {
                    console.log('Received message:', receivedData.message);
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
                    chatSection.classList.remove('hidden');
                    messagesDiv.classList.remove('hidden');
                    currentRecipient.classList.remove('hidden');
                    currentRecipient.classList.add('flex');
                    const userName = this.querySelector('p').textContent;
                    const userAvatar = this.querySelector('img').getAttribute('src');
                    document.getElementById('recipientAvatar').src = userAvatar;
                    document.getElementById('recipientName').textContent = userName;

                    messagesDiv.innerHTML = '';
                    messages.forEach((message) => {
                        appendMessage(message);
                    })
                    scrollToTop();
                    toggelUserList();
                })


            console.log('Selected user ID:', receiver_id);
        });
    });

    function sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value;

        if (message.trim() === '' || !receiver_id) {
            alert('Please enter a message and select a recipient');
            return;
        }

        const data = {
            message: message,
            sender_id: sender_id,
            receiver_id: receiver_id,
        };
        // console.log(data)

        // send post fetch to server
        fetch("{{ route('message.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF_TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(response => {
                socket.send(JSON.stringify(data));
                messageInput.value = '';
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