<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProjectsExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = Project::with('client:id,client_name')->get();
        foreach($data as $k => $project)
        {
            unset($project->id, $project->created_at, $project->updated_at);

            $data[$k]["client_id"] = !empty($project->client_id) ? $project->client->client_name : '-';
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            "Project Name",
            "Client Name"
        ];
    }
}
