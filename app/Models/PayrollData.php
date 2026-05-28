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
    ];

    public $timestamps = true;

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
