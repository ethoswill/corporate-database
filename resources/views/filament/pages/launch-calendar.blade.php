<x-filament-panels::page>
    <div class="space-y-4">
        <!-- Calendar Container -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div id="calendar" class="p-4"></div>
        </div>

        <!-- Event Modal -->
        @if($showEventModal)
        <div 
            x-data="{ showModal: false }"
            x-init="showModal = true"
            x-show="showModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-on:click="showModal = false; $wire.call('resetEventForm')"></div>
                
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $selectedEvent ? 'Event Details' : 'Create New Event' }}
                        </h3>
                        <button 
                            wire:click="resetEventForm"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    @if($selectedEvent)
                        <!-- View Event Details -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Company Name
                                </label>
                                <p class="text-gray-900 dark:text-gray-100">{{ $selectedEvent->company_name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Event Title
                                </label>
                                <p class="text-gray-900 dark:text-gray-100">{{ $selectedEvent->event_title }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Date
                                </label>
                                <p class="text-gray-900 dark:text-gray-100">{{ $selectedEvent->event_date->format('F j, Y') }}</p>
                            </div>

                            @if($selectedEvent->notes)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Notes
                                    </label>
                                    <p class="text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $selectedEvent->notes }}</p>
                                </div>
                            @endif

                            @if($selectedEvent->attachments && count($selectedEvent->attachments))
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Attachments
                                    </label>
                                    <div class="space-y-2">
                                        @foreach($selectedEvent->attachments as $attachment)
                                            <a href="{{ $attachment }}" target="_blank" class="flex items-center text-sm text-primary-600 hover:text-primary-800">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                </svg>
                                                View Attachment
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Create Event Form -->
                        <form wire:submit.prevent="createEvent" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Company Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="eventCompanyName"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Enter company name"
                                    required
                                />
                                @error('eventCompanyName')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Event Title <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="eventTitle"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Enter event title"
                                    required
                                />
                                @error('eventTitle')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Date <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    wire:model="eventDate"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100"
                                    required
                                />
                                @error('eventDate')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Notes
                                </label>
                                <textarea
                                    wire:model="eventNotes"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100 resize-none"
                                    placeholder="Add any notes..."
                                ></textarea>
                            </div>

                            <div class="flex gap-3 pt-4">
                                <button
                                    type="submit"
                                    class="flex-1 bg-primary-500 text-white py-2 px-4 rounded-lg hover:bg-primary-600 transition-colors"
                                >
                                    Create Event
                                </button>
                                <button
                                    type="button"
                                    wire:click="resetEventForm"
                                    class="flex-1 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 py-2 px-4 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    @script
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        const initCalendar = () => {
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;
            
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: {!! $this->eventsJson !!},
                eventClick: function(info) {
                    $wire.viewEvent(info.event.id);
                },
                dateClick: function(info) {
                    $wire.set('eventDate', info.dateStr);
                    $wire.set('showEventModal', true);
                },
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                },
                buttonText: {
                    today: 'Today',
                    month: 'Month',
                    list: 'List'
                },
                height: 'auto'
            });
            calendar.render();

            // Store calendar instance for refetching
            window.calendarInstance = calendar;

            $wire.on('event-created', () => {
                calendar.refetchEvents();
            });
        };

        document.addEventListener('DOMContentLoaded', initCalendar);
        
        // Re-initialize if calendar element becomes available after Livewire updates
        $wire.on('events-updated', () => {
            const calendarEl = document.getElementById('calendar');
            if (calendarEl && !window.calendarInstance) {
                initCalendar();
            } else if (window.calendarInstance) {
                window.calendarInstance.refetchEvents();
            }
        });
    </script>
    @endscript
</x-filament-panels::page>
