<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\Employee;
use Illuminate\Http\Request;

class AllowanceController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Allowance') || \Auth::user()->type == 'employee') {
            $allowances = Allowance::where('created_by', \Auth::user()->creatorId())->with('employee');
            
            // Filter by employee if specified
            if ($request->has('employee_id') && $request->employee_id != '') {
                $allowances->where('employee_id', $request->employee_id);
            }
            
            // Filter by month if specified
            if ($request->has('month') && $request->month != '') {
                $allowances->where('month', $request->month);
            }
            
            $allowances = $allowances->orderBy('created_at', 'desc')->get();
            $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            
            return view('allowance.index', compact('allowances', 'employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Allowance')) {
            $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $allowanceTypes = Allowance::allowanceTypes();
            $monthOptions = Allowance::monthOptions();
            
            return view('allowance.create', compact('employees', 'allowanceTypes', 'monthOptions'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Allowance')) {
            $validator = \Validator::make(
                $request->all(), [
                    'employee_id' => 'required',
                    'allowance_type' => 'required',
                    'month' => 'required',
                    'amount' => 'required|numeric|min:0',
                    'remark' => 'nullable|string',
                ]
            );
            
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                
                // Return JSON for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $messages->first()
                    ], 422);
                }
                
                return redirect()->back()->with('error', $messages->first());
            }

            // Check if allowance already exists for this employee, month, and type
            $existingAllowance = Allowance::where('employee_id', $request->employee_id)
                ->where('month', $request->month)
                ->where('allowance_type', $request->allowance_type)
                ->where('created_by', \Auth::user()->creatorId())
                ->first();
                
            if ($existingAllowance) {
                // Return JSON for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Allowance already exists for this employee, month, and type.')
                    ], 422);
                }
                
                return redirect()->back()->with('error', __('Allowance already exists for this employee, month, and type.'));
            }

            $allowance = new Allowance();
            $allowance->employee_id = $request->employee_id;
            $allowance->allowance_type = $request->allowance_type;
            $allowance->month = $request->month;
            $allowance->amount = $request->amount;
            $allowance->remark = $request->remark;
            $allowance->created_by = \Auth::user()->creatorId();
            $allowance->save();

            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                // Set session messages for after reload
                session()->flash('success', __('Allowance successfully created.'));
                session()->flash('new_allowance_id', $allowance->id);
                
                return response()->json([
                    'success' => true,
                    'message' => __('Allowance successfully created.'),
                    'redirect' => route('allowance.index')
                ]);
            }

            return redirect()->route('allowance.index')
                ->with('success', __('Allowance successfully created.'))
                ->with('new_allowance_id', $allowance->id);
        } else {
            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Permission denied.')
                ], 403);
            }
            
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Allowance $allowance)
    {
        return redirect()->route('allowance.index');
    }

    public function edit(Allowance $allowance)
    {
        if (\Auth::user()->can('Edit Allowance')) {
            if ($allowance->created_by == \Auth::user()->creatorId()) {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $allowanceTypes = Allowance::allowanceTypes();
                $monthOptions = Allowance::monthOptions();
                
                return view('allowance.edit', compact('allowance', 'employees', 'allowanceTypes', 'monthOptions'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Allowance $allowance)
    {
        if (\Auth::user()->can('Edit Allowance')) {
            if ($allowance->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(), [
                        'employee_id' => 'required',
                        'allowance_type' => 'required',
                        'month' => 'required',
                        'amount' => 'required|numeric|min:0',
                        'remark' => 'nullable|string',
                    ]
                );
                
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                // Check if allowance already exists for this employee, month, and type (excluding current record)
                $existingAllowance = Allowance::where('employee_id', $request->employee_id)
                    ->where('month', $request->month)
                    ->where('allowance_type', $request->allowance_type)
                    ->where('id', '!=', $allowance->id)
                    ->where('created_by', \Auth::user()->creatorId())
                    ->first();
                    
                if ($existingAllowance) {
                    return redirect()->back()->with('error', __('Allowance already exists for this employee, month, and type.'));
                }

                $allowance->employee_id = $request->employee_id;
                $allowance->allowance_type = $request->allowance_type;
                $allowance->month = $request->month;
                $allowance->amount = $request->amount;
                $allowance->remark = $request->remark;
                $allowance->save();

                return redirect()->route('allowance.index')->with('success', __('Allowance successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Allowance $allowance)
    {
        if (\Auth::user()->can('Delete Allowance')) {
            if ($allowance->created_by == \Auth::user()->creatorId()) {
                $allowance->delete();
                return redirect()->route('allowance.index')->with('success', __('Allowance successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
