<?php

namespace App\Http\Controllers;

use App\Models\AccountList;
use App\Models\Allowance;
use App\Models\AllowanceOption;
use App\Models\Commission;
use App\Models\DeductionOption;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\LoanOption;
use App\Models\OtherPayment;
use App\Models\Overtime;
use App\Models\PayrollData;
use App\Models\SaturationDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SetSalaryController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Set Salary')) {
            $employees = Employee::where(
                [
                    'created_by' => \Auth::user()->creatorId(),
                ]
            )->get();

            return view('setsalary.index', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (\Auth::user()->can('Edit Set Salary')) {

            $allowance_options = AllowanceOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $loan_options      = LoanOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $deduction_options = DeductionOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            if (\Auth::user()->type == 'employee') {
                $currentEmployee      = Employee::where('user_id', '=', \Auth::user()->id)->first();
                $allowances           = Allowance::where('employee_id', $currentEmployee->id)->get();
                $commissions          = Commission::where('employee_id', $currentEmployee->id)->get();
                $loans                = Loan::where('employee_id', $currentEmployee->id)->get();
                $saturationdeductions = SaturationDeduction::where('employee_id', $currentEmployee->id)->get();
                $otherpayments        = OtherPayment::where('employee_id', $currentEmployee->id)->get();
                $overtimes            = Overtime::where('employee_id', $currentEmployee->id)->get();
                $employee             = Employee::where('user_id', '=', \Auth::user()->id)->first();

                return view('setsalary.employee_salary', compact('employee', 'allowance_options', 'commissions', 'loan_options', 'overtimes', 'otherpayments', 'saturationdeductions', 'loans', 'deduction_options', 'allowances'));
            } else {
                $allowances           = Allowance::where('employee_id', $id)->get();
                $commissions          = Commission::where('employee_id', $id)->get();
                $loans                = Loan::where('employee_id', $id)->get();
                $saturationdeductions = SaturationDeduction::where('employee_id', $id)->get();
                $otherpayments        = OtherPayment::where('employee_id', $id)->get();
                $overtimes            = Overtime::where('employee_id', $id)->get();
                $employee             = Employee::find($id);

                return view('setsalary.edit', compact('employee', 'allowance_options', 'commissions', 'loan_options', 'overtimes', 'otherpayments', 'saturationdeductions', 'loans', 'deduction_options', 'allowances'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        
        $allowance_options = AllowanceOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $loan_options      = LoanOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $deduction_options = DeductionOption::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        if (\Auth::user()->type == 'employee') {
            $currentEmployee      = Employee::where('user_id', '=', \Auth::user()->id)->first();
            $allowances           = Allowance::where('employee_id', $currentEmployee->id)->get();
            $commissions          = Commission::where('employee_id', $currentEmployee->id)->get();
            $loans                = Loan::where('employee_id', $currentEmployee->id)->get();
            $saturationdeductions = SaturationDeduction::where('employee_id', $currentEmployee->id)->get();
            $otherpayments        = OtherPayment::where('employee_id', $currentEmployee->id)->get();
            $overtimes            = Overtime::where('employee_id', $currentEmployee->id)->get();
            $employee             = Employee::where('user_id', '=', \Auth::user()->id)->first();

            foreach ($allowances as  $value) {
                if ($value->type == 'percentage') {
                    $employee          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * $employee->set_salary / 100;
                    $value->tota_allow = $empsal;
                }
            }

            foreach ($commissions as  $value) {
                if ($value->type == 'percentage') {
                    $employee          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * $employee->set_salary / 100;
                    $value->tota_allow = $empsal;
                }
            }

            foreach ($loans as  $value) {
                if ($value->type == 'percentage') {
                    $employee          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * $employee->set_salary / 100;
                    $value->tota_allow = $empsal;
                }
            }

            foreach ($saturationdeductions as  $value) {
                if ($value->type == 'percentage') {
                    $employee          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * $employee->set_salary / 100;
                    $value->tota_allow = $empsal;
                }
            }

            foreach ($otherpayments as  $value) {
                if ($value->type == 'percentage') {
                    $employee          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * $employee->set_salary / 100;
                    $value->tota_allow = $empsal;
                }
            }


            return view('setsalary.employee_salary', compact('employee', 'allowance_options', 'commissions', 'loan_options', 'overtimes', 'otherpayments', 'saturationdeductions', 'loans', 'deduction_options', 'allowances'));
        } else {
            $allowances           = Allowance::where('employee_id', $id)->get();
            $commissions          = Commission::where('employee_id', $id)->get();
            $loans                = Loan::where('employee_id', $id)->get();
            $saturationdeductions = SaturationDeduction::where('employee_id', $id)->get();
            $otherpayments        = OtherPayment::where('employee_id', $id)->get();
            $overtimes            = Overtime::where('employee_id', $id)->get();
            $employee             = Employee::find($id);

            foreach ($allowances as  $value) {
                if ($value->type == 'percentage') {
                    $employee          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * $employee->set_salary / 100;
                    $value->tota_allow = $empsal;
                }
            }

            foreach ($commissions as  $value) {
                if ($value->type == 'percentage') {
                    $employee          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * $employee->set_salary / 100;
                    $value->tota_allow = $empsal;
                }
            }

            foreach ($loans as  $value) {
                if ($value->type == 'percentage') {
                    $employee          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * $employee->set_salary / 100;
                    $value->tota_allow = $empsal;
                }
            }

            foreach ($saturationdeductions as  $value) {
                if ($value->type == 'percentage') {
                    $employee          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * $employee->set_salary / 100;
                    $value->tota_allow = $empsal;
                }
            }

            foreach ($otherpayments as  $value) {
                if ($value->type == 'percentage') {
                    $employee          = Employee::find($value->employee_id);
                    $empsal  = $value->amount * $employee->set_salary / 100;
                    $value->tota_allow = $empsal;
                }
            }

            return view('setsalary.employee_salary', compact('employee', 'allowance_options', 'commissions', 'loan_options', 'overtimes', 'otherpayments', 'saturationdeductions', 'loans', 'deduction_options', 'allowances'));
        }
    }


    public function employeeUpdateSalary(Request $request, $id)
    {
        // Debug logging
        \Log::info('employeeUpdateSalary called', [
            'request_data' => $request->all(),
            'employee_id' => $id,
            'is_ajax' => $request->ajax()
        ]);

        $validator = \Validator::make(
            $request->all(),
            [
                'set_salary' => 'required|numeric|min:0',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            
            \Log::error('Validation failed', [
                'errors' => $messages->toArray(),
                'request_data' => $request->all()
            ]);

            if ($request->ajax()) {
                return response()->json(['error' => $messages->first()], 400);
            }
            return redirect()->back()->with('error', $messages->first());
        }
        
        try {
            $employee = Employee::findOrFail($id);
            \Log::info('Employee found', ['employee' => $employee->toArray()]);
            
            $employee->set_salary = $request->set_salary;
            $employee->save();
            
            \Log::info('Salary updated successfully', ['employee_id' => $id, 'new_salary' => $request->set_salary]);

            if ($request->ajax()) {
                return response()->json(['success' => 'Employee Salary Updated.']);
            }
            return redirect()->back()->with('success', 'Employee Salary Updated.');
        } catch (\Exception $e) {
            \Log::error('Error updating salary', [
                'error' => $e->getMessage(),
                'employee_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Database error: ' . $e->getMessage());
        }
    }

    public function employeeSalary()
    {
        if (\Auth::user()->type == "employee") {
            $employees = Employee::where('user_id', \Auth::user()->id)->get();
            return view('setsalary.index', compact('employees'));
        }
    }

    /**
     * Show the new full salary configuration page.
     */
    public function salaryPage($id)
    {
        if (!\Auth::user()->can('Edit Set Salary')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $employee = Employee::findOrFail($id);
        $payrollData = PayrollData::where('employee_id', $id)->first();

        return view('setsalary.set_salary_page', compact('employee', 'payrollData'));
    }

    /**
     * Save the salary and payroll data (allowances & deductions) to the database.
     */
    public function savePayroll(Request $request, $id)
    {
        if (!\Auth::user()->can('Edit Set Salary')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'set_salary'      => 'required|numeric|min:0',
            'basic'           => 'nullable|numeric|min:0|max:100',
            'medical'         => 'nullable|numeric|min:0|max:100',
            'hra'             => 'nullable|numeric|min:0|max:100',
            'conveyance'      => 'nullable|numeric|min:0|max:100',
            'education'       => 'nullable|numeric|min:0|max:100',
            'executive'       => 'nullable|numeric|min:0|max:100',
            'esi'             => 'nullable|numeric|min:0',
            'pf'              => 'nullable|numeric|min:0',
            'professional_tax'=> 'nullable|numeric|min:0',
        ]);

        // Update the employee's set salary
        $employee = Employee::findOrFail($id);
        $employee->set_salary = $request->set_salary;
        $employee->save();

        // Create or update the payroll data
        PayrollData::updateOrCreate(
            ['employee_id' => $id],
            [
                'basic'            => $request->basic ?? 0,
                'medical'          => $request->medical ?? 0,
                'hra'              => $request->hra ?? 0,
                'conveyance'       => $request->conveyance ?? 0,
                'education'        => $request->education ?? 0,
                'executive'        => $request->executive ?? 0,
                'esi'              => $request->esi ?? 0,
                'pf'               => $request->pf ?? 0,
                'professional_tax' => $request->professional_tax ?? 0,
            ]
        );

        return redirect()->route('setsalary.index')->with('success', __('Salary and payroll data saved successfully.'));
    }

    public function employeeBasicSalary($id)
    {
        $accounts = AccountList::where('created_by', \Auth::user()->creatorId())->get()->pluck('account_name', 'id');
        $accounts->prepend('Select Account Type', '');

        $employee     = Employee::find($id);

        return view('setsalary.basic_salary', compact('employee', 'accounts'));
    }
}
