<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TimeSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TimeSheetExport;
use App\Models\JobAllocation; // Add this line
use App\Models\Client;       // Add if not present
use App\Models\Project;     // Add if not present
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; 




class TimeSheetController extends Controller
{
    public function index(Request $request)
{
    if (Auth::user()->can('Manage TimeSheet')) {
        $employeesList = [];
        $timesheets = TimeSheet::with(['employee', 'client', 'project']);
        $clients = Client::pluck('client_name', 'id')->prepend('All', '');
        $projects = Project::pluck('project_name', 'id')->prepend('All', '');

        if (Auth::user()->type == 'employee') {
            $employeeId = Auth::user()->id;
            $employeesList = Employee::where('user_id', $employeeId)->first();

            // If dates are selected, filter timesheets and show total of date range
            if ($request->filled('start_date') || $request->filled('end_date')) {
                $startDate = $request->start_date ?? now()->format('Y-m-d');
                $endDate = $request->end_date ?? now()->format('Y-m-d');

                $timesheets->where('employee_id', $employeeId)
                           ->whereBetween('date', [$startDate, $endDate]);

                $totalTime = TimeSheet::where('employee_id', $employeeId)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('total_time');
            } else {
                // No dates selected: show all records, calculate today's total
                $timesheets->where('employee_id', $employeeId);
                
                $totalTime = TimeSheet::where('employee_id', $employeeId)
                    ->whereDate('date', now()->format('Y-m-d'))
                    ->sum('total_time');
            }

            $timesheets = $timesheets->orderBy('date', 'desc')->get();

        } else {
            // For admin or other roles
            $employeesList = Employee::pluck('name', 'user_id')->prepend('All', '');
            
            if ($request->filled('employee')) {
                $timesheets->where('employee_id', $request->employee);
            }

            if ($request->filled('client')) {
                $timesheets->where('client_id', $request->client);
            }

            if ($request->filled('project')) {
                $timesheets->where('project_id', $request->project);
            }

            if ($request->filled('start_date') || $request->filled('end_date')) {
                $startDate = $request->start_date ?? now()->format('Y-m-d');
                $endDate = $request->end_date ?? now()->format('Y-m-d');
                $timesheets->whereBetween('date', [$startDate, $endDate]);
            }

            $totalTime = null;
            $timesheets = $timesheets->orderBy('date', 'desc')->get();
        }

        return view('timeSheet.index', [
            'timeSheets' => $timesheets,
            'employeesList' => $employeesList,
            'clients' => $clients,
            'projects' => $projects,
            'totalTime' => $totalTime,
        ]);
    }

    return redirect()->back()->with('error', __('Permission denied.'));
}


    public function create()
    {
        if (Auth::user()->can('Create TimeSheet')) {
            $employees = []; // Initialize empty array for employees
            
            // Only load employees if user is admin/manager
            if (Auth::user()->type != 'employee') {
                $employees = Employee::pluck('name', 'user_id')->prepend('Select Employee', '');
            }
            
            $clients = Client::pluck('client_name', 'id');
            $projects = collect(); // Empty collection for projects (loaded via AJAX)
            
            return view('timeSheet.create', compact('employees', 'clients', 'projects'));
        }

        return redirect()->back()->with('error', 'Permission denied.');
    }


    public function store(Request $request)
    {
        if (Auth::user()->can('Create TimeSheet')) {
            $request->validate([
                // ... your validation rules ...
            ]);

            $employeeId = Auth::user()->type == 'employee' ? Auth::user()->id : $request->employee_id;
            
            $timeSheet = new TimeSheet();
            $timeSheet->employee_id = $employeeId;
            $timeSheet->client_id = $request->client_id;
            $timeSheet->project_id = $request->project_id;
            $timeSheet->date = $request->date;
            $timeSheet->total_time = $request->total_time;
            $timeSheet->billable = $request->billable;
            $timeSheet->location = $request->location;
            $timeSheet->narration = $request->narration;
            $timeSheet->expense = $request->expense ?? 0.00;
            
            // The day_total will be calculated automatically in the model's saving event
            $timeSheet->save();

            return redirect()->route('timesheet.index')->with('success', __('Timesheet successfully created.'));
        }

        return redirect()->back()->with('error', 'Permission denied.');
    }

