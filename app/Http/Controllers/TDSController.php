<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Tds;
use App\Models\TdsAllowance;
use App\Models\TdsDeduction;
use App\Models\TdsPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TDSController extends Controller
{
    public function index()
    {
        if (\Auth::user()->type == 'company') {
            $employees = Employee::leftjoin('tds', 'employees.id', '=', 'tds.employee_id')
                ->where('employees.created_by', \Auth::user()->creatorId())
                ->whereNotNull('employees.set_salary')
                ->where('employees.set_salary', '>', 0)
                ->select('employees.*', 'tds.tds_type')
                ->get();
            
            // Calculate total taxable for each employee
            foreach ($employees as $employee) {
                if ($employee->tds_type !== null) {
                    // Get employee allowances and deductions
                    $allowances = \App\Models\TdsAllowance::where('employee_id', $employee->id)->get();
                    $deductions = \App\Models\TdsDeduction::where('employee_id', $employee->id)->get();
                    $tdsPayments = \App\Models\TdsPayment::where('employee_id', $employee->id)->get();
                    
                    // Calculate total taxable based on regime type
                    if ($employee->tds_type == 0) {
                        // Old Regime formula
                        $totalTaxable = ($employee->set_salary * 12) + $allowances->sum('amount') - (2500 + 50000 + $deductions->sum('amount'));
                        
                        // Calculate tax based on Old Regime slabs
                        if ($totalTaxable <= 250000) {
                            $tax = 0;
                        } elseif ($totalTaxable <= 500000) {
                            $tax = ($totalTaxable - 250000) * 0.05;
                        } elseif ($totalTaxable <= 1000000) {
                            $tax = 12500 + (($totalTaxable - 500000) * 0.20);
                        } else {
                            $tax = 112500 + (($totalTaxable - 1000000) * 0.30);
                        }
                    } else {
                        // New Regime formula  
                        $totalTaxable = ($employee->set_salary * 12) + $allowances->sum('amount') - 75000 - $deductions->sum('amount');
                        
                        // Calculate tax based on NEW REGIME slabs - simplified logic
                        $tax = 0;
                        
                        if ($totalTaxable > 300000 && $totalTaxable <= 600000) {
                            $tax = ($totalTaxable - 300000) * 0.05;
                        } elseif ($totalTaxable > 600000 && $totalTaxable <= 900000) {
                            $tax = 15000 + (($totalTaxable - 600000) * 0.10);
                        } elseif ($totalTaxable > 900000 && $totalTaxable <= 1200000) {
                            $tax = 45000 + (($totalTaxable - 900000) * 0.15);
                        } elseif ($totalTaxable > 1200000 && $totalTaxable <= 1500000) {
                            $tax = 90000 + (($totalTaxable - 1200000) * 0.20);
                        } elseif ($totalTaxable > 1500000) {
                            $tax = 150000 + (($totalTaxable - 1500000) * 0.30);
                        }
                    }
                    
                    // Calculate cess and total tax amount
                    $cess = round($tax * 0.04);
                    $totalTaxAmount = round($tax + $cess);
                    
                    // Calculate total paid based on actual paid amounts
                    $paidMonths = $tdsPayments->where('is_paid', true)->pluck('month_number')->toArray();
                    $totalPaid = 0;
                    foreach ($tdsPayments->where('is_paid', true) as $payment) {
                        $totalPaid += round($payment->amount);
                    }
                    
                    $tdsBalance = $totalTaxAmount - $totalPaid;
                    $remainingMonths = 12 - count($paidMonths);
                    
                    // Calculate current month TDS amount
                    $currentMonthTds = 0;
                    if ($remainingMonths > 0) {
                        $currentMonthTds = round($tdsBalance / $remainingMonths);
                    }
                    
                    $employee->total_taxable = $totalTaxable;
                    $employee->total_tax_amount = $totalTaxAmount;
                    $employee->monthly_tds = $currentMonthTds;
                }
            }

            return view('tds.index', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        // Only allow company users
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $employee = Employee::find($id);
        
        if (!$employee || $employee->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Employee not found.'));
        }

        // Get employee allowances
        $allowances = TdsAllowance::where('employee_id', $id)->get();
        
        // Get employee deductions
        $deductions = TdsDeduction::where('employee_id', $id)->get();
        
        // Get employee TDS payments
        $tdsPayments = TdsPayment::where('employee_id', $id)->get();

        return view('tds.old_regime', compact('employee', 'allowances', 'deductions', 'tdsPayments'));
    }

    public function newRegime($id)
    {
        // Only allow company users
        if (\Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $employee = Employee::find($id);
        
        if (!$employee || $employee->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Employee not found.'));
        }

        // Get employee allowances
        $allowances = TdsAllowance::where('employee_id', $id)->get();
        
        // Get employee deductions
        $deductions = TdsDeduction::where('employee_id', $id)->get();
        
        // Get employee TDS payments
        $tdsPayments = TdsPayment::where('employee_id', $id)->get();

        return view('tds.new_regime', compact('employee', 'allowances', 'deductions', 'tdsPayments'));
    }

    public function oldRegime($id)
    {
        $employee = Employee::find($id);
        if (!$employee || $employee->created_by != \Auth::user()->creatorId()) {
            abort(404);
        }
        
        // Get employee allowances
        $allowances = TdsAllowance::where('employee_id', $id)->get();
        
        // Get employee deductions
        $deductions = TdsDeduction::where('employee_id', $id)->get();
        
        // Get employee TDS payments
        $tdsPayments = TdsPayment::where('employee_id', $id)->get();
        
        return view('tds.old_regime', compact('employee', 'allowances', 'deductions', 'tdsPayments'));
    }

    public function saveTdsType(Request $request)
    {
        \Log::info('TDS Save Request received: ' . json_encode([
            'employee_id' => $request->employee_id,
            'tds_type' => $request->tds_type,
            'user_type' => \Auth::user()->type,
            'user_id' => \Auth::id()
        ]));
        
        // Only allow company users
        if (\Auth::user()->type != 'company') {
            \Log::error('Permission denied for user type: ' . \Auth::user()->type);
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'tds_type' => 'required|integer|in:0,1',
        ]);

        try {
            $employee = Employee::find($request->employee_id);
            if (!$employee || $employee->created_by != \Auth::user()->creatorId()) {
                \Log::error('Employee not found or access denied. Employee ID: ' . $request->employee_id);
                return response()->json(['error' => __('Employee not found.')], 404);
            }

            // Check if TDS record already exists for this employee
            $existingTds = Tds::where('employee_id', $request->employee_id)->first();

            if ($existingTds) {
                // Update existing record
                \Log::info('Updating existing TDS record for employee: ' . $request->employee_id);
                $existingTds->update([
                    'tds_type' => $request->tds_type,
                ]);
                $message = __('TDS type updated successfully.');
            } else {
                // Create new TDS record
                \Log::info('Creating new TDS record for employee: ' . $request->employee_id);
                Tds::create([
                    'employee_id' => $request->employee_id,
                    'tds_type' => $request->tds_type,
                ]);
                $message = __('TDS type saved successfully.');
            }

            \Log::info('TDS type saved successfully: ' . json_encode([
                'employee_id' => $request->employee_id,
                'tds_type' => $request->tds_type,
                'message' => $message
            ]));

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            \Log::error('Save TDS type error: ' . $e->getMessage());
            return response()->json([
                'error' => __('Error saving TDS type.')
            ], 500);
        }
    }

    public function logClick(Request $request)
    {
        \Log::info('TDS Edit Button Clicked: ' . json_encode([
            'action' => $request->action,
            'employee_id' => $request->employee_id,
            'employee_name' => $request->employee_name,
            'user_id' => Auth::id(),
            'timestamp' => now()
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Click logged successfully'
        ]);
    }

    public function deleteDeduction($id)
    {
        // Only allow company users
        if (\Auth::user()->type != 'company') {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        $deduction = \App\Models\TdsDeduction::find($id);
        
        if (!$deduction) {
            return response()->json([
                'error' => __('Deduction not found.')
            ], 404);
        }

        // Check if deduction belongs to user's company
        $employee = Employee::find($deduction->employee_id);
        if (!$employee || $employee->created_by != \Auth::user()->creatorId()) {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        $deduction->delete();

        return response()->json([
            'success' => true,
            'message' => __('Deduction deleted successfully.')
        ]);
    }

    public function deleteAllowance($id)
    {
        // Only allow company users
        if (\Auth::user()->type != 'company') {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        $allowance = \App\Models\TdsAllowance::find($id);
        
        if (!$allowance) {
            return response()->json([
                'error' => __('Allowance not found.')
            ], 404);
        }

        // Check if allowance belongs to user's company
        $employee = Employee::find($allowance->employee_id);
        if (!$employee || $employee->created_by != \Auth::user()->creatorId()) {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        $allowance->delete();

        return response()->json([
            'success' => true,
            'message' => __('Allowance deleted successfully.')
        ]);
    }

    public function storeDeduction(Request $request)
    {
        // Only allow company users
        if (\Auth::user()->type != 'company') {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        // Validate request data
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'deduction_type' => 'required|string|max:255'
        ]);

        // Check if employee belongs to user's company
        $employee = Employee::find($request->employee_id);
        if (!$employee || $employee->created_by != \Auth::user()->creatorId()) {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        // Create new deduction
        $deduction = \App\Models\TdsDeduction::create([
            'employee_id' => $request->employee_id,
            'amount' => $request->amount,
            'description' => $request->description ?? '',
            'deduction_type' => $request->deduction_type,
            'created_by' => \Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Deduction added successfully.'),
            'deduction' => $deduction
        ]);
    }

    public function storeAllowance(Request $request)
    {
        // Only allow company users
        if (\Auth::user()->type != 'company') {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        // Validate request data
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'allowance_type' => 'required|string|max:255'
        ]);

        // Check if employee belongs to user's company
        $employee = Employee::find($request->employee_id);
        if (!$employee || $employee->created_by != \Auth::user()->creatorId()) {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        // Create new allowance
        $allowance = \App\Models\TdsAllowance::create([
            'employee_id' => $request->employee_id,
            'amount' => $request->amount,
            'description' => $request->description ?? '',
            'allowance_type' => $request->allowance_type,
            'created_by' => \Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Allowance added successfully.'),
            'allowance' => $allowance
        ]);
    }

    public function updateDeduction(Request $request, $id)
    {
        // Only allow company users
        if (\Auth::user()->type != 'company') {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        // Validate request data
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'deduction_type' => 'required|string|max:255'
        ]);

        $deduction = \App\Models\TdsDeduction::find($id);
        
        if (!$deduction) {
            return response()->json([
                'error' => __('Deduction not found.')
            ], 404);
        }

        // Check if deduction belongs to user's company
        $employee = Employee::find($deduction->employee_id);
        if (!$employee || $employee->created_by != \Auth::user()->creatorId()) {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        // Update deduction
        $deduction->update([
            'amount' => $request->amount,
            'description' => $request->description ?? $deduction->description,
            'deduction_type' => $request->deduction_type
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Deduction updated successfully.'),
            'deduction' => $deduction
        ]);
    }

    public function updateAllowance(Request $request, $id)
    {
        // Only allow company users
        if (\Auth::user()->type != 'company') {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        // Validate request data
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'allowance_type' => 'required|string|max:255'
        ]);

        $allowance = \App\Models\TdsAllowance::find($id);
        
        if (!$allowance) {
            return response()->json([
                'error' => __('Allowance not found.')
            ], 404);
        }

        // Check if allowance belongs to user's company
        $employee = Employee::find($allowance->employee_id);
        if (!$employee || $employee->created_by != \Auth::user()->creatorId()) {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        // Update allowance
        $allowance->update([
            'amount' => $request->amount,
            'description' => $request->description ?? $allowance->description,
            'allowance_type' => $request->allowance_type
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Allowance updated successfully.'),
            'allowance' => $allowance
        ]);
    }

    public function togglePayment(Request $request)
    {
        // Only allow company users
        if (\Auth::user()->type != 'company') {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        // Validate request data
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month_number' => 'required|integer|min:1|max:12',
            'amount' => 'required|numeric|min:0'
        ]);

        $employeeId = $request->employee_id;
        $monthNumber = $request->month_number;
        $amount = $request->amount;

        // Check if employee belongs to user's company
        $employee = Employee::find($employeeId);
        if (!$employee || $employee->created_by != \Auth::user()->creatorId()) {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        // Find or create TDS payment record
        $payment = \App\Models\TdsPayment::where('employee_id', $employeeId)
            ->where('month_number', $monthNumber)
            ->first();

        if (!$payment) {
            // Create new payment record
            $payment = \App\Models\TdsPayment::create([
                'employee_id' => $employeeId,
                'month_number' => $monthNumber,
                'month_name' => date('F', mktime(0, 0, 0, $monthNumber, 1)),
                'amount' => $amount,
                'is_paid' => true,
                'created_by' => \Auth::user()->creatorId()
            ]);
        } else {
            // Toggle payment status
            $payment->update([
                'is_paid' => !$payment->is_paid,
                'amount' => $amount
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $payment->is_paid ? __('TDS payment marked as paid.') : __('TDS payment marked as unpaid.'),
            'payment' => $payment
        ]);
    }

    public function updateMonthlyTds(Request $request)
    {
        // Only allow company users
        if (\Auth::user()->type != 'company') {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        // Validate request data
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'monthly_tds' => 'required|array',
            'monthly_tds.*.month' => 'required|integer|min:1|max:12',
            'monthly_tds.*.amount' => 'required|numeric|min:0'
        ]);

        $employeeId = $request->employee_id;
        $monthlyTds = $request->monthly_tds;

        // Check if employee belongs to user's company
        $employee = Employee::find($employeeId);
        if (!$employee || $employee->created_by != \Auth::user()->creatorId()) {
            return response()->json([
                'error' => __('Permission denied.')
            ], 403);
        }

        // Update or create monthly TDS records
        foreach ($monthlyTds as $monthlyData) {
            $monthNumber = $monthlyData['month'];
            $amount = $monthlyData['amount'];

            $payment = \App\Models\TdsPayment::where('employee_id', $employeeId)
                ->where('month_number', $monthNumber)
                ->first();

            if ($payment) {
                // Update existing payment
                $payment->update([
                    'amount' => $amount,
                    'is_paid' => $amount > 0 // Mark as paid if amount > 0
                ]);
            } else {
                // Create new payment record
                \App\Models\TdsPayment::create([
                    'employee_id' => $employeeId,
                    'month_number' => $monthNumber,
                    'month_name' => date('F', mktime(0, 0, 0, $monthNumber, 1)),
                    'amount' => $amount,
                    'is_paid' => $amount > 0, // Mark as paid if amount > 0
                    'created_by' => \Auth::user()->creatorId()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('Monthly TDS updated successfully.'),
            'monthly_tds' => $monthlyTds
        ]);
    }
}
