<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'title',
        'days',
        'type',
        'is_unlimited',
        'carry_forward_enabled',
        'max_carry_forward_days',
        'eligible_employee_types',
        'created_by',
    ];

    protected $casts = [
        'eligible_employee_types' => 'array',
    ];
}
