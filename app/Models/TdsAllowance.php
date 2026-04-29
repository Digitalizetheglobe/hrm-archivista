<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TdsAllowance extends Model
{
    protected $fillable = [
        'employee_id',
        'allowance_type',
        'amount',
        'description',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
