<x-filament-panels::page>
    <div class="flex h-[calc(100vh-12rem)] gap-4" x-data="{ showCreateModal: false }">
        @if($showCreateForm)
        <!-- Slide-in Create Ticket Modal -->
        <div 
            x-show="showCreateModal" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            x-init="showCreateModal = true"
            class="fixed inset-y-0 right-0 w-1/3 bg-white dark:bg-gray-800 shadow-2xl z-50 overflow-y-auto border-l border-gray-200 dark:border-gray-700"
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create New Ticket</h2>
                    <button 
                        wire:click="$set('showCreateForm', false)"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form wire:submit.prevent="createTicket" class="space-y-6">
                    <div>
                        <label class="block text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
                            What do you need help with?
                        </label>
                        <input
                            type="text"
                            wire:model="ticketSubject"
                            class="w-full px-4 py-3 text-lg border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100"
                            placeholder="e.g., Order issue, Website problem, etc."
                            required
                            autofocus
                        />
                        @error('ticketSubject')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            class="flex-1 bg-primary-500 text-white py-3 px-6 text-lg rounded-lg hover:bg-primary-600 transition-colors font-medium"
                        >
                            Start Chat
                        </button>
                        <button
                            type="button"
                            wire:click="$set('showCreateForm', false)"
                            class="flex-1 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 py-3 px-6 text-lg rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <div class="flex h-[calc(100vh-12rem)] gap-4">
        <!-- Left Sidebar - Ticket List -->
        <div class="w-1/3 flex flex-col border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 overflow-hidden">
            <!-- Tabs -->
            <div class="flex border-b border-gray-200 dark:border-gray-700">
                <button
                    wire:click="setActiveTab('open')"
                    class="flex-1 px-4 py-3 text-sm font-medium transition-colors {{ $activeTab === 'open' ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50 dark:bg-primary-900/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                >
                    Open ({{ $this->tickets->where('status', 'open')->count() }})
                </button>
                <button
                    wire:click="setActiveTab('archived')"
                    class="flex-1 px-4 py-3 text-sm font-medium transition-colors {{ $activeTab === 'archived' ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50 dark:bg-primary-900/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                >
                    Archived ({{ $this->tickets->where('status', 'archived')->count() }})
                </button>
            </div>

            <!-- Ticket List -->
            <div class="flex-1 overflow-y-auto">
                @forelse($this->tickets as $ticket)
                    <div
                        wire:click="selectTicket({{ $ticket->id }})"
                        class="px-4 py-4 border-b border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ $selectedTicket && $selectedTicket->id === $ticket->id ? 'bg-primary-50 dark:bg-primary-900/20 border-l-4 border-l-primary-600' : '' }}"
                    >
                        <h3 class="font-semibold text-base text-gray-900 dark:text-gray-100 mb-2">
                            {{ $ticket->title }}
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $ticket->last_message_at ? $ticket->last_message_at->diffForHumans() : $ticket->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="flex items-center justify-center h-full">
                        <p class="text-gray-500 dark:text-gray-400">No {{ $activeTab }} tickets</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Side - Ticket Detail -->
        <div class="flex-1 flex flex-col border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 overflow-hidden">
            @if($selectedTicket)
                <!-- Ticket Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <input
                                type="text"
                                wire:model="selectedTicket.title"
                                wire:blur="updateTicketTitle"
                                class="text-2xl font-bold text-gray-900 dark:text-gray-100 bg-transparent border-none focus:border-b-2 focus:border-primary-500 focus:outline-none focus:bg-white dark:focus:bg-gray-800 rounded-none w-full"
                            />
                        </div>
                        <button
                            wire:click="toggleTicketStatus"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                                {{ $selectedTicket->status === 'open' ? 'bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200' : 'bg-primary-500 text-white hover:bg-primary-600' }}"
                        >
                            {{ $selectedTicket->status === 'open' ? 'Archive' : 'Open' }}
                        </button>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    @forelse($selectedTicket->messages as $message)
                        <div class="flex items-end space-x-2 {{ $message->user_id === auth()->id() ? 'flex-row-reverse' : '' }}">
                            <div class="flex-shrink-0 w-8"></div>
                            <div class="flex-1 {{ $message->user_id === auth()->id() ? 'flex justify-end' : '' }}">
                                <div class="group relative {{ $message->user_id === auth()->id() ? 'flex justify-end' : '' }}">
                                    <div class="max-w-md {{ $message->user_id === auth()->id() ? 'bg-primary-500 text-white rounded-2xl rounded-tr-sm px-4 py-2 shadow-sm' : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-2xl rounded-tl-sm px-4 py-2 shadow-sm' }}">
                                        <p class="text-sm whitespace-pre-wrap">{{ $message->content }}</p>
                                    </div>
                                    <span class="absolute bottom-0 {{ $message->user_id === auth()->id() ? '-right-12 text-xs text-gray-500 dark:text-gray-400' : '-left-12 text-xs text-gray-500 dark:text-gray-400' }} opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                        {{ $message->created_at->format('g:i A') }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full {{ $message->user_id === auth()->id() ? 'bg-primary-600' : 'bg-primary-500' }} flex items-center justify-center text-white text-sm font-semibold">
                                    {{ strtoupper(substr($message->user->name, 0, 1)) }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex items-center justify-center h-full">
                            <p class="text-gray-500 dark:text-gray-400">No messages yet</p>
                        </div>
                    @endforelse
                </div>

                <!-- Message Input -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <form wire:submit.prevent="sendMessage" class="flex items-center space-x-3">
                        <div class="flex-1">
                            <textarea
                                wire:model="message"
                                placeholder="Type your message..."
                                rows="2"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:text-gray-100 resize-none"
                            ></textarea>
                        </div>
                        <button
                            type="submit"
                            class="px-6 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
                        >
                            Send
                        </button>
                    </form>
                </div>
            @else
                <!-- Empty State -->
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No ticket selected</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select a ticket to view messages</p>
                    </div>
                </div>
            @endif
        </div>
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
                const messagesContainer = document.querySelector('.flex-1.overflow-y-auto.p-6');
                if (messagesContainer) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            }, 100);
        });

        // Auto-scroll on page load
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const messagesContainer = document.querySelector('.flex-1.overflow-y-auto.p-6');
                if (messagesContainer) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            }, 100);
        });
    </script>
    @endscript
</x-filament-panels::page>
