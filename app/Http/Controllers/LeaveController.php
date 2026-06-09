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

    public static function getCompOffEarned($employeeId)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return 0;
        }
        $compOffs = \DB::table('comp_offs')->where('created_by', $employee->created_by)->get();
        $earnedDays = 0;
        foreach ($compOffs as $compOff) {
            $employeeIds = json_decode($compOff->employee_ids, true) ?? [];
            if (in_array($employeeId, $employeeIds)) {
                $dates = json_decode($compOff->dates, true) ?? [];
                $earnedDays += count($dates);
            }
        }
        return $earnedDays;
    }

    public static function getCompOffBalance($employeeId)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return 0;
        }

        $earnedDays = self::getCompOffEarned($employeeId);

        $compOffLeaveType = LeaveType::where('title', 'Comp-Off')
            ->where('created_by', $employee->created_by)
            ->first();
            
        if (!$compOffLeaveType) {
            return $earnedDays;
        }

        $usedDays = LocalLeave::where('employee_id', $employeeId)
            ->where('leave_type_id', $compOffLeaveType->id)
            ->whereIn('status', ['Approved', 'Pending'])
            ->sum('total_leave_days');

        return max(0, $earnedDays - $usedDays);
    }

    public static function getOrCreateCompOffLeaveType($creatorId)
    {
        return LeaveType::firstOrCreate(
            [
                'title' => 'Comp-Off',
                'created_by' => $creatorId,
            ],
            [
                'days' => 0,
                'type' => 'yearly',
                'is_unlimited' => false,
                'carry_forward_enabled' => false,
            ]
        );
    }

    /**
     * Get employee type identifier for leave type eligibility checking
     */
    private function getEmployeeTypeIdentifier($employee)
    {
        if ($employee->employee_type === 'Payroll') {
            return $employee->confirm_of_employment ? 'payroll_confirm' : 'payroll_not_confirm';
        } elseif ($employee->employee_type === 'Contract' || $employee->employee_type === 'Consultant') {
            return $employee->confirm_of_employment ? 'contract_confirm' : 'contract_not_confirm';
        }
        
        return null; // Unknown type
    }

    /**
     * Get allocated days for employee based on leave type
     */
    public function getAllocatedDaysForEmployee($employee, $leaveType)
    {
        // Use the centralized logic from LeaveAllocationService
        return $this->leaveAllocationService->getAllocatedDaysForEmployee($employee, $leaveType);
    }

    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Leave')) {
            $month = [
                '01' => 'JAN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR',
                '05' => 'MAY', '06' => 'JUN', '07' => 'JUL', '08' => 'AUG',
                '09' => 'SEP', '10' => 'OCT', '11' => 'NOV', '12' => 'DEC',
            ];
            $currentyear = date("Y");
            $tempyear = intval($currentyear) - 2;
            $year = [];
            for ($i = 0; $i < 10; $i++) {
                $year[$tempyear + $i] = $tempyear + $i;
            }

            $employeeList = Employee::where('created_by', \Auth::user()->creatorId())->pluck('name', 'id');
            $employeeList->prepend('All', '');

            $filterMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
            $filterYear = isset($_GET['year']) ? $_GET['year'] : date('Y');
            $filterEmployee = isset($_GET['employee_id']) ? $_GET['employee_id'] : '';

            if (\Auth::user()->type == 'employee') {
                $user     = \Auth::user();
                $employee = Employee::where('user_id', '=', $user->id)->first();
                $leavesQuery = LocalLeave::where('employee_id', '=', $employee->id);
                
                if ($filterMonth != '--') {
                    $leavesQuery->whereMonth('start_date', $filterMonth);
                }
                if ($filterYear != '--') {
                    $leavesQuery->whereYear('start_date', $filterYear);
                }
                $leaves = $leavesQuery->orderBy('id', 'desc')->get();
                
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
                })->map(function($leaveType) use ($employee) {
                    $leaveType->days = $this->getAllocatedDaysForEmployee($employee, $leaveType);
                    return $leaveType;
                });
                
            } else {
                $leavesQuery = LocalLeave::where('created_by', '=', \Auth::user()->creatorId())->with(['employees', 'leaveType']);
                
                if ($filterMonth != '--') {
                    $leavesQuery->whereMonth('start_date', $filterMonth);
                }
                if ($filterYear != '--') {
                    $leavesQuery->whereYear('start_date', $filterYear);
                }
                if (!empty($filterEmployee)) {
                    $leavesQuery->where('employee_id', $filterEmployee);
                }

                $leaves = $leavesQuery->orderBy('id', 'desc')->get();
                $leaveBalances = []; // For admin, we'll show per employee in the table
                $leaveTypes = collect(); // Empty for admin
                $employee = null; // Initialize employee for admin view
            }

            return view('leave.index', compact('leaves', 'leaveBalances', 'leaveTypes', 'employee', 'month', 'year', 'employeeList'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Leave')) {
            // Ensure Comp-Off leave type exists
            self::getOrCreateCompOffLeaveType(\Auth::user()->creatorId());

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

                    // Dynamic Comp-Off Balance checking
                    $compOffBalance = self::getCompOffBalance($employee->id);
                    $leaveBalances = $this->leaveAllocationService->getCurrentLeaveBalances($employee->id);

                    $isFirstMonth = false;
                    if (!empty($employee->company_doj)) {
                        $doj = \Carbon\Carbon::parse($employee->company_doj);
                        if (\Carbon\Carbon::now()->lt($doj->copy()->addMonth())) {
                            $isFirstMonth = true;
                        }
                    }

                    $leavetypes = $leavetypes->filter(function($leaveType) use ($compOffBalance, $isFirstMonth) {
                        $leaveTypeName = strtolower(trim($leaveType->title));
                        if ($isFirstMonth && $leaveTypeName !== 'lwp') {
                            return false;
                        }
                        if ($leaveTypeName === 'comp-off') {
                            return $compOffBalance > 0;
                        }
                        return true;
                    })->map(function($leaveType) use ($compOffBalance, $employee, $leaveBalances) {
                        $leaveTypeName = strtolower(trim($leaveType->title));
                        if ($leaveTypeName === 'comp-off') {
                            $leaveType->days = $compOffBalance;
                        } else {
                            if (isset($leaveBalances[$leaveTypeName])) {
                                $leaveType->days = $leaveBalances[$leaveTypeName]['total_allocated'];
                            } else {
                                $leaveType->days = $this->getAllocatedDaysForEmployee($employee, $leaveType);
                            }
                        }
                        return $leaveType;
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
                    'leave_duration' => 'required',
                    'leave_reason' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // Get employee and leave type information
            $employee = Employee::find($request->employee_id);
            $leave_type = LeaveType::find($request->leave_type_id);

            // Check if employee is in their first month after joining
            if (\Auth::user()->type == 'employee' && $employee && !empty($employee->company_doj)) {
                $leaveTypeName = strtolower(trim($leave_type->title));
                if ($leaveTypeName !== 'lwp') {
                    $doj = \Carbon\Carbon::parse($employee->company_doj);
                    $oneMonthAfterJoining = $doj->copy()->addMonth();
                    $now = \Carbon\Carbon::now();
                    $leaveStartDate = \Carbon\Carbon::parse($request->start_date);

                    if ($now->lt($oneMonthAfterJoining)) {
                        return redirect()->back()->with('error', __('You cannot apply for leave during your first month of joining (allowed from ' . $oneMonthAfterJoining->format('Y-m-d') . '). Only LWP is allowed.'));
                    }

                    if ($leaveStartDate->lt($oneMonthAfterJoining)) {
                        return redirect()->back()->with('error', __('You cannot take leave during your first month of joining (allowed from ' . $oneMonthAfterJoining->format('Y-m-d') . '). Only LWP is allowed.'));
                    }
                }
            }

            // Validate contract employee leave type restrictions
            if ($employee && ($employee->employee_type === 'Contract' || $employee->employee_type === 'Consultant')) {
                $leaveTypeName = strtolower(trim($leave_type->title));
                if (!$leave_type->is_unlimited && $leaveTypeName !== 'casual leave') {
                    return redirect()->back()->with('error', __('Contract/Consultant employees can only apply for Casual Leave and Unlimited Leaves.'));
                }
            }

            // Calculate total leave days
            if ($request->leave_duration == 'half_day') {
                $total_leave_days = 0.5;
                $request->merge(['end_date' => $request->start_date]); // Force same day for half-day
            } else {
                $total_leave_days = $this->calculateBusinessDays($request->start_date, $request->end_date);
            }

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

            // Skip leave balance check for unlimited leave types
            if (!$leave_type->is_unlimited) {
                if (strtolower(trim($leave_type->title)) === 'comp-off') {
                    $available = self::getCompOffBalance($request->employee_id);
                    if ($total_leave_days > $available) {
                        return redirect()->back()->with('error', __('You are not eligible for leave. Available Comp-Off balance: ' . $available . ' days.'));
                    }
                    if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $available) {
                        return redirect()->back()->with('error', __('Multiple leave entry is pending. Available Comp-Off balance: ' . ($available - $leaves_pending) . ' days.'));
                    }
                } else {
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
                        
                        // Get extra days
                        $extraDays = $carryForwardBalance->extra_days ?? 0;
                        
                        $carryForwardBalance->remaining_days = ($allocatedDays + $carriedForwardDays + $extraDays) - $carryForwardBalance->used_days;
                        $carryForwardBalance->save();
                        
                        // Total available days = allocated + carried forward + extra - used
                        $totalAvailable = ($allocatedDays + $carriedForwardDays + $extraDays) - $leaves_used_monthly;
                        
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
                        $currentYear = date('Y');
                        
                        $currentBalance = \App\Models\CarryForwardBalance::where('employee_id', $request->employee_id)
                            ->where('leave_type_id', $leave_type->id)
                            ->where('month', $currentYear)
                            ->where('period_type', 'yearly')
                            ->first();
                            
                        if (!$currentBalance) {
                            $currentBalance = \App\Models\CarryForwardBalance::where('employee_id', $request->employee_id)
                                ->where('leave_type_id', $leave_type->id)
                                ->where('month', 'like', $currentYear . '%')
                                ->where('extra_days', '>', 0)
                                ->first();
                        }
                        
                        $extraDays = $currentBalance->extra_days ?? 0;
                        $carriedForwardDays = $currentBalance->carried_forward_days ?? 0;
                        
                        $totalAvailable = ($allocatedDays + $carriedForwardDays + $extraDays) - $leaves_used;
                        
                        if ($total_leave_days > $totalAvailable) {
                            return redirect()->back()->with('error', __('You are not eligible for leave. Available: ' . $totalAvailable . ' days for this year.'));
                        }

                        if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $totalAvailable) {
                            return redirect()->back()->with('error', __('Multiple leave entry is pending. Available: ' . ($totalAvailable - $leaves_pending) . ' days for this year.'));
                        }

                        if ($totalAvailable >= $total_leave_days) {
                            // Proceed with leave creation
                        } else {
                            return redirect()->back()->with('error', __('Insufficient leave balance. Available: ' . $totalAvailable . ' days for this year.'));
                        }
                    }
                }
            }

            $leave    = new LocalLeave();
            $leave->employee_id      = $request->employee_id;
            $leave->leave_type_id    = $request->leave_type_id;
            $leave->applied_on       = date('Y-m-d');
            $leave->start_date       = $request->start_date;
            $leave->end_date         = ($request->leave_duration == 'half_day') ? $request->start_date : $request->end_date;
            $leave->total_leave_days = $total_leave_days;
            $leave->leave_duration   = $request->leave_duration;
            $leave->half_day_type    = ($request->leave_duration == 'half_day') ? $request->half_day_type : null;
            $leave->leave_reason     = $request->leave_reason;
            $leave->status           = 'Pending';
            $leave->created_by       = \Auth::user()->creatorId();
            $leave->save();

             // Send notification to company user
             $companyUser = \App\Models\User::find(\Auth::user()->creatorId());
             if ($companyUser) {
                 $leave->load(['employees', 'leaveType']);
                 $companyUser->notify(new \App\Notifications\LeaveNotification($leave, 'created'));
                 
                 // Send email template notification if enabled
                 $setings = Utility::settings();
                 if (isset($setings['new_leave_request']) && $setings['new_leave_request'] == 1) {
                     $duration = $request->start_date;
                     if ($request->leave_duration != 'half_day') {
                         $duration .= ' to ' . $request->end_date;
                     } else {
                         $duration .= ' (' . __('Half Day') . ')';
                     }
                     $uArr = [
                         'employee_name' => $employee->name,
                         'leave_type' => $leave_type->title,
                         'leave_start_end_time' => $duration,
                         'leave_reason' => $request->leave_reason,
                     ];
                     Utility::sendEmailTemplate('new_leave_request', [$companyUser->email], $uArr);
                 }
             }

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
                        
                        // Dynamic Comp-Off Balance checking
                        $compOffBalance = self::getCompOffBalance($employee->id);
                        $leaveBalances = $this->leaveAllocationService->getCurrentLeaveBalances($employee->id);

                        $leavetypes = $leavetypes->filter(function($leaveType) use ($employeeTypeIdentifier, $compOffBalance) {
                            if (strtolower(trim($leaveType->title)) === 'comp-off') {
                                return $compOffBalance > 0;
                            }
                            // If no eligible_employee_types set, show to all (backward compatibility)
                            if (!$leaveType->eligible_employee_types || empty($leaveType->eligible_employee_types)) {
                                return true;
                            }
                            
                            // Check if employee's type identifier is in the eligible list
                            return in_array($employeeTypeIdentifier, $leaveType->eligible_employee_types);
                        })->map(function($leaveType) use ($compOffBalance, $employee, $leaveBalances) {
                            $leaveTypeName = strtolower(trim($leaveType->title));
                            if ($leaveTypeName === 'comp-off') {
                                $leaveType->days = $compOffBalance;
                            } else {
                                if (isset($leaveBalances[$leaveTypeName])) {
                                    $leaveType->days = $leaveBalances[$leaveTypeName]['total_allocated'];
                                } else {
                                    $leaveType->days = $this->getAllocatedDaysForEmployee($employee, $leaveType);
                                }
                            }
                            return $leaveType;
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
                        'leave_duration' => 'required',
                        'leave_reason' => 'required',
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
                if ($employee && ($employee->employee_type === 'Contract' || $employee->employee_type === 'Consultant')) {
                    $leaveTypeName = strtolower(trim($leave_type->title));
                    if (!$leave_type->is_unlimited && $leaveTypeName !== 'casual leave') {
                        return redirect()->back()->with('error', __('Contract/Consultant employees can only apply for Casual Leave and Unlimited Leaves.'));
                    }
                }
                
                // Calculate total leave days
                if ($request->leave_duration == 'half_day') {
                    $total_leave_days = 0.5;
                } else {
                    $total_leave_days = $this->calculateBusinessDays($request->start_date, $request->end_date);
                }

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

                if (strtolower(trim($leave_type->title)) === 'comp-off') {
                    $earned = self::getCompOffEarned($request->employee_id);
                    $compOffLeaveType = LeaveType::where('title', 'Comp-Off')
                        ->where('created_by', $employee->created_by)
                        ->first();
                    $usedDaysExcludingCurrent = LocalLeave::where('employee_id', $request->employee_id)
                        ->where('leave_type_id', $compOffLeaveType->id)
                        ->whereIn('status', ['Approved'])
                        ->whereNotIn('id', [$leave->id])
                        ->sum('total_leave_days');
                    
                    $available = $earned - $usedDaysExcludingCurrent;
                    
                    if ($total_leave_days > $available) {
                        return redirect()->back()->with('error', __('You are not eligible for leave. Available Comp-Off balance: ' . $available . ' days.'));
                    }
                    if (!empty($leaves_pending) && $leaves_pending + $total_leave_days > $available) {
                        return redirect()->back()->with('error', __('Multiple leave entry is pending. Available Comp-Off balance: ' . ($available - $leaves_pending) . ' days.'));
                    }

                    $leave->employee_id      = $request->employee_id;
                    $leave->leave_type_id    = $request->leave_type_id;
                    $leave->start_date       = $request->start_date;
                    $leave->end_date         = ($request->leave_duration == 'half_day') ? $request->start_date : $request->end_date;
                    $leave->total_leave_days = $total_leave_days;
                    $leave->leave_duration   = $request->leave_duration;
                    $leave->half_day_type    = ($request->leave_duration == 'half_day') ? $request->half_day_type : null;
                    $leave->leave_reason     = $request->leave_reason;

                    $leave->save();

                    return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
                } else {
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
                        $leave->employee_id      = $request->employee_id;
                        $leave->leave_type_id    = $request->leave_type_id;
                        $leave->start_date       = $request->start_date;
                        $leave->end_date         = ($request->leave_duration == 'half_day') ? $request->start_date : $request->end_date;
                        $leave->total_leave_days = $total_leave_days;
                        $leave->leave_duration   = $request->leave_duration;
                        $leave->half_day_type    = ($request->leave_duration == 'half_day') ? $request->half_day_type : null;
                        $leave->leave_reason     = $request->leave_reason;

                        $leave->save();

                        return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
                    } else {
                        return redirect()->back()->with('error', __('Leave type ' . $leave_type->name . ' is provide maximum ' . $allocatedDays . "  days please make sure your selected days is under " . $allocatedDays . ' days.'));
                    }
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
        if (\Auth::user()->type == 'employee') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        
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

        // Send notification to employee
        $employee = Employee::find($leave->employee_id);
        if ($employee) {
            $employeeUser = \App\Models\User::find($employee->user_id);
            if ($employeeUser) {
                $leave->load(['employees', 'leaveType']);
                $employeeUser->notify(new \App\Notifications\LeaveNotification($leave, $request->status));
            }
        }

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
            $resp = null;
            if ($request->status == 'Approved' && !empty($setings['custom_leave_approve_subject']) && !empty($setings['custom_leave_approve_body'])) {
                $subject = $setings['custom_leave_approve_subject'];
                $body = $setings['custom_leave_approve_body'];

                $leaveTypeName = !empty($leave->leaveType) ? $leave->leaveType->title : '';

                // Replace placeholders in Body
                $body = str_replace('{leave_status_name}', $employee->name, $body);
                $body = str_replace('{leave_status}', $request->status, $body);
                $body = str_replace('{leave_reason}', $leave->leave_reason, $body);
                $body = str_replace('{leave_type}', $leaveTypeName, $body);
                $body = str_replace('{leave_start_date}', $leave->start_date, $body);
                $body = str_replace('{leave_end_date}', $leave->end_date, $body);
                $body = str_replace('{total_days}', $leave->total_leave_days, $body);
                $body = str_replace('{app_name}', env('APP_NAME'), $body);

                // Replace placeholders in Subject
                $subject = str_replace('{leave_status_name}', $employee->name, $subject);
                $subject = str_replace('{leave_status}', $request->status, $subject);
                $subject = str_replace('{leave_reason}', $leave->leave_reason, $subject);
                $subject = str_replace('{leave_type}', $leaveTypeName, $subject);
                $subject = str_replace('{leave_start_date}', $leave->start_date, $subject);
                $subject = str_replace('{leave_end_date}', $leave->end_date, $subject);
                $subject = str_replace('{total_days}', $leave->total_leave_days, $subject);
                $subject = str_replace('{app_name}', env('APP_NAME'), $subject);

                // We can construct a dummy object to pass to CommonEmailTemplate
                $dummyTemplate = new \stdClass();
                $dummyTemplate->subject = $subject;
                $dummyTemplate->content = '<div style="font-size: 14px; font-family: \'Open Sans\', sans-serif; color: #333; line-height: 1.6;">' . nl2br($body) . '</div>';

                try {
                    $mailSettings = Utility::getSMTPDetails(\Auth::user()->creatorId());
                    if ($mailSettings) {
                        \Mail::to([$employee->email])->send(new \App\Mail\CommonEmailTemplate($dummyTemplate, $mailSettings, $employee->email));
                        $resp = ['is_success' => true];
                    } else {
                        $resp = ['is_success' => false, 'error' => 'SMTP details not found'];
                    }
                } catch (\Exception $e) {
                    $resp = ['is_success' => false, 'error' => $e->getMessage()];
                }
            } else {
                $resp = Utility::sendEmailTemplate('leave_status', [$employee->email], $uArr);
            }
            return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }

        return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.'));
    }
    public function saveCustomEmail(Request $request)
    {
        $user = \Auth::user();
        if ($user->type == 'company') {
            $post = [
                'custom_leave_approve_subject' => $request->subject,
                'custom_leave_approve_body' => $request->body,
            ];
            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                        $data,
                        $key,
                        $user->creatorId(),
                    ]
                );
            }
            return redirect()->back()->with('success', __('Custom email template saved successfully.'));
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function getLeaveBalanceForEmployee($employeeId, $leaveTypeId)
    {
        $employee = Employee::find($employeeId);
        $leaveType = LeaveType::find($leaveTypeId);
        
        if (!$employee || !$leaveType) {
            return response()->json(['error' => 'Employee or leave type not found'], 404);
        }

        if (strtolower(trim($leaveType->title)) === 'comp-off') {
            $availableDays = self::getCompOffBalance($employeeId);
            return response()->json([
                'allocated_days' => self::getCompOffEarned($employeeId),
                'available_days' => max(0, $availableDays),
                'is_unlimited' => false,
                'employee_type' => $employee->employee_type,
                'confirm_of_employment' => $employee->confirm_of_employment
            ]);
        }
        $leaveBalances = $this->leaveAllocationService->getCurrentLeaveBalances($employeeId);
        $leaveTypeName = strtolower(trim($leaveType->title));
        
        if (isset($leaveBalances[$leaveTypeName])) {
            $balance = $leaveBalances[$leaveTypeName];
            $allocatedDays = $balance['total_allocated'];
            $availableDays = $balance['available'];
        } else {
            // Fallback
            $allocatedDays = $this->getAllocatedDaysForEmployee($employee, $leaveType);
            
            // Calculate current balance (simplified fallback)
            $currentYear = date('Y');
            $currentMonthNum = date('m');
            $monthStart = $currentYear . '-' . $currentMonthNum . '-01';
            $monthEnd = $currentYear . '-' . $currentMonthNum . '-' . date('t', strtotime($monthStart));
            
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
        
        // Ensure Comp-Off leave type exists
        self::getOrCreateCompOffLeaveType(\Auth::user()->creatorId());

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

            // Dynamic Comp-Off Balance checking
            $compOffBalance = self::getCompOffBalance($employeeId);
            $leaveBalances = $this->leaveAllocationService->getCurrentLeaveBalances($employeeId);

            $leavetypes = $leavetypes->filter(function($leaveType) use ($compOffBalance) {
                if (strtolower(trim($leaveType->title)) === 'comp-off') {
                    return $compOffBalance > 0;
                }
                return true;
            })->map(function($leaveType) use ($compOffBalance, $employee, $leaveBalances) {
                $leaveTypeName = strtolower(trim($leaveType->title));
                if ($leaveTypeName === 'comp-off') {
                    $leaveType->days = $compOffBalance;
                } else {
                    if (isset($leaveBalances[$leaveTypeName])) {
                        $leaveType->days = $leaveBalances[$leaveTypeName]['total_allocated'];
                    } else {
                        $leaveType->days = $this->getAllocatedDaysForEmployee($employee, $leaveType);
                    }
                }
                return $leaveType;
            });
        }
        
        return response()->json($leavetypes->values());
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
     * Calculate leave days between two dates (inclusive of both start and end date).
     * Counts all calendar days including weekends, since employees can take leave on any day.
     *
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    private function calculateBusinessDays($startDate, $endDate)
    {
        $start = new \DateTime($startDate);
        $end   = new \DateTime($endDate);

        $end->add(new \DateInterval('P1D')); // Include end date
        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end);

        $days = 0;
        foreach ($period as $date) {
            $dayOfWeek = (int)$date->format('N'); // 1 (Mon) to 7 (Sun)
            $dayOfMonth = (int)$date->format('j'); // 1 to 31

            // Check if it is the 2nd week of the month (dates 8 to 14)
            $isSecondWeek = ($dayOfMonth >= 8 && $dayOfMonth <= 14);
            
            // Check if it is the 4th week of the month (dates 22 to 28)
            $isFourthWeek = ($dayOfMonth >= 22 && $dayOfMonth <= 28);

            // All Sundays are week-offs
            if ($dayOfWeek === 7) {
                continue;
            }

            // 2nd and 4th Saturdays are week-offs
            if ($dayOfWeek === 6 && ($isSecondWeek || $isFourthWeek)) {
                continue;
            }

            $days++;
        }

        return max(1, $days);
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
            
            // Get search keyword
            $search = $request->get('search');

            // Get all employees for the current company
            $query = Employee::where('created_by', \Auth::user()->creatorId());
            
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }
            
            $employees = $query->get();
            
            // Get all leave types
            $leaveTypes = LeaveType::where('created_by', \Auth::user()->creatorId())->get();
            
            // Categorize employees by type
            $contractConfirmEmployees = $employees->filter(function($employee) {
                return ($employee->employee_type === 'Contract' || $employee->employee_type === 'Consultant') && $employee->confirm_of_employment;
            });
            
            $contractNotConfirmEmployees = $employees->filter(function($employee) {
                return ($employee->employee_type === 'Contract' || $employee->employee_type === 'Consultant') && !$employee->confirm_of_employment;
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
        $extraDays = 0;
        $currentMonth = date('Y-m', strtotime($monthStart));

        if ($leaveType->type == 'monthly' && $leaveType->carry_forward_enabled) {
            $previousMonth = date('Y-m', strtotime($monthStart . ' -1 month'));
            $carriedForwardDays = CarryForwardBalance::calculateCarryForward($employee->id, $leaveType->id, $previousMonth);
        }

        // Get extra days
        $periodToQuery = $leaveType->type === 'yearly' ? 'yearly' : 'monthly';
        $monthToQuery = $leaveType->type === 'yearly' ? date('Y', strtotime($monthStart)) : $currentMonth;
        
        $currentBalance = \App\Models\CarryForwardBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('month', $monthToQuery)
            ->where('period_type', $periodToQuery)
            ->first();
            
        // Fallback for custom allocations that might have been saved as 'monthly' for a 'yearly' leave type
        if (!$currentBalance && $leaveType->type === 'yearly') {
            $currentYear = date('Y', strtotime($monthStart));
            $currentBalance = \App\Models\CarryForwardBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('month', 'like', $currentYear . '%')
                ->where('extra_days', '>', 0)
                ->first();
        }
            
        if ($currentBalance) {
            $extraDays = $currentBalance->extra_days;
        }
        
        if ($leaveType->is_unlimited) {
            $remainingDays = -1; // Unlimited
        } else {
            $remainingDays = ($allocatedDays + $carriedForwardDays + $extraDays) - $usedDays;
        }
        
        return [
            'leave_type' => $leaveType,
            'allocated_days' => $allocatedDays + $extraDays,
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

    public function bulkDelete(Request $request)
    {
        if (\Auth::user()->type == 'employee') {
            return response()->json(['status' => 'error', 'message' => __('Permission denied.')], 403);
        }
        
        if (\Auth::user()->can('Delete Leave')) {
            $ids = $request->ids;
            if (!empty($ids)) {
                $deletedCount = 0;

                foreach ($ids as $id) {
                    $leave = LocalLeave::find($id);
                    if ($leave && $leave->created_by == \Auth::user()->creatorId()) {
                        $leave->delete();
                        $deletedCount++;
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => __($deletedCount . ' Leave(s) successfully deleted.')
                ]);
            }
            return response()->json(['status' => 'error', 'message' => __('Please select at least one item.')]);
        } else {
            return response()->json(['status' => 'error', 'message' => __('Permission denied.')], 403);
        }
    }
}
