<?php
$methods = <<<'EOD'

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

                        if (!isset($employeeData[$dateFormatted])) {
                            if ($weekOffDay && $dayName === $weekOffDay) {
                                $employeeData[$dateFormatted] = ['type' => 'week_off'];
                            } elseif (!$weekOffDay && in_array($dayName, ['saturday', 'sunday'])) {
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
                        $elTotalUsed = $leaveBalances['earned leave']['used_days'];
                        $elTotalRemaining = $leaveBalances['earned leave']['remaining_days'];
                    }

                    if (isset($leaveBalances['sick leave'])) {
                        $slTotalEarned = $leaveBalances['sick leave']['total_allocated'];
                        $slTotalUsed = $leaveBalances['sick leave']['used_days'];
                        $slTotalRemaining = $leaveBalances['sick leave']['remaining_days'];
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
EOD;

$file = 'c:\xampp\htdocs\hrm_archivista\app\Http\Controllers\AttendanceEmployeeController.php';
$content = file_get_contents($file);
$content = preg_replace('/}\s*$/', "\n$methods\n}", $content);
file_put_contents($file, $content);

echo "Success";
