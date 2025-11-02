<x-filament-panels::page>
    <div class="space-y-6 w-full" style="max-width: 100%; width: 100%;">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
            <a href="{{ route('filament.admin.pages.sales-reports') }}" class="hover:text-gray-900 transition">
                Sales Reports
            </a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">Total Sales Report</span>
        </div>

        <!-- Report Content -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if(!empty($reportData))
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            @if(!empty($reportData[0]))
                                <tr>
                                    @foreach($reportData[0] as $header)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ $header }}
                                        </th>
                                    @endforeach
                                </tr>
                            @endif
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach(array_slice($reportData, 1) as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    @foreach($row as $cell)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $cell ?? '' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No data available</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Upload a CSV file to view the report.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>

