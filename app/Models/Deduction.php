<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    protected $fillable = [
        'employee_id',
        'deduction_type',
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

    public static function deductionTypes()
    {
        return [
            'MLWF' => 'MLWF',
            'Other Deduction' => 'Other Deduction',
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

    public static function monthVariants($month)
    {
        $variants = array_filter([(string) $month]);

        if (preg_match('/^(\d{4})-(\d{1,2})$/', (string) $month, $matches)) {
            $year = (int) $matches[1];
            $monthNum = (int) $matches[2];
            $variants[] = sprintf('%04d-%02d', $year, $monthNum);
            $variants[] = sprintf('%04d-%d', $year, $monthNum);
        }

        return array_values(array_unique($variants));
    }

    public static function amountFor($employeeId, $type, $month)
    {
        $record = static::query()
            ->where('employee_id', $employeeId)
            ->where('deduction_type', $type)
            ->whereIn('month', self::monthVariants($month))
            ->first();

        return $record ? (float) $record->amount : 0;
    }
}
