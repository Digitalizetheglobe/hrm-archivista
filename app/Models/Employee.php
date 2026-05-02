<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SebastianBergmann\CodeCoverage\Percentage;
use App\Models\Allowance;
use App\Models\Commission;
use App\Models\Loan;
use App\Models\SaturationDeduction;
use App\Models\OtherPayment;
use App\Models\Overtime;
use App\Models\LocalLeave;
use App\Models\Utility;

class Employee extends Model
{
    protected $table = 'employees';
    protected $fillable = [
        'user_id',
        'name',
        'dob',
        'gender',
        'phone',
        'address',
        'email',
        'password',
        'employee_id',
        'branch_id',
        'department_id',
        'designation_id',
        'company_doj',
        'company_dol',
        'employee_type',
        'per_day_rate',
        'confirm_of_employment',
        'primary_skill',
        'secondary_skill',
        'certificate',
        'esic_no',
        'bank_ac_no',
        'project_id',
        'hourly_charged',
        'salary_type',
        'salary',
        'set_salary',
        'account_type',
        'created_by',
        'is_active',
        'created_at',
        'updated_at',
    ];

   

 
    public static function employee_id()
    {
        $employee = Employee::latest()->first();

        return !empty($employee) ? $employee->id + 1 : 1;
    }

    public function phone()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'phone');
    }

    public function department()
    {
        return $this->hasOne('App\Models\Department', 'id', 'department_id');
    }

    public function designation()
    {
        return $this->hasOne('App\Models\Designation', 'id', 'designation_id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function getSalaryTypeName()
    {
        $salaryTypeId = $this->attributes['salary_type'] ?? null;
        if (!$salaryTypeId) {
            return '-';
        }
        $salaryType = PayslipType::find($salaryTypeId);
        return $salaryType ? $salaryType->name : '-';
    }

    public function get_net_salary()
    {
        // Basic net salary calculation - can be expanded with allowances, deductions, etc.
        return $this->set_salary ?? 0;
    }

    public function getAccountTypeName()
    {
        $accountTypeId = $this->attributes['account_type'] ?? null;
        if (!$accountTypeId) {
            return '-';
        }
        $accountType = AccountList::find($accountTypeId);
        return $accountType ? $accountType->account_name : '-';
    }



    public function present_status($employee_id, $data)
    {
        return AttendanceEmployee::where('employee_id', $employee_id)->where('date', $data)->first();
    }
    public static function employee_name($name)
    {

        $employee = Employee::where('id', $name)->first();
        if (!empty($employee)) {
            return $employee->name;
        }
    }


    public static function login_user($name)
    {
        $user = User::where('id', $name)->first();
        return $user->name;
    }

    public function getUsedLeaves($leaveTypeId)
    {
        $date = Utility::AnnualLeaveCycle(); // Make sure Utility is imported
        
        return LocalLeave::where('employee_id', $this->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', 'Approved')
            ->whereBetween('created_at', [$date['start_date'], $date['end_date']])
            ->sum('total_leave_days');
    }

    public static function allowance($employee_id)
    {
        $allowances = Allowance::where('employee_id', $employee_id)->get();
        $total = 0;
        foreach ($allowances as $allowance) {
            if ($allowance->type == 'percentage') {
                $employee = Employee::find($employee_id);
                $total += $allowance->amount * $employee->set_salary / 100;
            } else {
                $total += $allowance->amount;
            }
        }
        return $total;
    }

    public static function commission($employee_id)
    {
        $commissions = Commission::where('employee_id', $employee_id)->get();
        $total = 0;
        foreach ($commissions as $commission) {
            if ($commission->type == 'percentage') {
                $employee = Employee::find($employee_id);
                $total += $commission->amount * $employee->set_salary / 100;
            } else {
                $total += $commission->amount;
            }
        }
        return $total;
    }

    public static function loan($employee_id)
    {
        $loans = Loan::where('employee_id', $employee_id)->get();
        $total = 0;
        foreach ($loans as $loan) {
            if ($loan->type == 'percentage') {
                $employee = Employee::find($employee_id);
                $total += $loan->amount * $employee->set_salary / 100;
            } else {
                $total += $loan->amount;
            }
        }
        return $total;
    }

    public static function saturation_deduction($employee_id)
    {
        $deductions = SaturationDeduction::where('employee_id', $employee_id)->get();
        $total = 0;
        foreach ($deductions as $deduction) {
            if ($deduction->type == 'percentage') {
                $employee = Employee::find($employee_id);
                $total += $deduction->amount * $employee->set_salary / 100;
            } else {
                $total += $deduction->amount;
            }
        }
        return $total;
    }

    public static function other_payment($employee_id)
    {
        $payments = OtherPayment::where('employee_id', $employee_id)->get();
        $total = 0;
        foreach ($payments as $payment) {
            if ($payment->type == 'percentage') {
                $employee = Employee::find($employee_id);
                $total += $payment->amount * $employee->set_salary / 100;
            } else {
                $total += $payment->amount;
            }
        }
        return $total;
    }

    public static function overtime($employee_id)
    {
        $overtimes = Overtime::where('employee_id', $employee_id)->get();
        $total = 0;
        foreach ($overtimes as $overtime) {
            $total += $overtime->rate * $overtime->hours;
        }
        return $total;
    }

     public function siteVisits()
    {
        return $this->hasMany(SiteVisit::class, 'employee_id', 'id');
    }
}