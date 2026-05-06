<?php

namespace App\Exports;


use App\Models\Employee;
use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvoiceExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $data;

    function __construct($data) {
        $this->data = $data;
    }

    public function collection()
    {
        $request=$this->data;

        $data = Invoice::where('created_by', \Auth::user()->creatorId());

        if(isset($request->filter_month) && !empty($request->filter_month)){
            $month=$request->filter_month;
        }else{
            $month=date('m', strtotime('last month'));
        }

        if(isset($request->filter_year) && !empty($request->filter_year)){
            $year=$request->filter_year;
        }else{
            $year=date('Y');
        }
        $formate_month_year = $year . '-' . $month;
        $data->where('salary_month', '=', $formate_month_year);
        $data=$data->get();
        $result = array();
        foreach($data as $k => $invoice)
        {
            $result[] = array(
                'employee_id'=> !empty($invoice->employees) ? \Auth::user()->employeeIdFormat($invoice->employees->employee_id) : '',
                'employee_name' => (!empty($invoice->employees)) ? $invoice->employees->name : '',
                'basic_salary' => \Auth::user()->priceFormat($invoice->basic_salary),
                'net_salary' =>  \Auth::user()->priceFormat($invoice->net_payble),
                'status' =>  $invoice->status == 0 ? 'UnPaid' :  'Paid',
                'account_holder_name' =>  (!empty($invoice->employees)) ? $invoice->employees->account_holder_name : '',
                'account_number' =>  (!empty($invoice->employees)) ? $invoice->employees->account_number : '',
                'bank_name' =>  (!empty($invoice->employees)) ? $invoice->employees->bank_name : '',
                'bank_identifier_code' => (!empty($invoice->employees)) ? $invoice->employees->bank_identifier_code : '',
                'branch_location' =>   (!empty($invoice->employees)) ? $invoice->employees->branch_location : '',
                'tax_payer_id' =>  (!empty($invoice->employees)) ? $invoice->employees->tax_payer_id : '',

            );
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            "EMP ID",
            "Name",
//            "Payroll Type",
            "Salary",
            "Net Salary",
            "Status",
            "Account Holder Name",
            "Account Number",
            "Bank Name",
            "Bank Identifier Code",
            "Branch Location",
            "Tax Payer Id",

        ];
    }
}
