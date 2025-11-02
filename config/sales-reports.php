<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sales Reports Google Sheets Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for mapping sales report rows to Google Sheets tabs
    |
    */
    
    'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID', ''),
    
    'tabs' => [
        'total_sales' => env('SALES_REPORT_TAB_TOTAL_SALES', 'Total Sales'),
        'top_performing_product' => env('SALES_REPORT_TAB_TOP_PRODUCT', 'Top Product'),
        'top_franchisee' => env('SALES_REPORT_TAB_TOP_FRANCHISEE', 'Top Franchisee'),
    ],
];

