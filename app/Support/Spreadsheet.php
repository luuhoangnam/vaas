<?php

namespace App\Support;

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use Illuminate\Support\Collection;

class Spreadsheet
{
    protected $spreadsheetId;

    public function __construct($spreadsheetId)
    {
        $this->spreadsheetId = $spreadsheetId;
    }

    public function read($range)
    {
        return $this->service()->spreadsheets_values->get($this->spreadsheetId, $range);
    }

    public function write($range, $values)
    {
        $requestBody = new Google_Service_Sheets_ValueRange;

        if ($values instanceof Collection) {
            $values = $values->toArray();
        }

        if ( ! is_array($values)) {
            $values = [[$values]];
        }

        $requestBody->values = $this->normalizeValues($values);

        return $this->service()->spreadsheets_values->update($this->spreadsheetId, $range, $requestBody, [
            'valueInputOption' => 'USER_ENTERED',
        ]);
    }

    protected function service(): Google_Service_Sheets
    {
        $client = new Google_Client;
        $client->setAuthConfig(resource_path('credentials/vaas_google_client_secret.json'));
        $client->setAccessType('offline');
        $client->setScopes([
            'https://www.googleapis.com/auth/spreadsheets.readonly',
            'https://www.googleapis.com/auth/spreadsheets',
            'https://www.googleapis.com/auth/drive.readonly',
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/drive',
        ]);

        return new Google_Service_Sheets($client);
    }

    protected function normalizeValues($values): array
    {
        foreach ($values as $x => $row) {
            foreach ($row as $y => $cell) {
                $values[$x][$y] = $cell ?: '';
            }
        }

        return $values;
    }
}