<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Summary Widget -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Total Sales -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Total Sales</h3>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $summary['total_sales'] }}</p>
                @if(!empty($summary['total_rebated']) && $summary['total_rebated'] !== '$0.00')
                    <p class="text-sm text-green-600 dark:text-green-400 mt-1">Total Rebated: {{ $summary['total_rebated'] }}</p>
                @endif
            </div>

            <!-- Top Products -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Top Products</h3>
                </div>
                <div class="space-y-1">
                    @foreach(array_slice($summary['top_products'], 0, 3) as $product)
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $product['name'] }}</p>
                    @endforeach
                </div>
            </div>

            <!-- Top Franchisees -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="flex-shrink-0 w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Top Franchisees</h3>
                </div>
                <div class="space-y-2">
                    @foreach(array_slice($summary['top_franchisees'], 0, 5) as $index => $franchisee)
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-gray-400 w-5">{{ $index + 1 }}.</span>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $franchisee['name'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">${{ number_format($franchisee['amount'], 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Monthly Folders -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Monthly Reports
                </h3>
            </div>
            
            <div class="p-6">
                @if(count($months) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($months as $month)
                            <a href="{{ route('filament.admin.pages.monthly-report', ['month' => $month['slug']]) }}" 
                               class="block bg-gray-50 dark:bg-gray-700 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-600 transition group">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-800 transition">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-blue-600 transition">
                                            {{ $month['name'] }}
                                        </h4>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">No monthly reports yet. Upload reports to get started.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
