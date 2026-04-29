<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarryForwardBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'month',
        'period_type',
        'carried_forward_days',
        'allocated_days',
        'used_days',
        'remaining_days',
    ];

    protected $casts = [
        'carried_forward_days' => 'decimal:2',
        'allocated_days' => 'decimal:2',
        'used_days' => 'decimal:2',
        'remaining_days' => 'decimal:2',
    ];

    /**
     * Get the employee that owns the carry forward balance.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the leave type that owns the carry forward balance.
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get or create carry forward balance for a specific month and period type
     */
    public static function getOrCreateBalance($employeeId, $leaveTypeId, $month, $periodType = 'monthly')
    {
        return self::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_type_id' => $leaveTypeId,
                'month' => $month,
                'period_type' => $periodType,
            ],
            [
                'carried_forward_days' => 0,
                'allocated_days' => 0,
                'used_days' => 0,
                'remaining_days' => 0,
            ]
        );
    }

    /**
     * Calculate carry forward for next month
     */
    public static function calculateCarryForward($employeeId, $leaveTypeId, $currentMonth)
    {
        $leaveType = LeaveType::find($leaveTypeId);
        
        // Only calculate for monthly leave types with carry forward enabled
        if (!$leaveType || $leaveType->type !== 'monthly' || !$leaveType->carry_forward_enabled) {
            return 0;
        }

        // Get current month balance
        $currentBalance = self::getOrCreateBalance($employeeId, $leaveTypeId, $currentMonth);
        
        // Calculate carry forward (remaining days, capped at max limit)
        $carryForwardAmount = min($currentBalance->remaining_days, $leaveType->max_carry_forward_days);
        
        return max(0, $carryForwardAmount);
    }
}
