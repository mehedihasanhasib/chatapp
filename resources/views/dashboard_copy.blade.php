<x-app-layout>
    <div class="bg-gradient-to-br from-indigo-100 to-purple-100 min-h-screen py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex flex-col h-[85vh]">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-6 flex justify-between items-center">
                        <h1 class="text-2xl font-bold">Chatter</h1>
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->name }}</div>

                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <!-- Authentication -->
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
                        <div class="w-1/4 bg-gray-50 border-r border-gray-200 overflow-y-auto">
                            <ul class="divide-y divide-gray-200">
                                @foreach ($users as $key => $user)
                                    @if (Auth::user()->id != $user->id)
                                        <li class="user cursor-pointer hover:bg-indigo-50 transition-colors duration-150" data-id="{{ $user->id }}">
                                            <div class="flex items-center px-6 py-4">
                                                <img class="h-12 w-12 rounded-full object-cover border-2 border-indigo-500" src="https://randomuser.me/api/portraits/thumb/men/{{ rand(1, 100) }}.jpg" alt="{{ $user->name }}">
                                                <div class="ml-4">
                                                    <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
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
                            <div id="currentRecipient" class="bg-gray-100 px-6 py-3 border-b border-gray-200 flex items-center gap-2">
                                <img id="recipientAvatar" class="h-12 w-12 rounded-full object-cover border-2 border-indigo-500" src="https://picsum.photos/100/200" alt="{{ $user->name }}">
                                <span id="recipientName" class="font-bold"></span></p>
                            </div>

                            <div id="messages" class="flex-1 overflow-y-auto p-6 space-y-6">
                                <!-- Messages will be dynamically inserted here -->
                            </div>

                            <!-- Message input -->
                            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
                                <div class="flex items-center">
                                    <input type="text" id="messageInput" placeholder="Type your message..." class="flex-1 appearance-none border border-gray-300 rounded-full w-full py-3 px-6 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-300 ease-in-out">
                                    <button onclick="sendMessage()" class="ml-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-full transition-colors duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



</x-app-layout>
<script>
    const socket = new WebSocket('ws://127.0.0.1:8082');
    const messagesDiv = document.getElementById('messages');
    let receiver_id;
    let sender_id = @json(Auth::user()->id);
    const user_name = @json(Auth::user()->name);

    socket.onopen = function(e) {
        console.log("Connection established");
        const userData = {
            userId: sender_id,
            userName: user_name
        };
        socket.send(JSON.stringify(userData));
        // messagesDiv.innerHTML += '<p>Connected to WebSocket server</p>';
    };

    socket.onmessage = function(event) {
        const userData = JSON.parse(event.data);
        const receivedData = JSON.parse(event.data);
        console.log(receivedData);

        document.querySelectorAll('.user').forEach(user => {
            if (user.getAttribute('data-id') == receivedData.userId) {
                const userName = user.querySelector('p').className = 'text-[green]'
            }
        });


        
        if (receivedData.receiver_id == @json(Auth::user()->id)) {
            console.log('Received from server:', receivedData.message);
            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${receivedData.sender_id === sender_id ? 'justify-end' : 'justify-start'}`;
            messageDiv.innerHTML = `
                <div class="${receivedData.sender_id === sender_id ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-800'} rounded-lg px-4 py-2 max-w-[70%]">
                    <p>${receivedData.message}</p>
                </div>
            `;
            messagesDiv.appendChild(messageDiv);
        }
    };

    socket.onclose = function(event) {
        if (event.wasClean) {
            console.log(`Connection closed cleanly, code=${event.code} reason=${event.reason}`);
        } else {
            console.log('Connection died');
        }
        // messagesDiv.innerHTML += '<p>Disconnected from WebSocket server</p>';
    };

    socket.onerror = function(error) {
        console.log(`WebSocket error: ${error}`);
        messagesDiv.innerHTML += `<p>Error: ${error}</p>`;
    };

    document.querySelectorAll('.user').forEach(user => {
        user.addEventListener('click', function() {
            receiver_id = this.getAttribute('data-id');
            const userName = this.querySelector('p').textContent;
            const userAvatar = this.querySelector('img').getAttribute('src');
            document.getElementById('recipientAvatar').src = userAvatar;
            document.getElementById('recipientName').textContent = userName;
            console.log('Selected user ID:', receiver_id);
        });
    });

    function sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value;

        const data = {
            message: message,
            sender_id: sender_id,
            receiver_id: receiver_id,
        }
        socket.send(JSON.stringify(data));
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${data.sender_id === sender_id ? 'justify-end' : 'justify-start'}`;
        messageDiv.innerHTML = `
                <div class="${data.sender_id === sender_id ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-800'} rounded-lg px-4 py-2 max-w-[70%]">
                    <p>${data.message}</p>
                </div>
            `;
        messagesDiv.appendChild(messageDiv);
    }
</script>
