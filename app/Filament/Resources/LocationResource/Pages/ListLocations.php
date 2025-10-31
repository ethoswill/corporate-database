<?php

namespace App\Filament\Resources\LocationResource\Pages;

use App\Filament\Resources\LocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Response;

class ListLocations extends ListRecords
{
    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_sample_csv')
                ->label('Download Sample CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $headers = [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => 'attachment; filename="locations_sample.csv"',
                    ];

                    $callback = function () {
                        $file = fopen('php://output', 'w');
                        
                        // CSV Headers matching the form fields
                        fputcsv($file, [
                            'Location Name',
                            'Logo File URL',
                            'Owner Name(s)',
                            'Studio Anniversary',
                            'Address Line 1',
                            'Address Line 2',
                            'City',
                            'State',
                            'Zip Code',
                            'Country',
                            'Phone Number',
                            'Email Address',
                            'Notes',
                            'Artwork File 1 URL',
                            'Artwork File 2 URL',
                            'Artwork File 3 URL',
                            'Artwork File 4 URL',
                            'Artwork File 5 URL',
                        ]);

                        // Add sample row
                        fputcsv($file, [
                            'Sample Location',
                            'https://example.com/logo.png',
                            'John Doe, Jane Doe',
                            '2020-01-15',
                            '123 Main Street',
                            'Suite 100',
                            'Springfield',
                            'IL',
                            '62701',
                            'US',
                            '555-123-4567',
                            'sample@example.com',
                            'Sample location notes',
                            'https://example.com/artwork1.png',
                            'https://example.com/artwork2.png',
                            '',
                            '',
                            '',
                        ]);

                        fclose($file);
                    };

                    return Response::stream($callback, 200, $headers);
                }),
            Actions\Action::make('import_csv')
                ->label('Import from CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('csv_file')
                        ->label('CSV File')
                        ->acceptedFileTypes(['text/csv', 'text/plain', '.csv'])
                        ->required()
                        ->disk('local')
                        ->directory('imports')
                        ->helperText('Upload a CSV file with the same headers as the sample CSV'),
                ])
                ->action(function (array $data) {
                    // Get the file path from storage
                    $filePath = $data['csv_file'];
                    $storage = \Illuminate\Support\Facades\Storage::disk('local');
                    
                    // Filament FileUpload with directory='imports' returns path like 'imports/filename.csv'
                    // Check if file exists using Storage facade
                    if (!$storage->exists($filePath)) {
                        // Try alternative - maybe it's just the filename
                        $altPath = 'imports/' . basename($filePath);
                        if ($storage->exists($altPath)) {
                            $filePath = $altPath;
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('File not found')
                                ->body('Could not locate the uploaded CSV file. Please try uploading again.')
                                ->send();
                            return;
                        }
                    }
                    
                    // Get the full file path
                    $csvPath = $storage->path($filePath);
                    
                    if (!file_exists($csvPath)) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('File not found')
                            ->body('Could not locate the uploaded CSV file on disk.')
                            ->send();
                        return;
                    }

                    $file = fopen($csvPath, 'r');
                    
                    if ($file === false) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('File read error')
                            ->body('Could not read the CSV file.')
                            ->send();
                        return;
                    }
                    
                    $header = fgetcsv($file); // Skip header row
                    
                    $imported = 0;
                    $errors = [];

                    while (($row = fgetcsv($file)) !== false) {
                        // Skip empty rows
                        if (empty(array_filter($row))) {
                            continue;
                        }
                        
                        if (count($row) < 13) {
                            $errors[] = 'Row has insufficient columns: ' . implode(',', $row);
                            continue;
                        }

                        try {
                            \App\Models\Location::create([
                                'user_id' => auth()->id(),
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
                            $errors[] = 'Error importing row: ' . $e->getMessage();
                        }
                    }

                    fclose($file);
                    
                    // Clean up uploaded file using Storage facade
                    try {
                        \Illuminate\Support\Facades\Storage::disk('local')->delete($filePath);
                    } catch (\Exception $e) {
                        // Ignore cleanup errors
                    }

                    if (!empty($errors)) {
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('Import completed with errors')
                            ->body("Imported {$imported} locations. " . count($errors) . " errors occurred.")
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Import successful')
                            ->body("Successfully imported {$imported} locations.")
                            ->send();
                    }
                }),
            Actions\Action::make('import_google_sheets')
                ->label('Import from Google Sheets')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('info')
                ->visible(function () {
                    return !empty(config('services.google.spreadsheet_id'));
                })
                ->form([
                    \Filament\Forms\Components\TextInput::make('spreadsheet_id')
                        ->label('Google Sheet ID')
                        ->helperText('Get this from your Google Sheet URL: docs.google.com/spreadsheets/d/YOUR_SHEET_ID/edit')
                        ->required()
                        ->default(fn () => config('services.google.spreadsheet_id')),
                ])
                ->action(function (array $data) {
                    try {
                        $service = new \App\Services\GoogleSheetsService();
                        $result = $service->importLocationsFromSheet($data['spreadsheet_id'], auth()->id());
                        
                        if (!empty($result['errors'])) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Import completed with errors')
                                ->body("Imported {$result['imported']} locations. " . count($result['errors']) . " errors occurred.")
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Import successful')
                                ->body("Successfully imported {$result['imported']} locations.")
                                ->send();
                        }
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('Import failed')
                            ->body('Error: ' . $e->getMessage())
                            ->send();
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }
}
