<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

class GoogleSheetsService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName('Corporate Database');
        $this->client->setScopes(Sheets::SPREADSHEETS);
        
        $credentialsPath = config('services.google.sheets_credentials_json');
        if (file_exists($credentialsPath)) {
            $this->client->setAuthConfig($credentialsPath);
        } else {
            // Allow constructor to pass but will fail on actual API calls
            // This allows the button to show even if credentials aren't set up yet
        }
        
        $this->client->setAccessType('offline');
        
        $this->service = new Sheets($this->client);
    }

    /**
     * Read data from Google Sheet
     */
    public function readSpreadsheet(string $spreadsheetId, string $range = 'Sheet1!A:Z'): array
    {
        try {
            $response = $this->service->spreadsheets_values->get($spreadsheetId, $range);
            return $response->getValues();
        } catch (\Exception $e) {
            \Log::error('Google Sheets read error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Write data to Google Sheet
     */
    public function writeSpreadsheet(string $spreadsheetId, string $range, array $values): void
    {
        try {
            $body = new ValueRange([
                'values' => $values
            ]);
            
            $params = [
                'valueInputOption' => 'RAW'
            ];
            
            $this->service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
        } catch (\Exception $e) {
            \Log::error('Google Sheets write error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Import locations from Google Sheet
     */
    public function importLocationsFromSheet(string $spreadsheetId, int $userId): array
    {
        $rows = $this->readSpreadsheet($spreadsheetId);
        
        if (empty($rows)) {
            return ['imported' => 0, 'errors' => ['No data found in spreadsheet']];
        }

        // Skip header row (first row)
        $header = array_shift($rows);
        
        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                if (count($row) < 13) {
                    $errors[] = "Row " . ($index + 2) . ": Insufficient columns";
                    continue;
                }

                \App\Models\Location::create([
                    'user_id' => $userId,
                    'name' => $row[0] ?? null,
                    'logo_file_url' => !empty($row[1]) ? $row[1] : null,
                    'owner_name' => !empty($row[2]) ? $row[2] : null,
                    'studio_anniversary' => !empty($row[3]) ? $row[3] : null,
                    'address_line_1' => $row[4] ?? null,
                    'address_line_2' => !empty($row[5]) ? $row[5] : null,
                    'city' => $row[6] ?? null,
                    'state' => $row[7] ?? null,
                    'zip_code' => $row[8] ?? null,
                    'country' => !empty($row[9]) ? $row[9] : 'US',
                    'phone' => !empty($row[10]) ? $row[10] : null,
                    'email' => !empty($row[11]) ? $row[11] : null,
                    'notes' => !empty($row[12]) ? $row[12] : null,
                    'lockup_file_1' => !empty($row[13]) ? $row[13] : null,
                    'lockup_file_2' => !empty($row[14]) ? $row[14] : null,
                    'lockup_file_3' => !empty($row[15]) ? $row[15] : null,
                    'lockup_file_4' => !empty($row[16]) ? $row[16] : null,
                    'lockup_file_5' => !empty($row[17]) ? $row[17] : null,
                ]);
                
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
    }
}

