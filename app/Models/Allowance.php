<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Allowance extends Model
{
    protected $fillable = [
        'employee_id',
        'allowance_type',
        'month',
        'amount',
        'remark',
        'created_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function allowanceTypes()
    {
        return [
            'Leave Encashment' => 'Leave Encashment',
            'Site Expenses' => 'Site Expenses',
            'Special Allowance' => 'Special Allowance',
        ];
    }

    public static function monthOptions()
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $month = date('Y-m', mktime(0, 0, 0, $i, 1, date('Y')));
            $months[$month] = date('F Y', mktime(0, 0, 0, $i, 1, date('Y')));
        }
        
        // Add previous year months if needed
        for ($i = 1; $i <= 12; $i++) {
            $month = date('Y-m', mktime(0, 0, 0, $i, 1, date('Y') - 1));
            $months[$month] = date('F Y', mktime(0, 0, 0, $i, 1, date('Y') - 1));
        }
        
        return $months;
    }
}
