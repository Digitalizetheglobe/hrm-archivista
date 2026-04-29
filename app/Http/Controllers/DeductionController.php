<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Models\Employee;
use Illuminate\Http\Request;

class DeductionController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Deduction') || \Auth::user()->type == 'employee') {
            $deductions = Deduction::where('created_by', \Auth::user()->creatorId())->with('employee');
            
            // Filter by employee if specified
            if ($request->has('employee_id') && $request->employee_id != '') {
                $deductions->where('employee_id', $request->employee_id);
            }
            
            // Filter by month if specified
            if ($request->has('month') && $request->month != '') {
                $deductions->where('month', $request->month);
            }
            
            $deductions = $deductions->orderBy('created_at', 'desc')->get();
            $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            
            return view('deduction.index', compact('deductions', 'employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Deduction')) {
            $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $deductionTypes = Deduction::deductionTypes();
            $monthOptions = Deduction::monthOptions();
            
            return view('deduction.create', compact('employees', 'deductionTypes', 'monthOptions'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Deduction')) {
            $validator = \Validator::make(
                $request->all(), [
                    'employee_id' => 'required',
                    'deduction_type' => 'required',
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

            // Check if deduction already exists for this employee, month, and type
            $existingDeduction = Deduction::where('employee_id', $request->employee_id)
                ->where('month', $request->month)
                ->where('deduction_type', $request->deduction_type)
                ->where('created_by', \Auth::user()->creatorId())
                ->first();
                
            if ($existingDeduction) {
                // Return JSON for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Deduction already exists for this employee, month, and type.')
                    ], 422);
                }
                
                return redirect()->back()->with('error', __('Deduction already exists for this employee, month, and type.'));
            }

            $deduction = new Deduction();
            $deduction->employee_id = $request->employee_id;
            $deduction->deduction_type = $request->deduction_type;
            $deduction->month = $request->month;
            $deduction->amount = $request->amount;
            $deduction->remark = $request->remark;
            $deduction->created_by = \Auth::user()->creatorId();
            $deduction->save();

            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                // Set session messages for after reload
                session()->flash('success', __('Deduction successfully created.'));
                session()->flash('new_deduction_id', $deduction->id);
                
                return response()->json([
                    'success' => true,
                    'message' => __('Deduction successfully created.'),
                    'redirect' => route('deduction.index')
                ]);
            }

            return redirect()->route('deduction.index')
                ->with('success', __('Deduction successfully created.'))
                ->with('new_deduction_id', $deduction->id);
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

    public function show(Deduction $deduction)
    {
        return redirect()->route('deduction.index');
    }

    public function edit(Deduction $deduction)
    {
        if (\Auth::user()->can('Edit Deduction')) {
            if ($deduction->created_by == \Auth::user()->creatorId()) {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $deductionTypes = Deduction::deductionTypes();
                $monthOptions = Deduction::monthOptions();
                
                return view('deduction.edit', compact('deduction', 'employees', 'deductionTypes', 'monthOptions'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Deduction $deduction)
    {
        if (\Auth::user()->can('Edit Deduction')) {
            if ($deduction->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(), [
                        'employee_id' => 'required',
                        'deduction_type' => 'required',
                        'month' => 'required',
                        'amount' => 'required|numeric|min:0',
                        'remark' => 'nullable|string',
                    ]
                );
                
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                // Check if deduction already exists for this employee, month, and type (excluding current record)
                $existingDeduction = Deduction::where('employee_id', $request->employee_id)
                    ->where('month', $request->month)
                    ->where('deduction_type', $request->deduction_type)
                    ->where('id', '!=', $deduction->id)
                    ->where('created_by', \Auth::user()->creatorId())
                    ->first();
                    
                if ($existingDeduction) {
                    return redirect()->back()->with('error', __('Deduction already exists for this employee, month, and type.'));
                }

                $deduction->employee_id = $request->employee_id;
                $deduction->deduction_type = $request->deduction_type;
                $deduction->month = $request->month;
                $deduction->amount = $request->amount;
                $deduction->remark = $request->remark;
                $deduction->save();

                return redirect()->route('deduction.index')->with('success', __('Deduction successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Deduction $deduction)
    {
        if (\Auth::user()->can('Delete Deduction')) {
            if ($deduction->created_by == \Auth::user()->creatorId()) {
                $deduction->delete();
                return redirect()->route('deduction.index')->with('success', __('Deduction successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
