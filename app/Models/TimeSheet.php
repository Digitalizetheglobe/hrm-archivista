<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSheet extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'client_id',
        'project_id',
        'total_time',
        'billable',
        'location',
        'narration',
        'expense',
        'day_total',
        'created_by',
        'full_name',
        'mobile_no'
    ];
    
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($timesheet) {
            // Calculate day_total by summing all timesheets for this employee on this date
            $totalHours = self::where('employee_id', $timesheet->employee_id)
                ->where('date', $timesheet->date)
                ->sum('total_time');
                
            $timesheet->day_total = $totalHours + $timesheet->total_time;
        });
    }
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'user_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}