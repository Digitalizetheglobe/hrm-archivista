<?php

namespace App\Imports;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Skip if client_name is empty
        if (empty($row['client_name'])) {
            return null;
        }

        return new Client([
            'client_name' => $row['client_name'],
            'created_by' => Auth::id(),
            // All other fields will be null by default
        ]);
    }
}