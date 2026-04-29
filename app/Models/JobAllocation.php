<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobAllocation extends Model
{
    protected $table = 'job_allocation';
    
    protected $fillable = [
        'client_id',
        'project_id',
        'status',
        'start_date',
        'end_date',
        'billable',
        'budgeting',
        'narration',
        'department_id',
        'employees_id',
        'approver',
        'created_by'
    ];

    public static $status = [
        'Ongoing' => 'Ongoing',
        'Completed' => 'Completed',
    ];

    public function client()
    {
        return $this->belongsTo('App\Models\Client', 'client_id');
    }
    
    
    public function project()
{
    return $this->belongsTo(Project::class); // Replace with your actual Project model if different
}
                
        public function departments()
        {
            return $this->belongsToMany(Department::class, 'job_allocation_departments');
        }
            
        public function employees()
        {
            return $this->belongsToMany(Employee::class, 'job_allocation_employees');
        }
    
    public function approverEmployee()
    {
        return $this->belongsTo('App\Models\Employee', 'approver');
    }
    
    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }
    

    
}