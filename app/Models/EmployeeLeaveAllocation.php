<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveAllocation extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'allocated_days',
        'created_by'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}
