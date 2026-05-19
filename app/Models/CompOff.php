<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompOff extends Model
{
    protected $table = 'comp_offs';
    
    protected $fillable = [
        'branch_id',
        'department_ids',
        'dates',
        'employee_ids',
        'created_by'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
