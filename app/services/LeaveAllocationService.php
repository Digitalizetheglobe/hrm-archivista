<?php

namespace App\Services;

use App\Models\CarryForwardBalance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveAllocationService
{
    /**
     * Process carry-forward and allocation for all eligible employees
     *
     * @param string $processDate
     * @param bool $force
     * @return array
     */
    public function processCarryForwardAndAllocation($processDate, $force = false)
    {
        $results = [
            'monthly_processed' => 0,
            'yearly_processed' => 0,
            'allocations_created' => 0,
            'employees_processed' => 0,
            'errors' => 0,
            'error_details' => []
        ];

        $processDateObj = new \DateTime($processDate);
        $currentMonth = $processDateObj->format('Y-m');
        $currentYear = $processDateObj->format('Y');

        try {
            // Get all leave types with carry forward enabled
            $leaveTypes = LeaveType::where('carry_forward_enabled', true)
                ->where('created_by', auth()->user()?->creatorId() ?? 1)
                ->get();

            foreach ($leaveTypes as $leaveType) {
                try {
                    if ($leaveType->type === 'monthly') {
                        $this->processMonthlyCarryForward($leaveType, $currentMonth, $force, $results);
                    } elseif ($leaveType->type === 'yearly') {
                        $this->processYearlyCarryForward($leaveType, $currentYear, $force, $results);
                    }
                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['error_details'][] = "Leave Type '{$leaveType->title}': " . $e->getMessage();
                    Log::error("Error processing leave type {$leaveType->title}", [
                        'error' => $e->getMessage(),
                        'leave_type_id' => $leaveType->id
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Critical error in leave allocation service', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        return $results;
    }

    /**
     * Process monthly carry-forward for a specific leave type
     *
     * @param LeaveType $leaveType
     * @param string $currentMonth
     * @param bool $force
     * @param array &$results
     */
    private function processMonthlyCarryForward(LeaveType $leaveType, $currentMonth, $force, &$results)
    {
        // Get previous month
        $previousMonth = date('Y-m', strtotime($currentMonth . '-01 -1 month'));
        
        // Get eligible employees
        $eligibleEmployees = $this->getEligibleEmployees($leaveType);
        
        foreach ($eligibleEmployees as $employee) {
            $results['employees_processed']++;
            
            try {
                DB::beginTransaction();
                
                // Get or create previous month balance
                $previousBalance = CarryForwardBalance::getOrCreateBalance(
                    $employee->id, 
                    $leaveType->id, 
                    $previousMonth,
                    'monthly'
                );
                
                // Calculate used days in previous month
                $usedDays = $this->calculateUsedDaysInPeriod($employee->id, $leaveType->id, $previousMonth, 'monthly');
                
                // Update previous month balance
                $previousBalance->allocated_days = $this->getAllocatedDaysForEmployee($employee, $leaveType);
                $previousBalance->used_days = $usedDays;
                $previousBalance->remaining_days = ($previousBalance->allocated_days + $previousBalance->carried_forward_days) - $usedDays;
                $previousBalance->save();
                
                // Calculate carry-forward amount
                $carryForwardAmount = $previousBalance->remaining_days > 0 
                    ? min($previousBalance->remaining_days, $leaveType->max_carry_forward_days) 
                    : 0;
                
                // Get or create current month balance
                $currentBalance = CarryForwardBalance::getOrCreateBalance(
                    $employee->id, 
                    $leaveType->id, 
                    $currentMonth,
                    'monthly'
                );
                
                // Update current month with carry-forward
                $currentBalance->carried_forward_days = $carryForwardAmount;
                $currentBalance->allocated_days = $this->getAllocatedDaysForEmployee($employee, $leaveType);
                $currentBalance->save();
                
                $results['monthly_processed']++;
                $results['allocations_created']++;
                
                DB::commit();
                
                Log::info('Monthly carry-forward processed', [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'month' => $currentMonth,
                    'carried_forward' => $carryForwardAmount
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                $results['errors']++;
                $results['error_details'][] = "Employee {$employee->id}, Leave Type '{$leaveType->title}': " . $e->getMessage();
                
                Log::error('Error processing monthly carry-forward for employee', [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Process yearly carry-forward for a specific leave type
     *
     * @param LeaveType $leaveType
     * @param string $currentYear
     * @param bool $force
     * @param array &$results
     */
    private function processYearlyCarryForward(LeaveType $leaveType, $currentYear, $force, &$results)
    {
        // Get previous year
        $previousYear = $currentYear - 1;
        
        // Get eligible employees
        $eligibleEmployees = $this->getEligibleEmployees($leaveType);
        
        foreach ($eligibleEmployees as $employee) {
            $results['employees_processed']++;
            
            try {
                DB::beginTransaction();
                
                // Calculate used days in previous year
                $usedDays = $this->calculateUsedDaysInPeriod($employee->id, $leaveType->id, $previousYear, 'yearly');
                
                // Get allocated days for the year
                $allocatedDays = $this->getAllocatedDaysForEmployee($employee, $leaveType) * 12; // Monthly allocation * 12 months
                
                // Calculate remaining days
                $remainingDays = $allocatedDays - $usedDays;
                
                // Calculate carry-forward amount
                $carryForwardAmount = $remainingDays > 0 
                    ? min($remainingDays, $leaveType->max_carry_forward_days) 
                    : 0;
                
                // Create current year balance with carry-forward
                $currentBalance = CarryForwardBalance::getOrCreateBalance(
                    $employee->id, 
                    $leaveType->id, 
                    $currentYear,
                    'yearly'
                );
                
                $currentBalance->carried_forward_days = $carryForwardAmount;
                $currentBalance->allocated_days = $allocatedDays;
                $currentBalance->save();
                
                $results['yearly_processed']++;
                $results['allocations_created']++;
                
                DB::commit();
                
                Log::info('Yearly carry-forward processed', [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => $currentYear,
                    'carried_forward' => $carryForwardAmount
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                $results['errors']++;
                $results['error_details'][] = "Employee {$employee->id}, Leave Type '{$leaveType->title}': " . $e->getMessage();
                
                Log::error('Error processing yearly carry-forward for employee', [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Get eligible employees for a leave type based on eligible_employee_types
     *
     * @param LeaveType $leaveType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getEligibleEmployees(LeaveType $leaveType)
    {
        if (!$leaveType->eligible_employee_types || empty($leaveType->eligible_employee_types)) {
            // If no specific eligibility, return all employees
            return Employee::where('created_by', auth()->user()?->creatorId() ?? 1)->get();
        }

        $query = Employee::where('created_by', auth()->user()?->creatorId() ?? 1);
        
        foreach ($leaveType->eligible_employee_types as $type) {
            switch ($type) {
                case 'payroll_confirm':
                    $query->orWhere(function($q) {
                        $q->where('employee_type', 'Payroll')
                          ->where('confirm_of_employment', true);
                    });
                    break;
                    
                case 'payroll_not_confirm':
                    $query->orWhere(function($q) {
                        $q->where('employee_type', 'Payroll')
                          ->where('confirm_of_employment', false);
                    });
                    break;
                    
                case 'contract_confirm':
                    $query->orWhere(function($q) {
                        $q->where('employee_type', 'Contract')
                          ->where('confirm_of_employment', true);
                    });
                    break;
                    
                case 'contract_not_confirm':
                    $query->orWhere(function($q) {
                        $q->where('employee_type', 'Contract')
                          ->where('confirm_of_employment', false);
                    });
                    break;
            }
        }
        
        return $query->get();
    }

    /**
     * Calculate used days in a specific period
     *
     * @param int $employeeId
     * @param int $leaveTypeId
     * @param string $period
     * @param string $type
     * @return float
     */
    private function calculateUsedDaysInPeriod($employeeId, $leaveTypeId, $period, $type)
    {
        $query = Leave::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', 'Approved');

        if ($type === 'monthly') {
            $startDate = $period . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
            $query->whereBetween('start_date', [$startDate, $endDate]);
        } elseif ($type === 'yearly') {
            $startDate = $period . '-01-01';
            $endDate = $period . '-12-31';
            $query->whereBetween('start_date', [$startDate, $endDate]);
        }

        return $query->sum('total_leave_days');
    }

    /**
     * Get allocated days for an employee based on leave type and employee type
     *
     * @param Employee $employee
     * @param LeaveType $leaveType
     * @return float
     */
    private function getAllocatedDaysForEmployee(Employee $employee, LeaveType $leaveType)
    {
        // For contract employees with casual leave, use special calculation
        if ($employee->employee_type === 'Contract' && 
            strtolower(trim($leaveType->title)) === 'casual leave') {
            
            return $employee->confirm_of_employment ? 2.5 : 1.5;
        }
        
        // For all other cases, use the leave type's default days
        return $leaveType->days;
    }

    /**
     * Get current leave balances for an employee (enhanced version)
     *
     * @param int $employeeId
     * @return array
     */
    public function getCurrentLeaveBalances($employeeId)
    {
        $currentMonth = date('Y-m');
        $currentYear = date('Y');
        
        $employee = Employee::find($employeeId);
        $leaveTypes = LeaveType::where('created_by', $employee->created_by)->get();
        
        // Filter leave types based on employee eligibility
        $eligibleLeaveTypes = $leaveTypes->filter(function($leaveType) use ($employee) {
            if (!$leaveType->eligible_employee_types || empty($leaveType->eligible_employee_types)) {
                return true;
            }
            
            $employeeTypeIdentifier = $this->getEmployeeTypeIdentifier($employee);
            return in_array($employeeTypeIdentifier, $leaveType->eligible_employee_types);
        });
        
        $balances = [];
        $totalLeavesThisMonth = 0;
        
        foreach ($eligibleLeaveTypes as $leaveType) {
            $leaveTypeName = strtolower(trim($leaveType->title));
            
            // Get current balance from carry_forward_balances table
            $currentBalance = CarryForwardBalance::where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveType->id)
                ->where('month', $currentMonth)
                ->where('period_type', 'monthly')
                ->first();
            
            if (!$currentBalance) {
                // Create balance if it doesn't exist
                $currentBalance = CarryForwardBalance::getOrCreateBalance($employeeId, $leaveType->id, $currentMonth, 'monthly');
                $currentBalance->allocated_days = $this->getAllocatedDaysForEmployee($employee, $leaveType);
                $currentBalance->save();
            }
            
            // Calculate used this month
            $usedThisMonth = $this->calculateUsedDaysInPeriod($employeeId, $leaveType->id, $currentMonth, 'monthly');
            $totalLeavesThisMonth += $usedThisMonth;
            
            // Calculate total used
            $totalUsed = $this->calculateUsedDaysInPeriod($employeeId, $leaveType->id, $currentYear, 'yearly');
            
            // Calculate available
            $availableDays = ($currentBalance->allocated_days + $currentBalance->carried_forward_days) - $usedThisMonth;
            
            $balanceData = [
                'title' => $leaveType->title,
                'total_allocated' => $currentBalance->allocated_days,
                'carried_forward' => $currentBalance->carried_forward_days,
                'used_this_month' => $usedThisMonth,
                'total_used' => $totalUsed,
                'available' => max(0, $availableDays),
                'type' => $leaveType->type,
                'days_per_period' => $currentBalance->allocated_days,
                'is_unlimited' => $leaveType->is_unlimited
            ];
            
            $balances[$leaveTypeName] = $balanceData;
            $balances[strtolower($leaveType->title)] = $balanceData;
        }
        
        $balances['total_leaves_this_month'] = $totalLeavesThisMonth;
        
        return $balances;
    }

    /**
     * Get employee type identifier for eligibility checking
     *
     * @param Employee $employee
     * @return string|null
     */
    private function getEmployeeTypeIdentifier(Employee $employee)
    {
        if ($employee->employee_type === 'Payroll') {
            return $employee->confirm_of_employment ? 'payroll_confirm' : 'payroll_not_confirm';
        } elseif ($employee->employee_type === 'Contract') {
            return $employee->confirm_of_employment ? 'contract_confirm' : 'contract_not_confirm';
        }
        
        return null;
    }
}
