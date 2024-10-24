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
    <section id="videocall-section" class="hidden"> 
        video call section
    </section>
    <section id="others-section" class="bg-gradient-to-br from-indigo-100 to-purple-100 min-h-screen py-4">
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
                        <div id="userList" class="md:w-[full] lg:w-[full] xl:w-[25%] w-full bg-gray-50 border-r border-gray-200 overflow-y-auto transition-all">
                            <ul class="divide-y divide-gray-200">
                                @foreach ($users as $key => $user)
                                    @if (Auth::user()->id != $user->id)
                                        <li class="user cursor-pointer transition-colors duration-100 border-none m-2" data-id="{{ $user->id }}">
                                            <div class="flex items-center px-6 py-3">
                                                <img class="h-12 w-12 rounded-full object-cover border-2 border-indigo-500" src="https://i.pravatar.cc/40?img={{ $key + 1 }}" alt="{{ $user->name }}">
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
                            <div id="currentRecipient" class="bg-gray-100 px-4 py-2 sm:px-6 sm:py-3 border-b border-gray-200 flex items-center justify-between gap-2">
                                <div class="flex items-center space-x-2">
                                    <img id="recipientAvatar" class="h-10 w-10 sm:h-12 sm:w-12 rounded-full object-cover border-2 border-indigo-500" src="https://picsum.photos/100/200" alt="{{ $user->name }}">
                                    <span id="recipientName" class="font-bold"></span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.716 3 6V5z" />
                                        </svg>
                                    </button>
                                    <button>
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path fill="none" stroke="black" stroke-width="2" d="M2 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8z" />
                                            <path fill="none" stroke="black" stroke-width="2" d="M18 12l4-3v6l-4-3z" />
                                            <circle cx="5" cy="10" r="0.5" fill="black" />
                                        </svg>
                                    </button>

                                </div>
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
    </section>
</x-app-layout>
@include('script')
