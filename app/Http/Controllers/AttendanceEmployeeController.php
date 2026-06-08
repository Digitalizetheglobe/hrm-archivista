<?php

namespace App\Http\Controllers;

use App\Imports\AttendanceImport;
use App\Models\AttendanceEmployee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\IpRestrict;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;  // Add this line



class AttendanceEmployeeController extends Controller
{
    /**
     * Calculate attendance status based on company rules
     * Working Hours: 8 hours
     * Late Mark: After 10:10 AM
     * Half Day: Less than 4 hours
     */
    private function calculateAttendanceStatus($clockIn, $clockOut, $date)
    {
        // Company rules
        $workingHours = 8; // 8 hours
        $halfDayHours = 6.5; // Minimum 6.5 hours for full day (less than 6.5 hrs = Half Day)
        $lateMarkTime = '09:50:00'; // Late after 9:50 AM
        
        $status = 'Present';
        $late = '00:00:00';
        $earlyLeaving = '00:00:00';
        $overtime = '00:00:00';
        $isLate = false;
        
        // Calculate late time (after 10:10 AM)
        if (strtotime($date . ' ' . $clockIn) > strtotime($date . ' ' . $lateMarkTime)) {
            $totalLateSeconds = strtotime($date . ' ' . $clockIn) - strtotime($date . ' ' . $lateMarkTime);
            $hours = floor($totalLateSeconds / 3600);
            $mins = floor($totalLateSeconds / 60 % 60);
            $secs = floor($totalLateSeconds % 60);
            $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            $isLate = true;
        }
        
        // Handle missed punch-out - check if it's after midnight
        if ($clockOut == '00:00:00' || empty($clockOut)) {
            // Check if current time is after midnight (12:00 AM)
            $currentDateTime = new DateTime();
            $midnightToday = new DateTime($date . ' 23:59:59'); // End of the attendance day
            
            if ($currentDateTime > $midnightToday) {
                // If after midnight, mark punch out as company end time
                $endTime = Utility::getValByName('company_end_time');
                $clockOut = !empty($endTime) ? $endTime . ':00' : '18:00:00';
                
                // Re-calculate worked hours with this new clockOut
                $workedSeconds = strtotime($clockOut) - strtotime($clockIn);
                $workedHours = $workedSeconds / 3600;
                
                if ($workedHours < $halfDayHours) {
                    $status = 'Half Day';
                } elseif ($workedHours < $workingHours) {
                    $status = 'Early Leaving';
                } else {
                    $status = 'Present';
                }

                // Recalculate early leaving/overtime for this automatic punch-out
                if (strtotime($clockOut) < strtotime($date . ' ' . $endTime)) {
                    $totalEarlyLeavingSeconds = strtotime($date . ' ' . $endTime) - strtotime($clockOut);
                    $hours = floor($totalEarlyLeavingSeconds / 3600);
                    $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
                    $secs = floor($totalEarlyLeavingSeconds % 60);
                    $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                } elseif (strtotime($clockOut) > strtotime($date . ' ' . $endTime)) {
                    $totalOvertimeSeconds = strtotime($clockOut) - strtotime($date . ' ' . $endTime);
                    $hours = floor($totalOvertimeSeconds / 3600);
                    $mins = floor($totalOvertimeSeconds / 60 % 60);
                    $secs = floor($totalOvertimeSeconds % 60);
                    $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                }
            } else {
                $status = 'Half Day'; // Still same day, mark as half day
                $clockOut = date('H:i:s', strtotime($clockIn) + (6.5 * 3600)); // 6.5 hours after punch-in
            }
        } else {
            // Calculate total worked hours
            $workedSeconds = strtotime($date . ' ' . $clockOut) - strtotime($date . ' ' . $clockIn);
            $workedHours = $workedSeconds / 3600;
            
            // Determine status based on worked hours and timing
            if ($workedHours < $halfDayHours) {
                $status = 'Half Day';
            } elseif ($workedHours < $workingHours) {
                $status = 'Early Leaving'; // Less than 8 hours but more than 4 hours
            } else {
                $status = 'Present'; // Full day (8+ hours)
                if ($isLate) {
                    // Keep as Present but mark as late - will show both badges
                }
            }
            
            // Calculate early leaving and overtime
            $endTime = Utility::getValByName('company_end_time');
            if (strtotime($date . ' ' . $clockOut) < strtotime($date . ' ' . $endTime)) {
                $totalEarlyLeavingSeconds = strtotime($date . ' ' . $endTime) - strtotime($date . ' ' . $clockOut);
                $hours = floor($totalEarlyLeavingSeconds / 3600);
                $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } elseif (strtotime($date . ' ' . $clockOut) > strtotime($date . ' ' . $endTime)) {
                $totalOvertimeSeconds = strtotime($date . ' ' . $clockOut) - strtotime($date . ' ' . $endTime);
                $hours = floor($totalOvertimeSeconds / 3600);
                $mins = floor($totalOvertimeSeconds / 60 % 60);
                $secs = floor($totalOvertimeSeconds % 60);
                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            }
        }
        
        return [
            'status' => $status,
            'late' => $late,
            'early_leaving' => $earlyLeaving,
            'overtime' => $overtime,
            'clock_out' => $clockOut,
            'is_late' => $isLate
        ];
    }

    public function siteVisitIndex(Request $request)
    {
        if (\Auth::user()->can('Manage Attendance')) {
            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            if (\Auth::user()->type == 'employee') {
                $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
                $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);
                
                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));
                    $attendanceEmployee->whereMonth('date', $month)->whereYear('date', $year);
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {
                    $attendanceEmployee->whereMonth('date', date('m'))->whereYear('date', date('Y'));
                }
                
