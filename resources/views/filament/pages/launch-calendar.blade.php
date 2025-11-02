<x-filament-panels::page>
    <div class="pb-0">
        <!-- Calendar Grid -->
        @php
            $startDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1);
            $firstDayOfWeek = $startDate->dayOfWeek; // 0 = Sunday, 6 = Saturday
            $daysInMonth = $startDate->daysInMonth;
            
            $monthEvents = $this->events->filter(function($event) use ($currentYear, $currentMonth) {
                return $event->event_date->year == $currentYear && $event->event_date->month == $currentMonth;
            })->groupBy(function($event) {
                return $event->event_date->format('Y-m-d');
            });
        @endphp
        
        <!-- Calendar Header -->
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                {{ $viewMode === 'calendar' ? $startDate->format('F Y') : 'Upcoming Events' }}
            </h2>
            <div class="flex items-center gap-2">
                @if($viewMode === 'calendar')
                    <button wire:click="previousMonth" class="p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button wire:click="goToToday" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                        Today
                    </button>
                    <button wire:click="nextMonth" class="p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @endif
                <div class="flex items-center gap-1 ml-2 border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                    <button 
                        wire:click="$set('viewMode', 'cards')"
                        class="px-4 py-2 text-sm font-medium transition-colors {{ $viewMode === 'cards' ? 'bg-primary-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                    >
                        Cards
                    </button>
                    <button 
                        wire:click="$set('viewMode', 'calendar')"
                        class="px-4 py-2 text-sm font-medium transition-colors {{ $viewMode === 'calendar' ? 'bg-primary-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                    >
                        Calendar
                    </button>
                </div>
            </div>
        </div>
        
        @if($viewMode === 'cards')
        <!-- Cards View -->
        @php
            $colors = [
                '#60A5FA', // Blue
                '#FBBF24', // Yellow
                '#A78BFA', // Purple
                '#34D399', // Green
                '#F87171', // Red
                '#FB7185', // Pink
                '#A78BFA', // Violet
                '#22D3EE', // Cyan
                '#FBBF24', // Orange
                '#86EFAC', // Light Green
                '#60A5FA', // Sky
                '#C084FC', // Lavender
            ];
            $upcomingEvents = $this->events->where('event_date', '>=', now()->startOfDay())->sortBy('event_date');
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($upcomingEvents as $event)
                @php
                    $colorIndex = abs(crc32($event->company_name)) % count($colors);
                    $colorHex = $colors[$colorIndex];
                @endphp
                <div 
                    x-data="{ showContextMenu: false, contextMenuX: 0, contextMenuY: 0 }"
                    @contextmenu.prevent="showContextMenu = true; contextMenuX = $event.clientX; contextMenuY = $event.clientY; return false"
                    @click.away="showContextMenu = false"
                    class="relative"
                >
                    <!-- Context Menu -->
                    <div
                        x-show="showContextMenu"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="fixed z-50 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg"
                        style="display: none;"
                        :style="'left: ' + contextMenuX + 'px; top: ' + contextMenuY + 'px;'"
                        @click.stop
                    >
                        <button
                            onclick="$wire.editEvent({{ $event->id }})"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            Edit Event
                        </button>
                        <button
                            onclick="$wire.duplicateEvent({{ $event->id }})"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border-t border-gray-200 dark:border-gray-700"
                        >
                            Duplicate Event
                        </button>
                    </div>
                    <div 
                        wire:click="viewEvent({{ $event->id }})"
                        class="rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden cursor-pointer hover:shadow-md transition group"
                        style="background: linear-gradient(to bottom, {{ $colorHex }}15, {{ $colorHex }}05);"
                    >
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                        {{ $event->event_title }}
                                    </h3>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        {{ $event->company_name }}
                                    </p>
                                </div>
                                <div class="flex items-start gap-2">
                                    @if($event->reminder_enabled)
                                        <div class="flex-shrink-0 mt-1">
                                            <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                                        </div>
                                    @endif
                                    <div class="flex-shrink-0 text-right">
                                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100 leading-none">
                                            {{ $event->event_date->format('j') }}
                                        </div>
                                        <div class="text-xs uppercase text-gray-500 dark:text-gray-400 leading-tight mt-1">
                                            {{ $event->event_date->format('M') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @if($event->notes)
                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-4">
                                    {{ $event->notes }}
                                </p>
                            @endif
                            
                            @if($event->attachment_path)
                                <div class="flex items-center gap-2 text-sm text-primary-600 dark:text-primary-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <span>Attachment</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No upcoming events</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new event.</p>
                </div>
            @endforelse
        </div>
        @else
        <!-- Calendar View -->
        <!-- Color Key -->
        @if($this->companies->count() > 0)
        @php
            $colors = [
                '#60A5FA', // Blue
                '#FBBF24', // Yellow
                '#A78BFA', // Purple
                '#34D399', // Green
                '#F87171', // Red
                '#FB7185', // Pink
                '#A78BFA', // Violet
                '#22D3EE', // Cyan
                '#FBBF24', // Orange
                '#86EFAC', // Light Green
                '#60A5FA', // Sky
                '#C084FC', // Lavender
            ];
        @endphp
        <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Company Color Key</h3>
            <div class="flex flex-wrap gap-3">
                @foreach($this->companies as $company)
                    @php
                        $colorIndex = abs(crc32($company)) % count($colors);
                        $colorHex = $colors[$colorIndex];
                    @endphp
                    <div class="flex items-center gap-2">
                        <div 
                            class="w-4 h-4 rounded"
                            style="background-color: {{ $colorHex }};"
                        ></div>
                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $company }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- Calendar Grid -->
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 bg-gray-50 dark:bg-gray-900">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="p-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                        {{ $day }}
                    </div>
                @endforeach
            </div>
            
            <!-- Calendar Days -->
            <div class="grid grid-cols-7 divide-x divide-y divide-gray-200 dark:divide-gray-700">
                @for($i = 0; $i < $firstDayOfWeek; $i++)
                    <div style="height: 150px;" class="bg-gray-50 dark:bg-gray-800"></div>
                @endfor
                
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $date = \Carbon\Carbon::create($currentYear, $currentMonth, $day);
                        $dateKey = $date->format('Y-m-d');
                        $dayEvents = $monthEvents->get($dateKey, collect());
                        $isToday = $date->isToday();
                    @endphp
                    <div style="height: 150px;" class="p-4 flex flex-col {{ $isToday ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-white dark:bg-gray-800' }} hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex items-center justify-between mb-3 flex-shrink-0">
                            <span class="text-lg font-bold {{ $isToday ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-gray-100' }}">
                                {{ $day }}
                            </span>
                        </div>
                        <div class="space-y-2 flex-1 overflow-hidden">
                            @foreach($dayEvents->take(3) as $event)
                                @php
                                    $colors = [
                                        '#60A5FA', // Blue
                                        '#FBBF24', // Yellow
                                        '#A78BFA', // Purple
                                        '#34D399', // Green
                                        '#F87171', // Red
                                        '#FB7185', // Pink
                                        '#A78BFA', // Violet
                                        '#22D3EE', // Cyan
                                        '#FBBF24', // Orange
                                        '#86EFAC', // Light Green
                                        '#60A5FA', // Sky
                                        '#C084FC', // Lavender
                                    ];
                                    // Use company name to get consistent color
                                    $colorIndex = abs(crc32($event->company_name)) % count($colors);
                                    $colorHex = $colors[$colorIndex];
                                @endphp
                                <div 
                                    wire:click="viewEvent({{ $event->id }})"
                                    class="text-sm px-3 py-2 rounded-lg cursor-pointer hover:opacity-80 transition-opacity"
                                    style="background-color: {{ $colorHex }}; color: #000; white-space: nowrap;"
                                    title="{{ $event->company_name }} - {{ $event->event_title }}"
                                >
                                    {{ Str::limit($event->event_title, 22) }}
                                </div>
                            @endforeach
                            @if($dayEvents->count() > 3)
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    +{{ $dayEvents->count() - 3 }} more
                                </div>
                            @endif
                        </div>
                    </div>
                @endfor
                
                @php
                    $totalCells = 42; // 6 weeks * 7 days
                    $usedCells = $firstDayOfWeek + $daysInMonth;
                    $remainingCells = $totalCells - $usedCells;
                @endphp
                @for($i = 0; $i < $remainingCells; $i++)
                    <div style="height: 150px;" class="bg-gray-50 dark:bg-gray-800"></div>
                @endfor
            </div>
        </div>
        @endif
    </div>

    <!-- Modal -->
    @if($showCreateModal)
    <div 
        x-data="{ showModal: true }"
        x-show="showModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-0 z-50"
    >
        <div class="fixed inset-0 bg-black bg-opacity-30" x-on:click="showModal = false; $wire.call('resetEventForm')"></div>
        
        <div class="fixed right-0 top-0 bottom-0 w-full max-w-md bg-white dark:bg-gray-800 shadow-xl overflow-y-auto">
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $editingEventId ? 'Edit Event' : 'New Event' }}
                    </h3>
                    <button 
                        wire:click="resetEventForm"
                        class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="createEvent" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Company Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="companyName"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                            placeholder="Company name"
                            required
                        />
                        @error('companyName')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Event Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            wire:model="eventTitle"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                            placeholder="Event title"
                            required
                        />
                        @error('eventTitle')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            wire:model="eventDate"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                            required
                        />
                        @error('eventDate')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Notes
                        </label>
                        <textarea
                            wire:model="eventNotes"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100 resize-none text-sm"
                            placeholder="Add any notes..."
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Attachment
                        </label>
                        <input
                            type="file"
                            wire:model="attachment"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100 file:mr-3 file:py-1 file:px-3 file:border-0 file:rounded-lg file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                        />
                        @error('attachment')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 pt-2">
                        <button
                            type="button"
                            wire:click="resetEventForm"
                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600"
                        >
                            {{ $editingEventId ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- View Event Modal -->
    @if($viewingEventId && $this->viewingEvent)
        <div 
            x-data="{ showModal: true }"
            x-show="showModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-0 z-50"
        >
            <div class="fixed inset-0 bg-black bg-opacity-30" x-on:click="showModal = false; $wire.call('closeViewModal')"></div>
            
            <div class="fixed right-0 top-0 bottom-0 w-full max-w-md bg-white dark:bg-gray-800 shadow-xl overflow-y-auto">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Event Details
                            </h3>
                            <button 
                                wire:click="toggleReminder({{ $this->viewingEvent->id }})"
                                class="p-1.5 rounded-lg transition-colors {{ $this->viewingEvent->reminder_enabled ? 'text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                                title="{{ $this->viewingEvent->reminder_enabled ? 'Disable reminder' : 'Enable reminder' }}"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </button>
                        </div>
                        <button 
                            wire:click="closeViewModal"
                            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Event Details -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Company Name
                            </label>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ $this->viewingEvent->company_name }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Event Title
                            </label>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                {{ $this->viewingEvent->event_title }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Date
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100">
                                {{ $this->viewingEvent->event_date->format('F j, Y') }}
                            </p>
                        </div>

                        @if($this->viewingEvent->notes)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Notes
                            </label>
                            <p class="text-base text-gray-900 dark:text-gray-100 whitespace-pre-wrap">
                                {{ $this->viewingEvent->notes }}
                            </p>
                        </div>
                        @endif

                        @if($this->viewingEvent->attachment_path)
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Attachment
                            </label>
                            <a 
                                href="{{ asset('storage/' . $this->viewingEvent->attachment_path) }}" 
                                target="_blank"
                                class="inline-flex items-center text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                {{ $this->viewingEvent->attachment_name ?? 'Download' }}
                            </a>
                        </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            wire:click="closeViewModal"
                            class="px-4 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                        >
                            Close
                        </button>
                        <a
                            href="{{ $this->googleCalendarUrl }}"
                            target="_blank"
                            class="px-4 py-1.5 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600 flex items-center justify-center"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Add to Google Calendar
                        </a>
                        <button
                            type="button"
                            wire:click="closeViewModal; $wire.editEvent({{ $this->viewingEvent->id }})"
                            class="px-4 py-1.5 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600"
                        >
                            Edit Event
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Task Modal -->
    @if($showTaskModal)
        <div 
            x-data="{ showModal: true }"
            x-show="showModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-0 z-50"
        >
            <div class="fixed inset-0 bg-black bg-opacity-30" x-on:click="showModal = false; $wire.call('closeTaskModal')"></div>
            
            <div class="fixed right-0 top-0 bottom-0 w-full max-w-md bg-white dark:bg-gray-800 shadow-xl overflow-y-auto">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Add Task
                        </h3>
                        <button 
                            wire:click="closeTaskModal"
                            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Form -->
                    <form wire:submit.prevent="createTask" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Task Title <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="taskTitle"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                placeholder="Enter task title"
                                required
                            />
                            @error('taskTitle')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Description
                            </label>
                            <textarea
                                wire:model="taskDescription"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100 resize-none text-sm"
                                placeholder="Add any details..."
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Due Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                wire:model="taskDueDate"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                required
                            />
                            @error('taskDueDate')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Assign To
                            </label>
                            <select
                                wire:model="taskAssignedTo"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                            >
                                <option value="">No one</option>
                                @foreach(\App\Models\User::all() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 pt-2">
                            <button
                                type="button"
                                wire:click="closeTaskModal"
                                class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="flex-1 px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600"
                            >
                                Create Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
