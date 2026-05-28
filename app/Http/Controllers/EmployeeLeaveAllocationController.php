<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\CarryForwardBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeLeaveAllocationController extends Controller
{
    public function index()
    {
        if (Auth::user()->type == 'company') {
            $employees = Employee::where('created_by', Auth::user()->creatorId())->get();
            return view('employee_leave_allocation.index', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($employee_id)
    {
        if (Auth::user()->type == 'company') {
            $employee = Employee::find($employee_id);
            $allLeaveTypes = LeaveType::where('created_by', Auth::user()->creatorId())->get();
            
            // Determine employee's eligibility identifier
            $employeeTypeIdentifier = null;
            if ($employee->employee_type === 'Payroll') {
                $employeeTypeIdentifier = $employee->confirm_of_employment ? 'payroll_confirm' : 'payroll_not_confirm';
            } elseif ($employee->employee_type === 'Contract' || $employee->employee_type === 'Consultant') {
                $employeeTypeIdentifier = $employee->confirm_of_employment ? 'contract_confirm' : 'contract_not_confirm';
            }

            // Filter leave types
            $leaveTypes = $allLeaveTypes->filter(function($leaveType) use ($employeeTypeIdentifier) {
                // Exclude Comp-Off as it has its own logic
                if (strtolower(trim($leaveType->title)) === 'comp-off') {
                    return false;
                }

                if (!$leaveType->eligible_employee_types || empty($leaveType->eligible_employee_types)) {
                    return true;
                }
                return in_array($employeeTypeIdentifier, $leaveType->eligible_employee_types);
            });

            return view('employee_leave_allocation.edit', compact('employee', 'leaveTypes'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $employee_id)
    {
        if (Auth::user()->type == 'company') {
            $allocationsData = $request->input('allocations', []);

            $employee = Employee::find($employee_id);
            $allocationService = new \App\Services\LeaveAllocationService();
            $currentMonth = date('Y-m');

            foreach ($allocationsData as $leaveTypeId => $days) {
                if ($days !== null && $days !== '' && $days > 0) {
                    $leaveType = LeaveType::find($leaveTypeId);
                    if ($leaveType) {
                        // Get or create the current balance
                        $currentBalance = CarryForwardBalance::getOrCreateBalance($employee_id, $leaveTypeId, $currentMonth, 'monthly');
                        
                        // Add the top-up days directly to extra_days
                        $currentBalance->extra_days += $days;

                        // Ensure allocated_days is up to date (using base LeaveType days)
                        $newAllocatedDays = $allocationService->getAllocatedDaysForEmployee($employee, $leaveType);
                        $currentBalance->allocated_days = $newAllocatedDays;

                        // Recalculate remaining_days
                        $availableDays = ($newAllocatedDays + $currentBalance->carried_forward_days + $currentBalance->extra_days) - $currentBalance->used_days;
                        $currentBalance->remaining_days = max(0, $availableDays);

                        $currentBalance->save();

                        // Update current yearly balance
                        $currentYearlyBalance = CarryForwardBalance::getOrCreateBalance($employee_id, $leaveTypeId, date('Y'), 'yearly');
                        $currentYearlyBalance->extra_days += $days;
                        $currentYearlyBalance->allocated_days = $newAllocatedDays * 12;
                        $availableYearlyDays = ($currentYearlyBalance->allocated_days + $currentYearlyBalance->carried_forward_days + $currentYearlyBalance->extra_days) - $currentYearlyBalance->used_days;
                        $currentYearlyBalance->remaining_days = max(0, $availableYearlyDays);
                        $currentYearlyBalance->save();
                    }
                }
            }

            return redirect()->route('employee-leave-allocations.index')->with('success', __('Extra leaves successfully added to balance.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