                $attendanceEmployee = $attendanceEmployee->whereNotNull('clock_in_2')->where('clock_in_2', '!=', '00:00:00')->orderBy('id', 'desc')->get();
            } else {
                $employee = Employee::select('id')->where('created_by', \Auth::user()->creatorId());
                if (!empty($request->branch)) {
                    $employee->where('branch_id', $request->branch);
                }
                if (!empty($request->department)) {
                    $employee->where('department_id', $request->department);
                }
                $employee = $employee->get()->pluck('id');

                $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee);
                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));
                    $attendanceEmployee->whereMonth('date', $month)->whereYear('date', $year);
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {
                    $attendanceEmployee->whereMonth('date', date('m'))->whereYear('date', date('Y'));
                }
                
                $attendanceEmployee = $attendanceEmployee->whereNotNull('clock_in_2')->where('clock_in_2', '!=', '00:00:00')->orderBy('id', 'desc')->get();
            }

            return view('attendance.site_visit', compact('attendanceEmployee', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Attendance')) {
            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('All', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('All', '');

            $employeeList = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employeeList->prepend('All', '');

            if (\Auth::user()->type == 'employee') {
                $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;

                $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));


                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {
                    $month      = date('m');
                    $year       = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                }
                $attendanceEmployee = $attendanceEmployee->orderBy('id', 'desc')->get();
            } else {
                $employee = Employee::select('id')->where('created_by', \Auth::user()->creatorId());
                if (!empty($request->branch)) {
                    $employee->where('branch_id', $request->branch);
                }

                if (!empty($request->department)) {
                    $employee->where('department_id', $request->department);
                }

                if (!empty($request->employee)) {
                    $employee->where('id', $request->employee);
                }

                $employee = $employee->get()->pluck('id');

                $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employee);
                if ($request->type == 'monthly' && !empty($request->month)) {

                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));

                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                } elseif ($request->type == 'daily' && !empty($request->date)) {
                    $attendanceEmployee->where('date', $request->date);
                } else {

                    $month      = date('m');
                    $year       = date('Y');
                    $start_date = date($year . '-' . $month . '-01');
                    $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
                    // old date
                    // $end_date   = date($year . '-' . $month . '-t');

                    $attendanceEmployee->whereBetween(
                        'date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                }
                $attendanceEmployee = $attendanceEmployee->orderBy('id', 'desc')->get();
            }

            return view('attendance.index', compact('attendanceEmployee', 'branch', 'department', 'employeeList'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function export(Request $request)
    {
        $name = 'attendance_' . date('Y-m-d i:h:s');
        $data = \Excel::download(new \App\Exports\AttendanceExport($request->all()), $name . '.xlsx');

        return $data;
    }

    public function create()
    {
        if (\Auth::user()->can('Create Attendance')) {
            $employees = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '=', "employee")->get()->pluck('name', 'id');

            return view('attendance.create', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    
    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Attendance')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'date' => 'required',
                    'clock_in' => 'required',
                    'clock_out' => 'required',
                    'clock_in_latitude' => 'nullable|string',
                    'clock_in_longitude' => 'nullable|string',
                    'clock_in_location' => 'nullable|string',
                    'clock_out_latitude' => 'nullable|string',
                    'clock_out_longitude' => 'nullable|string',
                    'clock_out_location' => 'nullable|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            // Debug: Log all request data
            \Log::info('Attendance store request data:', $request->all());

            $startTime  = Utility::getValByName('company_start_time');
            $endTime    = Utility::getValByName('company_end_time');
            $attendance = AttendanceEmployee::where('employee_id', '=', $request->employee_id)
                ->where('date', '=', $request->date)
                ->where('clock_out', '=', '00:00:00')
                ->get()
                ->toArray();

            if ($attendance) {
                return redirect()->route('attendanceemployee.index')->with('error', __('Employee Attendance Already Created.'));
            } else {
                // Use new attendance calculation logic
                $clockIn = $request->clock_in . ':00';
                $clockOut = $request->clock_out . ':00';
                
                $attendanceData = $this->calculateAttendanceStatus($clockIn, $clockOut, $request->date);

                $employeeAttendance = new AttendanceEmployee();
                $employeeAttendance->employee_id   = $request->employee_id;
                $employeeAttendance->date          = $request->date;
                $employeeAttendance->status        = $attendanceData['status'];
                $employeeAttendance->clock_in      = $clockIn;
                $employeeAttendance->clock_out     = $attendanceData['clock_out'];
                $employeeAttendance->late          = $attendanceData['late'];
                $employeeAttendance->early_leaving = $attendanceData['early_leaving'];
                $employeeAttendance->overtime      = $attendanceData['overtime'];
                $employeeAttendance->total_rest    = '00:00:00';
                
                // Add location data
                $employeeAttendance->clock_in_latitude   = $request->clock_in_latitude;
                $employeeAttendance->clock_in_longitude  = $request->clock_in_longitude;
                $employeeAttendance->clock_in_location   = $request->clock_in_location;
                $employeeAttendance->clock_out_latitude  = $request->clock_out_latitude;
                $employeeAttendance->clock_out_longitude = $request->clock_out_longitude;
                $employeeAttendance->clock_out_location  = $request->clock_out_location;
                
                // Debug logging
                \Log::info('Attendance location data:', [
                    'clock_in_latitude' => $request->clock_in_latitude,
                    'clock_in_longitude' => $request->clock_in_longitude,
                    'clock_in_location' => $request->clock_in_location,
                    'clock_out_latitude' => $request->clock_out_latitude,
                    'clock_out_longitude' => $request->clock_out_longitude,
                    'clock_out_location' => $request->clock_out_location,
                ]);
                
                $employeeAttendance->created_by    = \Auth::user()->creatorId();
                $employeeAttendance->save();

                // Send Email Notification
                $emailData = [
                    'employee_name' => \Auth::user()->name,
                    'date'          => $request->date,
                    'clock_in'      => $request->clock_in,
                ];

                Mail::to('connect360.software@gmail.com')->send(new AttendanceNotification($emailData));

                return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully created and email sent.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function show(Request $request)
    {
        // return redirect()->back();
        return redirect()->route('attendanceemployee.index');
    }
    public function edit($id)
    {
        if (\Auth::user()->can('Edit Attendance')) {
            $attendanceEmployee = AttendanceEmployee::where('id', $id)->first();
            $employees          = Employee::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('attendance.edit', compact('attendanceEmployee', 'employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function update(Request $request, $id)
    // {
    //     if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') {
    //         $employeeId      = AttendanceEmployee::where('employee_id', $request->employee_id)->first();
    //         $check = AttendanceEmployee::where('employee_id', '=', $request->employee_id)->where('date', $request->date)->first();

    //         $startTime = Utility::getValByName('company_start_time');
    //         $endTime   = Utility::getValByName('company_end_time');

    //         $clockIn = $request->clock_in;
    //         $clockOut = $request->clock_out;

    //         if ($clockIn) {
    //             $status = "present";
    //         } else {
    //             $status = "leave";
    //         }

    //         $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

    //         $hours = floor($totalLateSeconds / 3600);
    //         $mins  = floor($totalLateSeconds / 60 % 60);
    //         $secs  = floor($totalLateSeconds % 60);
    //         $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //         $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
    //         $hours                    = floor($totalEarlyLeavingSeconds / 3600);
    //         $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
    //         $secs                     = floor($totalEarlyLeavingSeconds % 60);
    //         $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //         if (strtotime($clockOut) > strtotime($endTime)) {
    //             //Overtime
    //             $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
    //             $hours                = floor($totalOvertimeSeconds / 3600);
    //             $mins                 = floor($totalOvertimeSeconds / 60 % 60);
    //             $secs                 = floor($totalOvertimeSeconds % 60);
    //             $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    //         } else {
    //             $overtime = '00:00:00';
    //         }
    //         if ($check->date == date('Y-m-d')) {
    //             $check->update([
    //                 'late' => $late,
    //                 'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
    //                 'overtime' => $overtime,
    //                 'clock_in' => $clockIn,
    //                 'clock_out' => $clockOut
    //             ]);

    //             return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
    //         } else {
    //             return redirect()->route('attendanceemployee.index')->with('error', __('You can only update current day attendance'));
    //         }
    //     }

    //     $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
    //     $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();
    //     if (!empty($todayAttendance) && $todayAttendance->clock_out == '00:00:00') {
    //         $startTime = Utility::getValByName('company_start_time');
    //         $endTime   = Utility::getValByName('company_end_time');
    //         if (Auth::user()->type == 'employee') {

    //             $date = date("Y-m-d");
    //             $time = date("H:i:s");

    //             //early Leaving
    //             $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
    //             $hours                    = floor($totalEarlyLeavingSeconds / 3600);
    //             $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
    //             $secs                     = floor($totalEarlyLeavingSeconds % 60);
    //             $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //             if (time() > strtotime($date . $endTime)) {
    //                 //Overtime
    //                 $totalOvertimeSeconds = time() - strtotime($date . $endTime);
    //                 $hours                = floor($totalOvertimeSeconds / 3600);
    //                 $mins                 = floor($totalOvertimeSeconds / 60 % 60);
    //                 $secs                 = floor($totalOvertimeSeconds % 60);
    //                 $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    //             } else {
    //                 $overtime = '00:00:00';
    //             }

    //             $attendanceEmployee                = AttendanceEmployee::find($id);
    //             $attendanceEmployee->clock_out     = $time;
    //             $attendanceEmployee->early_leaving = $earlyLeaving;
    //             $attendanceEmployee->overtime      = $overtime;
    //             $attendanceEmployee->save();

    //             return redirect()->route('dashboard')->with('success', __('Employee successfully clock Out.'));
    //         } else {
    //             $date = date("Y-m-d");
    //             //late
    //             $totalLateSeconds = strtotime($request->clock_in) - strtotime($date . $startTime);

    //             $hours = floor($totalLateSeconds / 3600);
    //             $mins  = floor($totalLateSeconds / 60 % 60);
    //             $secs  = floor($totalLateSeconds % 60);
    //             $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

    //             //early Leaving
    //             $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($request->clock_out);
    //             $hours                    = floor($totalEarlyLeavingSeconds / 3600);
    //             $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
    //             $secs                     = floor($totalEarlyLeavingSeconds % 60);
    //             $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


    //             if (strtotime($request->clock_out) > strtotime($date . $endTime)) {
    //                 //Overtime
    //                 $totalOvertimeSeconds = strtotime($request->clock_out) - strtotime($date . $endTime);
    //                 $hours                = floor($totalOvertimeSeconds / 3600);
    //                 $mins                 = floor($totalOvertimeSeconds / 60 % 60);
    //                 $secs                 = floor($totalOvertimeSeconds % 60);
    //                 $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    //             } else {
    //                 $overtime = '00:00:00';
    //             }
    
    //             $attendanceEmployee                = AttendanceEmployee::find($id);
    //             $attendanceEmployee->employee_id   = $request->employee_id;
    //             $attendanceEmployee->date          = $request->date;
    //             $attendanceEmployee->clock_in      = $request->clock_in;
    //             $attendanceEmployee->clock_out     = $request->clock_out;
    //             $attendanceEmployee->late          = $late;
    //             $attendanceEmployee->early_leaving = $earlyLeaving;
    //             $attendanceEmployee->overtime      = $overtime;
    //             $attendanceEmployee->total_rest    = '00:00:00';

    //             $attendanceEmployee->save();

    //             return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
    //         }
    //     } else {
    //         return redirect()->back()->with('error', __('Employee are not allow multiple time clock in & clock for every day.'));
    //     }
    // }

    public function update(Request $request, $id)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr') {
            $employeeId      = AttendanceEmployee::where('employee_id', $request->employee_id)->first();
            $check = AttendanceEmployee::where('id', '=', $id)->where('employee_id', '=', $request->employee_id)->where('date', $request->date)->first();

            $startTime = Utility::getValByName('company_start_time');
            $endTime   = Utility::getValByName('company_end_time');

            $clockIn = $request->clock_in;
            $clockOut = $request->clock_out;

            // Use new attendance calculation logic
            $attendanceData = $this->calculateAttendanceStatus($clockIn, $clockOut, $request->date);
            
            if ($check->date == date('Y-m-d')) {
                $check->update([
                    'status' => $attendanceData['status'],
                    'late' => $attendanceData['late'],
                    'early_leaving' => $attendanceData['early_leaving'],
                    'overtime' => $attendanceData['overtime'],
                    'clock_in' => $clockIn,
                    'clock_out' => $attendanceData['clock_out']
                ]);

                return redirect()->route('attendanceemployee.index')->with('success', __('Employee attendance successfully updated.'));
            } else {
                return redirect()->route('attendanceemployee.index')->with('error', __('You can only update current day attendance.'));
            }
        }

        $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();

        $startTime = Utility::getValByName('company_start_time');
        $endTime   = Utility::getValByName('company_end_time');
        if (Auth::user()->type == 'employee') {

            $date = date("Y-m-d");
            $time = date("H:i:s");

            //early Leaving
            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - time();
            $hours                    = floor($totalEarlyLeavingSeconds / 3600);
            $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs                     = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            if (time() > strtotime($date . $endTime)) {
                //Overtime
                $totalOvertimeSeconds = time() - strtotime($date . $endTime);
                $hours                = floor($totalOvertimeSeconds / 3600);
                $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                $secs                 = floor($totalOvertimeSeconds % 60);
                $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            $attendanceEmployee['clock_out']     = $time;
            $attendanceEmployee['early_leaving'] = $earlyLeaving;
            $attendanceEmployee['overtime']      = $overtime;

            if (!empty($request->date)) {
                $attendanceEmployee['date']       =  $request->date;
            }
            AttendanceEmployee::where('id', $id)->update($attendanceEmployee);

            return redirect()->route('dashboard')->with('success', __('Employee successfully clock Out.'));
        } else {
            $date = date("Y-m-d");
            $clockout_time = date("H:i:s");
            //late
            $totalLateSeconds = strtotime($clockout_time) - strtotime($date . $startTime);

            $hours            = abs(floor($totalLateSeconds / 3600));
            $mins             = abs(floor($totalLateSeconds / 60 % 60));
            $secs             = abs(floor($totalLateSeconds % 60));

            $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

            //early Leaving
            $totalEarlyLeavingSeconds = strtotime($date . $endTime) - strtotime($clockout_time);
            $hours                    = floor($totalEarlyLeavingSeconds / 3600);
            $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
            $secs                     = floor($totalEarlyLeavingSeconds % 60);
            $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


            if (strtotime($clockout_time) > strtotime($date . $endTime)) {
                //Overtime
                $totalOvertimeSeconds = strtotime($clockout_time) - strtotime($date . $endTime);
                $hours                = floor($totalOvertimeSeconds / 3600);
                $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                $secs                 = floor($totalOvertimeSeconds % 60);
                $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } else {
                $overtime = '00:00:00';
            }

            $attendanceEmployee                = AttendanceEmployee::find($id);
            $attendanceEmployee->clock_out     = $clockout_time;
            $attendanceEmployee->late          = $late;
            $attendanceEmployee->early_leaving = $earlyLeaving;
            $attendanceEmployee->overtime      = $overtime;
            $attendanceEmployee->total_rest    = '00:00:00';

            $attendanceEmployee->save();

            return redirect()->back()->with('success', __('Employee attendance successfully updated.'));
        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('Delete Attendance')) {
            $attendance = AttendanceEmployee::where('id', $id)->first();

            $attendance->delete();

            return redirect()->route('attendanceemployee.index')->with('success', __('Attendance successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // public function attendance(Request $request)
    // {
    //     $settings = Utility::settings();

    //     if ($settings['ip_restrict'] == 'on') {
    //         $userIp = request()->ip();
    //         $ip     = IpRestrict::where('created_by', \Auth::user()->creatorId())->whereIn('ip', [$userIp])->first();
    //         if (!empty($ip)) {
    //             return redirect()->back()->with('error', __('this ip is not allowed to clock in & clock out.'));
    //         }
    //     }

    //     $employeeId      = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
    //     $todayAttendance = AttendanceEmployee::where('employee_id', '=', $employeeId)->where('date', date('Y-m-d'))->first();
    //     if (empty($todayAttendance)) {

    //         $startTime = Utility::getValByName('company_start_time');
    //         $endTime   = Utility::getValByName('company_end_time');

    //         $attendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', $employeeId)->where('clock_out', '=', '00:00:00')->first();

    //         if ($attendance != null) {
    //             $attendance            = AttendanceEmployee::find($attendance->id);
    //             $attendance->clock_out = $endTime;
    //             $attendance->save();
    //         }

    //         $date = date("Y-m-d");
    //         $time = date("H:i:s");

    //         //late
    //         $totalLateSeconds = time() - strtotime($date . $startTime);
    //         $hours            = floor($totalLateSeconds / 3600);
    //         $mins             = floor($totalLateSeconds / 60 % 60);
    //         $secs             = floor($totalLateSeconds % 60);
    //         $late             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);


    //         $checkDb = AttendanceEmployee::where('employee_id', '=', \Auth::user()->id)->get()->toArray();


    //         if (empty($checkDb)) {
    //             $employeeAttendance                = new AttendanceEmployee();
    //             $employeeAttendance->employee_id   = $employeeId;
    //             $employeeAttendance->date          = $date;
    //             $employeeAttendance->status        = 'Present';
    //             $employeeAttendance->clock_in      = $time;
    //             $employeeAttendance->clock_out     = '00:00:00';
    //             $employeeAttendance->late          = $late;
    //             $employeeAttendance->early_leaving = '00:00:00';
    //             $employeeAttendance->overtime      = '00:00:00';
    //             $employeeAttendance->total_rest    = '00:00:00';
    //             $employeeAttendance->created_by    = \Auth::user()->id;

    //             $employeeAttendance->save();

    //             return redirect()->route('dashboard')->with('success', __('Employee Successfully Clock In.'));
    //         }
    //         foreach ($checkDb as $check) {


    //             $employeeAttendance                = new AttendanceEmployee();
    //             $employeeAttendance->employee_id   = $employeeId;
    //             $employeeAttendance->date          = $date;
    //             $employeeAttendance->status        = 'Present';
    //             $employeeAttendance->clock_in      = $time;
    //             $employeeAttendance->clock_out     = '00:00:00';
    //             $employeeAttendance->late          = $late;
    //             $employeeAttendance->early_leaving = '00:00:00';
    //             $employeeAttendance->overtime      = '00:00:00';
    //             $employeeAttendance->total_rest    = '00:00:00';
    //             $employeeAttendance->created_by    = \Auth::user()->id;

    //             $employeeAttendance->save();

    //             return redirect()->route('dashboard')->with('success', __('Employee Successfully Clock In.'));
    //         }
    //     } else {
    //         return redirect()->back()->with('error', __('Employee are not allow multiple time clock in & clock for every day.'));
    //     }
    // }

    
    public function bulkAttendance(Request $request)
    {
        if (\Auth::user()->can('Create Attendance')) {

            $branch = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $branch->prepend('Select Branch', '');

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('Select Department', '');

            $employees = [];
            if (!empty($request->branch) && !empty($request->department)) {
                $employees = Employee::where('created_by', \Auth::user()->creatorId())->where('branch_id', $request->branch)->where('department_id', $request->department)->get();
            }

            return view('attendance.bulk', compact('employees', 'branch', 'department'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bulkAttendanceData(Request $request)
    {
        if (\Auth::user()->can('Create Attendance')) {
            if (!empty($request->branch) && !empty($request->department)) {
                $startTime = Utility::getValByName('company_start_time');
                $endTime   = Utility::getValByName('company_end_time');
                $date      = $request->date;

                $employees = $request->employee_id;
                $atte      = [];
                foreach ($employees as $employee) {
                    $present = 'present-' . $employee;
                    $in      = 'in-' . $employee;
                    $out     = 'out-' . $employee;
                    $atte[]  = $present;
                    if ($request->$present == 'on') {
                        $attendance = AttendanceEmployee::where('employee_id', '=', $employee)->where('date', '=', $request->date)->first();

                        // If an attendance record already exists, skip it completely.
                        if (!empty($attendance)) {
                            continue;
                        }

                        $in  = date("H:i:s", strtotime($request->$in));
                        $out = date("H:i:s", strtotime($request->$out));

                        // Use new attendance calculation logic
                        $attendanceData = $this->calculateAttendanceStatus($in, $out, $request->date);

                        $employeeAttendance              = new AttendanceEmployee();
                        $employeeAttendance->employee_id = $employee;
                        $employeeAttendance->created_by  = \Auth::user()->creatorId();
                        $employeeAttendance->date          = $request->date;
                        $employeeAttendance->status        = $attendanceData['status'];
                        $employeeAttendance->clock_in      = $in;
                        $employeeAttendance->clock_out     = $attendanceData['clock_out'];
                        $employeeAttendance->late          = $attendanceData['late'];
                        $employeeAttendance->early_leaving = $attendanceData['early_leaving'];
                        $employeeAttendance->overtime      = $attendanceData['overtime'];
                        $employeeAttendance->total_rest    = '00:00:00';
                        
                        // Add location data for bulk attendance
                        $clockInLat = $request->input("clock_in_latitude_{$employee}");
                        $clockInLng = $request->input("clock_in_longitude_{$employee}");
                        $clockInLoc = $request->input("clock_in_location_{$employee}");
                        $clockOutLat = $request->input("clock_out_latitude_{$employee}");
                        $clockOutLng = $request->input("clock_out_longitude_{$employee}");
                        $clockOutLoc = $request->input("clock_out_location_{$employee}");
                        
                        $employeeAttendance->clock_in_latitude   = $clockInLat;
                        $employeeAttendance->clock_in_longitude  = $clockInLng;
                        $employeeAttendance->clock_in_location   = $clockInLoc;
                        $employeeAttendance->clock_out_latitude  = $clockOutLat;
                        $employeeAttendance->clock_out_longitude = $clockOutLng;
                        $employeeAttendance->clock_out_location  = $clockOutLoc;
                        
                        // Debug logging
                        \Log::info("Bulk attendance location data for employee {$employee}:", [
                            'clock_in_latitude' => $clockInLat,
                            'clock_in_longitude' => $clockInLng,
                            'clock_in_location' => $clockInLoc,
                            'clock_out_latitude' => $clockOutLat,
                            'clock_out_longitude' => $clockOutLng,
                            'clock_out_location' => $clockOutLoc,
                        ]);
                        
                        $employeeAttendance->save();
                    }
                }

                return redirect()->back()->with('success', __('Employee attendance successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Branch & department field required.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function importFile()
    {
        return view('attendance.import');
    }

    public function import(Request $request)
    {
        $rules = [
            'file' => 'required|mimes:csv,txt,xlsx',
        ];
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $attendance = (new AttendanceImport())->toArray(request()->file('file'))[0];

        $email_data = [];
        foreach ($attendance as $key => $employee) {
            if ($key != 0) {
                echo "<pre>";
                if ($employee != null && Employee::where('email', $employee[0])->where('created_by', \Auth::user()->creatorId())->exists()) {
                    $email = $employee[0];
                } else {
                    $email_data[] = $employee[0];
                }
            }
        }
        $totalattendance = count($attendance) - 1;
        $errorArray    = [];

        $startTime = Utility::getValByName('company_start_time');
        $endTime   = Utility::getValByName('company_end_time');

        if (!empty($attendanceData)) {
            $errorArray[] = $attendanceData;
        } else {
            foreach ($attendance as $key => $value) {
                if ($key != 0) {
                    $employeeData = Employee::where('email', $value[0])->where('created_by', \Auth::user()->creatorId())->first();
                    // $employeeId = 0;
                    if (!empty($employeeData)) {
                        $employeeId = $employeeData->id;


                        $clockIn = $value[2];
                        $clockOut = $value[3];

                        if ($clockIn) {
                            $status = "present";
                        } else {
                            $status = "leave";
                        }

                        $totalLateSeconds = strtotime($clockIn) - strtotime($startTime);

                        $hours = floor($totalLateSeconds / 3600);
                        $mins  = floor($totalLateSeconds / 60 % 60);
                        $secs  = floor($totalLateSeconds % 60);
                        $late  = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                        $totalEarlyLeavingSeconds = strtotime($endTime) - strtotime($clockOut);
                        $hours                    = floor($totalEarlyLeavingSeconds / 3600);
                        $mins                     = floor($totalEarlyLeavingSeconds / 60 % 60);
                        $secs                     = floor($totalEarlyLeavingSeconds % 60);
                        $earlyLeaving             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

                        if (strtotime($clockOut) > strtotime($endTime)) {
                            //Overtime
                            $totalOvertimeSeconds = strtotime($clockOut) - strtotime($endTime);
                            $hours                = floor($totalOvertimeSeconds / 3600);
                            $mins                 = floor($totalOvertimeSeconds / 60 % 60);
                            $secs                 = floor($totalOvertimeSeconds % 60);
                            $overtime             = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        } else {
                            $overtime = '00:00:00';
                        }

                        $check = AttendanceEmployee::where('employee_id', $employeeId)->where('date', $value[1])->first();
                        if ($check) {
                            $check->update([
                                'late' => $late,
                                'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                                'overtime' => $overtime,
                                'clock_in' => $value[2],
                                'clock_out' => $value[3]
                            ]);
                        } else {
                            $time_sheet = AttendanceEmployee::create([
                                'employee_id' => $employeeId,
                                'date' => $value[1],
                                'status' => $status,
                                'late' => $late,
                                'early_leaving' => ($earlyLeaving > 0) ? $earlyLeaving : '00:00:00',
                                'overtime' => $overtime,
                                'clock_in' => $value[2],
                                'clock_out' => $value[3],
                                'created_by' => \Auth::user()->id,
                            ]);
                        }
                    }
                } else {
                    $email_data = implode(' And ', $email_data);
                }
            }
            if (!empty($email_data)) {
                return redirect()->back()->with('status', 'this record is not import. ' . '</br>' . $email_data);
            } else {
                if (empty($errorArray)) {
                    $data['status'] = 'success';
                    $data['msg']    = __('Record successfully imported');
                } else {

                    $data['status'] = 'error';
                    $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalattendance . ' ' . 'record');


                    foreach ($errorArray as $errorData) {
                        $errorRecord[] = implode(',', $errorData->toArray());
                    }

                    \Session::put('errorArray', $errorRecord);
                }

                return redirect()->back()->with($data['status'], $data['msg']);
            }
        }
    }

  

    public function attendance(Request $request)
    {
        $settings = Utility::settings();

        // IP Restriction Check
        if (!empty($settings['ip_restrict']) && $settings['ip_restrict'] == 'on') {
            $userIp = request()->ip();
            $ip = IpRestrict::where('created_by', Auth::user()->creatorId())->whereIn('ip', [$userIp])->first();
            if (empty($ip)) {
                return redirect()->back()->with('error', __('This IP is not allowed to clock in & clock out.'));
            }
        }

        $employeeId = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
        $date = date("Y-m-d");
        $time = date("H:i:s");
        
        $latitude = $request->input('latitude', null);
        $longitude = $request->input('longitude', null);
        $accuracy = $request->input('accuracy', null);

        $siteVisit = \App\Models\SiteVisit::where('employee_id', $employeeId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('status', 'Approved')
            ->first();

        // Check if the user has already punched in today
        $attendance = AttendanceEmployee::where('employee_id', $employeeId)->where('date', $date)->first();

        // ================= PUNCH IN (Normal) =================
        if (!$attendance) {
            // Location is MANDATORY for Punch In
            if (!$latitude || !$longitude || $latitude == '0' || $longitude == '0') {
                return redirect()->back()->with('error', __('Location is required for Punch In. Please enable location services and try again.'));
            }

            $attendance = new AttendanceEmployee();
            $attendance->employee_id = $employeeId;
            $attendance->date = $date;
            $attendance->status = 'Present';
            $attendance->clock_in = $time;
            $attendance->clock_out = '00:00:00';
            $attendance->late = $this->calculateLateMark($time, $date);
            $attendance->early_leaving = '00:00:00';
            $attendance->overtime = '00:00:00';
            $attendance->total_rest = '00:00:00';
            $attendance->created_by = \Auth::user()->id;
            
            // Set location data
            $attendance->clock_in_latitude = $latitude;
            $attendance->clock_in_longitude = $longitude;
            $attendance->clock_in_location = $this->geocodeCoordinates($latitude, $longitude);
            
            $attendance->save();

            return redirect()->back()->with('success', __('Punch In successful.'));
        } 
        
        // ================= SITE VISIT PUNCHES (Nested) =================
        if ($siteVisit) {
            // PUNCH IN (Site Visit)
            if (empty($attendance->clock_in_2) || $attendance->clock_in_2 === '00:00:00') {
                // Location is MANDATORY for Site Visit Punch In
                if (!$latitude || !$longitude || $latitude == '0' || $longitude == '0') {
                    return redirect()->back()->with('error', __('Location is required for Site Visit Punch In. Please enable location services and try again.'));
                }

                $attendance->clock_in_2 = $time;
                $attendance->clock_in_2_latitude = $latitude;
                $attendance->clock_in_2_longitude = $longitude;
                $attendance->clock_in_2_location = $this->geocodeCoordinates($latitude, $longitude);
                $attendance->clock_in_2_location_captured_at = now();
                
                $attendance->save();

                return redirect()->back()->with('success', __('Site Visit Punch In successful.'));
            }

            // PUNCH OUT (Site Visit)
            if (empty($attendance->clock_out_2) || $attendance->clock_out_2 === '00:00:00') {
                // Location is MANDATORY for Site Visit Punch Out
                if (!$latitude || !$longitude || $latitude == '0' || $longitude == '0') {
                    return redirect()->back()->with('error', __('Location is required for Site Visit Punch Out. Please enable location services and try again.'));
                }

                $attendance->clock_out_2 = $time;
                $attendance->clock_out_2_latitude = $latitude;
                $attendance->clock_out_2_longitude = $longitude;
                $attendance->clock_out_2_location = $this->geocodeCoordinates($latitude, $longitude);
                $attendance->clock_out_2_location_captured_at = now();

                $attendance->save();

                return redirect()->back()->with('success', __('Site Visit Punch Out successful.'));
            }
        }

        // ================= PUNCH OUT (Normal) =================
        if ($attendance->clock_out == '00:00:00') {
            // If there's an approved site visit, must complete site visit punches first
            if ($siteVisit && (empty($attendance->clock_out_2) || $attendance->clock_out_2 === '00:00:00')) {
                return redirect()->back()->with('error', __('Please complete your Site Visit punches before normal Punch Out.'));
            }

            // Location is MANDATORY for Punch Out
            if (!$latitude || !$longitude || $latitude == '0' || $longitude == '0') {
                return redirect()->back()->with('error', __('Location is required for Punch Out. Please enable location services and try again.'));
            }

            $attendance->clock_out = $time;
            $attendance->early_leaving = $this->calculateEarlyLeaving($time, $date);
            
            // Calculate total worked hours
            $s1_start = strtotime($attendance->clock_in);
            $s1_end = strtotime($time);
            
            // If site visit exists, we might want to subtract site visit time if it was "outside"
            // But the user said: In at home -> In at site -> Out at site -> Out at home.
            // This means Session 2 is INSIDE Session 1.
            // Total hours is just Session 1.
            $totalSec = max($s1_end - $s1_start, 0);
            
            $attendance->status = ($totalSec >= 6.5 * 3600) ? 'Present' : 'Half Day';
            
            // Overtime (after 8.5 hours)
            $otSec = $totalSec - (8.5 * 3600);
            $attendance->overtime = ($otSec > 0) ? gmdate('H:i:s', $otSec) : '00:00:00';

            // Set location data
            $attendance->clock_out_latitude = $latitude;
            $attendance->clock_out_longitude = $longitude;
            $attendance->clock_out_location = $this->geocodeCoordinates($latitude, $longitude);

            $attendance->save();

            return redirect()->back()->with('success', __('Punch Out successful.'));
        } 

        return redirect()->back()->with('error', __('Attendance already completed for today.'));
    }

    protected function geocodeCoordinates($latitude, $longitude)
    {
        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 2,
                'connect_timeout' => 2,
                'headers' => [
                    'User-Agent' => 'HRM-System/1.0 (attendance)',
                    'Accept' => 'application/json'
                ]
            ]);

            $response = $client->get('https://nominatim.openstreetmap.org/reverse', [
                'query' => [
                    'format' => 'json',
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'zoom' => 18,
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            if (!empty($data['display_name'])) {
                return $data['display_name'];
            }
        } catch (\Throwable $e) {
            \Log::warning('Geocoding failed', ['error' => $e->getMessage()]);
        }
        return 'Lat: ' . number_format($latitude, 5) . ', Lng: ' . number_format($longitude, 5);
    }

    protected function calculateLateMark($clockIn, $date)
    {
        if (empty($clockIn) || $clockIn == '00:00:00') return '00:00:00';
        $clockInTime = \Carbon\Carbon::parse($date . ' ' . $clockIn);
        
        $threshold = \Carbon\Carbon::parse($date . ' 09:50:00');
            
        if ($clockInTime->gt($threshold)) {
            return gmdate('H:i:s', $clockInTime->diffInSeconds($threshold));
        }
        return '00:00:00';
    }

    protected function calculateEarlyLeaving($clockOut, $date)
    {
        if (empty($clockOut) || $clockOut == '00:00:00') return '00:00:00';
        $clockOutTime = \Carbon\Carbon::parse($date . ' ' . $clockOut);
        $threshold = \Carbon\Carbon::parse($date . ' 19:00:00'); // 7:00 PM
        
        if ($clockOutTime->lt($threshold)) {
            return gmdate('H:i:s', $threshold->diffInSeconds($clockOutTime));
        }
        return '00:00:00';
    }

   




    public function calendar(Request $request)
    {
        if (\Auth::user()->can('Manage Attendance')) {
            $employees = [];
            $selectedEmployee = null;
            
            // Get terminated employee IDs
            $terminatedEmployeeIds = \App\Models\Termination::pluck('employee_id')->toArray();
            
            // Exclude terminated employees and non-employee users (Director, Hr) from the list
            $allEmployees = \App\Models\Employee::where('created_by', \Auth::user()->creatorId())
                ->whereNotIn('id', $terminatedEmployeeIds)
                ->whereHas('user', function($query) {
                    $query->where('type', 'employee');
                })
                ->orderBy('employee_id', 'asc')
                ->get();

            // For employee users - automatically select their own record
            if (\Auth::user()->type == 'employee') {
                $selectedEmployee = \App\Models\Employee::where('user_id', \Auth::user()->id)->first();
                if ($selectedEmployee) {
                    $employees = [$selectedEmployee];
                }
            } 
            // For company users - check if employee is selected
            else {
                if ($request->has('employee_id') && $request->employee_id) {
                    $selectedEmployee = \App\Models\Employee::find($request->employee_id);
                    if ($selectedEmployee) {
                        $employees = [$selectedEmployee];
                    }
                }
            }

            // Get current month and year
            $currentMonth = request()->input('month', date('m'));
            $currentYear = request()->input('year', date('Y'));

            $currentDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1);
            $previousMonth = $currentDate->copy()->subMonth();
            $nextMonth = $currentDate->copy()->addMonth();

            $attendanceData = [];
            
            $leaveAllocationService = new \App\services\LeaveAllocationService();

            // Only process data if we have a selected employee
            if ($selectedEmployee) {
                foreach ($employees as $employee) {
                    // Get all attendance records (no month filter)
                    $attendances = \DB::table('attendance_employees')
                        ->where('employee_id', $employee->id)
                        ->get()
                        ->map(function ($item) {
                            $date = \Carbon\Carbon::parse($item->date)->format('Y-m-d');
                            return [
                                'date' => $date,
                                'clock_in' => $item->clock_in,
                                'clock_out' => $item->clock_out,
                                'status' => $item->status,
                                'late' => $item->late,
                                'early_leaving' => $item->early_leaving,
                            ];
                        });

                    // Get all approved leaves (no month filter)
                    $leaves = \App\Models\Leave::where('employee_id', $employee->id)
                        ->where('status', 'Approved')
                        ->with('leaveType')
                        ->get()
                        ->map(function ($item) {
                            return [
                                'start_date' => \Carbon\Carbon::parse($item->start_date)->format('Y-m-d'),
                                'end_date' => \Carbon\Carbon::parse($item->end_date)->format('Y-m-d'),
                                'leave_reason' => $item->leave_reason,
                                'leave_type' => $item->leaveType ? $item->leaveType->title : 'Unknown'
                            ];
                        });

                    $weekOffDay = strtolower($employee->week_off_day ?? ''); // e.g. 'sunday'

                    $employeeData = [];

                    // Mark 'present' or 'single_punch' from attendance records
                    foreach ($attendances as $attendance) {
                        $isSinglePunch = empty($attendance['clock_out']) || 
                                        $attendance['clock_out'] == '00:00:00' || 
                                        $attendance['clock_out'] == null;
                        
                        $isLate = !empty($attendance['late']) && $attendance['late'] !== '00:00:00';
                        $isEarlyLeaving = !empty($attendance['early_leaving']) && $attendance['early_leaving'] !== '00:00:00';

                        $type = $isSinglePunch ? 'single_punch' : 'present';
                        if ($attendance['status'] === 'Half Day') {
                            $type = 'half_day';
                        }

                        $employeeData[$attendance['date']] = [
                            'type' => $type,
                            'clock_in' => $attendance['clock_in'],
                            'clock_out' => $attendance['clock_out'],
                            'is_late' => $isLate,
                            'late_time' => $attendance['late'],
                            'is_early_leaving' => $isEarlyLeaving,
                            'early_leaving_time' => $attendance['early_leaving'],
                            'raw_status' => $attendance['status']
                        ];
                    }

                    // Mark 'leave' days
                    foreach ($leaves as $leave) {
                        $start = \Carbon\Carbon::parse($leave['start_date']);
                        $end = \Carbon\Carbon::parse($leave['end_date']);

                        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                            $formattedDate = $date->format('Y-m-d');

                            $dayOfWeek = (int)$date->format('N'); // 1 to 7
                            $dayOfMonth = (int)$date->format('j'); // 1 to 31
                            $isSecondWeek = ($dayOfMonth >= 8 && $dayOfMonth <= 14);
                            $isFourthWeek = ($dayOfMonth >= 22 && $dayOfMonth <= 28);
                            
                            // Check if it's a week off
                            $isCompanyWeekOff = ($dayOfWeek === 7) || ($dayOfWeek === 6 && ($isSecondWeek || $isFourthWeek));
                            
                            if ($isCompanyWeekOff) {
                                continue; // Skip marking as leave, the week_off logic will catch it
                            }

                            if (!isset($employeeData[$formattedDate])) {
                                $employeeData[$formattedDate] = [
                                    'type' => 'leave',
                                    'reason' => $leave['leave_reason'],
                                    'leave_type' => $leave['leave_type']
                                ];
                            }
                        }
                    }

                    // Get Comp-Off Data for this employee
                    $compOffEarned = LeaveController::getCompOffEarned($employee->id);
                    $compOffBalance = LeaveController::getCompOffBalance($employee->id);
                    $compOffUsed = max(0, $compOffEarned - $compOffBalance);

                    // Fill in 'week_off' and 'absent' for all dates in the calendar view
                    $startRange = $currentDate->copy()->subMonth(); 
                    $endRange = $currentDate->copy()->addMonth(); 
                    
                    for ($date = $startRange->copy(); $date->lte($endRange); $date->addDay()) {
                        $dateFormatted = $date->format('Y-m-d');
                        $dayName = strtolower($date->format('l'));
                        $dayOfWeek = (int)$date->format('N'); // 1 to 7
                        $dayOfMonth = (int)$date->format('j'); // 1 to 31
                        
                        $isSecondWeek = ($dayOfMonth >= 8 && $dayOfMonth <= 14);
                        $isFourthWeek = ($dayOfMonth >= 22 && $dayOfMonth <= 28);

                        if (!isset($employeeData[$dateFormatted])) {
                            // Apply default company week off logic (All Sundays, 2nd & 4th Saturdays)
                            $isCompanyWeekOff = ($dayOfWeek === 7) || ($dayOfWeek === 6 && ($isSecondWeek || $isFourthWeek));
                            
                            if ($weekOffDay && $dayName === $weekOffDay) {
                                $employeeData[$dateFormatted] = ['type' => 'week_off'];
                            } elseif (!$weekOffDay && $isCompanyWeekOff) {
                                $employeeData[$dateFormatted] = ['type' => 'week_off'];
                            } elseif ($date->lte(\Carbon\Carbon::today())) {
                                $employeeData[$dateFormatted] = ['type' => 'absent'];
                            }
                        }
                    }

                    // Sort data by date
                    ksort($employeeData);

                    // Fetch actual leave balances using LeaveAllocationService
                    $leaveBalances = $leaveAllocationService->getCurrentLeaveBalances($employee->id);
                    
                    $elTotalEarned = 0; $elTotalUsed = 0; $elTotalRemaining = 0;
                    $slTotalEarned = 0; $slTotalUsed = 0; $slTotalRemaining = 0;

                    if (isset($leaveBalances['earned leave'])) {
                        $elTotalEarned = $leaveBalances['earned leave']['total_allocated'];
                        $elTotalUsed = $leaveBalances['earned leave']['total_used'];
                        $elTotalRemaining = $leaveBalances['earned leave']['available'];
                    }

                    if (isset($leaveBalances['sick leave'])) {
                        $slTotalEarned = $leaveBalances['sick leave']['total_allocated'];
                        $slTotalUsed = $leaveBalances['sick leave']['total_used'];
                        $slTotalRemaining = $leaveBalances['sick leave']['available'];
                    }

                    $attendanceData[$employee->id] = [
                        'name' => $employee->full_name ?? $employee->name,
                        'week_off' => $weekOffDay,
                        'total_earned_comp_offs' => $compOffEarned,
                        'total_used_comp_offs' => $compOffUsed,
                        'total_remaining_comp_offs' => $compOffBalance,
                        'el_earned' => $elTotalEarned,
                        'el_used' => $elTotalUsed,
                        'el_remaining' => $elTotalRemaining,
                        'sl_earned' => $slTotalEarned,
                        'sl_used' => $slTotalUsed,
                        'sl_remaining' => $slTotalRemaining,
                        'data' => $employeeData
                    ];
                }
            }

            return view('attendance.calendar', [
                'attendanceData' => $attendanceData,
                'currentMonth' => $currentMonth,
                'currentYear' => $currentYear,
                'previousMonth' => $previousMonth->format('m'),
                'previousYear' => $previousMonth->format('Y'),
                'nextMonth' => $nextMonth->format('m'),
                'nextYear' => $nextMonth->format('Y'),
                'allEmployees' => $allEmployees,
                'selectedEmployee' => $selectedEmployee
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function updateCalendarAttendance(Request $request)
    {
        if (\Auth::user()->can('Manage Attendance') && \Auth::user()->type != 'employee') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'employee_id' => 'required',
                    'date' => 'required|date',
                    'status' => 'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()]);
            }

            $employee = \App\Models\Employee::find($request->employee_id);
            if (!$employee) {
                return response()->json(['error' => __('Employee not found.')]);
            }

            $date = $request->date;
            $newStatus = $request->status;
            $in = $request->clock_in ?? '10:00:00';
            $out = $request->clock_out ?? '19:00:00';

            // Check if attempting to set a Leave directly from Calendar (not fully supported without LeaveLedger changes in Archivista)
            // But we can map "Paid Leave", "Sick Leave" back to standard attendance update for now.
            // In Archivista, leaves are managed in the `leaves` table. If someone selects "Present" or "Absent", it modifies attendance.
            
            if (in_array($newStatus, ['Paid Leave', 'Sick Leave', 'Comp-Off'])) {
                 return response()->json(['error' => __('Please apply leaves via the Leave Management module.')]);
            }

            // For Present / Half Day / Absent
            $statusCode = 'Present'; 
            if ($newStatus === 'Half Day') $statusCode = 'Half Day';
            if ($newStatus === 'Absent') $statusCode = 'Absent';

            if ($statusCode === 'Absent') {
                // If marked absent, delete any existing attendance row for that day
                $existing = \App\Models\AttendanceEmployee::where('employee_id', $employee->id)->where('date', $date)->first();
                if ($existing) {
                    $existing->delete();
                }
                return response()->json(['success' => __('Attendance successfully updated.')]);
            } else {
                // Calculate status (this uses Archivista's logic)
                $attendanceData = $this->calculateAttendanceStatus($in . ':00', $out . ':00', $date);

                $existing = \App\Models\AttendanceEmployee::where('employee_id', $employee->id)->where('date', $date)->first();
                if ($existing) {
                    $existing->update([
                        'status' => $statusCode,
                        'clock_in' => $in . ':00',
                        'clock_out' => $out . ':00',
                        'late' => $attendanceData['late'],
                        'early_leaving' => $attendanceData['early_leaving'],
                        'overtime' => $attendanceData['overtime']
                    ]);
                } else {
                    $employeeAttendance = new \App\Models\AttendanceEmployee();
                    $employeeAttendance->employee_id   = $employee->id;
                    $employeeAttendance->date          = $date;
                    $employeeAttendance->status        = $statusCode;
                    $employeeAttendance->clock_in      = $in . ':00';
                    $employeeAttendance->clock_out     = $out . ':00';
                    $employeeAttendance->late          = $attendanceData['late'];
                    $employeeAttendance->early_leaving = $attendanceData['early_leaving'];
                    $employeeAttendance->overtime      = $attendanceData['overtime'];
                    $employeeAttendance->total_rest    = '00:00:00';
                    $employeeAttendance->created_by    = \Auth::user()->creatorId();
                    $employeeAttendance->save();
                }
                return response()->json(['success' => __('Attendance successfully updated.')]);
            }
        }
        return response()->json(['error' => __('Permission denied.')]);
    }
}