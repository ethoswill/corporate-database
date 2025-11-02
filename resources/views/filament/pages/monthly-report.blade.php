<x-filament-panels::page>
    <div class="space-y-6 w-full" style="max-width: 100%; width: 100%;">
        <!-- Breadcrumb with Month Navigation -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <a href="{{ route('filament.admin.pages.sales-reports') }}" class="hover:text-gray-900 transition">
                    Sales Reports
                </a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-gray-900 font-medium">{{ $monthName }}</span>
            </div>
            <div class="flex items-center gap-4">
                @if($adjacentMonths['prev'])
                    <a href="{{ route('filament.admin.pages.monthly-report', ['month' => $adjacentMonths['prev']]) }}" 
                       class="flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                @else
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-800 opacity-50 cursor-not-allowed">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </div>
                @endif
                
                @if($adjacentMonths['next'])
                    <a href="{{ route('filament.admin.pages.monthly-report', ['month' => $adjacentMonths['next']]) }}" 
                       class="flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @else
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-800 opacity-50 cursor-not-allowed">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                @endif
            </div>
        </div>

        <!-- Reports Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Sales Card -->
            <a href="{{ route('filament.admin.pages.month-total-sales', ['month' => $month]) }}" 
               class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition group">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Total Sales
                        </h3>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
                <div class="p-6 text-center">
                    @if(isset($reports['total-sales']) && !empty($reports['total-sales']['data']))
                        @php
                            // Calculate total from all rows (sum of second column)
                            $totalSales = 0;
                            foreach (array_slice($reports['total-sales']['data'], 1) as $row) {
                                if (isset($row[1]) && is_numeric($row[1])) {
                                    $totalSales += (float)$row[1];
                                }
                            }
                        @endphp
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            ${{ number_format($totalSales, 2) }}
                        </p>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No data</p>
                    @endif
                </div>
            </a>

            <!-- Top Products Card -->
            <a href="{{ route('filament.admin.pages.month-top-product', ['month' => $month]) }}" 
               class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition group">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Top Products
                        </h3>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
                <div class="p-6">
                    @if(isset($reports['top-product']) && !empty($reports['top-product']['data']))
                        <div class="space-y-3">
                            @foreach(array_slice($reports['top-product']['data'], 1, 5) as $index => $row)
                                <div class="flex items-center gap-3">
                                    <span class="text-lg font-semibold text-gray-400 w-6">{{ $index + 1 }}.</span>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $row[0] ?? '' }}
                                        </p>
                                        @php
                                            // Sum all numeric columns after the product name
                                            $productTotal = 0;
                                            foreach ($row as $colIndex => $cell) {
                                                if ($colIndex > 0 && is_numeric($cell)) {
                                                    $productTotal += (float)$cell;
                                                }
                                            }
                                        @endphp
                                        @if($productTotal > 0)
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                ${{ number_format($productTotal, 2) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500 dark:text-gray-400">No data</p>
                    @endif
                </div>
            </a>

            <!-- Top Franchisees Card -->
            <a href="{{ route('filament.admin.pages.month-top-franchisee', ['month' => $month]) }}" 
               class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition group">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Top Franchisees
                        </h3>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
                <div class="p-6">
                    @if(isset($reports['top-franchisee']) && !empty($reports['top-franchisee']['data']))
                        <div class="space-y-3">
                            @foreach(array_slice($reports['top-franchisee']['data'], 1, 5) as $index => $row)
                                <div class="flex items-center gap-3">
                                    <span class="text-lg font-semibold text-gray-400 w-6">{{ $index + 1 }}.</span>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ ucwords(strtolower($row[0] ?? '')) }}
                                        </p>
                                        @if(isset($row[1]) && is_numeric($row[1]))
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                ${{ number_format((float)$row[1], 2) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500 dark:text-gray-400">No data</p>
                    @endif
                </div>
            </a>
        </div>
    </div>
</x-filament-panels::page>

