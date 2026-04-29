<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('Manage Leave Type'))
        {
            $leavetypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('leavetype.index', compact('leavetypes'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->can('Create Leave Type'))
        {
            return view('leavetype.create');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('Create Leave Type'))
        {
            \Log::info('Leave Type Creation Attempt', ['request_data' => $request->all()]);
            
            try {
                $request->validate([
                    'title' => 'required',
                    'type' => 'required|in:monthly,yearly',
                    'is_unlimited' => 'boolean',
                    'carry_forward_enabled' => 'boolean',
                    'max_carry_forward_days' => 'nullable|numeric|min:0.01',
                    'days' => 'required_unless:is_unlimited,true|nullable|numeric|min:0.01',
                    'eligible_employee_types' => 'required|array|min:1',
                    'eligible_employee_types.*' => 'required|string|in:payroll_confirm,payroll_not_confirm,contract_confirm,contract_not_confirm',
                ]);

                \Log::info('Validation Passed');

                $leavetype = new LeaveType();
                $leavetype->title = $request->title;
                $leavetype->type = $request->type;
                $leavetype->is_unlimited = $request->has('is_unlimited') ? true : false;
                $leavetype->carry_forward_enabled = $request->has('carry_forward_enabled') ? true : false;
                $leavetype->max_carry_forward_days = ($leavetype->carry_forward_enabled && $request->max_carry_forward_days) ? $request->max_carry_forward_days : 0;
                $leavetype->days = $leavetype->is_unlimited ? 0 : $request->days;
                $leavetype->eligible_employee_types = $request->eligible_employee_types;
                $leavetype->created_by = \Auth::user()->creatorId();
                
                \Log::info('About to save leave type', ['leavetype_data' => $leavetype->toArray()]);
                
                $leavetype->save();
                
                \Log::info('Leave Type Saved Successfully', ['leavetype_id' => $leavetype->id]);

                return redirect()->route('leavetype.index')->with('success', __('LeaveType successfully created.'));
                
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('Validation Failed', ['errors' => $e->errors()]);
                throw $e;
            } catch (\Exception $e) {
                \Log::error('Save Failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return redirect()->back()->with('error', 'Failed to create leave type: ' . $e->getMessage());
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(LeaveType $leavetype)
    {
        return redirect()->route('leavetype.index');
    }

    public function edit(LeaveType $leavetype)
    {
        if(\Auth::user()->can('Edit Leave Type'))
        {
            if($leavetype->created_by == \Auth::user()->creatorId())
            {
                return view('leavetype.edit', compact('leavetype'));
            }
            else
            {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, LeaveType $leavetype)
    {
        \Log::info('Leave Type Update Attempt', [
            'leavetype_id' => $leavetype->id, 
            'request_data' => $request->all(),
            'method' => $request->method()
        ]);
        
        if(\Auth::user()->can('Edit Leave Type'))
        {
            if($leavetype->created_by == \Auth::user()->creatorId())
            {
                try {
                    $validator = \Validator::make(
                        $request->all(), [
                            'title' => 'required',
                            'type' => 'required|in:monthly,yearly',
                            'is_unlimited' => 'boolean',
                            'carry_forward_enabled' => 'boolean',
                            'max_carry_forward_days' => 'nullable|numeric|min:0.01',
                            'days' => 'required_unless:is_unlimited,true|nullable|numeric|min:0.01',
                            'eligible_employee_types' => 'required|array|min:1',
                            'eligible_employee_types.*' => 'required|string|in:payroll_confirm,payroll_not_confirm,contract_confirm,contract_not_confirm',
                        ]
                    );

                    if($validator->fails())
                    {
                        \Log::error('Update Validation Failed', ['errors' => $validator->errors()->toArray()]);
                        $messages = $validator->getMessageBag();
                        return redirect()->back()->with('error', $messages->first());
                    }

                    \Log::info('Update Validation Passed');

                    $leavetype->title = $request->title;
                    $leavetype->type = $request->type;
                    $leavetype->is_unlimited = $request->has('is_unlimited') ? true : false;
                    $leavetype->carry_forward_enabled = $request->has('carry_forward_enabled') ? true : false;
                    $leavetype->max_carry_forward_days = ($leavetype->carry_forward_enabled && $request->max_carry_forward_days) ? $request->max_carry_forward_days : 0;
                    $leavetype->days = $leavetype->is_unlimited ? 0 : $request->days;
                    $leavetype->eligible_employee_types = $request->eligible_employee_types;
                    
                    \Log::info('About to update leave type', ['leavetype_data' => $leavetype->toArray()]);
                    
                    $leavetype->save();
                    
                    \Log::info('Leave Type Updated Successfully', ['leavetype_id' => $leavetype->id]);

                    return redirect()->route('leavetype.index')->with('success', __('LeaveType successfully updated.'));
                    
                } catch (\Exception $e) {
                    \Log::error('Update Failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    return redirect()->back()->with('error', 'Failed to update leave type: ' . $e->getMessage());
                }
            }
            else
            {
                \Log::error('Permission denied - wrong creator');
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            \Log::error('Permission denied - no edit permission');
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(LeaveType $leavetype)
    {
        if(\Auth::user()->can('Delete Leave Type'))
        {
            if($leavetype->created_by == \Auth::user()->creatorId())
            {
                $leave = Leave::where('leave_type_id', $leavetype->id)->get();
                if(count($leave) == 0)
                {
                    $leavetype->delete();
                }
                else
                {
                    return redirect()->route('leavetype.index')->with('error', __('This leavetype has leave. Please remove the leave from this leavetype.'));
                }

                return redirect()->route('leavetype.index')->with('success', __('LeaveType successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
