<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ClientsImport;

class ClientController extends Controller
{
    public function index()
    {
        if (Gate::denies('Manage Employee')) {
            abort(403, 'Unauthorized action.');
        }

        $clients = Client::where('created_by', Auth::id())->get();
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        if (Gate::denies('Create Employee')) {
            abort(403, 'Unauthorized action.');
        }

        $sites = Site::pluck('name', 'id');
        return view('clients.create', compact('sites'));
    }

    public function store(Request $request)
    {
        if (Gate::denies('Create Employee')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_code' => 'nullable|string|max:50',
            'client_address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'gst_no' => 'nullable|string|max:50',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:100',
            'contact_person_name' => 'nullable|string|max:100',
            'contact_person_phone' => 'nullable|string|max:20',
            'remark' => 'nullable|string',
            'clients_group_id' => 'required|exists:sites,id',
        ]);

        Client::create([
            'clients_group_id' => $request->clients_group_id,
            'client_code' => $request->client_code,
            'client_name' => $request->client_name,
            'client_address' => $request->client_address,
            'state' => $request->state,
            'country' => $request->country,
            'gst_no' => $request->gst_no,
            'company_phone' => $request->company_phone,
            'company_email' => $request->company_email,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_phone' => $request->contact_person_phone,
            'remark' => $request->remark,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    public function edit(Client $client)
    {
        if (Gate::denies('Edit Employee')) {
            abort(403, 'Unauthorized action.');
        }

        $sites = Site::pluck('name', 'id');
        return view('clients.edit', compact('client', 'sites'));
    }

    public function update(Request $request, Client $client)
    {
        if (Gate::denies('Edit Employee')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_code' => 'nullable|string|max:50',
            'client_address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'gst_no' => 'nullable|string|max:50',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:100',
            'contact_person_name' => 'nullable|string|max:100',
            'contact_person_phone' => 'nullable|string|max:20',
            'remark' => 'nullable|string',
            'clients_group_id' => 'required|exists:sites,id',
        ]);

        $client->update([
            'clients_group_id' => $request->clients_group_id,
            'client_code' => $request->client_code,
            'client_name' => $request->client_name,
            'client_address' => $request->client_address,
            'state' => $request->state,
            'country' => $request->country,
            'gst_no' => $request->gst_no,
            'company_phone' => $request->company_phone,
            'company_email' => $request->company_email,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_phone' => $request->contact_person_phone,
            'remark' => $request->remark,
        ]);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        if (Gate::denies('Delete Employee')) {
            abort(403, 'Unauthorized action.');
        }

        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }

    public function import()
    {
        if (Gate::denies('Create Employee')) {
            abort(403, 'Unauthorized action.');
        }
    
        return view('clients.import');
    }
    
    public function processImport(Request $request)
    {
        if (Gate::denies('Create Employee')) {
            abort(403, 'Unauthorized action.');
        }
    
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }
    
        try {
            Excel::import(new ClientsImport(), $request->file('file'));
            return redirect()->route('clients.index')->with('success', __('Clients imported successfully.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Error importing clients: ') . $e->getMessage());
        }
    }

    
}