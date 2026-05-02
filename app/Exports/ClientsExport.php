<?php

namespace App\Exports;

use App\Models\Client;
use App\Models\Site;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientsExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = Client::where('created_by', \Auth::user()->creatorId())->get();
        foreach($data as $k => $client)
        {
            unset($client->id, $client->created_by, $client->created_at, $client->updated_at);

            $data[$k]["clients_group_id"] = !empty($client->clients_group_id) ? $client->site->name : '-';
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            "Client Code",
            "Client Name", 
            "Client Address",
            "State",
            "Country",
            "GST No",
            "Company Phone",
            "Company Email",
            "Contact Person Name",
            "Contact Person Phone",
            "Remark",
            "Site/Group"
        ];
    }
}