    public function edit(TimeSheet $timeSheet)
    {
        if (Auth::user()->can('Edit TimeSheet')) {
            $employees = [];
            
            if (Auth::user()->type != 'employee') {
                $employees = Employee::pluck('name', 'user_id')->prepend('Select Employee', '');
            }
            
            $clients = Client::pluck('client_name', 'id');
            $projects = Project::where('client_id', $timeSheet->client_id)->pluck('project_name', 'id');
            
            return view('timeSheet.edit', compact('timeSheet', 'employees', 'clients', 'projects'));
        }
    
        return redirect()->back()->with('error', 'Permission denied.');
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('Edit TimeSheet')) {
            $request->validate([
                'employee_id' => 'required_if:user_type,!=,employee|exists:employees,user_id',
                'date' => 'required|date',
                'client_id' => 'required|exists:clients,id',
                'project_id' => 'required|exists:projects,id',
                'total_time' => 'required|string|max:10',
                'billable' => 'required|in:Billable,Non-Billable',
                'location' => 'nullable|string|max:255',
                'narration' => 'nullable|string',
                'expense' => 'nullable|numeric|min:0',
            ]);

            $timeSheet = TimeSheet::findOrFail($id);
            
            $employeeId = Auth::user()->type == 'employee' ? Auth::user()->id : $request->employee_id;
            
            $timeSheet->employee_id = $employeeId;
            $timeSheet->client_id = $request->client_id;
            $timeSheet->project_id = $request->project_id;
            $timeSheet->date = $request->date;
            $timeSheet->total_time = $request->total_time;
            $timeSheet->billable = $request->billable;
            $timeSheet->location = $request->location;
            $timeSheet->narration = $request->narration;
            $timeSheet->expense = $request->expense ?? 0.00;
            
            $timeSheet->save();

            return redirect()->route('timesheet.index')->with('success', __('Timesheet successfully updated.'));
        }

        return redirect()->back()->with('error', 'Permission denied.');
    }

    public function destroy($id)
    {
        if (Auth::user()->can('Delete TimeSheet')) {
            $timeSheet = TimeSheet::findOrFail($id);
            $timeSheet->delete();
            return redirect()->route('timesheet.index')->with('success', 'Timesheet deleted successfully!');
        }

        return redirect()->back()->with('error', 'Permission denied.');
    }

    public function show(TimeSheet $timeSheet)
    {
        if (Auth::user()->can('View TimeSheet')) {
            return view('timeSheet.show', compact('timeSheet'));
        }

        return redirect()->back()->with('error', 'Permission denied.');
    }

    public function export(Request $request)
    {
        return Excel::download(new TimeSheetExport($request), 'timesheets.xlsx');
    }
public function getClientProjects($clientId)
{
    \Log::info("Fetching projects for client: $clientId");
    
    try {
        // Verify client exists
        if (!\App\Models\Client::where('id', $clientId)->exists()) {
            \Log::error("Client not found", ['client_id' => $clientId]);
            return response()->json(['error' => 'Client not found'], 404);
        }

        // Get projects with debug logging
        $projects = \App\Models\Project::where('client_id', $clientId)
            ->select('id', 'project_name')
            ->get();
            
        \Log::debug("Projects found:", [
            'count' => $projects->count(),
            'sample' => $projects->first()
        ]);

        return response()->json($projects->pluck('project_name', 'id'));

    } catch (\Exception $e) {
        \Log::error("Error fetching projects:", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'Server error'], 500);
    }
}
}