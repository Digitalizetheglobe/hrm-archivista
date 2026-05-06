<?php

namespace App\Http\Controllers;

use App\Exports\InvoiceExport;
use App\Models\Allowance;
use App\Models\Commission;
use App\Models\Employee;
use App\Models\Loan;
use App\Mail\InvoiceSend;
use App\Models\AccountList;
use App\Models\Expense;
use App\Models\OtherPayment;
use App\Models\Overtime;
use App\Models\Invoice;
use App\Models\Resignation;
use App\Models\SaturationDeduction;
use App\Models\Termination;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('Manage Pay Slip') || \Auth::user()->type == 'employee') {
            $employees = Employee::where(
                [
                    'created_by' => \Auth::user()->creatorId(),
                ]
            )->first();

            $month = [
                '01' => 'JAN',
                '02' => 'FEB',
                '03' => 'MAR',
                '04' => 'APR',
                '05' => 'MAY',
                '06' => 'JUN',
                '07' => 'JUL',
                '08' => 'AUG',
                '09' => 'SEP',
                '10' => 'OCT',
                '11' => 'NOV',
                '12' => 'DEC',
            ];
            $currentyear = date("Y");
            $tempyear = intval($currentyear) - 2;
            $year = [];
            for ($i = 0; $i < 10; $i++) {
                $year[$tempyear + $i] = $tempyear + $i;
            }

            return view('invoice.index', compact('employees', 'month', 'year'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'month' => 'required',
                'year' => 'required',

            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $month = $request->month;
        $year  = $request->year;


        $formate_month_year = $year . '-' . $month;
        $validatePaysilp    = Invoice::where('salary_month', '=', $formate_month_year)->where('created_by', \Auth::user()->creatorId())->pluck('employee_id');
        $payslip_employee   = Employee::where('created_by', \Auth::user()->creatorId())->where('company_doj', '<=', date($year . '-' . $month . '-t'))->whereIn('employee_type', ['Contract', 'Consultant'])->count();
        if ($payslip_employee == 0) {
            return redirect()->route('invoice.index')->with('error', __('No contract or consultant employees found for invoice generation. Please ensure your employees are marked as "Contract" or "Consultant" type in their profile.'));
        }

        if ($payslip_employee > count($validatePaysilp)) {
            $employees = Employee::where('created_by', \Auth::user()->creatorId())
                ->where('company_doj', '<=', date($year . '-' . $month . '-t'))
                ->whereNotIn('employee_id', $validatePaysilp)
                ->where('set_salary', '>', 0)
                ->whereIn('employee_type', ['Contract', 'Consultant'])
                ->get();
                
            if ($employees->isEmpty()) {
                return redirect()->route('invoice.index')->with('info', __('No eligible contract employees found for invoice generation. Employees must have valid salaries set.'));
            }
            
            $generatedCount = 0;
            foreach ($employees as $employee) {

                $chek = Invoice::where(['employee_id' => $employee->id, 'salary_month' => $formate_month_year])->first();
                $terminationDate = Termination::where('employee_id', $employee->id)
                    ->whereDate('termination_date', '<=', Carbon::create($year, $month)->endOfMonth())
                    ->exists();

                $resignationDate = Resignation::where('employee_id', $employee->id)
                    ->whereDate('resignation_date', '<=', Carbon::create($year, $month)->endOfMonth())
                    ->exists();

                if ($terminationDate || $resignationDate) {
                    continue;
                }

                if (!$chek && $chek == null) {
                    $payslipEmployee                       = new Invoice();
                    $payslipEmployee->employee_id          = $employee->id;
                    $payslipEmployee->net_payble           = $employee->get_net_salary();
                    $payslipEmployee->salary_month         = $formate_month_year;
                    $payslipEmployee->status               = 0;
                    $payslipEmployee->basic_salary         = !empty($employee->set_salary) ? $employee->set_salary : 0;
                    $payslipEmployee->allowance            = Employee::allowance($employee->id);
                    $payslipEmployee->commission           = Employee::commission($employee->id);
                    $payslipEmployee->loan                 = Employee::loan($employee->id);
                    $payslipEmployee->saturation_deduction = Employee::saturation_deduction($employee->id);
                    $payslipEmployee->other_payment        = Employee::other_payment($employee->id);
                    $payslipEmployee->overtime             = Employee::overtime($employee->id);
                    $payslipEmployee->created_by           = \Auth::user()->creatorId();
                    $payslipEmployee->save();
                    $generatedCount++;

                    //Slack Notification
                    $setting  = Utility::settings(\Auth::user()->creatorId());
                    if (isset($setting['monthly_payslip_notification']) && $setting['monthly_payslip_notification'] == 1) {
                        $uArr = [
                            'year' => $formate_month_year,
                        ];
                        Utility::send_slack_msg('new_monthly_payslip', $uArr);
                    }
                    //Telegram Notification
                    $setting  = Utility::settings(\Auth::user()->creatorId());
                    if (isset($setting['telegram_monthly_payslip_notification']) && $setting['telegram_monthly_payslip_notification'] == 1) {

                        $uArr = [
                            'year' => $formate_month_year,
                        ];

                        Utility::send_telegram_msg('new_monthly_payslip', $uArr);
                    }

                    //twilio
                    $setting  = Utility::settings(\Auth::user()->creatorId());
                    $emp = Employee::where('id', $payslipEmployee->employee_id)->first();
                    if (isset($setting['twilio_monthly_payslip_notification']) && $setting['twilio_monthly_payslip_notification'] == 1) {

                        $uArr = [
                            'year' => $formate_month_year,
                        ];
                        Utility::send_twilio_msg($emp->phone, 'new_monthly_payslip', $uArr);
                    }

                    //webhook
                    $module = 'New Monthly Payslip';
                    $webhook =  Utility::webhookSetting($module);
                    if ($webhook) {
                        $parameter = json_encode($payslipEmployee);
                        $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                        if ($status == true) {
                            return redirect()->back()->with('success', __('Successfully generated :count invoices for contract and consultant employees with valid salaries.', ['count' => $generatedCount]));
                        } else {
                            return redirect()->back()->with('error', __('Webhook call failed.'));
                        }
                    }
                }
            }
            return redirect()->route('invoice.index')->with('success', __('Successfully generated :count invoices for contract and consultant employees with valid salaries.', ['count' => $generatedCount]));
        } else {
            return redirect()->route('invoice.index')->with('error', __('Invoices already created for all eligible contract and consultant employees for this month.'));
        }
    }

    public function destroy($id)
    {
        $payslip = Invoice::find($id);

        $payslip->delete();

        return true;
    }

    public function showemployee($paySlip)
    {

        $payslip = Invoice::find($paySlip);


        return view('invoice.show', compact('payslip'));
    }

    public function search_json(Request $request)
    {
        $formate_month_year = $request->datePicker;
        $validatePaysilp    = Invoice::where('salary_month', '=', $formate_month_year)->where('created_by', \Auth::user()->creatorId())->get()->toarray();
        $data = [];
        if (empty($validatePaysilp)) {
            $data = [];
            return;
        } else {
            $paylip_employee = Invoice::select(
                [
                    'employees.id',
                    'employees.employee_id',
                    'employees.name',
                    'employees.set_salary',
                    'in_voices.basic_salary',
                    'in_voices.net_payble',
                    'in_voices.id as pay_slip_id',
                    'in_voices.status',
                    'employees.user_id',
                ]
            )->leftjoin(
                'employees',
                function ($join) {
                    $join->on('employees.id', '=', 'in_voices.employee_id');
                }
            )
            ->where('in_voices.salary_month', '=', $formate_month_year)
            ->where('employees.created_by', \Auth::user()->creatorId())->get();

            foreach ($paylip_employee as $employee) {
                if (Auth::user()->type == 'employee') {
                    if (Auth::user()->id == $employee->user_id) {
                        $tmp   = [];
                        $tmp[] = $employee->id;
                        $tmp[] = \Auth::user()->employeeIdFormat($employee->employee_id);
                        $tmp[] = $employee->name;
                        $tmp[] = !empty($employee->basic_salary) ? \Auth::user()->priceFormat($employee->set_salary) : '-';
                        $tmp[] = !empty($employee->net_payble) ? \Auth::user()->priceFormat($employee->net_payble) : '-';
                        if ($employee->status == 1) {
                            $tmp[] = 'paid';
                        } else {
                            $tmp[] = 'unpaid';
                        }
                        $tmp[]  = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
                        $tmp['url']  = route('employee.show', Crypt::encrypt($employee->id));
                        $data[] = $tmp;
                    }
                } else {

                    $tmp   = [];
                    $tmp[] = $employee->id;
                    $tmp[] = \Auth::user()->employeeIdFormat($employee->employee_id);
                    $tmp[] = $employee->name;
                    $tmp[] = !empty($employee->basic_salary) ? \Auth::user()->priceFormat($employee->basic_salary) : '-';
                    $tmp[] = !empty($employee->net_payble) ? \Auth::user()->priceFormat($employee->net_payble) : '-';
                    if ($employee->status == 1) {
                        $tmp[] = 'Paid';
                    } else {
                        $tmp[] = 'UnPaid';
                    }
                    $tmp[]  = !empty($employee->pay_slip_id) ? $employee->pay_slip_id : 0;
                    $tmp['url']  = route('employee.show', Crypt::encrypt($employee->id));
                    $data[] = $tmp;
                }
            }

            return $data;
        }
    }

    public function paysalary($id, $date)
    {
        $employeePayslip = Invoice::where('employee_id', '=', $id)->where('created_by', \Auth::user()->creatorId())->where('salary_month', '=', $date)->first();
        $get_employee = Employee::where('id', $id)->where('created_by', \Auth::user()->creatorId())->first();
        $get_account = AccountList::where('id', $get_employee->account_type)->where('created_by', \Auth::user()->creatorId())->first();
        $initial_balance = !empty($get_account->initial_balance) ? $get_account->initial_balance : 0;
        if (!empty($employeePayslip)) {
            $accurateNetSalary = Utility::calculateNetInvoiceSalary($id, $date);
            $employeePayslip->net_payble = $accurateNetSalary;
            $employeePayslip->status = 1;
            $employeePayslip->save();

            if ($get_account) {
                $total_balance = $initial_balance - $accurateNetSalary;
                $get_account->initial_balance = $total_balance;
                $get_account->save();

                $set_expense = new Expense();
                $set_expense->account_id = $get_account->id;
                $set_expense->amount = $employeePayslip->net_payble;
                $set_expense->date = date('Y-m-d');
                $set_expense->expense_category_id = '';
                $set_expense->payee_id = $get_employee->id;
                $set_expense->payment_type_id = '';
                $set_expense->referal_id = '';
                $set_expense->description = '';
                $set_expense->created_by = $get_employee->created_by;
                $set_expense->save();
            }

            return redirect()->route('invoice.index')->with('success', __('Invoice Payment successfully.'));
        } else {
            return redirect()->route('invoice.index')->with('error', __('Invoice Payment failed.'));
        }
    }

    public function bulk_pay_create($date)
    {
        $Employees       = Invoice::where('salary_month', $date)->where('created_by', \Auth::user()->creatorId())->get();
        $unpaidEmployees = Invoice::where('salary_month', $date)->where('created_by', \Auth::user()->creatorId())->where('status', '=', 0)->get();

        return view('invoice.bulkcreate', compact('Employees', 'unpaidEmployees', 'date'));
    }

    public function bulkpayment(Request $request, $date)
    {
        $unpaidEmployees = Invoice::where('salary_month', $date)->where('created_by', \Auth::user()->creatorId())->where('status', '=', 0)->get();

        foreach ($unpaidEmployees as $employee) {
            $employee->status = 1;
            $employee->save();
        }

        return redirect()->route('invoice.index')->with('success', __('Invoice Bulk Payment successfully.'));
    }

    public function employeepayslip()
    {
        $employees = Employee::where(
            [
                'user_id' => \Auth::user()->id,
            ]
        )->first();

        $payslip = Invoice::where('employee_id', '=', $employees->id)->get();

        return view('invoice.employeepayslip', compact('payslip'));
    }

    public function pdf($id, $month)
    {
        $payslip  = Invoice::where('employee_id', $id)->where('salary_month', $month)->where('created_by', \Auth::user()->creatorId())->first();

        $employee = Employee::find($payslip->employee_id);

        $payslipDetail = Utility::employeeInvoiceDetail($id, $month);

        return view('invoice.pdf', compact('payslip', 'employee', 'payslipDetail'));
    }

    public function send($id, $month)
    {
        $payslip  = Invoice::where('employee_id', $id)->where('salary_month', $month)->where('created_by', \Auth::user()->creatorId())->first();
        $employee = Employee::find($payslip->employee_id);

        $payslip->name  = $employee->name;
        $payslip->email = $employee->email;

        $payslipId    = Crypt::encrypt($payslip->id);
        $payslip->url = route('invoice.payslipPdf', $payslipId);
        $setings = Utility::settings();

        if ($setings['new_payroll'] == 1) {
            $uArr = [
                'payslip_email' => $payslip->email,
                'name'  => $payslip->name,
                'url' => $payslip->url,
                'salary_month' => $payslip->salary_month,
            ];

            $resp = Utility::sendEmailTemplate('new_payroll', [$payslip->email], $uArr);
            return redirect()->back()->with('success', __('Invoice successfully sent.')  . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }

        return redirect()->back()->with('success', __('Invoice successfully sent.'));
    }

    public function payslipPdf($id)
    {
        $payslipId = Crypt::decrypt($id);
        $payslip  = Invoice::where('id', $payslipId)->first();
        $month = $payslip->salary_month;
        $employee = Employee::find($payslip->employee_id);

        $payslipDetail = Utility::employeeInvoiceDetail($payslip->employee_id, $month);

        return view('invoice.payslipPdf', compact('payslip', 'employee', 'payslipDetail'));
    }

    public function editEmployee($paySlip)
    {
        $payslip = Invoice::find($paySlip);

        return view('invoice.salaryEdit', compact('payslip'));
    }

    public function updateEmployee(Request $request, $id)
    {
        if (isset($request->allowance) && !empty($request->allowance)) {
            $allowances   = $request->allowance;
            $allowanceIds = $request->allowance_id;
            foreach ($allowances as $k => $allownace) {
                $allowanceData         = Allowance::find($allowanceIds[$k]);
                $allowanceData->amount = $allownace;
                $allowanceData->save();
            }
        }


        if (isset($request->commission) && !empty($request->commission)) {
            $commissions   = $request->commission;
            $commissionIds = $request->commission_id;
            foreach ($commissions as $k => $commission) {
                $commissionData         = Commission::find($commissionIds[$k]);
                $commissionData->amount = $commission;
                $commissionData->save();
            }
        }

        if (isset($request->loan) && !empty($request->loan)) {
            $loans   = $request->loan;
            $loanIds = $request->loan_id;
            foreach ($loans as $k => $loan) {
                $loanData         = Loan::find($loanIds[$k]);
                $loanData->amount = $loan;
                $loanData->save();
            }
        }


        if (isset($request->saturation_deductions) && !empty($request->saturation_deductions)) {
            $saturation_deductionss   = $request->saturation_deductions;
            $saturation_deductionsIds = $request->saturation_deductions_id;
            foreach ($saturation_deductionss as $k => $saturation_deductions) {

                $saturation_deductionsData         = SaturationDeduction::find($saturation_deductionsIds[$k]);
                $saturation_deductionsData->amount = $saturation_deductions;
                $saturation_deductionsData->save();
            }
        }


        if (isset($request->other_payment) && !empty($request->other_payment)) {
            $other_payments   = $request->other_payment;
            $other_paymentIds = $request->other_payment_id;
            foreach ($other_payments as $k => $other_payment) {
                $other_paymentData         = OtherPayment::find($other_paymentIds[$k]);
                $other_paymentData->amount = $other_payment;
                $other_paymentData->save();
            }
        }


        if (isset($request->rate) && !empty($request->rate)) {
            $rates   = $request->rate;
            $rateIds = $request->rate_id;
            $hourses = $request->hours;

            foreach ($rates as $k => $rate) {
                $overtime        = Overtime::find($rateIds[$k]);
                $overtime->rate  = $rate;
                $overtime->hours = $hourses[$k];
                $overtime->save();
            }
        }

        $payslipEmployee                       = Invoice::find($request->payslip_id);
        $payslipEmployee->allowance            = Employee::allowance($payslipEmployee->employee_id);
        $payslipEmployee->commission           = Employee::commission($payslipEmployee->employee_id);
        $payslipEmployee->loan                 = Employee::loan($payslipEmployee->employee_id);
        $payslipEmployee->saturation_deduction = Employee::saturation_deduction($payslipEmployee->employee_id);
        $payslipEmployee->other_payment        = Employee::other_payment($payslipEmployee->employee_id);
        $payslipEmployee->overtime             = Employee::overtime($payslipEmployee->employee_id);
        $payslipEmployee->net_payble           = Employee::find($payslipEmployee->employee_id)->get_net_salary();
        $payslipEmployee->save();

        return redirect()->route('invoice.index')->with('success', __('Contract or Consultant employee invoice successfully updated.'));
    }

    public function InvoiceExport(Request $request)
    {
        $name = 'invoice_' . date('Y-m-d i:h:s');
        $data = \Excel::download(new InvoiceExport($request), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }
}
