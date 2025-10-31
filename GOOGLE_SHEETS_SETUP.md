# Google Sheets Integration Setup

This document explains how to set up Google Sheets API integration for bulk importing locations.

## Prerequisites

1. A Google Cloud Project with Sheets API enabled
2. Service Account credentials (JSON file)

## Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google Sheets API:
   - Navigate to "APIs & Services" > "Library"
   - Search for "Google Sheets API"
   - Click "Enable"

## Step 2: Create Service Account

1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "Service Account"
3. Fill in the service account details
4. Click "Create and Continue"
5. Grant the service account access (optional)
6. Click "Done"

## Step 3: Generate Service Account Key

1. Click on the service account you just created
2. Go to the "Keys" tab
3. Click "Add Key" > "Create new key"
4. Choose JSON format
5. Download the JSON file

## Step 4: Share Google Sheet with Service Account

1. Open your Google Sheet
2. Click "Share" button
3. Add the service account email (found in the JSON file as `client_email`)
4. Give it "Editor" access
5. Copy the Sheet ID from the URL:
   - The Sheet ID is the long string between `/d/` and `/edit` in the URL
   - Example: `https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit`
   - Sheet ID: `1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms`

## Step 5: Configure Laravel

1. Place the JSON credentials file in `storage/app/google-credentials.json` (or your preferred secure location)

2. Add to `.env`:
```env
GOOGLE_SHEETS_CREDENTIALS_PATH=storage/app/google-credentials.json
GOOGLE_SHEETS_SPREADSHEET_ID=your_sheet_id_here
```

3. Add to `config/services.php`:
```php
'google' => [
    'sheets_credentials_json' => env('GOOGLE_SHEETS_CREDENTIALS_PATH'),
    'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID'),
],
```

## Step 6: Google Sheet Format

Your Google Sheet should have the following headers in Row 1:

| Location Name | Logo File URL | Owner Name(s) | Studio Anniversary | Address Line 1 | Address Line 2 | City | State | Zip Code | Country | Phone Number | Email Address | Notes | Artwork File 1 URL | Artwork File 2 URL | Artwork File 3 URL | Artwork File 4 URL | Artwork File 5 URL |
|--------------|---------------|---------------|-------------------|----------------|----------------|------|-------|---------|---------|--------------|---------------|-------|-------------------|-------------------|-------------------|-------------------|-------------------|

**Important Notes:**
- **Logo File URL**: Enter a full URL to the logo file (e.g., `https://example.com/logo.png`). This avoids file upload issues.
- **Artwork File URLs**: Enter full URLs to artwork files (e.g., `https://example.com/artwork1.png`). You can provide up to 5 artwork file URLs. Leave blank if not needed.
- Studio Anniversary should be in `YYYY-MM-DD` format
- Country defaults to "US" if left blank
- All other fields are optional but recommended

## Step 7: Import from Google Sheets

Once configured, you can import locations from your Google Sheet by:

1. Using the Google Sheets service in your code:
```php
use App\Services\GoogleSheetsService;

$service = new GoogleSheetsService();
$result = $service->importLocationsFromSheet($spreadsheetId, auth()->id());

// $result contains ['imported' => count, 'errors' => array]
```

2. Or add an import action in Filament (you can extend the ListLocations page)

## Security Note

- Never commit the credentials JSON file to version control
- Add `storage/app/google-credentials.json` to `.gitignore`
- Use environment variables for sensitive information

