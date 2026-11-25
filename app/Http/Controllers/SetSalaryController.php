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

        // If payroll data has no component amounts set yet (or all 0) but employee has set_salary,
        // provide default standard breakdown:
        // Basic: 40%, HRA: 16%, Medical: 6%, Conveyance: 4%, Education: 4%, Executive: 30%, PT: 200, PF: 1800
        $hasSavedComponents = $payrollData && (
            (float)$payrollData->basic > 0 ||
            (float)$payrollData->hra > 0 ||
            (float)$payrollData->medical > 0 ||
            (float)$payrollData->conveyance > 0 ||
            (float)$payrollData->education > 0 ||
            (float)$payrollData->executive > 0
        );

        $defaultComponents = [];
        if (!$hasSavedComponents && (float)$employee->set_salary > 0) {
            $salary = (float)$employee->set_salary;
            $basic = round($salary * 0.40, 2);
            $defaultComponents = [
                'basic'            => $basic,
                'hra'              => round($salary * 0.16, 2),
                'medical'          => round($salary * 0.06, 2),
                'conveyance'       => round($salary * 0.04, 2),
                'education'        => round($salary * 0.04, 2),
                'executive'        => round($salary * 0.30, 2),
                'professional_tax' => 200,
                'pf'               => ($basic >= 15000 ? 1800 : round($basic * 0.12, 2)),
                'esi'              => ($salary <= 21000 ? round($basic * 0.0075, 2) : 0),
                'tds'              => 0,
            ];
        }

        return view('setsalary.set_salary_page', compact('employee', 'payrollData', 'defaultComponents'));
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
            'set_salary'        => 'required|numeric|min:0',
            'basic'             => 'nullable|numeric|min:0',
            'medical'           => 'nullable|numeric|min:0',
            'hra'               => 'nullable|numeric|min:0',
            'conveyance'        => 'nullable|numeric|min:0',
            'education'         => 'nullable|numeric|min:0',
            'executive'         => 'nullable|numeric|min:0',
            'esi'               => 'nullable|numeric|min:0',
            'pf'                => 'nullable|numeric|min:0',
            'professional_tax'  => 'nullable|numeric|min:0',
            'tds'               => 'nullable|numeric|min:0',
            'custom_allowances' => 'nullable|array',
            'custom_deductions' => 'nullable|array',
        ]);

        // Process custom allowances
        $customAllowances = [];
        if ($request->has('custom_allowances') && is_array($request->custom_allowances)) {
            foreach ($request->custom_allowances as $item) {
                $name = trim($item['name'] ?? '');
                $amount = isset($item['amount']) && is_numeric($item['amount']) ? (float)$item['amount'] : null;
                if ($name !== '' && $amount !== null) {
                    $customAllowances[] = [
                        'name' => $name,
                        'amount' => $amount,
                    ];
                }
            }
        }

        // Process custom deductions
        $customDeductions = [];
        if ($request->has('custom_deductions') && is_array($request->custom_deductions)) {
            foreach ($request->custom_deductions as $item) {
                $name = trim($item['name'] ?? '');
                $amount = isset($item['amount']) && is_numeric($item['amount']) ? (float)$item['amount'] : null;
                if ($name !== '' && $amount !== null) {
                    $customDeductions[] = [
                        'name' => $name,
                        'amount' => $amount,
                    ];
                }
            }
        }

        // Update the employee's set salary
        $employee = Employee::findOrFail($id);
        $employee->set_salary = $request->set_salary;
        $employee->save();

        // Create or update the payroll data
        PayrollData::updateOrCreate(
            ['employee_id' => $id],
            [
                'basic'             => $request->basic ?? 0,
                'medical'           => $request->medical ?? 0,
                'hra'               => $request->hra ?? 0,
                'conveyance'        => $request->conveyance ?? 0,
                'education'         => $request->education ?? 0,
                'executive'         => $request->executive ?? 0,
                'custom_allowances' => $customAllowances,
                'esi'               => $request->esi ?? 0,
                'pf'                => $request->pf ?? 0,
                'professional_tax'  => $request->professional_tax ?? 0,
                'tds'               => $request->tds ?? 0,
                'custom_deductions' => $customDeductions,
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
