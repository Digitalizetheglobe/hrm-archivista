<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'location',
        'status',
        'reason',
        'approved_by',
        'created_by',
    ];

    public static $statues = [
        'Pending',
        'Approved',
        'Rejected',
    ];

    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }

    public function creator()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
}
