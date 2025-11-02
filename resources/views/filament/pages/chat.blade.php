<x-filament-panels::page>
    <div class="w-full" style="background: linear-gradient(to bottom, #f0f0f0, #ffffff);">
        <div class="flex w-full" style="height: calc(100vh - 15rem);">
        <!-- Left Sidebar - Chat List -->
        <div class="w-80 flex flex-col bg-white dark:bg-gray-900 border-r border-gray-300 dark:border-gray-700 overflow-hidden">
            <!-- Tabs -->
            <div class="flex border-b border-gray-200 dark:border-gray-700">
                <button
                    wire:click="setActiveTab('active')"
                    class="flex-1 px-4 py-3 text-sm font-medium transition-colors {{ $activeTab === 'active' ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50 dark:bg-primary-900/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                >
                    Active @if($this->unreadCount > 0)<span class="ml-1 text-primary-600 font-bold">({{ $this->unreadCount }})</span>@endif
                </button>
                <button
                    wire:click="setActiveTab('archived')"
                    class="flex-1 px-4 py-3 text-sm font-medium transition-colors {{ $activeTab === 'archived' ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50 dark:bg-primary-900/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                >
                    Archived
                </button>
            </div>

            <!-- Chat List -->
            <div class="flex-1 overflow-y-auto">
                @forelse($this->tickets as $ticket)
                    <div
                        x-data="{ showContextMenu: false, contextMenuX: 0, contextMenuY: 0 }"
                        @contextmenu.prevent="showContextMenu = true; contextMenuX = $event.clientX; contextMenuY = $event.clientY"
                        @click.away="showContextMenu = false"
                        class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors {{ $selectedTicket && $selectedTicket->id === $ticket->id ? 'bg-white dark:bg-gray-800' : '' }} {{ $ticket->status === 'unread' ? 'bg-blue-50 dark:bg-blue-900/20' : '' }} relative cursor-pointer"
                        wire:click="selectTicket({{ $ticket->id }})"
                    >
                        <!-- Context Menu -->
                        <div
                            x-show="showContextMenu"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            class="fixed z-50 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg"
                            :style="'left: ' + contextMenuX + 'px; top: ' + contextMenuY + 'px;'"
                            @click.stop
                        >
                            @if($ticket->status !== 'archived')
                            <button
                                wire:click="markAsUnread({{ $ticket->id }}); $dispatch('click-away')"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                            >
                                Mark as unread
                            </button>
                            @endif
                            <button
                                wire:click="toggleTicketStatusContext({{ $ticket->id }}); $dispatch('click-away')"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ $ticket->status !== 'archived' ? 'border-t border-gray-200 dark:border-gray-700' : '' }}"
                            >
                                {{ $ticket->status === 'archived' ? 'Unarchive' : 'Archive' }}
                            </button>
                        </div>
                        
                        <!-- Ticket content -->
                        <div class="flex-1">
                            <div class="mb-0.5">
                                <h3 class="text-sm text-gray-900 dark:text-gray-100 truncate {{ $ticket->status === 'unread' ? 'font-bold' : 'font-medium' }}">
                                    {{ $ticket->title }}
                                </h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $ticket->last_message_at ? $ticket->last_message_at->diffForHumans() : $ticket->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex items-center justify-center h-full">
                        <p class="text-gray-500 dark:text-gray-400 text-sm">No {{ $activeTab }} chats</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Side - Chat Detail -->
        <div class="flex-1 flex flex-col bg-white dark:bg-gray-900 overflow-hidden">
            @if($selectedTicket)
                <!-- Chat Header -->
                <div class="px-6 py-3 border-b border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <input
                                type="text"
                                wire:model="selectedTicket.title"
                                wire:blur="updateTicketTitle"
                                class="text-lg {{ $selectedTicket->status === 'unread' ? 'font-bold' : 'font-semibold' }} text-gray-900 dark:text-gray-100 bg-transparent border-none focus:border-b-2 focus:border-primary-500 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-800 rounded-none w-full"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                wire:click="toggleTicketStatus"
                                class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors
                                    {{ $selectedTicket->status === 'archived' ? 'bg-primary-500 text-white hover:bg-primary-600' : 'bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200' }}"
                            >
                                {{ $selectedTicket->status === 'archived' ? 'Unarchive' : 'Archive' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-1 overflow-y-auto px-4 py-6 space-y-1" style="background: linear-gradient(to bottom, #f0f0f0, #ffffff);">
                    @forelse($selectedTicket->messages as $message)
                        <div class="flex items-end space-x-2 {{ $message->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                            <div class="flex-shrink-0 w-6"></div>
                            <div class="flex-1 {{ $message->user_id === auth()->id() ? 'flex justify-end' : '' }}">
                                <div class="group relative {{ $message->user_id === auth()->id() ? 'flex justify-end' : '' }}">
                                    <div class="max-w-md {{ $message->user_id === auth()->id() ? 'bg-primary-500 text-white rounded-3xl rounded-tr-none px-4 py-2' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-3xl rounded-tl-none px-4 py-2 shadow-sm' }}">
                                        @if($message->attachment_path)
                                            @if($message->attachment_type === 'image')
                                                <img src="{{ asset('storage/' . $message->attachment_path) }}" alt="{{ $message->attachment_name }}" class="max-w-xs rounded-lg mb-2 cursor-pointer" onclick="window.open('{{ asset('storage/' . $message->attachment_path) }}', '_blank')">
                                            @else
                                                <a href="{{ asset('storage/' . $message->attachment_path) }}" target="_blank" class="flex items-center gap-2 p-2 bg-white/20 dark:bg-gray-700/50 rounded-lg mb-2 hover:bg-white/30 transition-colors {{ $message->user_id !== auth()->id() ? '!bg-gray-100 dark:!bg-gray-700' : '' }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                    <span class="text-xs truncate">{{ $message->attachment_name }}</span>
                                                </a>
                                            @endif
                                        @endif
                                        @if($message->content)
                                            <p class="text-sm whitespace-pre-wrap leading-relaxed">{{ $message->content }}</p>
                                        @endif
                                    </div>
                                    <span class="absolute bottom-0 {{ $message->user_id === auth()->id() ? '-right-10 text-xs text-gray-500 dark:text-gray-400' : '-left-10 text-xs text-gray-500 dark:text-gray-400' }} opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                        {{ $message->created_at->format('g:i A') }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="h-6 w-6 rounded-full {{ $message->user_id === auth()->id() ? 'bg-primary-600' : 'bg-primary-500' }} flex items-center justify-center text-white text-xs font-semibold">
                                    {{ strtoupper(substr($message->user->name, 0, 1)) }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex items-center justify-center h-full">
                            <p class="text-gray-500 dark:text-gray-400 text-sm">No messages yet</p>
                        </div>
                    @endforelse
                </div>

                <!-- Message Input -->
                <div class="px-4 py-3 bg-gray-100 dark:bg-gray-950 border-t border-gray-300 dark:border-gray-700">
                    <form wire:submit.prevent="sendMessage" class="flex items-center space-x-2">
                        <label class="cursor-pointer">
                            <input type="file" wire:model="attachment" class="hidden" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                            <svg class="w-6 h-6 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                        </label>
                        <div class="flex-1 bg-white dark:bg-gray-900 rounded-full border border-gray-300 dark:border-gray-700">
                            <textarea
                                wire:model="message"
                                placeholder="Type a message..."
                                rows="1"
                                class="w-full px-4 py-2 bg-transparent border-none rounded-full focus:ring-0 focus:outline-none dark:text-gray-100 resize-none"
                            ></textarea>
                        </div>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-primary-500 text-white rounded-full hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </form>
                    @if($attachment)
                    <div class="mt-2 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span>{{ $attachment->getClientOriginalName() }}</span>
                    </div>
                    @endif
                </div>
            @else
                <!-- Empty State -->
                <div class="flex items-center justify-center h-full bg-gray-50 dark:bg-gray-900">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No chat selected</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select a chat to view messages</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div 
        x-data="{ refreshInterval: null }"
        x-init="
            // Poll for new messages every 2 seconds when a ticket is selected (like iMessage)
            $watch('$wire.selectedTicket', (ticket) => {
                if (ticket) {
                    if (refreshInterval) clearInterval(refreshInterval);
                    refreshInterval = setInterval(() => {
                        $wire.refreshTicket();
                    }, 2000);
                } else if (refreshInterval) {
                    clearInterval(refreshInterval);
                    refreshInterval = null;
                }
            });
        "
    ></div>

    @script
    <script>
        // Auto-scroll on message sent
        $wire.on('message-sent', () => {
            setTimeout(() => {
                const messagesContainer = document.querySelector('.flex-1.overflow-y-auto.px-4');
                if (messagesContainer) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            }, 100);
        });

        // Auto-scroll on page load
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const messagesContainer = document.querySelector('.flex-1.overflow-y-auto.px-4');
                if (messagesContainer) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            }, 100);
        });
    </script>
    @endscript

    <!-- Modal -->
    @if($showCreateForm)
    <div 
        x-data="{ showModal: false }"
        x-init="showModal = true"
        x-show="showModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-0 z-50"
        style="display: none;"
    >
        <div class="fixed inset-0 bg-black bg-opacity-30" x-on:click="showModal = false; $wire.call('resetCreateForm')"></div>
        
        <div class="fixed right-0 top-0 bottom-0 w-full max-w-md bg-white dark:bg-gray-800 shadow-xl overflow-y-auto">
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Start New Chat
                    </h3>
                    <button 
                        wire:click="resetCreateForm"
                        class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="createTicket" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            What do you need help with? <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="ticketSubject"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                            placeholder="e.g., Order issue, Website problem, etc."
                            required
                        />
                        @error('ticketSubject')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 pt-2">
                        <button
                            type="button"
                            wire:click="resetCreateForm"
                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600"
                        >
                            Start Chat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
