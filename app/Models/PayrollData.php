<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollData extends Model
{
    use HasFactory;

    protected $table = 'payroll_data';

    protected $fillable = [
        'employee_id',
        'basic',
        'medical',
        'hra',
        'conveyance',
        'education',
        'executive',
        'esi',
        'pf',
        'professional_tax',
        'tds',
    ];

    public $timestamps = true;

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public const ALLOWANCE_FIELDS = ['basic', 'medical', 'hra', 'conveyance', 'education', 'executive'];

    public static function tdsFor($employeeId): float
    {
        $row = static::where('employee_id', $employeeId)->first();

        return (float) ($row->tds ?? 0);
    }

    /**
     * Rupee amounts stored for the six allowance components.
     */
    public static function componentAmounts($payrollData): array
    {
        $amounts = [];
        foreach (self::ALLOWANCE_FIELDS as $field) {
            $amounts[$field] = round((float) ($payrollData->{$field} ?? 0), 2);
        }

        return $amounts;
    }
}
