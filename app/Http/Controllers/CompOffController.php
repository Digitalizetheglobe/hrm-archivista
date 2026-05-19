<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\CompOff;
use Illuminate\Http\Request;

class CompOffController extends Controller
{
    public function index()
    {
        $comp_offs = CompOff::where('created_by', '=', \Auth::user()->creatorId())->get();
        return view('compoff.index', compact('comp_offs'));
    }

    public function create()
    {
        $branches = Branch::where('created_by', '=', \Auth::user()->creatorId())->pluck('name', 'id');
        return view('compoff.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'branch_id' => 'required|exists:branches,id',
                'department_ids' => 'required|array',
                'department_ids.*' => 'exists:departments,id',
                'employee_ids' => 'required|array',
                'employee_ids.*' => 'exists:employees,id',
                'dates' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', $validator->errors()->first());
        }

        try {
            $comp_off = new CompOff();
            $comp_off->branch_id = $request->branch_id;
            $comp_off->department_ids = json_encode($request->department_ids);
            
            // Format dates string "2026-05-20, 2026-05-21" to JSON array
            $datesArray = array_map('trim', explode(',', $request->dates));
            $comp_off->dates = json_encode($datesArray);
            
            $comp_off->employee_ids = json_encode($request->employee_ids);
            $comp_off->created_by = \Auth::user()->creatorId();
            $comp_off->save();

            return redirect()->route('compoff.index')->with('success', __('Comp-Off entry successfully created.'));
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', __('Error creating Comp-Off: ') . $e->getMessage());
        }
    }

    public function show($id)
    {
        $comp_off = CompOff::findOrFail($id);
        $departmentIds = json_decode($comp_off->department_ids, true) ?? [];
        $employeeIds = json_decode($comp_off->employee_ids, true) ?? [];
        
        $departments = Department::whereIn('id', $departmentIds)->pluck('name')->toArray();
        $employees = Employee::whereIn('id', $employeeIds)->get();
        $dates = json_decode($comp_off->dates, true) ?? [];

        return view('compoff.show', compact('comp_off', 'departments', 'employees', 'dates'));
    }

    public function edit($id)
    {
        $comp_off = CompOff::findOrFail($id);
        
        $selectedBranch = $comp_off->branch_id;
        $selectedDepartments = json_decode($comp_off->department_ids, true) ?? [];
        $selectedEmployees = json_decode($comp_off->employee_ids, true) ?? [];
        $dates = json_decode($comp_off->dates, true) ?? [];
        $datesString = implode(', ', $dates);

        $branches = Branch::where('created_by', '=', \Auth::user()->creatorId())->pluck('name', 'id');
        $departments = Department::where('branch_id', $selectedBranch)->pluck('name', 'id')->toArray();
        $employees = Employee::whereIn('department_id', $selectedDepartments)->pluck('name', 'id')->toArray();

        return view('compoff.edit', compact(
            'comp_off',
            'branches',
            'departments',
            'employees',
            'selectedBranch',
            'selectedDepartments',
            'selectedEmployees',
            'datesString',
            'dates'
        ));
    }

    public function update(Request $request, $id)
    {
        $comp_off = CompOff::findOrFail($id);
        
        $validator = \Validator::make(
            $request->all(),
            [
                'branch_id' => 'required|exists:branches,id',
                'department_ids' => 'required|array',
                'department_ids.*' => 'exists:departments,id',
                'employee_ids' => 'required|array',
                'employee_ids.*' => 'exists:employees,id',
                'dates' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', $validator->errors()->first());
        }

        try {
            $comp_off->branch_id = $request->branch_id;
            $comp_off->department_ids = json_encode($request->department_ids);
            
            $datesArray = array_map('trim', explode(',', $request->dates));
            $comp_off->dates = json_encode($datesArray);
            
            $comp_off->employee_ids = json_encode($request->employee_ids);
            $comp_off->save();

            return redirect()->route('compoff.index')->with('success', __('Comp-Off entry successfully updated.'));
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', __('Error updating Comp-Off: ') . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $comp_off = CompOff::findOrFail($id);
        $comp_off->delete();
        return redirect()->route('compoff.index')->with('success', __('Comp-Off entry successfully deleted.'));
    }

    public function getDepartmentsByBranch(Request $request)
    {
        $branchId = $request->input('branch_id');
        $departments = Department::where('branch_id', $branchId)->pluck('name', 'id');
        return response()->json($departments);
    }

    public function getEmployeesByDepartments(Request $request)
    {
        $departmentIds = $request->input('department_ids', []);
        
        if (empty($departmentIds)) {
            return response()->json([]);
        }

        $employees = Employee::whereIn('department_id', $departmentIds)
            ->select('id', 'name', 'department_id')
            ->orderBy('name')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'department_id' => $employee->department_id,
                    'department_name' => Department::find($employee->department_id)->name ?? 'N/A'
                ];
            });

        return response()->json($employees);
    }
}
