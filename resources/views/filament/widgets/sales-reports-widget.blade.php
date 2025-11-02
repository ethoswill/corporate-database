<x-filament-widgets::widget class="fi-sales-reports-widget">
    <x-filament::section>
        <a href="{{ route('filament.admin.pages.sales-reports') }}" class="block group">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-16 h-16 bg-green-100 dark:bg-green-900 rounded-xl flex items-center justify-center group-hover:bg-green-200 dark:group-hover:bg-green-800 transition">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition">
                        Check Sales Reports
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        View revenue and performance
                    </p>
                </div>
                <svg class="w-6 h-6 text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>
    </x-filament::section>
</x-filament-widgets::widget>

