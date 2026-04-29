<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tds extends Model
{
    protected $table = 'tds';
    protected $fillable = [
        'employee_id',
        'tds_type',
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
}
