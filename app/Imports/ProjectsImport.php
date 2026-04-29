<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\Client;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProjectsImport implements ToCollection, WithHeadingRow
{
    private $errors = []; // array to store errors

    public function collection(Collection $rows)
    {
        $validator = Validator::make($rows->toArray(), [
            '*.project_name' => 'required|string|max:255',
            '*.client_id' => [
                'required',
                Rule::exists('clients', 'id')
            ],
        ], [
            '*.project_name.required' => 'Project name is required at row :attribute',
            '*.client_id.required' => 'Client ID is required at row :attribute',
            '*.client_id.exists' => 'Client ID does not exist at row :attribute',
        ]);

        if ($validator->fails()) {
            $this->errors = $validator->errors()->all();
            return null;
        }

        foreach ($rows as $row) {
            // Skip if any required field is empty
            if (empty($row['project_name']) || empty($row['client_id'])) {
                continue;
            }

            Project::updateOrCreate(
                [
                    'project_name' => $row['project_name'],
                    'client_id' => $row['client_id']
                ],
                [
                    'updated_at' => now()
                ]
            );
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}