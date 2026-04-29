@php
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
        $period = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));
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
        $presentDays = count($attendanceRecords);
        
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
        
        // Calculate salary components based on percentages from gross salary
        $basicComponent = $grossSalary * 0.40; // 40% of gross salary
        $hraComponent = $grossSalary * 0.16;   // 16% of gross salary
        $medicalComponent = $grossSalary * 0.06; // 6% of gross salary
        $conveyanceComponent = $grossSalary * 0.04; // 4% of gross salary
        $educationAllowance = $grossSalary * 0.04; // 4% of gross salary
        $executive = $grossSalary * 0.30; // 30% of gross salary
        
        \Log::debug('Salary components calculated', [
            'gross_salary' => $grossSalary,
            'basic' => $basicComponent,
            'hra' => $hraComponent,
            'medical' => $medicalComponent,
            'conveyance' => $conveyanceComponent,
            'education' => $educationAllowance,
            'executive' => $executive,
            'total_components' => $basicComponent + $hraComponent + $medicalComponent + $conveyanceComponent + $educationAllowance + $executive
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
                // Count Saturdays (6) and Sundays (7)
                if ($day->format('N') == 6 || $day->format('N') == 7) {
                    $weeklyOff++;
                }
            }
            
            \Log::info('Weekly Off calculated', ['weekly_off' => $weeklyOff]);
        } catch (\Exception $e) {
            \Log::error('Error calculating Weekly Off', ['error' => $e->getMessage()]);
            $weeklyOff = 0;
        }
        
        // 3. Calculate Total Leaves taken by employee in the month (excluding LWP)
        try {
            $totalAvailed = \DB::table('leaves')
                ->join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
                ->where('leaves.employee_id', $employee->id)
                ->where('leaves.status', 'Approved')
                ->where('leaves.start_date', '<=', $endDate)
                ->where('leaves.end_date', '>=', $startDate)
                ->where('leave_types.title', 'NOT LIKE', '%LWP%')
                ->sum('leaves.total_leave_days');
                
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
            $lwpDays = \DB::table('leaves')
                ->join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
                ->where('leaves.employee_id', $employee->id)
                ->where('leaves.status', 'Approved')
                ->where('leave_types.title', 'LIKE', '%LWP%')
                ->where('leaves.start_date', '<=', $endDate)
                ->where('leaves.end_date', '>=', $startDate)
                ->sum('leaves.total_leave_days');
                
            \Log::info('LWP Days calculated', ['lwp_days' => $lwpDays]);
        } catch (\Exception $e) {
            \Log::error('Error calculating LWP Days', ['error' => $e->getMessage()]);
            $lwpDays = 0;
        }
        
        // 6. Calculate Days Payable: Present Days + Weekly Off + Total Leave + OT Hrs + PH - LWP
        $otHours = 0; // You can update this later if you have OT calculation
        $calculatedDaysPayable = $presentDays + $weeklyOff + $totalAvailed + $otHours + $holidays - $lwpDays;
        
        \Log::info('Days Payable final calculation', [
            'present_days' => $presentDays,
            'weekly_off' => $weeklyOff,
            'total_leave' => $totalAvailed,
            'ot_hours' => $otHours,
            'holidays' => $holidays,
            'lwp_days' => $lwpDays,
            'calculation' => "{$presentDays} + {$weeklyOff} + {$totalAvailed} + {$otHours} + {$holidays} - {$lwpDays} = {$calculatedDaysPayable}",
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
        $absentDaysNew = $totalDays - $calculatedDaysPayable;
        
        // Per Day Salary is always divided by 30 (as per requirement)
        $perDaySalary = $grossSalary / 30;
        
        // Calculate deduction for absent days using new logic
        $deductionForAbsent = (float)$absentDaysNew * $perDaySalary;
        
        // Keep the existing casual leave calculation
        $deductionForCasualLeave = (float)$casualLeaveDays * $perDaySalary;
        $ptDeduction = is_numeric($payslip->professional_tax ?? 200) ? (float)($payslip->professional_tax ?? 200) : 200;
        
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

        // Calculate PF deduction (12% of basic salary)
        $pfDeduction = $basicComponent * 0.12; // 12% of basic salary
        $esiDeduction = $basicComponent * 0.0075; // 0.75% of basic salary
        
        // Fetch deduction values from database
        $mlwfDeduction = 0;
        $otherDeduction = 0;
        try {
            $salaryMonth = $payslip->salary_month;
            
            // Fetch MLWF deduction
            $mlwfRecord = \DB::table('deductions')
                ->where('employee_id', $employee->id)
                ->where('deduction_type', 'MLWF')
                ->where('month', $salaryMonth)
                ->first();
            
            if ($mlwfRecord) {
                $mlwfDeduction = (float)$mlwfRecord->amount;
            }
            
            // Fetch Other Deduction
            $otherRecord = \DB::table('deductions')
                ->where('employee_id', $employee->id)
                ->where('deduction_type', 'Other Deduction')
                ->where('month', $salaryMonth)
                ->first();
            
            if ($otherRecord) {
                $otherDeduction = (float)$otherRecord->amount;
            }
            
            \Log::info('Deductions fetched from database', [
                'employee_id' => $employee->id,
                'month' => $salaryMonth,
                'mlwf_deduction' => $mlwfDeduction,
                'other_deduction' => $otherDeduction
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching deductions', [
                'error' => $e->getMessage(),
                'employee_id' => $employee->id
            ]);
            $mlwfDeduction = 0;
            $otherDeduction = 0;
        }
         
        // Initialize other deduction variables
        $advanceDeduction = 0; // Advance deduction
        $tdsDeduction = 0; // TDS deduction
        
        // Calculate Monthly TDS for the employee
        try {
            // Get TDS record for employee
            $tdsRecord = \DB::table('tds')
                ->where('employee_id', $employee->id)
                ->first();
            
            if ($tdsRecord) {
                // Get employee allowances and deductions for TDS calculation
                $tdsAllowances = \DB::table('tds_allowances')
                    ->where('employee_id', $employee->id)
                    ->get();
                
                $tdsDeductions = \DB::table('tds_deductions')
                    ->where('employee_id', $employee->id)
                    ->get();
                
                $tdsPayments = \DB::table('tds_payments')
                    ->where('employee_id', $employee->id)
                    ->get();
                
                // Calculate total taxable based on regime type
                $totalTaxable = 0;
                if ($tdsRecord->tds_type == 0) {
                    // Old Regime formula
                    $totalTaxable = ($employee->set_salary * 12) + $tdsAllowances->sum('amount') - (2500 + 50000 + $tdsDeductions->sum('amount'));
                    
                    // Calculate tax based on Old Regime slabs
                    $tax = 0;
                    if ($totalTaxable <= 250000) {
                        $tax = 0;
                    } elseif ($totalTaxable <= 500000) {
                        $tax = ($totalTaxable - 250000) * 0.05;
                    } elseif ($totalTaxable <= 1000000) {
                        $tax = 12500 + (($totalTaxable - 500000) * 0.20);
                    } else {
                        $tax = 112500 + (($totalTaxable - 1000000) * 0.30);
                    }
                } else {
                    // New Regime formula  
                    $totalTaxable = ($employee->set_salary * 12) + $tdsAllowances->sum('amount') - 75000 - $tdsDeductions->sum('amount');
                    
                    // Calculate tax based on NEW REGIME slabs
                    $tax = 0;
                    if ($totalTaxable > 300000 && $totalTaxable <= 600000) {
                        $tax = ($totalTaxable - 300000) * 0.05;
                    } elseif ($totalTaxable > 600000 && $totalTaxable <= 900000) {
                        $tax = 15000 + (($totalTaxable - 600000) * 0.10);
                    } elseif ($totalTaxable > 900000 && $totalTaxable <= 1200000) {
                        $tax = 45000 + (($totalTaxable - 900000) * 0.15);
                    } elseif ($totalTaxable > 1200000 && $totalTaxable <= 1500000) {
                        $tax = 90000 + (($totalTaxable - 1200000) * 0.20);
                    } elseif ($totalTaxable > 1500000) {
                        $tax = 150000 + (($totalTaxable - 1500000) * 0.30);
                    }
                }
                
                // Calculate cess and total tax amount
                $cess = round($tax * 0.04);
                $totalTaxAmount = round($tax + $cess);
                
                // Calculate total paid based on actual paid amounts
                $paidMonths = $tdsPayments->where('is_paid', true)->pluck('month_number')->toArray();
                $totalPaid = 0;
                foreach ($tdsPayments->where('is_paid', true) as $payment) {
                    $totalPaid += round($payment->amount);
                }
                
                $tdsBalance = $totalTaxAmount - $totalPaid;
                $remainingMonths = 12 - count($paidMonths);
                
                // Calculate current month TDS amount
                $currentMonthTds = 0;
                if ($remainingMonths > 0) {
                    $currentMonthTds = round($tdsBalance / $remainingMonths);
                }
                
                $tdsDeduction = $currentMonthTds;
                
                \Log::info('TDS calculation completed', [
                    'employee_id' => $employee->id,
                    'tds_type' => $tdsRecord->tds_type,
                    'total_taxable' => $totalTaxable,
                    'total_tax_amount' => $totalTaxAmount,
                    'monthly_tds' => $tdsDeduction
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error calculating TDS', [
                'error' => $e->getMessage(),
                'employee_id' => $employee->id
            ]);
            $tdsDeduction = 0;
        }

        // Calculate gross salary as sum of all components
        $grossSalaryWithExtra = $basicComponent + $hraComponent + $medicalComponent + $conveyanceComponent + $educationAllowance + $executive + (float)$extraAllowance + $totalAllowances;

        $totalDeductions = (float)$ptDeduction + (float)$loanDeduction + (float)$pfDeduction + (float)$esiDeduction + (float)$mlwfDeduction + (float)$advanceDeduction + (float)$otherDeduction + (float)$tdsDeduction + (float)$deductionForAbsent;
        $netSalary = (float)$grossSalaryWithExtra - (float)$totalDeductions;
        
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
                // Count Saturdays (6) and Sundays (7)
                if ($day->format('N') == 6 || $day->format('N') == 7) {
                    $weeklyOff++;
                }
            }
            
            \Log::info('Weekly Off calculated', ['weekly_off' => $weeklyOff]);
        } catch (\Exception $e) {
            \Log::error('Error calculating Weekly Off', ['error' => $e->getMessage()]);
            $weeklyOff = 0;
        }
        
        // 3. Calculate Total Leaves taken by employee in the month (excluding LWP)
        try {
            $totalAvailed = \DB::table('leaves')
                ->join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
                ->where('leaves.employee_id', $employee->id)
                ->where('leaves.status', 'Approved')
                ->where('leaves.start_date', '<=', $endDate)
                ->where('leaves.end_date', '>=', $startDate)
                ->where('leave_types.title', 'NOT LIKE', '%LWP%')
                ->sum('leaves.total_leave_days');
                
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
            $lwpDays = \DB::table('leaves')
                ->join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
                ->where('leaves.employee_id', $employee->id)
                ->where('leaves.status', 'Approved')
                ->where('leave_types.title', 'LIKE', '%LWP%')
                ->where('leaves.start_date', '<=', $endDate)
                ->where('leaves.end_date', '>=', $startDate)
                ->sum('leaves.total_leave_days');
                
            \Log::info('LWP Days calculated', ['lwp_days' => $lwpDays]);
        } catch (\Exception $e) {
            \Log::error('Error calculating LWP Days', ['error' => $e->getMessage()]);
            $lwpDays = 0;
        }
        
        // 6. Calculate Days Payable: Present Days + Weekly Off + Total Leave + OT Hrs + PH - LWP
        $otHours = 0; // You can update this later if you have OT calculation
        $calculatedDaysPayable = $presentDays + $weeklyOff + $totalAvailed + $otHours + $holidays - $lwpDays;
        
        \Log::info('Days Payable final calculation', [
            'present_days' => $presentDays,
            'weekly_off' => $weeklyOff,
            'total_leave' => $totalAvailed,
            'ot_hours' => $otHours,
            'holidays' => $holidays,
            'lwp_days' => $lwpDays,
            'calculation' => "{$presentDays} + {$weeklyOff} + {$totalAvailed} + {$otHours} + {$holidays} - {$lwpDays} = {$calculatedDaysPayable}",
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
            
            // Get opening balance (previous balance + carried forward)
            $openingBalance = \App\Models\Leave::join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
                ->where('leaves.employee_id', $employee->id)
                ->where('leaves.leave_type_id', $leaveType->id)
                ->where('leaves.status', 'Approved')
                ->whereMonth('leaves.start_date', '<', date('m', strtotime($salaryMonth)))
                ->orWhere(function($query) use ($salaryMonth, $leaveType, $employee) {
                    $query->where('leaves.employee_id', $employee->id)
                          ->where('leaves.leave_type_id', $leaveType->id)
                          ->where('leaves.status', 'Approved')
                          ->whereYear('leaves.start_date', '<', date('Y', strtotime($salaryMonth)));
                })
                ->sum('leaves.total_leave_days');
                
            // Get credited leaves for current month
            $credited = $leaveType->days; // Monthly allocation from leave_types table
            
            // Get availed leaves for current month
            $availed = \App\Models\Leave::join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
                ->where('leaves.employee_id', $employee->id)
                ->where('leaves.leave_type_id', $leaveType->id)
                ->where('leaves.status', 'Approved')
                ->whereMonth('leaves.start_date', date('m', strtotime($salaryMonth)))
                ->whereYear('leaves.start_date', date('Y', strtotime($salaryMonth)))
                ->sum('leaves.total_leave_days');
                
            // Calculate closing balance
            $closingBalance = ($openingBalance + $credited) - $availed;
            
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
@endphp

<div class="modal-body">
    <div class="text-md-end mb-2">
        <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="bottom"
            title="{{ __('Download') }}" onclick="saveAsPDF()"><span class="fa fa-download"></span></a>

        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
            <a title="Mail Send" href="{{ route('payslip.send', [$employee->id, $payslip->salary_month]) }}" 
                class="btn btn-sm btn-warning"><span class="fa fa-paper-plane"></span></a>
        @endif
    </div>
    
    <div class="invoice" id="printableArea">
        <div class="row">
            <div class="col-form-label">
                <!-- Main Container with Border -->
                <div style="border: 3px solid #000; padding: 0; font-family: Arial, sans-serif;">
                    
                    <!-- Header Section -->
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 30%; border-right: 2px solid #000; padding: 15px; text-align: center; vertical-align: middle;">
                                <img style="border: 1px solid black;" src="{{ asset('storage/uploads/logo/logo.webp') }}" width="150px" onerror="this.onerror=null; this.src='{{ url('storage/uploads/logo/logo.svg') }}';">
                                <br>
                           
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <h2 style="margin: 0; font-size: 24px; font-weight: bold;">{{ \Utility::getValByName('company_name') }}</h2>
                                <div style="font-size: 14px; margin: 8px 0;">
                                    <strong>Office Address :</strong> {{ $officeAddress }}
                                </div>
                                
                            </td>
                        </tr>
                    </table>

                    <!-- Salary Slip Title -->
                    <div style="border-top: 2px solid #000; border-bottom: 1px solid #000; padding: 10px; text-align: center; background-color: #f8f9fa;">
                        <h3 style="margin: 0; font-size: 18px; font-weight: bold;">Salary Slip for {{ strtoupper(date('F - Y', strtotime($payslip->salary_month))) }}</h3>
                    </div>

                    
                    <!-- Employee Details Section -->
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                        <tr>
                            <!-- Left Column -->
                            <td style="width: 33.33%; border-right: 2px solid #000; padding: 0; vertical-align: top;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 8px; font-weight: bold;">Employee Name :</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ ucwords(strtolower($employee->name)) }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 8px; font-weight: bold;">Department:</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ $employee->department->name ?? 'Assistant Manager - Talent Acquisition' }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 8px; font-weight: bold;">Date of Joining:</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ \Auth::user()->dateFormat($employee->company_doj) }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 8px; font-weight: bold;">ESIC Number:</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ $employee->esic_no ?? 'N/A' }}</td>
                                    </tr>
                                    
                                </table>
                            </td>
                            
                            <!-- Middle Column -->
                            <td style="width: 33.33%;  padding: 0; vertical-align: top;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 8px; font-weight: bold;">Employee ID :</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</td>   
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 8px; font-weight: bold;">Designation :</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ $employee->designation->name ?? 'Assistant Manager - Talent Acquisition' }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 8px; font-weight: bold;">PF Number :</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ $pfNumber }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #000;">
                                        <td style="padding: 8px; font-weight: bold;">Bank Account Number:</td>
                                        <td style="padding: 8px; border-left: 1px solid #000;">{{ $employee->bank_ac_no ?? 'N/A' }}</td>
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
                                        <th colspan="6" style="padding: 10px; text-align: center; font-size: 14px; font-weight: bold; border-bottom: 1px solid #000;">Leave</th>
                                    </tr>
                                    <tr style="background-color: #f8f9fa;">
                                        <th style="padding: 8px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: center;">Leave Type</th>
                                        <th style="padding: 8px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: center;">Op. Bal</th>
                                        <th style="padding: 8px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: center;">Credited</th>
                                        <th style="padding: 8px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: center;">Availed</th>
                                        <th style="padding: 8px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; text-align: center;">Cl. Bal</th>
                                    </tr>
                                    @if(!empty($leaveDetails))
                                        @php
                                            $totalAvailed = 0;
                                        @endphp
                                        @foreach($leaveDetails as $leave)
                                        <tr>
                                            <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: center;">{{ $leave['title'] }}</td>
                                            <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: right;">{{ number_format($leave['opening_balance'], 2) }}</td>
                                            <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: right;">{{ number_format($leave['credited'], 2) }}</td>
                                            <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000; text-align: right;">{{ number_format($leave['availed'], 2) }}</td>
                                            <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; text-align: right;">{{ number_format($leave['closing_balance'], 2) }}</td>
                                        </tr>
                                        @php
                                            $totalAvailed += $leave['availed'];
                                        @endphp
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; text-align: center;">No leave records found</td>
                                        </tr>
                                    @endif
                                </table>
                            </td>
                            
                            <!-- Days Payable Column -->
                            <td style="width: 50%; padding: 0; vertical-align: top;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr style="background-color: #f8f9fa;">
                                        <th colspan="2" style="padding: 10px; text-align: center; font-size: 14px; font-weight: bold; border-bottom: 1px solid #000;">Days Payable</th>
                                    </tr>
                                    <tr style="background-color: #f8f9fa;">
                                        <th style="padding: 8px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000;">Particulars</th>
                                        <th style="padding: 8px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; text-align: right;">Days</th>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Present Days</td>
                                        <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; text-align: right;">{{ $presentDays }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Weekly Off</td>
                                        <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; text-align: right;">{{ number_format($weeklyOff, 2) }}</td>
                                    </tr>
                                    <tr >
                                        <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Total Leave</td>
                                        <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; text-align: right;">{{ number_format($totalAvailed, 2) }}</td>
                                    </tr>

                                    <tr>
                                        <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000;">OT Hrs</td>
                                        <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; text-align: right;">0.00</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000;">PH</td>
                                        <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; text-align: right;">{{ number_format($holidays, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; border-right: 1px solid #000;">LWP</td>
                                        <td style="padding: 8px; font-size: 11px; border-bottom: 1px solid #000; text-align: right;">{{ number_format($lwpDays, 2) }}</td>
                                    </tr>
                                    <tr style="background-color: #f8f9fa;">
                                        <td style="padding: 8px; font-size: 11px; font-weight: bold; border-right: 1px solid #000;">Days Payable</td>
                                        <td style="padding: 8px; font-size: 11px; font-weight: bold; text-align: right;">{{ number_format($calculatedDaysPayable, 2) }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    
                    <!-- Earnings and Deductions Section -->
                    <div style="border-top: 0px solid #000;">
                        <table style="width: 100%; border-collapse: collapse; border-top: 2px solid #000;">
                            <tr>
                                <!-- Earnings Column -->
                                <td style="width: 50%; border-right: 2px solid #000; padding: 0; vertical-align: top; ">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr style="background-color: #f8f9fa;">
                                            <th colspan="2" style="padding: 10px; text-align: center; font-size: 16px; font-weight: bold; border-bottom: 1px solid #000;">Earnings</th>
                                        </tr>
                                        <tr style="background-color: #f8f9fa;">
                                            <th style="padding: 8px; font-size: 12px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000;">Components</th>
                                            <th style="padding: 8px; font-size: 12px; font-weight: bold; border-bottom: 1px solid #000; text-align: right;">Amount (Rs.)</th>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Basic</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($basicComponent) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Medical</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($medicalComponent) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">HRA</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($hraComponent) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">CONVEYANCE</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($conveyanceComponent) }}</td>
                                        </tr>
                                        <tr style="height: 35px;">
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">EDUCATION</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($educationAllowance) }}</td>
                                        </tr>
                                        <tr style="height: 35px;">
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">EXECUTIVE</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($executive) }}</td>
                                        </tr>
                                        @if(!empty($employeeAllowances))
                                            @foreach($employeeAllowances as $allowance)
                                            <tr>
                                                <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">{{ strtoupper($allowance['type']) }}</td>
                                                <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($allowance['amount']) }}</td>
                                            </tr>
                                            @endforeach
                                        @endif
                                        <tr style="height: 35px;">
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Extra Allowance</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($extraAllowance ?? 0) }}</td>
                                        </tr>
                                        <tr style="background-color: #f8f9fa;">
                                            <td style="padding: 10px; font-size: 14px; font-weight: bold; border-right: 1px solid #000;">Gross Earning (A)</td>
                                            <td style="padding: 10px; font-size: 14px; font-weight: bold; text-align: right;">{{ \Auth::user()->priceFormat($grossSalaryWithExtra) }}</td>
                                        </tr>
                                    </table>
                                </td>
                                
                                <!-- Deductions Column -->
                                <td style="width: 50%; padding: 0; vertical-align: top;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr style="background-color: #f8f9fa;">
                                            <th colspan="2" style="padding: 10px; text-align: center; font-size: 16px; font-weight: bold; border-bottom: 1px solid #000;">Deductions</th>
                                        </tr>
                                        <tr style="background-color: #f8f9fa;">
                                            <th style="padding: 8px; font-size: 12px; font-weight: bold; border-bottom: 1px solid #000; border-right: 1px solid #000;">Common Deductions</th>
                                            <th style="padding: 8px; font-size: 12px; font-weight: bold; border-bottom: 1px solid #000; text-align: right;">Amount (Rs.)</th>
                                        </tr>

                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">ESI</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($esiDeduction) }}</td>
                                        </tr>

                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">PF</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($pfDeduction) }}</td>
                                        </tr>

                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Professional Tax</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($ptDeduction) }}</td>
                                        </tr>

                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">MLWF</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($mlwfDeduction) }}</td>
                                        </tr>

                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Advance</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($advanceDeduction) }}</td>
                                        </tr>

                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Other Deduction</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($otherDeduction) }}</td>
                                        </tr>

                                        <!-- <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">TDS</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($tdsDeduction) }}</td>
                                        </tr>                                        

                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;">Absent Days ({{ $absentDaysNew }} days)</td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat($deductionForAbsent) }}</td>
                                        </tr> -->

                                        <tr>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; border-right: 1px solid #000;"></td>
                                            <td style="padding: 8px; font-size: 12px; border-bottom: 1px solid #000; text-align: right;">{{ \Auth::user()->priceFormat(0) }}</td>
                                        </tr>

                                        <tr style="background-color: #f8f9fa;">
                                            <td style="padding: 10px; font-size: 14px; font-weight: bold; border-right: 1px solid #000;">Total Deductions (B)</td>
                                            <td style="padding: 10px; font-size: 14px; font-weight: bold; text-align: right;">{{ \Auth::user()->priceFormat($totalDeductions) }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>


                    
                    <!-- Net Pay Section -->
                    <div style="border-top: 2px solid #000;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr style="background-color: #f8f9fa;">
                                <td style="padding: 12px; font-size: 16px; font-weight: bold; border-right: 1px solid #000; border-bottom: 1px solid #000;">Net Pay (A - B)</td>
                                <td style="padding: 12px; font-size: 16px; font-weight: bold; text-align: left; border-bottom: 1px solid #000;">{{ \Auth::user()->priceFormat($netSalary) }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; font-size: 12px; font-weight: bold; border-right: 1px solid #000;">Total Pay</td>
                                <td style="padding: 10px; font-size: 12px;">{{ ucwords($netSalaryInWords) }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Footer Note -->
                    <div style="border-top: 2px solid #000; padding: 15px; text-align: center; font-size: 12px; font-weight: bold;">
                        Note: This is a Computer Generated Slip and does not require signature
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
    function saveAsPDF() {
        var element = document.getElementById('printableArea');
        var opt = {
            margin: 0.3,
            filename: '{{ $employee->name }}_{{ $payslip->salary_month }}_payslip',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 4,
                dpi: 72,
                letterRendering: true
            },
            jsPDF: {
                unit: 'in',
                format: 'A4'
            }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>

<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
    function saveAsPDF() {
        var element = document.getElementById('printableArea');
        var opt = {
            margin: 0.3,
            filename: '{{ $employee->name }}',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 4,
                dpi: 72,
                letterRendering: true
            },
            jsPDF: {
                unit: 'in',
                format: 'A4'
            }
        };
        html2pdf().set(opt).from(element).save();
    }
</script
