<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Leave as LocalLeave;
use App\Models\LeaveType;
use App\Models\CarryForwardBalance;
use App\Mail\LeaveActionSend;
use App\Models\Utility;
use App\Services\LeaveAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Imports\EmployeesImport;
use App\Exports\LeaveExport;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class LeaveController extends Controller
{
    /**
     * The leave allocation service instance.
     *
     * @var LeaveAllocationService
     */
    protected $leaveAllocationService;

    /**
     * Create a new controller instance.
     *
     * @param LeaveAllocationService $leaveAllocationService
     */
    public function __construct(LeaveAllocationService $leaveAllocationService)
    {
        $this->leaveAllocationService = $leaveAllocationService;
    }

    /**
     * Get employee type identifier for leave type eligibility checking
     */
    private function getEmployeeTypeIdentifier($employee)
    {
        if ($employee->employee_type === 'Payroll') {
            return $employee->confirm_of_employment ? 'payroll_confirm' : 'payroll_not_confirm';
        } elseif ($employee->employee_type === 'Contract') {
            return $employee->confirm_of_employment ? 'contract_confirm' : 'contract_not_confirm';
        }
        
        return null; // Unknown type
    }

    /**
     * Get allocated days for employee based on leave type
     */
    private function getAllocatedDaysForEmployee($employee, $leaveType)
    {
        // Return the days allocated for this leave type
        return $leaveType->days ?? 0;
    }

    public function index()
    {
        if (\Auth::user()->can('Manage Leave')) {
            if (\Auth::user()->type == 'employee') {
                $user     = \Auth::user();
                $employee = Employee::where('user_id', '=', $user->id)->first();
                $leaves = LocalLeave::where('employee_id', '=', $employee->id)->get();
                
                // Calculate leave balance data for dashboard using the new service
                $leaveBalances = $this->leaveAllocationService->getCurrentLeaveBalances($employee->id);
                
                // Get filtered leave types for this employee (exclude test leave types)
                $leaveTypes = LeaveType::where('created_by', \Auth::user()->creatorId())
                    ->where(function($query) {
                        $query->where('title', 'not like', '%Test%')
                              ->where('title', 'not like', '%TEST%');
                    })
                    ->get();
                
                // Filter leave types based on employee type
                $employeeTypeIdentifier = $this->getEmployeeTypeIdentifier($employee);
                \Log::info('Employee Filter Debug:', [
                    'employee_id' => $employee->id,
                    'employee_type' => $employee->employee_type,
                    'confirm_of_employment' => $employee->confirm_of_employment,
                    'identifier' => $employeeTypeIdentifier
                ]);
                
                $leaveTypes = $leaveTypes->filter(function($leaveType) use ($employeeTypeIdentifier) {
                    // If no eligible_employee_types set, show to all (backward compatibility)
                    if (!$leaveType->eligible_employee_types || empty($leaveType->eligible_employee_types)) {
                        \Log::info('Leave Type Shows (no restrictions):', ['title' => $leaveType->title]);
                        return true;
                    }
                    
                    // Check if employee's type identifier is in the eligible list
                    $wouldShow = in_array($employeeTypeIdentifier, $leaveType->eligible_employee_types);
                    \Log::info('Leave Type Check:', [
                        'title' => $leaveType->title,
                        'eligible_types' => $leaveType->eligible_employee_types,
                        'employee_identifier' => $employeeTypeIdentifier,
                        'would_show' => $wouldShow
                    ]);
                    
                    return $wouldShow;
                });
                
            } else {
                $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())->with(['employees', 'leaveType'])->get();
                $leaveBalances = []; // For admin, we'll show per employee in the table
                $leaveTypes = collect(); // Empty for admin
                $employee = null; // Initialize employee for admin view
            }

            return view('leave.index', compact('leaves', 'leaveBalances', 'leaveTypes', 'employee'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Leave')) {
            if (Auth::user()->type == 'employee') {
                $employees = Employee::where('user_id', '=', \Auth::user()->id)->first();
               
            } else {
                $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            }
            $leavetypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
            
            // Filter leave types based on employee type for employee users
            if (Auth::user()->type == 'employee') {
                $employee = Employee::where('user_id', '=', \Auth::user()->id)->first();
                if ($employee) {
                    // Get employee type identifier
                    $employeeTypeIdentifier = $this->getEmployeeTypeIdentifier($employee);
                    
                    // Filter leave types based on eligible_employee_types
                    $leavetypes = $leavetypes->filter(function($leaveType) use ($employeeTypeIdentifier) {
                        // If no eligible_employee_types set, show to all (backward compatibility)
                        if (!$leaveType->eligible_employee_types || empty($leaveType->eligible_employee_types)) {
                            return true;
                        }
                        
                        // Check if employee's type identifier is in the eligible list
                        return in_array($employeeTypeIdentifier, $leaveType->eligible_employee_types);
                    });
                }
            }
            
            // Debug: Log leave types data
            \Log::info('Leave Types Data:', [
                'count' => $leavetypes->count(),
                'data' => $leavetypes->toArray()
            ]);
            
            // Debug: Check specific unlimited leave types
            $lwp = $leavetypes->where('title', 'LWP')->first();
            $wfh = $leavetypes->where('title', 'WFH')->first();
            \Log::info('LWP Data:', $lwp ? ['data' => $lwp->toArray()] : ['status' => 'Not found']);
            \Log::info('WFH Data:', $wfh ? ['data' => $wfh->toArray()] : ['status' => 'Not found']);
    
            return view('leave.create', compact('employees', 'leavetypes'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
    

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Leave')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'leave_type_id' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'leave_reason' => 'required',
                    'remark' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // Get employee and leave type information
            $employee = Employee::find($request->employee_id);
            $leave_type = LeaveType::find($request->leave_type_id);

            // Validate contract employee leave type restrictions
            if ($employee && $employee->employee_type === 'Contract') {
                $leaveTypeName = strtolower(trim($leave_type->title));
                if (!$leave_type->is_unlimited && $leaveTypeName !== 'casual leave') {
                    return redirect()->back()->with('error', __('Contract employees can only apply for Casual Leave and Unlimited Leaves.'));
                }
            }

            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $endDate->add(new \DateInterval('P1D'));
            // $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
            $date = Utility::AnnualLeaveCycle();

            if (\Auth::user()->type == 'employee') {
                // Leave day
                $leaves_used   = LocalLeave::where('employee_id', '=', $request->employee_id)->where('leave_type_id', $leave_type->id)->where('status', 'Approved')->whereBetween('created_at', [$date['start_date'],$date['end_date']])->sum('total_leave_days');

                $leaves_pending  = LocalLeave::where('employee_id', '=', $request->employee_id)->where('leave_type_id', $leave_type->id)->where('status', 'Pending')->whereBetween('created_at', [$date['start_date'],$date['end_date']])->sum('total_leave_days');
            } else {
                // Leave day
                $leaves_used   = LocalLeave::where('employee_id', '=', $request->employee_id)->where('leave_type_id', $leave_type->id)->where('status', 'Approved')->whereBetween('created_at', [$date['start_date'],$date['end_date']])->sum('total_leave_days');

                $leaves_pending  = LocalLeave::where('employee_id', '=', $request->employee_id)->where('leave_type_id', $leave_type->id)->where('status', 'Pending')->whereBetween('created_at', [$date['start_date'],$date['end_date']])->sum('total_leave_days');
            }

            $total_leave_days = $this->calculateBusinessDays($request->start_date, $request->end_date);

            // Skip leave balance check for unlimited leave types
            if (!$leave_type->is_unlimited) {
                // Get allocated days based on employee type
                $allocatedDays = $this->getAllocatedDaysForEmployee($employee, $leave_type);
                
                // Calculate available days based on leave type period
                if ($leave_type->type == 'monthly') {
                    // For monthly leave types, calculate days for current month with carry-forward
                    $currentMonth = date('Y-m');
                    $currentYear = date('Y');
                    $currentMonthNum = date('m');
                    $monthStart = $currentYear . '-' . $currentMonthNum . '-01';
                    $monthEnd = $currentYear . '-' . $currentMonthNum . '-' . date('t', strtotime($currentYear . '-' . $currentMonthNum . '-01'));
                    
                    // Get or create current month carry forward balance
                    $carryForwardBalance = CarryForwardBalance::getOrCreateBalance($request->employee_id, $leave_type->id, $currentMonth);
                    
                    // Calculate carry forward from previous month (only if enabled)
                    $carriedForwardDays = 0;
                    if ($leave_type->carry_forward_enabled) {
                        $previousMonth = date('Y-m', strtotime($currentYear . '-' . $currentMonthNum . '-01 -1 month'));
                        $carriedForwardDays = CarryForwardBalance::calculateCarryForward($request->employee_id, $leave_type->id, $previousMonth);
                        
                        // Update current month balance with carried forward days
                        $carryForwardBalance->carried_forward_days = $carriedForwardDays;
                        $carryForwardBalance->save();
                    }
                    
                    // Calculate used days this month
                    $leaves_used_monthly = LocalLeave::where('employee_id', '=', $request->employee_id)
                        ->where('leave_type_id', $leave_type->id)
                        ->where('status', 'Approved')
                        ->whereBetween('created_at', [$monthStart, $monthEnd])
                        ->sum('total_leave_days');
                    
                    $leaves_pending_monthly = LocalLeave::where('employee_id', '=', $request->employee_id)
                        ->where('leave_type_id', $leave_type->id)
                        ->where('status', 'Pending')
                        ->whereBetween('created_at', [$monthStart, $monthEnd])
                        ->sum('total_leave_days');
                    
                    // Update carry forward balance record
                    $carryForwardBalance->allocated_days = $allocatedDays;
                    $carryForwardBalance->used_days = $leaves_used_monthly + $leaves_pending_monthly;
                    $carryForwardBalance->remaining_days = ($allocatedDays + $carriedForwardDays) - $carryForwardBalance->used_days;
                    $carryForwardBalance->save();
                    
                    // Total available days = allocated + carried forward - used
                    $totalAvailable = ($allocatedDays + $carriedForwardDays) - $leaves_used_monthly;
                    
                    if ($total_leave_days > $totalAvailable) {
                        $carryInfo = $leave_type->carry_forward_enabled ? " (including {$carriedForwardDays} carried forward)" : "";
                        return redirect()->back()->with('error', __('You are not eligible for leave. Available: ' . $totalAvailable . ' days for this month' . $carryInfo . '.'));
                    }
                    
                    if (!empty($leaves_pending_monthly) && $leaves_pending_monthly + $total_leave_days > $totalAvailable) {
                        return redirect()->back()->with('error', __('Multiple leave entry is pending. Available: ' . ($totalAvailable - $leaves_pending_monthly) . ' days for this month.'));
                    }
                    
                    if ($totalAvailable >= $total_leave_days) {
                        // Proceed with leave creation
                    } else {
                        return redirect()->back()->with('error', __('Insufficient leave balance. Available: ' . $totalAvailable . ' days for this month.'));
                    }
                } else {
                    // For yearly leave types
                    $return = $allocatedDays - $leaves_used;
                    if ($total_leave_days > $return) {
                        return redirect()->back()->with('error', __('You are not eligible for leave.'));
                    }

                    if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $return) {
                        return redirect()->back()->with('error', __('Multiple leave entry is pending.'));
                    }

                    if ($allocatedDays >= $total_leave_days) {
                        // Proceed with leave creation
                    } else {
                        return redirect()->back()->with('error', __('Insufficient leave balance.'));
                    }
                }
            }

            $leave    = new LocalLeave();
            if (\Auth::user()->type == "employee") {
                $leave->employee_id = $request->employee_id;
            } else {
                $leave->employee_id = $request->employee_id;
            }
            $leave->leave_type_id    = $request->leave_type_id;
            $leave->applied_on       = date('Y-m-d');
            $leave->start_date       = $request->start_date;
            $leave->end_date         = $request->end_date;
            $leave->total_leave_days = $total_leave_days;
            $leave->leave_reason     = $request->leave_reason;
            $leave->remark           = $request->remark;
            $leave->status           = 'Pending';
            $leave->created_by       = \Auth::user()->creatorId();
            $leave->save();

                if ($request->leave_type_id == 'comp_off') {
                $employee = Employee::find($request->employee_id);
                if ($employee->comp_off_balance <= 0) {
                    return redirect()->back()->with('error', __('No comp-offs available.'));
                }
                $employee->comp_off_balance -= 1;
                $employee->save();
            }

                // Google celander
            if ($request->get('synchronize_type')  == 'google_calender') {

                $type = 'leave';
                $request1 = new GoogleEvent();
                $request1->title = !empty(\Auth::user()->getLeaveType($leave->leave_type_id)) ? \Auth::user()->getLeaveType($leave->leave_type_id)->title : '';
                $request1->start_date = $request->start_date;
                $request1->end_date = $request->end_date;
                Utility::addCalendarData($request1, $type);
            }

                return redirect()->route('leave.index')->with('success', __('Leave successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(LocalLeave $leave)
    {
        return redirect()->route('leave.index');
    }

    public function edit(LocalLeave $leave)
    {
        if (\Auth::user()->can('Edit Leave')) {
            if ($leave->created_by == \Auth::user()->creatorId()) {

                if (Auth::user()->type == 'employee') {
                    $employees = Employee::where('employee_id', '=', \Auth::user()->creatorId())->first();
                } else {
                    $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                }

                // $employees = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

                // $leavetypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('title', 'id');
                $leavetypes      = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
                
                // Filter leave types based on employee type for employee users
                if (Auth::user()->type == 'employee') {
                    $employee = Employee::where('user_id', '=', \Auth::user()->id)->first();
                    if ($employee) {
                        // Get employee type identifier
                        $employeeTypeIdentifier = $this->getEmployeeTypeIdentifier($employee);
                        
                        // Filter leave types based on eligible_employee_types
                        $leavetypes = $leavetypes->filter(function($leaveType) use ($employeeTypeIdentifier) {
                            // If no eligible_employee_types set, show to all (backward compatibility)
                            if (!$leaveType->eligible_employee_types || empty($leaveType->eligible_employee_types)) {
                                return true;
                            }
                            
                            // Check if employee's type identifier is in the eligible list
                            return in_array($employeeTypeIdentifier, $leaveType->eligible_employee_types);
                        });
                    }
                }

                return view('leave.edit', compact('leave', 'employees', 'leavetypes'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $leave)
    {
        $leave = LocalLeave::find($leave);
        if (\Auth::user()->can('Edit Leave')) {
            if ($leave->created_by == Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'employee_id' => 'required',
                        'leave_type_id' => 'required',
                        'start_date' => 'required',
                        'end_date' => 'required',
                        'leave_reason' => 'required',
                        'remark' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                
                // Get employee and leave type information
                $employee = Employee::find($request->employee_id);
                $leave_type = LeaveType::find($request->leave_type_id);
                
                // Validate contract employee leave type restrictions
                if ($employee && $employee->employee_type === 'Contract') {
                    $leaveTypeName = strtolower(trim($leave_type->title));
                    if (!$leave_type->is_unlimited && $leaveTypeName !== 'casual leave') {
                        return redirect()->back()->with('error', __('Contract employees can only apply for Casual Leave and Unlimited Leaves.'));
                    }
                }
                
                // For admin users, get the employee from the request
                if (Auth::user()->type != 'employee') {
                    $employee = Employee::find($request->employee_id);
                }

                $startDate = new \DateTime($request->start_date);
                $endDate = new \DateTime($request->end_date);
                $endDate->add(new \DateInterval('P1D'));
                // $total_leave_days = !empty($startDate->diff($endDate)) ? $startDate->diff($endDate)->days : 0;
                $date = Utility::AnnualLeaveCycle();

                if (\Auth::user()->type == 'employee') {
                    // Leave day
                    $leaves_used   = LocalLeave::whereNotIn('id', [$leave->id])->where('employee_id', '=', $employee->id)->where('leave_type_id', $leave_type->id)->where('status', 'Approved')->whereBetween('created_at', [$date['start_date'],$date['end_date']])->sum('total_leave_days');

                    $leaves_pending  = LocalLeave::whereNotIn('id', [$leave->id])->where('employee_id', '=', $employee->id)->where('leave_type_id', $leave_type->id)->where('status', 'Pending')->whereBetween('created_at', [$date['start_date'],$date['end_date']])->sum('total_leave_days');
                } else {
                    // Leave day
                    $leaves_used   = LocalLeave::whereNotIn('id', [$leave->id])->where('employee_id', '=', $request->employee_id)->where('leave_type_id', $leave_type->id)->where('status', 'Approved')->whereBetween('created_at', [$date['start_date'],$date['end_date']])->sum('total_leave_days');

                    $leaves_pending  = LocalLeave::whereNotIn('id', [$leave->id])->where('employee_id', '=', $request->employee_id)->where('leave_type_id', $leave_type->id)->where('status', 'Pending')->whereBetween('created_at', [$date['start_date'],$date['end_date']])->sum('total_leave_days');
                }

                $total_leave_days = $this->calculateBusinessDays($request->start_date, $request->end_date);

                // Get allocated days based on employee type
                $allocatedDays = $this->getAllocatedDaysForEmployee($employee, $leave_type);
                
                $return = $allocatedDays - $leaves_used;
                if ($total_leave_days > $return) {
                    return redirect()->back()->with('error', __('You are not eligible for leave.'));
                }

                if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $return) {
                    return redirect()->back()->with('error', __('Multiple leave entry is pending.'));
                }

                if ($allocatedDays >= $total_leave_days) {
                    if (\Auth::user()->type == 'employee') {
                        $leave->employee_id = $employee->id;
                    } else {
                        $leave->employee_id      = $request->employee_id;
                    }
                    $leave->leave_type_id    = $request->leave_type_id;
                    $leave->start_date       = $request->start_date;
                    $leave->end_date         = $request->end_date;
                    $leave->total_leave_days = $total_leave_days;
                    $leave->leave_reason     = $request->leave_reason;
                    $leave->remark           = $request->remark;
                    // $leave->status           = $request->status;

                    $leave->save();

                    return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
                } else {
                    return redirect()->back()->with('error', __('Leave type ' . $leave_type->name . ' is provide maximum ' . $allocatedDays . "  days please make sure your selected days is under " . $allocatedDays . ' days.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(LocalLeave $leave)
    {
        if (\Auth::user()->can('Delete Leave')) {
            if ($leave->created_by == \Auth::user()->creatorId()) {
                $leave->delete();

                return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function export()
    {
        $name = 'leave_' . date('Y-m-d i:h:s');
        $data = Excel::download(new LeaveExport(), $name . '.xlsx');

        return $data;
    }

    public function action($id)
    {
        $leave     = LocalLeave::find($id);
        $employee  = Employee::find($leave->employee_id);
        $leavetype = LeaveType::find($leave->leave_type_id);



        return view('leave.action', compact('employee', 'leavetype', 'leave'));
    }

    public function changeaction(Request $request)
    {
        $leave = LocalLeave::find($request->leave_id);

        $leave->status = $request->status;
        if ($leave->status == 'Approved') {
            $total_leave_days        = $this->calculateBusinessDays($leave->start_date, $leave->end_date);
            $leave->total_leave_days = $total_leave_days;
            $leave->status           = 'Approved';
        }

        $leave->save();

        // twilio
        $setting = Utility::settings(\Auth::user()->creatorId());
        $emp = Employee::find($leave->employee_id);
        if (isset($setting['twilio_leave_approve_notification']) && $setting['twilio_leave_approve_notification'] == 1) {
            // $msg = __("Your leave has been") . ' ' . $leave->status . '.';

            $uArr = [
                'leave_status' => $leave->status,
            ];


            Utility::send_twilio_msg($emp->phone, 'leave_approve_reject', $uArr);
        }

        $setings = Utility::settings();

        if ($setings['leave_status'] == 1) {
            $employee     = Employee::where('id', $leave->employee_id)->where('created_by', '=', \Auth::user()->creatorId())->first();

            $uArr = [
                'leave_email' => $employee->email,
                'leave_status_name' => $employee->name,
                'leave_status' => $request->status,
                'leave_reason' => $leave->leave_reason,
                'leave_start_date' => $leave->start_date,
                'leave_end_date' => $leave->end_date,
                'total_leave_days' => $leave->total_leave_days,

            ];
            $resp = Utility::sendEmailTemplate('leave_status', [$employee->email], $uArr);
            return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }

        return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.'));
    }

    public function getLeaveBalanceForEmployee($employeeId, $leaveTypeId)
    {
        $employee = Employee::find($employeeId);
        $leaveType = LeaveType::find($leaveTypeId);
        
        if (!$employee || !$leaveType) {
            return response()->json(['error' => 'Employee or leave type not found'], 404);
        }
        
        // Get allocated days based on employee type
        $allocatedDays = $this->getAllocatedDaysForEmployee($employee, $leaveType);
        
        // Calculate current balance
        $currentMonth = date('Y-m');
        $currentYear = date('Y');
        $currentMonthNum = date('m');
        $monthStart = $currentYear . '-' . $currentMonthNum . '-01';
        $monthEnd = $currentYear . '-' . $currentMonthNum . '-' . date('t', strtotime($currentYear . '-' . $currentMonthNum . '-01'));
        
        if ($leaveType->type == 'monthly') {
            $usedThisMonth = LocalLeave::where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveTypeId)
                ->where('status', 'Approved')
                ->where(function($query) use ($monthStart, $monthEnd) {
                    $query->where(function($subQuery) use ($monthStart, $monthEnd) {
                        $subQuery->where('start_date', '>=', $monthStart)
                               ->where('end_date', '<=', $monthEnd);
                    })->orWhere(function($subQuery) use ($monthStart, $monthEnd) {
                        $subQuery->where('start_date', '>=', $monthStart)
                               ->where('start_date', '<=', $monthEnd)
                               ->where('end_date', '>', $monthEnd);
                    })->orWhere(function($subQuery) use ($monthStart, $monthEnd) {
                        $subQuery->where('start_date', '<', $monthStart)
                               ->where('end_date', '>=', $monthStart)
                               ->where('end_date', '<=', $monthEnd);
                    });
                })
                ->sum('total_leave_days');
            
            $availableDays = $allocatedDays - $usedThisMonth;
        } else {
            $date = Utility::AnnualLeaveCycle();
            $totalUsed = LocalLeave::where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveTypeId)
                ->where('status', 'Approved')
                ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
                ->sum('total_leave_days');
            
            $availableDays = $allocatedDays - $totalUsed;
        }
        
        return response()->json([
            'allocated_days' => $allocatedDays,
            'available_days' => max(0, $availableDays),
            'is_unlimited' => $leaveType->is_unlimited,
            'employee_type' => $employee->employee_type,
            'confirm_of_employment' => $employee->confirm_of_employment
        ]);
    }

    public function getLeaveTypesForEmployee($employeeId)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
        
        $leavetypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
        
        // Filter leave types based on employee type
        if ($employee) {
            // Get employee type identifier
            $employeeTypeIdentifier = $this->getEmployeeTypeIdentifier($employee);
            
            // Filter leave types based on eligible_employee_types
            $leavetypes = $leavetypes->filter(function($leaveType) use ($employeeTypeIdentifier) {
                // If no eligible_employee_types set, show to all (backward compatibility)
                if (!$leaveType->eligible_employee_types || empty($leaveType->eligible_employee_types)) {
                    return true;
                }
                
                // Check if employee's type identifier is in the eligible list
                return in_array($employeeTypeIdentifier, $leaveType->eligible_employee_types);
            });
        }
        
        return response()->json($leavetypes);
    }

    public function jsoncount(Request $request)
    {
        $date = Utility::AnnualLeaveCycle();
        
        // Start with base query
        $query = LeaveType::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave, leave_types.title, leave_types.days,leave_types.id'))
            ->leftjoin(
                'leaves',
                function ($join) use ($request, $date) {
                    $join->on('leaves.leave_type_id', '=', 'leave_types.id');
                    $join->where('leaves.employee_id', '=', $request->employee_id);
                    $join->where('leaves.status', '=', 'Approved');
                    $join->whereBetween('leaves.created_at', [$date['start_date'],$date['end_date']]);
                }
            )->where('leave_types.created_by', '=', \Auth::user()->creatorId())->groupBy('leave_types.id');
        
        // Get leave types and apply employee filtering if needed
        $leaveTypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
        
        // Filter leave types based on employee type for employee users
        if (Auth::user()->type == 'employee') {
            $employee = Employee::where('user_id', '=', \Auth::user()->id)->first();
            if ($employee && $employee->id == $request->employee_id) {
                // Get employee type identifier
                $employeeTypeIdentifier = $this->getEmployeeTypeIdentifier($employee);
                
                // Filter leave types based on eligible_employee_types
                $eligibleLeaveTypeIds = $leaveTypes->filter(function($leaveType) use ($employeeTypeIdentifier) {
                    // If no eligible_employee_types set, show to all (backward compatibility)
                    if (!$leaveType->eligible_employee_types || empty($leaveType->eligible_employee_types)) {
                        return true;
                    }
                    
                    // Check if employee's type identifier is in the eligible list
                    return in_array($employeeTypeIdentifier, $leaveType->eligible_employee_types);
                })->pluck('id')->toArray();
                
                // Apply filtering to the main query
                if (!empty($eligibleLeaveTypeIds)) {
                    $query->whereIn('leave_types.id', $eligibleLeaveTypeIds);
                }
            }
        }
        
        $leave_counts = $query->get();
        return $leave_counts;
    }

    public function calender(Request $request)
    {
        $created_by = \Auth::user()->creatorId();
        $Meetings = LocalLeave::where('created_by', $created_by)->get();

        $today_date = date('m');
        $current_month_event = LocalLeave::select('id', 'start_date', 'employee_id', 'created_at')->whereRaw('MONTH(start_date)=' . $today_date)->get();

        $arrMeeting = [];

        foreach ($Meetings as $meeting) {
            $arr['id']        = $meeting['id'];
            $arr['employee_id']     = $meeting['employee_id'];
            // $arr['leave_type_id']     = date('Y-m-d', strtotime($meeting['start_date']));
        }

        $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())->get();
        if (\Auth::user()->type == 'employee') {
            $user     = \Auth::user();
            $employee = Employee::where('user_id', '=', $user->id)->first();
            $leaves   = LocalLeave::where('employee_id', '=', $employee->id)->get();
        } else {
            $leaves = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())->get();
        }

        return view('leave.calender', compact('leaves'));
    }

    public function get_leave_data(Request $request)
    {
        $arrayJson = [];
        if ($request->get('calender_type') == 'google_calender') {
            $type = 'leave';
            $arrayJson =  Utility::getCalendarData($type);
        } else {
            $data = LocalLeave::where('created_by', \Auth::user()->creatorId())->get();

            foreach ($data as $val) {
                $end_date = date_create($val->end_date);
                date_add($end_date, date_interval_create_from_date_string("1 days"));
                $arrayJson[] = [
                    "id" => $val->id,
                    "title" => !empty(\Auth::user()->getLeaveType($val->leave_type_id)) ? \Auth::user()->getLeaveType($val->leave_type_id)->title : '',
                    "start" => $val->start_date,
                    "end" => date_format($end_date, "Y-m-d H:i:s"),
                    "className" => $val->color,
                    "textColor" => '#FFF',
                    "allDay" => true,
                    "url" => route('leave.action', $val['id']),
                ];
            }
        }

        return $arrayJson;
    }

    /**
     * Calculate business days between two dates, excluding weekends (Saturday and Sunday)
     *
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    private function calculateBusinessDays($startDate, $endDate)
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $end->add(new \DateInterval('P1D')); // Include end date in calculation
        
        $businessDays = 0;
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($start, $interval, $end);
        
        foreach ($period as $day) {
            // Exclude Saturday (6) and Sunday (7)
            if ($day->format('N') != 6 && $day->format('N') != 7) {
                $businessDays++;
            }
        }
        
        return $businessDays;
    }

    /**
     * Display comprehensive leave details for all employees categorized by type
     */
    public function leaveDetails(Request $request)
    {
        if (\Auth::user()->can('Manage Leave')) {
            $selectedMonth = $request->get('month', date('Y-m'));
            $monthStart = $selectedMonth . '-01';
            $monthEnd = $selectedMonth . '-' . date('t', strtotime($monthStart));
            
            // Get all employees for the current company
            $employees = Employee::where('created_by', \Auth::user()->creatorId())->get();
            
            // Get all leave types
            $leaveTypes = LeaveType::where('created_by', \Auth::user()->creatorId())->get();
            
            // Categorize employees by type
            $contractConfirmEmployees = $employees->filter(function($employee) {
                return $employee->employee_type === 'Contract' && $employee->confirm_of_employment;
            });
            
            $contractNotConfirmEmployees = $employees->filter(function($employee) {
                return $employee->employee_type === 'Contract' && !$employee->confirm_of_employment;
            });
            
            $payrollEmployees = $employees->filter(function($employee) {
                return $employee->employee_type === 'Payroll';
            });
            
            // Calculate leave details for each category
            $leaveDetails = [
                'contract_confirm' => $this->calculateCategoryLeaveDetails($contractConfirmEmployees, $leaveTypes, $monthStart, $monthEnd),
                'contract_not_confirm' => $this->calculateCategoryLeaveDetails($contractNotConfirmEmployees, $leaveTypes, $monthStart, $monthEnd),
                'payroll' => $this->calculateCategoryLeaveDetails($payrollEmployees, $leaveTypes, $monthStart, $monthEnd)
            ];
            
            // Calculate monthly summary
            $monthlySummary = $this->calculateMonthlySummary($employees, $selectedMonth);
            
            return view('leave.leave_details', compact(
                'leaveDetails', 
                'contractConfirmEmployees', 
                'contractNotConfirmEmployees', 
                'payrollEmployees',
                'leaveTypes',
                'selectedMonth',
                'monthlySummary'
            ));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    /**
     * Calculate leave details for a specific employee category
     */
    private function calculateCategoryLeaveDetails($employees, $leaveTypes, $monthStart, $monthEnd)
    {
        $categoryDetails = [];
        
        foreach ($employees as $employee) {
            $employeeDetails = [
                'employee' => $employee,
                'leave_balances' => []
            ];
            
            foreach ($leaveTypes as $leaveType) {
                // Get employee type identifier for eligibility checking
                $employeeTypeIdentifier = $this->getEmployeeTypeIdentifier($employee);
                
                // Check if this leave type is eligible for this employee
                if (!$leaveType->eligible_employee_types || empty($leaveType->eligible_employee_types) || 
                    in_array($employeeTypeIdentifier, $leaveType->eligible_employee_types)) {
                    
                    // Calculate leave balance for this employee and leave type
                    $balance = $this->calculateEmployeeLeaveBalance($employee, $leaveType, $monthStart, $monthEnd);
                    $employeeDetails['leave_balances'][$leaveType->id] = $balance;
                }
            }
            
            $categoryDetails[] = $employeeDetails;
        }
        
        return $categoryDetails;
    }
    
    /**
     * Calculate leave balance for a specific employee and leave type
     */
    private function calculateEmployeeLeaveBalance($employee, $leaveType, $monthStart, $monthEnd)
    {
        // Get allocated days based on employee type
        $allocatedDays = $this->getAllocatedDaysForEmployee($employee, $leaveType);
        
        // Calculate used days in the selected month
        $usedDays = LocalLeave::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'Approved')
            ->where(function($query) use ($monthStart, $monthEnd) {
                $query->where(function($subQuery) use ($monthStart, $monthEnd) {
                    $subQuery->where('start_date', '>=', $monthStart)
                           ->where('end_date', '<=', $monthEnd);
                })->orWhere(function($subQuery) use ($monthStart, $monthEnd) {
                    $subQuery->where('start_date', '>=', $monthStart)
                           ->where('start_date', '<=', $monthEnd)
                           ->where('end_date', '>', $monthEnd);
                })->orWhere(function($subQuery) use ($monthStart, $monthEnd) {
                    $subQuery->where('start_date', '<', $monthStart)
                           ->where('end_date', '>=', $monthStart)
                           ->where('end_date', '<=', $monthEnd);
                });
            })
            ->sum('total_leave_days');
        
        // Calculate carried forward days for monthly leave types
        $carriedForwardDays = 0;
        if ($leaveType->type == 'monthly' && $leaveType->carry_forward_enabled) {
            $previousMonth = date('Y-m', strtotime($monthStart . ' -1 month'));
            $carriedForwardDays = CarryForwardBalance::calculateCarryForward($employee->id, $leaveType->id, $previousMonth);
        }
        
        // Calculate remaining daysCasual Leave for Employees

        if ($leaveType->is_unlimited) {
            $remainingDays = -1; // Unlimited
        } else {
            $remainingDays = ($allocatedDays + $carriedForwardDays) - $usedDays;
        }
        
        return [
            'leave_type' => $leaveType,
            'allocated_days' => $allocatedDays,
            'carried_forward_days' => $carriedForwardDays,
            'used_days' => $usedDays,
            'remaining_days' => max(0, $remainingDays),
            'is_unlimited' => $leaveType->is_unlimited
        ];
    }
    
    /**
     * Calculate monthly summary for all employees
     */
    private function calculateMonthlySummary($employees, $selectedMonth)
    {
        $monthStart = $selectedMonth . '-01';
        $monthEnd = $selectedMonth . '-' . date('t', strtotime($monthStart));
        
        $totalLeaves = 0;
        $totalCredited = 0;
        $totalUsed = 0;
        
        foreach ($employees as $employee) {
            $leaveTypes = LeaveType::where('created_by', \Auth::user()->creatorId())->get();
            $employeeTypeIdentifier = $this->getEmployeeTypeIdentifier($employee);
            
            foreach ($leaveTypes as $leaveType) {
                // Check eligibility
                if (!$leaveType->eligible_employee_types || empty($leaveType->eligible_employee_types) || 
                    in_array($employeeTypeIdentifier, $leaveType->eligible_employee_types)) {
                    
                    if (!$leaveType->is_unlimited) {
                        $allocatedDays = $this->getAllocatedDaysForEmployee($employee, $leaveType);
                        $totalCredited += $allocatedDays;
                        
                        // Calculate carried forward
                        if ($leaveType->type == 'monthly' && $leaveType->carry_forward_enabled) {
                            $previousMonth = date('Y-m', strtotime($monthStart . ' -1 month'));
                            $carriedForwardDays = CarryForwardBalance::calculateCarryForward($employee->id, $leaveType->id, $previousMonth);
                            $totalCredited += $carriedForwardDays;
                        }
                    }
                    
                    // Calculate used days
                    $usedDays = LocalLeave::where('employee_id', $employee->id)
                        ->where('leave_type_id', $leaveType->id)
                        ->where('status', 'Approved')
                        ->where(function($query) use ($monthStart, $monthEnd) {
                            $query->where(function($subQuery) use ($monthStart, $monthEnd) {
                                $subQuery->where('start_date', '>=', $monthStart)
                                       ->where('end_date', '<=', $monthEnd);
                            })->orWhere(function($subQuery) use ($monthStart, $monthEnd) {
                                $subQuery->where('start_date', '>=', $monthStart)
                                       ->where('start_date', '<=', $monthEnd)
                                       ->where('end_date', '>', $monthEnd);
                            })->orWhere(function($subQuery) use ($monthStart, $monthEnd) {
                                $subQuery->where('start_date', '<', $monthStart)
                                       ->where('end_date', '>=', $monthStart)
                                       ->where('end_date', '<=', $monthEnd);
                            });
                        })
                        ->sum('total_leave_days');
                    
                    $totalUsed += $usedDays;
                }
            }
        }
        
        $totalLeaves = $totalCredited;
        $remainingLeaves = $totalCredited - $totalUsed;
        
        return [
            'total_leaves' => $totalLeaves,
            'credited_leaves' => $totalCredited,
            'used_leaves' => $totalUsed,
            'remaining_leaves' => max(0, $remainingLeaves)
        ];
    }
}
