<?php

namespace App\Http\Controllers;

use App\Models\JobAllocation;
use App\Models\Client;
use App\Models\Project;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class JobAllocationController extends Controller
{
    public function index()
    {
        $allocations = JobAllocation::where('created_by', '=', \Auth::user()->creatorId())->get();

        $data['total'] = JobAllocation::where('created_by', '=', \Auth::user()->creatorId())->count();
        $data['Ongoing'] = JobAllocation::where('status', 'Ongoing')->where('created_by', '=', \Auth::user()->creatorId())->count();
        $data['Completed'] = JobAllocation::where('status', 'Completed')->where('created_by', '=', \Auth::user()->creatorId())->count();

        return view('joballocation.index', compact('allocations', 'data'));
    }

    public function create()
    {
        $clients = Client::pluck('client_name', 'id')->toArray(); // Adjust based on your client model
        $projects = Project::pluck('project_name', 'id')->toArray(); // Adjust based on your project model
        $employees = Employee::pluck('name', 'id')->toArray(); // Adjust based on your employee model
        $departments = Department::pluck('name', 'id')->toArray(); // Adjust based on your department model
        $status = ['Ongoing' => 'Ongoing', 'Completed' => 'Completed']; // Adjust as necessary

        $jobAllocation = new \App\Models\JobAllocation(); // Add this line

    
        return view('joballocation.create', compact('clients', 'projects', 'employees', 'departments', 'status'));
    }
    

    public function store(Request $request)
{
    $validator = \Validator::make(
        $request->all(),
        [
            'client_id' => 'required',
            'project_id' => 'required',
            'start_date' => 'required|date',
            'status' => 'required',
            'billable' => 'required|boolean',
            'budgeting' => 'required|in:employees,projects',
            'department_ids' => 'required|array',
            'department_ids.*' => 'exists:departments,id',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ]
    );

    if ($validator->fails()) {
        $messages = $validator->getMessageBag();
        return redirect()->back()->withInput()->with('error', $messages->first());
    }

    try {
        $jobAllocation = new JobAllocation();
        $jobAllocation->client_id = $request->client_id;
        $jobAllocation->project_id = $request->project_id;
        $jobAllocation->status = $request->status;
        $jobAllocation->start_date = $request->start_date;
        $jobAllocation->end_date = $request->end_date;
        $jobAllocation->billable = $request->billable;
        $jobAllocation->budgeting = $request->budgeting;
        $jobAllocation->narration = $request->narration;
        
        // Save department IDs as JSON array
        $jobAllocation->department_id = json_encode($request->department_ids);
        
        // Save employee IDs as JSON array
        $jobAllocation->employees_id = json_encode($request->employee_ids);
        
        // Prepare approvers data - department_id with all selected employees in that department
        $approversData = [];
        $selectedEmployees = Employee::whereIn('id', $request->employee_ids)
                                    ->get()
                                    ->groupBy('department_id');
        
        foreach ($request->department_ids as $departmentId) {
            $employeeIds = [];
            if (isset($selectedEmployees[$departmentId])) {
                $employeeIds = $selectedEmployees[$departmentId]->pluck('id')->toArray();
            }
            
            if (!empty($employeeIds)) {
                $approversData[] = [
                    'department_id' => $departmentId,
                    'employee_ids' => $employeeIds
                ];
            }
        }
        
        $jobAllocation->approver = json_encode($approversData);
        
        $jobAllocation->created_by = \Auth::user()->creatorId();
        $jobAllocation->save();

        return redirect()->route('joballocation.index')->with('success', __('Job Allocation successfully created.'));
    } catch (\Exception $e) {
        return redirect()->back()->withInput()->with('error', __('Error creating job allocation: ') . $e->getMessage());
    }
}

    public function show($id)
    {
        $jobAllocation = JobAllocation::with('project')->find($id); // 👈 load related project
    
        if (!$jobAllocation) {
            return redirect()->route('joballocations.index')->with('error', 'Job allocation not found.');
        }
    
        return view('joballocation.show', compact('jobAllocation'));
    }
    

    public function edit($id)
{
    $jobAllocation = JobAllocation::findOrFail($id);

    // Check if the current user has permission to edit
    if ($jobAllocation->created_by != \Auth::user()->creatorId()) {
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    // Decode JSON fields
    $selectedDepartments = json_decode($jobAllocation->department_id ?? '[]', true) ?? [];
    $selectedEmployees = json_decode($jobAllocation->employees_id ?? '[]', true) ?? [];
    $approvers = json_decode($jobAllocation->approver, true) ?? [];

    // Get all departments and employees for dropdowns
    $departments = Department::pluck('name', 'id')->toArray();
    
    // Get employees from selected departments + any previously selected employees
    $employeesQuery = Employee::whereIn('department_id', $selectedDepartments)
                            ->orWhereIn('id', $selectedEmployees);
    
    $employees = $employeesQuery->pluck('name', 'id')->toArray();

    // Other data
    $clients = Client::pluck('client_name', 'id')->toArray();
    $projects = Project::where('client_id', $jobAllocation->client_id)->pluck('project_name', 'id')->toArray();
    $status = ['Ongoing' => 'Ongoing', 'Completed' => 'Completed'];

    return view('joballocation.edit', compact(
        'jobAllocation',
        'clients',
        'projects',
        'departments',
        'employees',
        'status',
        'selectedDepartments',
        'selectedEmployees',
        'approvers'
    ));
}

public function update(Request $request, $id)   
{
    $jobAllocation = JobAllocation::findOrFail($id);
    
    if ($jobAllocation->created_by != \Auth::user()->creatorId()) {
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    $validator = \Validator::make($request->all(), [
        'client_id' => 'required',
        'project_id' => 'required',
        'start_date' => 'required|date',
        'status' => 'required',
        'billable' => 'required|boolean',
        'budgeting' => 'required|in:employees,projects',
        'department_ids' => 'required|array',
        'department_ids.*' => 'exists:departments,id',
        'employee_ids' => 'required|array',
        'employee_ids.*' => 'exists:employees,id',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    try {
        // Update basic fields
        $jobAllocation->client_id = $request->client_id;
        $jobAllocation->project_id = $request->project_id;
        $jobAllocation->status = $request->status;
        $jobAllocation->start_date = $request->start_date;
        $jobAllocation->end_date = $request->end_date;
        $jobAllocation->billable = $request->billable;
        $jobAllocation->budgeting = $request->budgeting;
        $jobAllocation->narration = $request->narration;
        
        // Update JSON-encoded fields
        $jobAllocation->department_id = json_encode($request->department_ids);
        $jobAllocation->employees_id = json_encode($request->employee_ids);

        // Prepare approvers data - department_id with all selected employees in that department
        $approversData = [];
        $selectedEmployees = Employee::whereIn('id', $request->employee_ids)
                                    ->get()
                                    ->groupBy('department_id');
        
        foreach ($request->department_ids as $departmentId) {
            $employeeIds = [];
            if (isset($selectedEmployees[$departmentId])) {
                $employeeIds = $selectedEmployees[$departmentId]->pluck('id')->toArray();
            }
            
            if (!empty($employeeIds)) {
                $approversData[] = [
                    'department_id' => $departmentId,
                    'employee_ids' => $employeeIds
                ];
            }
        }
        
        $jobAllocation->approver = json_encode($approversData);
        $jobAllocation->save();

        return redirect()->route('joballocation.index')
            ->with('success', __('Job Allocation updated successfully.'));

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', __('Error updating job allocation: ') . $e->getMessage())
            ->withInput();
    }
}
    public function destroy(JobAllocation $jobAllocation)
    {
        $jobAllocation->delete();
        return redirect()->route('joballocation.index')->with('success', __('Job Allocation successfully deleted.'));
    }

    public function getProjects($client_id)
    {
        $projects = Project::where('client_id', $client_id)->pluck('project_name', 'id');
        
        if ($projects->isEmpty()) {
            return response()->json([], 404);
        }
        
        return response()->json($projects);
    }

    public function getEmployeesByDepartments(Request $request)
    {
        $departmentIds = $request->input('department_ids', []);
        
        // Validate input
        if (empty($departmentIds)) {
            return response()->json([]);
        }

        // Get employees from selected departments
        $employees = Employee::whereIn('department_id', $departmentIds)
            ->select('id', 'name', 'department_id')
            ->orderBy('name')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'department_id' => $employee->department_id
                ];
            });

        return response()->json($employees);
    }

    public function getApprovers(Request $request)
    {
        $departmentIds = $request->input('department_ids', []);
        $selectedEmployeeIds = $request->input('employee_ids', []);

        $departments = Department::whereIn('id', $departmentIds)->get();
        $response = [];

        foreach ($departments as $department) {
            // Get employees that are both in this department AND selected in the dropdown
            $employees = Employee::where('department_id', $department->id)
                ->whereIn('id', $selectedEmployeeIds)
                ->select('id', 'name')
                ->get()
                ->map(function($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->name
                    ];
                })
                ->toArray();

            $response[] = [
                'department_id' => $department->id,
                'department_name' => $department->name,
                'employees' => $employees
            ];
        }

        return response()->json($response);
    }

}