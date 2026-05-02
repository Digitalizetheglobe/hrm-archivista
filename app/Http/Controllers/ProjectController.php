<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client; // Add this line
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // For direct SQL queries
use App\Imports\ProjectsImport;
use App\Exports\ProjectsExport;
use Maatwebsite\Excel\Facades\Excel;


class ProjectController extends Controller
{
    // Display a listing of the projects
    public function index()
    {
        if (Auth::user()->type == 'employee') {
            $projects = Project::select('project_name', 'client_id')->get();
        } else {
            $projects = Project::with('client:id,client_name')
                        ->select('projects.*')
                        ->get();
        }
        
        return view('projects.index', compact('projects'));
    }

    // In ProjectController.php
    public function create()
    {
        if (Auth::user()->can('Create Employee')) {
            // Use pluck() with both id and name to maintain key-value pairs
            $clients = Client::orderBy('client_name')->pluck('client_name', 'id');
            return view('projects.create', compact('clients'));
        }
        return redirect()->back()->with('error', 'Permission denied.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
        ]);
    
        Project::create($validated);
    
        return redirect()->route('projects.index')->with('success', 'Project created successfully!');
    }

    public function edit(Project $project)
    {
        if (auth()->user()->can('Edit Meeting')) {
            $clients = Client::orderBy('client_name')->pluck('client_name', 'id');
            return view('projects.edit', compact('project', 'clients'));
        }
        abort(403, 'Permission Denied');
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('Edit Meeting', $project);

        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
        ]);

        // Using raw SQL to update project
        DB::update("
            UPDATE projects 
            SET project_name = ?, client_id = ?, updated_at = NOW()
            WHERE id = ?
        ", [
            $validated['project_name'],
            $validated['client_id'],
            $project->id
        ]);

        return redirect()->route('projects.index');
    }


    // Remove the specified project
    public function destroy(Project $project)
    {
        if (auth()->user()->can('Edit Meeting')) {
            $this->authorize('delete project', $project);
            $project->delete();
            return redirect()->route('projects.index');        
        }

        abort(403, 'Permission Denied');
       
    }

    public function import()
    {
        if (Auth::user()->can('Create Employee')) {
            return view('projects.import');
        }
        return redirect()->back()->with('error', 'Permission denied.');
    }

    public function processImport(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv'
    ]);

    $import = new ProjectsImport();
    Excel::import($import, $request->file('file'));

    $errors = $import->getErrors();
    
    if (!empty($errors)) {
        return redirect()
            ->back()
            ->with('error', implode('<br>', $errors))
            ->withInput();
    }

    return redirect()
        ->route('projects.index')
        ->with('success', 'Projects imported successfully!');
}

    public function export()
    {
        if (Auth::user()->can('Create Employee')) {
            return Excel::download(new ProjectsExport(), 'projects_' . date('Y-m-d') . '.xlsx');
        }
        return redirect()->back()->with('error', 'Permission denied.');
    }
}

