<?php
// Enable detailed error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    \Log::info('Starting payslip generation', ['timestamp' => now()]);

    // Initialize with null checks and logging
    $employee = $employee ?? null;
    $payslip = $payslip ?? null;
    
    if (!$employee || !$payslip) {
        $errorMessage = 'Payslip Error: Missing employee or payslip data';
        \Log::error($errorMessage, [
            'employee_exists' => isset($employee),
            'payslip_exists' => isset($payslip),
            'route' => request()->fullUrl()
        ]);
        abort(404, $errorMessage);
    }

    \Log::info('Generating payslip for employee', [
        'employee_id' => $employee->id,
        'payslip_id' => $payslip->id ?? 'N/A',
        'salary_month' => $payslip->salary_month ?? 'N/A'
    ]);

    // Handle logo loading with error logging
    try {
        $logo = \App\Models\Utility::get_file('uploads/logo/');
        $company_logo = Utility::get_company_logo();
        \Log::debug('Logo loaded successfully', ['logo_path' => $logo]);
    } catch (\Exception $e) {
        \Log::error('Logo Loading Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $logo = null;
        $company_logo = null;
    }

    // Date calculations with error handling
    try {
        $totalDays = date('t', strtotime($payslip->salary_month . '-01'));
        if ($totalDays === false) {
            throw new \Exception('Invalid date format for salary month');
        }
        \Log::debug('Calculated total days in month', ['totalDays' => $totalDays]);
    } catch (\Exception $e) {
        \Log::error('Date Calculation Error', [
            'salary_month' => $payslip->salary_month,
            'error' => $e->getMessage()
        ]);
        $totalDays = 30; // Fallback value
    }

    // Initialize counters
    $presentDays = 0;
    $absentDays = 0;
    $leaveDays = 0;
    $casualLeaveDays = 0;
    $unlimitedLeaveDays = 0;

    // Date period setup
    try {
        $startDate = new DateTime($payslip->salary_month . '-01');
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');
        $interval = new DateInterval('P1D');
        $periodEndDate = clone $endDate;
        $periodEndDate->modify('+1 day');
        $period = new DatePeriod($startDate, $interval, $periodEndDate);
        \Log::debug('Date period created successfully');
    } catch (\Exception $e) {
        \Log::error('Date Period Creation Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        abort(500, 'Failed to process date range');
    }

    // Get attendance records with error handling
    try {
        $attendanceRecords = DB::table('attendance_employees')
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();
        \Log::debug('Attendance records retrieved', ['count' => count($attendanceRecords)]);
    } catch (\Exception $e) {
        \Log::error('Attendance Records Query Error', [
            'error' => $e->getMessage(),
            'query' => 'attendance_employees for employee '.$employee->id,
            'trace' => $e->getTraceAsString()
        ]);
        $attendanceRecords = collect();
    }

    // Get approved leaves with error handling
    try {
        $leaves = DB::table('leaves')
            ->join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
            ->where('leaves.employee_id', $employee->id)
            ->where('leaves.status', 'Approved')
            ->whereBetween('leaves.start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orWhereBetween('leaves.end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select('leaves.*', 'leave_types.title as leave_type')
            ->get();
        \Log::debug('Leave records retrieved', ['count' => count($leaves)]);
    } catch (\Exception $e) {
        \Log::error('Leave Records Query Error', [
            'error' => $e->getMessage(),
            'query' => 'leaves for employee '.$employee->id,
            'trace' => $e->getTraceAsString()
        ]);
        $leaves = collect();
    }

    // Calculate attendance, leaves, and deductions
    try {
        $presentDays = 0;
        foreach ($attendanceRecords as $record) {
            if (isset($record->status) && $record->status === 'Half Day') {
                $presentDays += 0.5;
            } else {
                $presentDays += 1.0;
            }
        }
        
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            
            // Check if employee clocked in
            $attended = $attendanceRecords->contains('date', $dateStr);
            
            if (!$attended) {
                $onLeave = false;
                $leaveType = '';
                
                // Check if employee was on leave
                foreach ($leaves as $leave) {
                    try {
                        $leaveStart = new DateTime($leave->start_date);
                        $leaveEnd = new DateTime($leave->end_date);
                        $leavePeriod = new DatePeriod($leaveStart, $interval, $leaveEnd->modify('+1 day'));
                        
                        foreach ($leavePeriod as $leaveDay) {
                            if ($leaveDay->format('Y-m-d') == $dateStr) {
                                $onLeave = true;
                                $leaveType = strtolower($leave->leave_type ?? '');
                                break 2;
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('Leave Date Processing Error', [
                            'leave_id' => $leave->id ?? 'N/A',
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }
                
                if ($onLeave) {
                    if ($leaveType == 'unlimited leave') {
                        $unlimitedLeaveDays++;
                        $absentDays++; // Count unlimited leave as absent
                    } elseif ($leaveType == 'casual leave') {
                        $casualLeaveDays++;
                        $leaveDays++;
                    } elseif (!empty($leaveType)) {
                        $leaveDays++; // Other approved leaves
                    } else {
                        $absentDays++; // No leave, no attendance - pure absent
                    }
                } else {
                    $absentDays++; // No leave, no attendance - pure absent
                }
            }
        }
        
        \Log::info('Attendance calculations completed', [
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'leave_days' => $leaveDays,
            'casual_leave_days' => $casualLeaveDays,
            'unlimited_leave_days' => $unlimitedLeaveDays
        ]);
    } catch (\Exception $e) {
        \Log::error('Attendance Calculation Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        abort(500, 'Failed to calculate attendance');
    }

    $payrollData = \DB::table('payroll_data')->where('employee_id', $employee->id)->first();

    // Calculate salary components with error handling
    try {
        // Ensure all values are properly converted to float
        $grossSalary = is_numeric($payslip->basic_salary) ? (float)$payslip->basic_salary : 0;
        if ($grossSalary <= 0) {
            throw new \Exception('Invalid gross salary amount: ' . $payslip->basic_salary);
        }
        
        // Log the raw values before calculation
        \Log::debug('Salary calculation inputs', [
            'basic_salary_raw' => $payslip->basic_salary,
            'type' => gettype($payslip->basic_salary),
            'loan_raw' => $payslip->loan ?? 'N/A',
            'loan_type' => isset($payslip->loan) ? gettype($payslip->loan) : 'N/A'
        ]);
        
        if ($payrollData) {
            // Calculate salary components based on percentages from payroll_data
            $basicComponent = $grossSalary * ($payrollData->basic / 100);
            $hraComponent = $grossSalary * ($payrollData->hra / 100);
            $medicalComponent = $grossSalary * ($payrollData->medical / 100);
            $conveyanceComponent = $grossSalary * ($payrollData->conveyance / 100);
            $educationAllowance = $grossSalary * ($payrollData->education / 100);
            $executive = $grossSalary * ($payrollData->executive / 100);
        } else {
            // Fallback to hardcoded percentages from gross salary
            $basicComponent = $grossSalary * 0.40;
            $hraComponent = $grossSalary * 0.16;
            $medicalComponent = $grossSalary * 0.06;
            $conveyanceComponent = $grossSalary * 0.04;
            $educationAllowance = $grossSalary * 0.04;
            $executive = $grossSalary * 0.30;
        }

        // Auto-balance remaining amount into Special Allowance to ensure exact match with Set Salary
        $sumOfComponents = $basicComponent + $hraComponent + $medicalComponent + $conveyanceComponent + $educationAllowance + $executive;
        $specialAllowance = $grossSalary - $sumOfComponents;
        
        \Log::debug('Salary components calculated', [
            'gross_salary' => $grossSalary,
            'basic' => $basicComponent,
            'hra' => $hraComponent,
            'medical' => $medicalComponent,
            'conveyance' => $conveyanceComponent,
            'education' => $educationAllowance,
            'executive' => $executive,
            'special_allowance' => $specialAllowance,
            'total_components' => $sumOfComponents + $specialAllowance
        ]);
    } catch (\Exception $e) {
        \Log::error('Salary Component Calculation Error', [
            'error' => $e->getMessage(),
            'basic_salary' => $payslip->basic_salary ?? 'N/A',
            'type' => isset($payslip->basic_salary) ? gettype($payslip->basic_salary) : 'N/A',
            'trace' => $e->getTraceAsString()
        ]);
        abort(500, 'Invalid salary data: ' . $e->getMessage());
    }
    
    // Calculate Days Payable components properly from database
    try {
        $salaryMonth = $payslip->salary_month;
        $startDate = $salaryMonth . '-01';
        $endDate = date('Y-m-t', strtotime($startDate)); // Last day of month
        
        \Log::info('Days Payable calculation started', [
            'salary_month' => $salaryMonth,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        // 1. Present Days is already calculated above in the attendance calculations section
        // No need to query attendance table as it doesn't exist - use existing $presentDays variable
        \Log::info('Using existing Present Days calculation', ['present_days' => $presentDays]);
        
        // 2. Calculate Weekly Off (Saturdays & Sundays) for the month
        try {
            $weeklyOff = 0;
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod($start, $interval, $end);
            
            foreach ($period as $day) {
                $dayOfWeek = (int)$day->format('N'); // 1 = Mon, 7 = Sun
                $dayOfMonth = (int)$day->format('j');
                $isSunday = ($dayOfWeek === 7);
                $isSecondWeek = ($dayOfMonth >= 8 && $dayOfMonth <= 14);
                $isFourthWeek = ($dayOfMonth >= 22 && $dayOfMonth <= 28);
                $isSecondOrFourthSaturday = ($dayOfWeek === 6 && ($isSecondWeek || $isFourthWeek));
                
                if ($isSunday || $isSecondOrFourthSaturday) {
                    $hasAttended = $attendanceRecords->contains('date', $day->format('Y-m-d'));
                    if (!$hasAttended) {
                        $weeklyOff++;
                    }
                }
            }
            
            \Log::info('Weekly Off calculated', ['weekly_off' => $weeklyOff]);
        } catch (\Exception $e) {
            \Log::error('Error calculating Weekly Off', ['error' => $e->getMessage()]);
            $weeklyOff = 0;
        }
        
        // 3. Calculate Total Leaves taken by employee in the month (excluding LWP)
        try {
            $totalAvailedLeavesList = \DB::table('leaves')
                ->join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
                ->where('leaves.employee_id', $employee->id)
                ->where('leaves.status', 'Approved')
                ->where('leaves.start_date', '<=', $endDate)
                ->where('leaves.end_date', '>=', $startDate)
                ->where('leave_types.title', 'NOT LIKE', '%LWP%')
                ->select('leaves.total_leave_days', 'leaves.leave_duration')
                ->get();
            
            $totalAvailed = 0;
            foreach ($totalAvailedLeavesList as $l) {
                if (strtolower(trim($l->leave_duration ?? '')) === 'half_day') {
                    $totalAvailed += 0.5;
                } else {
                    $totalAvailed += (float)$l->total_leave_days;
                }
            }
                
            \Log::info('Total Leaves calculated', ['total_leave' => $totalAvailed]);
        } catch (\Exception $e) {
            \Log::error('Error calculating Total Leaves', ['error' => $e->getMessage()]);
            $totalAvailed = 0;
        }
        
        // 4. Calculate Public Holidays for the month
        try {
            $holidays = \DB::table('holidays')
                ->where('start_date', '<=', $endDate)
                ->where('end_date', '>=', $startDate)
                ->count();
                
            \Log::info('Public Holidays calculated', ['holidays' => $holidays]);
        } catch (\Exception $e) {
            \Log::error('Error calculating Public Holidays', ['error' => $e->getMessage()]);
            $holidays = 0;
        }
        
        // 5. Calculate LWP Days (Leave Without Pay)
        try {
            $lwpLeavesList = \DB::table('leaves')
                ->join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
                ->where('leaves.employee_id', $employee->id)
                ->where('leaves.status', 'Approved')
                ->where('leave_types.title', 'LIKE', '%LWP%')
                ->where('leaves.start_date', '<=', $endDate)
                ->where('leaves.end_date', '>=', $startDate)
                ->select('leaves.total_leave_days', 'leaves.leave_duration')
                ->get();
            
            $lwpDays = 0;
            foreach ($lwpLeavesList as $l) {
                if (strtolower(trim($l->leave_duration ?? '')) === 'half_day') {
                    $lwpDays += 0.5;
                } else {
                    $lwpDays += (float)$l->total_leave_days;
                }
            }
                
            \Log::info('LWP Days calculated', ['lwp_days' => $lwpDays]);
        } catch (\Exception $e) {
            \Log::error('Error calculating LWP Days', ['error' => $e->getMessage()]);
            $lwpDays = 0;
        }
        
        // 6. Calculate Days Payable: Present Days + Weekly Off + Total Leave + PH - LWP
        $calculatedDaysPayable = $presentDays + $weeklyOff + $totalAvailed + $holidays - $lwpDays;
        $calculatedDaysPayable = min((float)$totalDays, $calculatedDaysPayable);
        
        \Log::info('Days Payable final calculation', [
            'present_days' => $presentDays,
            'weekly_off' => $weeklyOff,
            'total_leave' => $totalAvailed,
            'holidays' => $holidays,
            'lwp_days' => $lwpDays,
            'calculation' => "{$presentDays} + {$weeklyOff} + {$totalAvailed} + {$holidays} - {$lwpDays} = {$calculatedDaysPayable}",
            'calculated_days_payable' => $calculatedDaysPayable
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Major error in Days Payable calculation', ['error' => $e->getMessage()]);
        // Fallback values
        $presentDays = 0;
        $weeklyOff = 0;
        $totalAvailed = 0;
        $holidays = 0;
        $lwpDays = 0;
        $calculatedDaysPayable = $totalDays;
    }

    // Calculate salary deductions (MOVED AFTER Days Payable calculation)
    try {
        // NEW LOGIC: Calculate absent days as Month Days - Calculated Days Payable
        $absentDaysNew = max(0.0, (float)$totalDays - $calculatedDaysPayable);
        
        // Per Day Salary is always divided by 30 (as per requirement)
        $perDaySalary = $grossSalary / 30;
        
        // Calculate deduction for absent days using new logic
        $deductionForAbsent = (float)$absentDaysNew * $perDaySalary;
        
        // Keep the existing casual leave calculation
        $deductionForCasualLeave = (float)$casualLeaveDays * $perDaySalary;
        if (isset($payrollData) && $payrollData) {
            $ptDeduction = (float)$payrollData->professional_tax;
        } else {
            $ptDeduction = is_numeric($payslip->professional_tax ?? 200) ? (float)($payslip->professional_tax ?? 200) : 200;
        }
        
        \Log::info('Updated Absent Days Deduction Calculation', [
            'total_days_in_month' => $totalDays,
            'calculated_days_payable' => $calculatedDaysPayable,
            'new_absent_days' => $absentDaysNew,
            'gross_salary' => $grossSalary,
            'per_day_salary' => $perDaySalary,
            'deduction_for_absent' => $deductionForAbsent,
            'formula' => "{$totalDays} - {$calculatedDaysPayable} = {$absentDaysNew} absent days × {$perDaySalary} = {$deductionForAbsent}"
        ]);
        
        \Log::debug('Deductions calculated', [
            'per_day_salary' => $perDaySalary,
            'absent_deduction' => $deductionForAbsent,
            'casual_leave_deduction' => $deductionForCasualLeave,
            'professional_tax' => $ptDeduction,
            'absent_days_type' => gettype($absentDaysNew),
            'casual_leave_days_type' => gettype($casualLeaveDays)
        ]);
    } catch (\Exception $e) {
        \Log::error('Deduction Calculation Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'values' => [
                'grossSalary' => $grossSalary ?? 'N/A',
                'absentDays' => $absentDays ?? 'N/A',
                'casualLeaveDays' => $casualLeaveDays ?? 'N/A'
            ]
        ]);
        abort(500, 'Failed to calculate deductions: ' . $e->getMessage());
    }

    // Loan calculations with error handling
    try {
        $loanDeduction = 0;
        $remainingLoan = 0;

        if (isset($payslip->loan)) {
            // Handle case where loan is stored as JSON array
            if (is_string($payslip->loan) && str_starts_with($payslip->loan, '[')) {
                $loanArray = json_decode($payslip->loan, true);
                $loanDeduction = is_array($loanArray) ? array_sum($loanArray) : 0;
            } else {
                $loanDeduction = is_numeric($payslip->loan) ? max(0, (float)$payslip->loan) : 0;
            }

            if ($loanDeduction > 0) {
                try {
                    $totalLoans = \App\Models\Loan::where('employee_id', $employee->id)
                        ->sum('amount');
                    $remainingLoan = $totalLoans - $loanDeduction;
                } catch (\Exception $e) {
                    $remainingLoan = 0;
                }
            }
        }

        \Log::debug('Loan calculations completed', [
            'loan_deduction' => $loanDeduction,
            'remaining_loan' => $remainingLoan,
            'loan_raw_value' => $payslip->loan ?? 'N/A',
            'loan_raw_type' => isset($payslip->loan) ? gettype($payslip->loan) : 'N/A'
        ]);
    } catch (\Exception $e) {
        \Log::error('Loan Calculation Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'loan_value' => $payslip->loan ?? 'N/A'
        ]);
        $loanDeduction = 0;
        $remainingLoan = 0;
    }

    // Final calculations with strict type checking
    try {
        $loanDeduction = isset($payslip->loan) ? (float)$payslip->loan : 0;
        $extraAllowance = isset($extraAllowance) ? (float)$extraAllowance : 0;

        // Fetch employee allowances for the selected month
        $employeeAllowances = [];
        try {
            $salaryMonth = $payslip->salary_month;
            $allowances = \DB::table('allowances')
                ->where('employee_id', $employee->id)
                ->where('month', $salaryMonth)
                ->get();
            
            $totalAllowances = 0;
            foreach ($allowances as $allowance) {
                $employeeAllowances[] = [
                    'type' => $allowance->allowance_type,
                    'amount' => (float)$allowance->amount
                ];
                $totalAllowances += (float)$allowance->amount;
            }
            
            \Log::info('Employee allowances fetched', [
                'employee_id' => $employee->id,
                'month' => $salaryMonth,
                'allowances_count' => count($employeeAllowances),
                'total_allowances' => $totalAllowances
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching allowances', [
                'error' => $e->getMessage(),
                'employee_id' => $employee->id
            ]);
            $employeeAllowances = [];
            $totalAllowances = 0;
        }

        // Calculate PF and ESI deductions from payroll_data or fallback to defaults
        if (isset($payrollData) && $payrollData) {
            $pfDeduction = (float)$payrollData->pf;
            $esiDeduction = (float)$payrollData->esi;
        } else {
            $pfDeduction = $basicComponent * 0.12; // 12% of basic salary
            $esiDeduction = $basicComponent * 0.0075; // 0.75% of basic salary
        }
        
        // Deductions entered on Manage Deduction (MLWF, TDS, Other)
        $salaryMonth = $payslip->salary_month;
        $mlwfDeduction = \App\Models\Deduction::amountFor($employee->id, 'MLWF', $salaryMonth);
        $otherDeduction = \App\Models\Deduction::amountFor($employee->id, 'Other Deduction', $salaryMonth);
        $tdsDeduction = \App\Models\Deduction::amountFor($employee->id, 'TDS', $salaryMonth);

        // Calculate gross salary as exactly Set Salary + any Extra DB allowances
        $grossSalaryWithExtra = $grossSalary + $totalAllowances;

        $totalDeductions = (float)$ptDeduction + (float)$loanDeduction + (float)$pfDeduction + (float)$esiDeduction + (float)$mlwfDeduction + (float)$otherDeduction + (float)$tdsDeduction;
        $netSalary = (float)$grossSalaryWithExtra - (float)$totalDeductions;
        
        // Save the correctly calculated net salary to the database
        $payslip->net_payble = $netSalary;
        $payslip->save();
        
        \Log::info('Final salary calculations', [
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'type_checks' => [
                'grossSalary' => gettype($grossSalary),
                'deductionForAbsent' => gettype($deductionForAbsent),
                'deductionForCasualLeave' => gettype($deductionForCasualLeave),
                'ptDeduction' => gettype($ptDeduction),
                'loanDeduction' => gettype($loanDeduction),
                'totalDeductions' => gettype($totalDeductions)
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error('Final Calculation Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'values' => [
                'grossSalary' => $grossSalary ?? 'N/A',
                'deductionForAbsent' => $deductionForAbsent ?? 'N/A',
                'deductionForCasualLeave' => $deductionForCasualLeave ?? 'N/A',
                'ptDeduction' => $ptDeduction ?? 'N/A',
                'loanDeduction' => $loanDeduction ?? 'N/A'
            ],
            'types' => [
                'grossSalary' => isset($grossSalary) ? gettype($grossSalary) : 'N/A',
                'deductionForAbsent' => isset($deductionForAbsent) ? gettype($deductionForAbsent) : 'N/A',
                'deductionForCasualLeave' => isset($deductionForCasualLeave) ? gettype($deductionForCasualLeave) : 'N/A',
                'ptDeduction' => isset($ptDeduction) ? gettype($ptDeduction) : 'N/A',
                'loanDeduction' => isset($loanDeduction) ? gettype($loanDeduction) : 'N/A'
            ]
        ]);
        abort(500, 'Failed to calculate final salary: ' . $e->getMessage());
    }

    // Helper function to convert two digits
    function convertTwoDigit($num, $words) {
        if ($num == 0) return '';
        
        if ($num < 21) {
            return $words[$num];
        } else {
            $tens = floor($num / 10) * 10;
            $units = $num % 10;
            $result = $words[$tens];
            if ($units > 0) {
                $result .= ' ' . $words[$units];
            }
            return $result;
        }
    }

    // Number to words conversion with error handling
    function numberToWords($number) {
        try {
            $number = max(0, floatval($number));
            $no = floor($number);
            $point = round(($number - $no) * 100);
            
            \Log::debug('numberToWords input', ['number' => $number, 'no' => $no, 'point' => $point]);

            $words = array(
                '0' => '', '1' => 'One', '2' => 'Two', '3' => 'Three', '4' => 'Four', '5' => 'Five',
                '6' => 'Six', '7' => 'Seven', '8' => 'Eight', '9' => 'Nine', '10' => 'Ten',
                '11' => 'Eleven', '12' => 'Twelve', '13' => 'Thirteen', '14' => 'Fourteen',
                '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen', '18' => 'Eighteen',
                '19' => 'Nineteen', '20' => 'Twenty', '30' => 'Thirty', '40' => 'Forty',
                '50' => 'Fifty', '60' => 'Sixty', '70' => 'Seventy', '80' => 'Eighty', '90' => 'Ninety'
            );
            
            $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
            $result = '';
            
            if ($no > 0) {
                // Handle Crores
                if ($no >= 10000000) {
                    $crores = floor($no / 10000000);
                    $no = $no % 10000000;
                    if ($crores > 0) {
                        $result .= convertTwoDigit($crores, $words) . ' Crore ';
                    }
                }
                
                // Handle Lakhs
                if ($no >= 100000) {
                    $lakhs = floor($no / 100000);
                    $no = $no % 100000;
                    if ($lakhs > 0) {
                        $result .= convertTwoDigit($lakhs, $words) . ' Lakh ';
                    }
                }
                
                // Handle Thousands
                if ($no >= 1000) {
                    $thousands = floor($no / 1000);
                    $no = $no % 1000;
                    if ($thousands > 0) {
                        $result .= convertTwoDigit($thousands, $words) . ' Thousand ';
                    }
                }
                
                // Handle Hundreds
                if ($no >= 100) {
                    $hundreds = floor($no / 100);
                    $no = $no % 100;
                    if ($hundreds > 0) {
                        $result .= convertTwoDigit($hundreds, $words) . ' Hundred ';
                    }
                }
                
                // Handle remaining (less than 100)
                if ($no > 0) {
                    if ($result != '') {
                        $result .= 'and ';
                    }
                    $result .= convertTwoDigit($no, $words);
                }
            }
            
            $points = '';
            if ($point > 0) {
                $points = " and ";
                if ($point < 21) {
                    $points .= ($words[$point] ?? '') . " Paise";
                } else {
                    $tens = floor($point / 10) * 10;
                    $units = $point % 10;
                    $points .= ($words[$tens] ?? '') . " " . ($words[$units] ?? '') . " Paise";
                }
            }
            
            $finalResult = trim($result . " Rupees" . $points) . " Only";
            \Log::debug('numberToWords final result', ['final_result' => $finalResult]);
            
            return $finalResult;
        } catch (\Exception $e) {
            \Log::error('Number to Words Conversion Error', [
                'number' => $number,
                'error' => $e->getMessage()
            ]);
            return 'Amount in words conversion failed';
        }
    }

    $netSalaryInWords = numberToWords($netSalary);
    
    // Calculate PF Number based on branch
    $branchName = strtolower($employee->branch->name ?? '');
    if ($branchName == 'pune') {
        $pfNumber = 'MH/120559/';
        $officeAddress = '201 & 202, Sai Empire, Near Kapil Malhar, Baner Pune Maharashtra 411 045';
    } elseif ($branchName == 'chennai') {
        $pfNumber = '120559/';
        $officeAddress = 'C4-4th Floor, Tower-III #766, Shakthi Tower, Anna Salai Chennai 600 002';
    } else {
        $pfNumber = '-';
        $officeAddress = \Utility::getValByName('company_address') . ', ' . \Utility::getValByName('company_city');
    }
    
    // Calculate holidays for the current month
    try {
        $salaryMonth = $payslip->salary_month . '-01';
        $startDate = date('Y-m-01', strtotime($salaryMonth));
        $endDate = date('Y-m-t', strtotime($salaryMonth));
        
        $holidays = \App\Models\Holiday::where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($query) use ($startDate, $endDate) {
                          $query->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                      });
            })
            ->where('created_by', \Auth::user()->creatorId())
            ->count();
    } catch (\Exception $e) {
        $holidays = 0;
    }
    
    // Calculate Saturdays and Sundays for the current month
    try {
        $weeklyOff = 0;
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            $dayOfWeek = date('w', strtotime($currentDate));
            if ($dayOfWeek == 0 || $dayOfWeek == 6) { // Sunday = 0, Saturday = 6
                $weeklyOff++;
            }
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }
    } catch (\Exception $e) {
        $weeklyOff = 0;
    }
    
    // Calculate LWP (Leave Without Pay) for the current month
    try {
        $lwpDays = \App\Models\Leave::join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
            ->where('leaves.employee_id', $employee->id)
            ->where('leaves.status', 'Approved')
            ->where('leave_types.title', 'LIKE', '%LWP%')
            ->whereMonth('leaves.start_date', date('m', strtotime($salaryMonth)))
            ->whereYear('leaves.start_date', date('Y', strtotime($salaryMonth)))
            ->sum('leaves.total_leave_days');
            
        // Also check for leaves that span across the month
        $lwpDays += \App\Models\Leave::join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
            ->where('leaves.employee_id', $employee->id)
            ->where('leaves.status', 'Approved')
            ->where('leave_types.title', 'LIKE', '%LWP%')
            ->where(function($query) use ($startDate, $endDate) {
                $query->where('leaves.start_date', '<=', $startDate)
                      ->where('leaves.end_date', '>=', $endDate);
            })
            ->sum('leaves.total_leave_days');
    } catch (\Exception $e) {
        $lwpDays = 0;
    }
    
    // Calculate Days Payable components properly from database
    try {
        $salaryMonth = $payslip->salary_month;
        $startDate = $salaryMonth . '-01';
        $endDate = date('Y-m-t', strtotime($startDate)); // Last day of month
        
        \Log::info('Days Payable calculation started', [
            'salary_month' => $salaryMonth,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        // 1. Present Days is already calculated above in the attendance calculations section
        // No need to query attendance table as it doesn't exist - use existing $presentDays variable
        \Log::info('Using existing Present Days calculation', ['present_days' => $presentDays]);
        
        // 2. Calculate Weekly Off (Saturdays & Sundays) for the month
        try {
            $weeklyOff = 0;
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod($start, $interval, $end);
            
            foreach ($period as $day) {
                $dayOfWeek = (int)$day->format('N'); // 1 = Mon, 7 = Sun
                $dayOfMonth = (int)$day->format('j');
                $isSunday = ($dayOfWeek === 7);
                $isSecondWeek = ($dayOfMonth >= 8 && $dayOfMonth <= 14);
                $isFourthWeek = ($dayOfMonth >= 22 && $dayOfMonth <= 28);
                $isSecondOrFourthSaturday = ($dayOfWeek === 6 && ($isSecondWeek || $isFourthWeek));
                
                if ($isSunday || $isSecondOrFourthSaturday) {
                    $hasAttended = $attendanceRecords->contains('date', $day->format('Y-m-d'));
                    if (!$hasAttended) {
                        $weeklyOff++;
                    }
                }
            }
            
            \Log::info('Weekly Off calculated', ['weekly_off' => $weeklyOff]);
        } catch (\Exception $e) {
            \Log::error('Error calculating Weekly Off', ['error' => $e->getMessage()]);
            $weeklyOff = 0;
        }
        
        // 3. Calculate Total Leaves taken by employee in the month (excluding LWP)
        try {
            $totalAvailedLeavesList = \DB::table('leaves')
                ->join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
                ->where('leaves.employee_id', $employee->id)
                ->where('leaves.status', 'Approved')
                ->where('leaves.start_date', '<=', $endDate)
                ->where('leaves.end_date', '>=', $startDate)
                ->where('leave_types.title', 'NOT LIKE', '%LWP%')
                ->select('leaves.total_leave_days', 'leaves.leave_duration')
                ->get();
            
            $totalAvailed = 0;
            foreach ($totalAvailedLeavesList as $l) {
                if (strtolower(trim($l->leave_duration ?? '')) === 'half_day') {
                    $totalAvailed += 0.5;
                } else {
                    $totalAvailed += (float)$l->total_leave_days;
                }
            }
                
            \Log::info('Total Leaves calculated', ['total_leave' => $totalAvailed]);
        } catch (\Exception $e) {
            \Log::error('Error calculating Total Leaves', ['error' => $e->getMessage()]);
            $totalAvailed = 0;
        }
        
        // 4. Calculate Public Holidays for the month
        try {
            $holidays = \DB::table('holidays')
                ->where('start_date', '<=', $endDate)
                ->where('end_date', '>=', $startDate)
                ->count();
                
            \Log::info('Public Holidays calculated', ['holidays' => $holidays]);
        } catch (\Exception $e) {
            \Log::error('Error calculating Public Holidays', ['error' => $e->getMessage()]);
            $holidays = 0;
        }
        
        // 5. Calculate LWP Days (Leave Without Pay)
        try {
            $lwpLeavesList = \DB::table('leaves')
                ->join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
                ->where('leaves.employee_id', $employee->id)
                ->where('leaves.status', 'Approved')
                ->where('leave_types.title', 'LIKE', '%LWP%')
                ->where('leaves.start_date', '<=', $endDate)
                ->where('leaves.end_date', '>=', $startDate)
                ->select('leaves.total_leave_days', 'leaves.leave_duration')
                ->get();
            
            $lwpDays = 0;
            foreach ($lwpLeavesList as $l) {
                if (strtolower(trim($l->leave_duration ?? '')) === 'half_day') {
                    $lwpDays += 0.5;
                } else {
                    $lwpDays += (float)$l->total_leave_days;
                }
            }
                
            \Log::info('LWP Days calculated', ['lwp_days' => $lwpDays]);
        } catch (\Exception $e) {
            \Log::error('Error calculating LWP Days', ['error' => $e->getMessage()]);
            $lwpDays = 0;
        }
        
        // 6. Calculate Days Payable: Present Days + Weekly Off + Total Leave + PH - LWP
        $calculatedDaysPayable = $presentDays + $weeklyOff + $totalAvailed + $holidays - $lwpDays;
        $calculatedDaysPayable = min((float)$totalDays, $calculatedDaysPayable);
        
        \Log::info('Days Payable final calculation', [
            'present_days' => $presentDays,
            'weekly_off' => $weeklyOff,
            'total_leave' => $totalAvailed,
            'holidays' => $holidays,
            'lwp_days' => $lwpDays,
            'calculation' => "{$presentDays} + {$weeklyOff} + {$totalAvailed} + {$holidays} - {$lwpDays} = {$calculatedDaysPayable}",
            'calculated_days_payable' => $calculatedDaysPayable
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Major error in Days Payable calculation', ['error' => $e->getMessage()]);
        // Fallback values
        $presentDays = 0;
        $weeklyOff = 0;
        $totalAvailed = 0;
        $holidays = 0;
        $lwpDays = 0;
        $calculatedDaysPayable = $totalDays;
    }
    
    // Calculate leave details for employee
    try {
        $leaveDetails = [];
        $employeeType = $employee->employee_type;
        
        // Get employee type identifier based on confirmation status (same as LeaveController)
        if ($employeeType === 'Payroll') {
            $employeeTypeIdentifier = $employee->confirm_of_employment ? 'payroll_confirm' : 'payroll_not_confirm';
        } elseif ($employeeType === 'Contract') {
            $employeeTypeIdentifier = $employee->confirm_of_employment ? 'contract_confirm' : 'contract_not_confirm';
        } else {
            $employeeTypeIdentifier = null;
        }
        
        // Debug logging
        \Log::info('Employee Leave Debug', [
            'employee_id' => $employee->id,
            'employee_type' => $employeeType,
            'employee_type_identifier' => $employeeTypeIdentifier,
            'confirm_of_employment' => $employee->confirm_of_employment,
            'employee_name' => $employee->name
        ]);
        
        $leaveTypes = \App\Models\LeaveType::where('created_by', \Auth::user()->creatorId())
            ->where('is_unlimited', 0)
            ->where(function($query) use ($employeeTypeIdentifier) {
                if ($employeeTypeIdentifier) {
                    $query->whereJsonContains('eligible_employee_types', $employeeTypeIdentifier)
                          ->orWhereJsonContains('eligible_employee_types', strtolower($employeeTypeIdentifier))
                          ->orWhereJsonContains('eligible_employee_types', ucfirst(strtolower($employeeTypeIdentifier)))
                          ->orWhereNull('eligible_employee_types'); // Show if no restriction
                } else {
                    $query->orWhereNull('eligible_employee_types'); // Show if no restriction
                }
            })
            ->get();
            
        // Debug leave types found
        \Log::info('Leave Types Found', [
            'count' => $leaveTypes->count(),
            'leave_types' => $leaveTypes->toArray()
        ]);
            
        foreach ($leaveTypes as $leaveType) {
            // Debug each leave type
            \Log::info('Processing Leave Type', [
                'leave_type_id' => $leaveType->id,
                'title' => $leaveType->title,
                'eligible_employee_types' => $leaveType->eligible_employee_types,
                'days' => $leaveType->days
            ]);
            
            $isCompOff = strtolower(trim($leaveType->title)) === 'comp-off';
            
            if ($isCompOff) {
                $compOffs = \DB::table('comp_offs')->where('created_by', $employee->created_by)->get();
                $earnedBefore = 0;
                $earnedCurrent = 0;
                
                foreach ($compOffs as $compOff) {
                    $employeeIds = json_decode($compOff->employee_ids, true) ?? [];
                    if (in_array($employee->id, $employeeIds)) {
                        $dates = json_decode($compOff->dates, true) ?? [];
                        foreach ($dates as $d) {
                            $dMonth = date('Y-m', strtotime($d));
                            if ($dMonth < $salaryMonth) {
                                $earnedBefore++;
                            } elseif ($dMonth == $salaryMonth) {
                                $earnedCurrent++;
                            }
                        }
                    }
                }
                
                $availedBefore = \App\Models\Leave::where('employee_id', $employee->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('status', 'Approved')
                    ->where('start_date', '<', $salaryMonth . '-01')
                    ->sum('total_leave_days');
                
                $openingBalance = max(0, $earnedBefore - $availedBefore);
                $credited = $earnedCurrent;
                
                $availed = \App\Models\Leave::where('employee_id', $employee->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('status', 'Approved')
                    ->where('start_date', '>=', $salaryMonth . '-01')
                    ->where('start_date', '<=', date('Y-m-t', strtotime($salaryMonth . '-01')))
                    ->sum('total_leave_days');
                    
                $closingBalance = ($openingBalance + $credited) - $availed;
            } else {
                // Fetch leave balances from CarryForwardBalance as it contains extra_days added via employee-leave-allocations
                $cfb = \App\Models\CarryForwardBalance::where('employee_id', $employee->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('month', $salaryMonth)
                    ->where('period_type', 'monthly')
                    ->first();
                
                if ($cfb) {
                    $openingBalance = $cfb->carried_forward_days + $cfb->extra_days;
                    $credited = $cfb->allocated_days;
                    $availed = $cfb->used_days;
                    $closingBalance = $cfb->remaining_days;
                } else {
                    // Fallback to default calculation if no balance record exists yet
                    $openingBalance = 0;
                    
                    $credited = $leaveType->days;
                    
                    $leavesForType = \App\Models\Leave::where('employee_id', $employee->id)
                        ->where('leave_type_id', $leaveType->id)
                        ->where('status', 'Approved')
                        ->whereMonth('start_date', date('m', strtotime($salaryMonth)))
                        ->whereYear('start_date', date('Y', strtotime($salaryMonth)))
                        ->get();
                        
                    $availed = 0;
                    foreach ($leavesForType as $l) {
                        if (strtolower(trim($l->leave_duration ?? '')) === 'half_day') {
                            $availed += 0.5;
                        } else {
                            $availed += (float)$l->total_leave_days;
                        }
                    }
                        
                    $closingBalance = ($openingBalance + $credited) - $availed;
                }
            }
            
            if ($credited > 0 || $openingBalance > 0 || $availed > 0) {
                $leaveDetails[] = [
                    'title' => $leaveType->title,
                    'opening_balance' => $openingBalance,
                    'credited' => $credited,
                    'availed' => $availed,
                    'closing_balance' => $closingBalance
                ];
            }
        }
        
        // Debug final results
        \Log::info('Final Leave Details', [
            'leave_details_count' => count($leaveDetails),
            'leave_details' => $leaveDetails
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Leave Calculation Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $leaveDetails = [];
    }
    
    \Log::info('Final salary values for display', [
        'gross_salary_original' => $grossSalary,
        'extra_allowance' => $extraAllowance ?? 0,
        'gross_salary_with_extra' => $grossSalaryWithExtra,
        'total_deductions' => $totalDeductions,
        'net_salary' => $netSalary,
        'net_salary_in_words' => $netSalaryInWords,
        'pf_number' => $pfNumber,
        'branch_name' => $branchName
    ]);
    
    // Calculate Annual Income for the last 12 months
    $annualIncomeData = [];
    $totalAnnualIncome = 0;
    try {
        $payslipMonth = date('m', strtotime($payslip->salary_month));
        $payslipYear = date('Y', strtotime($payslip->salary_month));
        $fyStart = ($payslipMonth >= 4) ? $payslipYear . '-04' : ($payslipYear - 1) . '-04';

        $pastSlips = \App\Models\PaySlip::where('employee_id', $employee->id)
            ->where('salary_month', '>=', $fyStart)
            ->where('salary_month', '<=', $payslip->salary_month)
            ->orderBy('salary_month', 'desc')
            ->get()
            ->reverse();
            
        foreach ($pastSlips as $slip) {
            $amt = (!empty($slip->net_payble) && $slip->net_payble != 0) ? (float)$slip->net_payble : (float)$slip->basic_salary;
            $annualIncomeData[] = [
                'month' => date('F_y', strtotime($slip->salary_month)),
                'amount' => $amt
            ];
            $totalAnnualIncome += $amt;
        }
        
        \Log::info('Annual income calculated', [
            'months_count' => count($annualIncomeData),
            'total_annual_income' => $totalAnnualIncome
        ]);
    } catch (\Exception $e) {
        \Log::error('Annual Income Calculation Error', ['error' => $e->getMessage()]);
    }

    \Log::info('Payslip generation completed successfully');

} catch (\Throwable $th) {
    \Log::error('Payslip Generation Failed', [
        'error' => $th->getMessage(),
        'trace' => $th->getTraceAsString(),
        'employee_id' => $employee->id ?? 'N/A',
        'payslip_id' => $payslip->id ?? 'N/A',
        'request_data' => request()->all()
    ]);
    throw $th; // Re-throw after logging
}
?>

<!-- Code -->

<div class="modal-body">
    <div class="text-md-end mb-2">
        <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="bottom"
            title="<?php echo e(__('Download')); ?>" onclick="saveAsPDF()"><span class="fa fa-download"></span></a>

        <?php if(\Auth::user()->type == 'company' || \Auth::user()->type == 'hr'): ?>
            <a title="Mail Send" href="<?php echo e(route('payslip.send', [$employee->id, $payslip->salary_month])); ?>" 
                class="btn btn-sm btn-warning"><span class="fa fa-paper-plane"></span></a>
        <?php endif; ?>
    </div>
    
    <style>
        @media (min-width: 992px) {
            .modal-dialog {
                max-width: 1100px !important;
            }
        }
        #printableArea {
            width: 100% !important;
        }
        .invoice {
            width: 100% !important;
            margin: 0 auto;
        }
    </style>

    <div class="invoice" id="printableArea">
        <div class="row">
            <div class="col-12">
                <!-- Main Container with Border -->
                <div style="width: 100%; border: 2px solid #000; padding: 0; font-family: Arial, sans-serif; font-size: 11px; line-height: 1.1;">
                    
                    <!-- Header Section -->
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 30%; border-right: 2px solid #000; padding: 4px; text-align: center; vertical-align: middle;">
                                <img style="border: 1px solid black;" src="<?php echo e(asset('storage/uploads/logo/logo.png')); ?>" width="120px" onerror="this.onerror=null; this.src='<?php echo e(url('storage/uploads/logo/logo.svg')); ?>';">
                                <br>
                           
                            </td>
                            <td style="padding: 4px; text-align: center;">
                                <h2 style="margin: 0; font-size: 20px; font-weight: bold;"><?php echo e(\Utility::getValByName('company_name')); ?></h2>
                                <div style="font-size: 14px; margin: 8px 0;">
                                    <strong>Office Address :</strong> <?php echo e($officeAddress); ?>

                                </div>
                                
                            </td>
                        </tr>
                    </table>

                    <!-- Salary Slip Title -->
                    <div style="border-top: 2px solid #000; border-bottom: 1px solid #000; padding: 4px; text-align: center; background-color: #f8f9fa;">
                        <h3 style="margin: 0; font-size: 18px; font-weight: bold;">Salary Slip for <?php echo e(strtoupper(date('F - Y', strtotime($payslip->salary_month)))); ?></h3>
                    </div>

                    
                    <!-- Employee Details Section -->
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                        <tr>
                            <!-- Left Column -->
                            <td style="width: 33.33%; border-right: 2px solid #000; padding: 0; vertical-align: top;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 4px; font-weight: bold;">Employee Name :</td>
                                        <td style="padding: 4px; border-left: 1px solid #000;"><?php echo e(ucwords(strtolower($employee->name))); ?></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 4px; font-weight: bold;">Department:</td>
                                        <td style="padding: 4px; border-left: 1px solid #000;"><?php echo e($employee->department->name ?? 'Assistant Manager - Talent Acquisition'); ?></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 4px; font-weight: bold;">Date of Joining:</td>
                                        <td style="padding: 4px; border-left: 1px solid #000;"><?php echo e(\Auth::user()->dateFormat($employee->company_doj)); ?></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 4px; font-weight: bold;">ESIC Number:</td>
                                        <td style="padding: 4px; border-left: 1px solid #000;"><?php echo e($employee->esic_no ?? 'N/A'); ?></td>
                                    </tr>
                                    
                                </table>
                            </td>
                            
                            <!-- Middle Column -->
                            <td style="width: 33.33%;  padding: 0; vertical-align: top;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 4px; font-weight: bold;">Employee ID :</td>
                                        <td style="padding: 4px; border-left: 1px solid #000;"><?php echo e(\Auth::user()->employeeIdFormat($employee->employee_id)); ?></td>   
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 4px; font-weight: bold;">Designation :</td>
                                        <td style="padding: 4px; border-left: 1px solid #000;"><?php echo e($employee->designation->name ?? 'Assistant Manager - Talent Acquisition'); ?></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 4px; font-weight: bold;">PF Number :</td>
                                        <td style="padding: 4px; border-left: 1px solid #000;"><?php echo e($pfNumber); ?></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 4px; font-weight: bold;">Bank Account Number:</td>
                                        <td style="padding: 4px; border-left: 1px solid #000;"><?php echo e($employee->bank_ac_no ?? 'N/A'); ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <!-- Leave and Attendance Section -->
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 10px; margin-bottom: 10px; border-top: 2px solid #000; border-bottom: 2px solid #000;">
                        <tr>
                            <!-- Leave Information Column -->
                            <td style="width: 50%; border-right: 2px solid #000; padding: 0; vertical-align: top;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr style="background-color: #f8f9fa;">
                                        <th colspan="6" style="padding: 4px; text-align: center; font-size: 14px; font-weight: bold; border-bottom: 1px solid #000;">Leave</th>
                                    </tr>
                                    <tr style="background-color: #f8f9fa;">
                                        <th style="padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: center;">Leave Type</th>
                                        <th style="padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: center;">Op. Bal</th>
                                        <th style="padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: center;">Credited</th>
                                        <th style="padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: center;">Availed</th>
                                        <th style="padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; text-align: center;">Cl. Bal</th>
                                    </tr>
                                    <?php if(!empty($leaveDetails)): ?>
                                        <?php
                                            $totalOpBal = 0;
                                            $totalCredited = 0;
                                            $totalAvailedLeaves = 0;
                                            $totalClBal = 0;
                                        ?>
                                        <?php $__currentLoopData = $leaveDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: center;"><?php echo e($leave['title']); ?></td>
                                            <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: right;"><?php echo e(number_format($leave['opening_balance'], 2)); ?></td>
                                            <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: right;"><?php echo e(number_format($leave['credited'], 2)); ?></td>
                                            <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: right;"><?php echo e(number_format($leave['availed'], 2)); ?></td>
                                            <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; text-align: right;"><?php echo e(number_format($leave['closing_balance'], 2)); ?></td>
                                        </tr>
                                        <?php
                                            $totalOpBal += $leave['opening_balance'];
                                            $totalCredited += $leave['credited'];
                                            $totalAvailedLeaves += $leave['availed'];
                                            $totalClBal += $leave['closing_balance'];
                                        ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <tr style="background-color: #f8f9fa;">
                                            <td style="padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: center;">Total Leave</td>
                                            <td style="padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: right;"><?php echo e(number_format($totalOpBal, 2)); ?></td>
                                            <td style="padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: right;"><?php echo e(number_format($totalCredited, 2)); ?></td>
                                            <td style="padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: right;"><?php echo e(number_format($totalAvailedLeaves, 2)); ?></td>
                                            <td style="padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; text-align: right;"><?php echo e(number_format($totalClBal, 2)); ?></td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; text-align: center;">No leave records found</td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </td>
                            
                            <!-- Days Payable Column -->
                            <td style="width: 50%; padding: 0; vertical-align: top;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr style="background-color: #f8f9fa;">
                                        <th colspan="2" style="padding: 4px; text-align: center; font-size: 14px; font-weight: bold; border-bottom: 1px solid #000;">Days Payable</th>
                                    </tr>
                                    <tr style="background-color: #f8f9fa;">
                                        <th style="padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000;">Particulars</th>
                                        <th style="padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; text-align: right;">Days</th>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Present Days</td>
                                        <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; text-align: right;"><?php echo e($presentDays); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Weekly Off</td>
                                        <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; text-align: right;"><?php echo e(number_format($weeklyOff, 2)); ?></td>
                                    </tr>
                                    <tr >
                                        <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Total Leave</td>
                                        <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; text-align: right;"><?php echo e(number_format($totalAvailed, 2)); ?></td>
                                    </tr>


                                    <tr>
                                        <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000;">PH</td>
                                        <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; text-align: right;"><?php echo e(number_format($holidays, 2)); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000;">LWP</td>
                                        <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; text-align: right;"><?php echo e(number_format(max(0, $totalDays - $calculatedDaysPayable), 2)); ?></td>
                                    </tr>
                                    <tr style="background-color: #f8f9fa;">
                                        <td style="padding: 4px; font-size: 11px; font-weight: bold; border-right: 1px solid #000;">Days Payable</td>
                                        <td style="padding: 4px; font-size: 11px; font-weight: bold; text-align: right;"><?php echo e(number_format($calculatedDaysPayable + max(0, $totalDays - $calculatedDaysPayable), 2)); ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    
                    <?php
                        $earningsCount = 6 + (!empty($employeeAllowances) ? count($employeeAllowances) : 0);
                        $deductionsCount = 4 + ($mlwfDeduction > 0 ? 1 : 0) + ($otherDeduction > 0 ? 1 : 0);
                        $annualIncomeCount = !empty($annualIncomeData) ? count($annualIncomeData) : 1;
                        $maxRows = max($earningsCount, $deductionsCount, $annualIncomeCount);
                    ?>
                    <!-- Earnings, Deductions, and Annual Income Section -->
                    <div style="border-top: 0px solid #000;">
                        <?php
                            // Build Earnings Array
                            $earningsList = [];
                            $earningsList[] = ['name' => 'Basic', 'amount' => $basicComponent];
                            $earningsList[] = ['name' => 'Medical', 'amount' => $medicalComponent];
                            $earningsList[] = ['name' => 'HRA', 'amount' => $hraComponent];
                            $earningsList[] = ['name' => 'CONVEYANCE', 'amount' => $conveyanceComponent];
                            $earningsList[] = ['name' => 'EDUCATION', 'amount' => $educationAllowance];
                            $earningsList[] = ['name' => 'EXECUTIVE', 'amount' => $executive];
                            if(!empty($employeeAllowances)) {
                                foreach($employeeAllowances as $allowance) {
                                    $earningsList[] = ['name' => strtoupper($allowance['type']), 'amount' => $allowance['amount']];
                                }
                            }
                            
                            // Build Deductions Array
                            $deductionsList = [];
                            $deductionsList[] = ['name' => 'ESI', 'amount' => $esiDeduction];
                            $deductionsList[] = ['name' => 'PF', 'amount' => $pfDeduction];
                            $deductionsList[] = ['name' => 'Professional Tax', 'amount' => $ptDeduction];
                            $deductionsList[] = ['name' => 'TDS', 'amount' => $tdsDeduction];
                            if($mlwfDeduction > 0) {
                                $deductionsList[] = ['name' => 'MLWF', 'amount' => $mlwfDeduction];
                            }
                            if($otherDeduction > 0) {
                                $deductionsList[] = ['name' => 'Other Deduction', 'amount' => $otherDeduction];
                            }
                            
                            // Build Annual Income Array
                            $annualIncomeList = [];
                            if(!empty($annualIncomeData)) {
                                foreach($annualIncomeData as $income) {
                                    $annualIncomeList[] = ['name' => $income['month'], 'amount' => $income['amount']];
                                }
                            } else {
                                $annualIncomeList[] = ['name' => 'No income records found', 'amount' => null, 'colspan' => 2];
                            }
                            
                            $maxRowsData = max(count($earningsList), count($deductionsList), count($annualIncomeList));
                        ?>
                        <table style="width: 100%; border-collapse: collapse; border-top: 2px solid #000;">
                            <!-- Headers -->
                            <tr>
                                <th colspan="2" style="width: 33.33%; padding: 4px; font-size: 14px; border-right: 2px solid #000; border-bottom: 1px solid #000; text-align: center; background-color: #f8f9fa;">Earnings</th>
                                <th colspan="2" style="width: 33.33%; padding: 4px; font-size: 14px; border-right: 2px solid #000; border-bottom: 1px solid #000; text-align: center; background-color: #f8f9fa;">Deductions</th>
                                <th colspan="2" style="width: 33.34%; padding: 4px; font-size: 14px; border-bottom: 1px solid #000; text-align: center; background-color: #f8f9fa;">Annual Income</th>
                            </tr>
                            <tr style="background-color: #f8f9fa;">
                                <!-- Earnings Headers -->
                                <th style="width: 20%; padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: left;">Components</th>
                                <th style="width: 13.33%; padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 2px solid #000; text-align: right;">Amount (Rs.)</th>
                                
                                <!-- Deductions Headers -->
                                <th style="width: 20%; padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: left;">Common Deductions</th>
                                <th style="width: 13.33%; padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 2px solid #000; text-align: right;">Amount (Rs.)</th>
                                
                                <!-- Annual Income Headers -->
                                <th style="width: 20%; padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: left;">Month</th>
                                <th style="width: 13.34%; padding: 4px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; text-align: right;">Amount (Rs.)</th>
                            </tr>
                            
                            <!-- Data Rows -->
                            <?php for($i = 0; $i < $maxRowsData; $i++): ?>
                            <tr>
                                <!-- Earnings Data -->
                                <?php if(isset($earningsList[$i])): ?>
                                    <td style="padding: 4px; font-size: 11px; border-right: 1px solid #000; border-bottom: 1px solid #000;"><?php echo e($earningsList[$i]['name']); ?></td>
                                    <td style="padding: 4px; font-size: 11px; border-right: 2px solid #000; border-bottom: 1px solid #000; text-align: right;"><?php echo e(\Auth::user()->priceFormat($earningsList[$i]['amount'])); ?></td>
                                <?php else: ?>
                                    <td style="padding: 4px; font-size: 11px; border-right: 1px solid #000; border-bottom: 0;">&nbsp;</td>
                                    <td style="padding: 4px; font-size: 11px; border-right: 2px solid #000; border-bottom: 0;">&nbsp;</td>
                                <?php endif; ?>
                                
                                <!-- Deductions Data -->
                                <?php if(isset($deductionsList[$i])): ?>
                                    <td style="padding: 4px; font-size: 11px; border-right: 1px solid #000; border-bottom: 1px solid #000;"><?php echo e($deductionsList[$i]['name']); ?></td>
                                    <td style="padding: 4px; font-size: 11px; border-right: 2px solid #000; border-bottom: 1px solid #000; text-align: right;"><?php echo e(\Auth::user()->priceFormat($deductionsList[$i]['amount'])); ?></td>
                                <?php else: ?>
                                    <td style="padding: 4px; font-size: 11px; border-right: 1px solid #000; border-bottom: 0;">&nbsp;</td>
                                    <td style="padding: 4px; font-size: 11px; border-right: 2px solid #000; border-bottom: 0;">&nbsp;</td>
                                <?php endif; ?>
                                
                                <!-- Annual Income Data -->
                                <?php if(isset($annualIncomeList[$i])): ?>
                                    <?php if(isset($annualIncomeList[$i]['colspan'])): ?>
                                        <td colspan="2" style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; text-align: center;"><?php echo e($annualIncomeList[$i]['name']); ?></td>
                                    <?php else: ?>
                                        <td style="padding: 4px; font-size: 11px; border-right: 1px solid #000; border-bottom: 1px solid #000;"><?php echo e($annualIncomeList[$i]['name']); ?></td>
                                        <td style="padding: 4px; font-size: 11px; border-bottom: 1px solid #000; text-align: right;"><?php echo e(\Auth::user()->priceFormat($annualIncomeList[$i]['amount'])); ?></td>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <td style="padding: 4px; font-size: 11px; border-right: 1px solid #000; border-bottom: 0;">&nbsp;</td>
                                    <td style="padding: 4px; font-size: 11px; border-bottom: 0;">&nbsp;</td>
                                <?php endif; ?>
                            </tr>
                            <?php endfor; ?>
                            
                            <!-- Totals Row -->
                            <tr style="background-color: #f8f9fa;">
                                <td style="padding: 4px; font-size: 12px; font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; border-top: 1px solid #000;">Gross Earning (A)</td>
                                <td style="padding: 4px; font-size: 12px; font-weight: bold; text-align: right; border-right: 2px solid #000; border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo e(\Auth::user()->priceFormat($grossSalaryWithExtra)); ?></td>
                                
                                <td style="padding: 4px; font-size: 12px; font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; border-top: 1px solid #000;">Total Deductions (B)</td>
                                <td style="padding: 4px; font-size: 12px; font-weight: bold; text-align: right; border-right: 2px solid #000; border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo e(\Auth::user()->priceFormat($totalDeductions)); ?></td>
                                
                                <td style="padding: 4px; font-size: 12px; font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000; border-top: 1px solid #000;">Total</td>
                                <td style="padding: 4px; font-size: 12px; font-weight: bold; text-align: right; border-bottom: 1px solid #000; border-top: 1px solid #000;"><?php echo e(\Auth::user()->priceFormat($totalAnnualIncome)); ?></td>
                            </tr>
                        </table>
                    </div>


                    
                    <!-- Net Pay Section -->
                    <div style="border-top: 2px solid #000;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr style="background-color: #f8f9fa;">
                                <td style="padding: 2px; font-size: 12px; font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000;">Net Pay (A - B)</td>
                                <td style="padding: 2px; font-size: 12px; font-weight: bold; text-align: left; border-bottom: 1px solid #000;"><?php echo e(\Auth::user()->priceFormat($netSalary)); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 2px; font-size: 11px; font-weight: bold; border-right: 1px solid #000;">Total Pay</td>
                                <td style="padding: 2px; font-size: 11px;"><?php echo e(ucwords($netSalaryInWords)); ?></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Footer Note -->
                    <div style="border-top: 2px solid #000; padding: 3px; text-align: center; font-size: 10px; font-weight: bold;">
                        Note: This is a Computer Generated Slip and does not require signature
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php echo e(asset('js/html2pdf.bundle.min.js')); ?>"></script>
<script>
    function saveAsPDF() {
        var element = document.getElementById('printableArea');
        var opt = {
            margin: 0.1,
            filename: '<?php echo e($employee->name); ?>_<?php echo e($payslip->salary_month); ?>_payslip',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 4,
                dpi: 72,
                letterRendering: true,
                useCORS: true
            },
            jsPDF: {
                unit: 'in',
                format: 'a4',
                orientation: 'landscape'
            },
            pagebreak: { mode: 'avoid-all' }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>

<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/payslip/pdf.blade.php ENDPATH**/ ?>