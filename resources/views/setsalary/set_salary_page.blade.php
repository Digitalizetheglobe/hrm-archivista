@extends('layouts.admin')

@section('page-title')
    {{ __('Set Salary') }} — {{ $employee->name }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('setsalary.index') }}">{{ __('Employee Salary') }}</a></li>
    <li class="breadcrumb-item">{{ __('Set Salary') }}</li>
@endsection

@section('content')

<style>
/* ===== Premium Set Salary Page ===== */
.ss-page-header {
    background: linear-gradient(135deg, #f6821f 0%, #e05c00 100%);
    border-radius: 16px;
    padding: 28px 32px;
    margin-bottom: 28px;
    box-shadow: 0 8px 32px rgba(246,130,31,0.28);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    position: relative;
    overflow: hidden;
}
.ss-page-header::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:220px; height:220px; background:rgba(255,255,255,0.07); border-radius:50%;
}
.ss-page-header::after {
    content:''; position:absolute; bottom:-80px; left:-30px;
    width:260px; height:260px; background:rgba(255,255,255,0.05); border-radius:50%;
}
.ss-header-info { position:relative; z-index:1; }
.ss-employee-name { font-size:1.5rem; font-weight:800; color:#fff; margin:0; }
.ss-employee-sub { font-size:0.82rem; color:rgba(255,255,255,0.72); margin-top:4px; }
.ss-badge-id {
    background:rgba(255,255,255,0.18); color:#fff;
    border-radius:20px; padding:3px 14px; font-size:0.78rem;
    font-weight:600; display:inline-block; margin-top:8px;
}
.ss-salary-box { position:relative; z-index:1; }
.ss-salary-input-label { font-size:0.75rem; color:#fff; font-weight:700; text-transform:uppercase; letter-spacing:1.2px; margin-bottom:8px; }
.ss-salary-input-wrap {
    display:flex; align-items:center;
    background:#fff; border-radius:8px;
    padding:4px 16px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
    transition: box-shadow 0.2s, transform 0.2s;
}
.ss-salary-input-wrap:focus-within {
    box-shadow:0 6px 20px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}
.ss-currency-symbol {
    color:#64748b; font-weight:700; font-size:1.1rem; margin-right:8px;
}
.ss-salary-input {
    border:none; background:transparent;
    color:#2d3748; padding:8px 0;
    font-size:1.25rem; font-weight:700; width:180px;
    outline:none;
}
.ss-salary-input::placeholder { color:#cbd5e1; }

/* Hide number input arrows */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
}
input[type=number] {
    -moz-appearance: textfield;
}

/* Tables */
.ss-section-card {
    background:#fff; border-radius:16px;
    border:1.5px solid #f0f2f5;
    box-shadow:0 4px 24px rgba(0,0,0,0.06);
    margin-bottom:24px; overflow:hidden;
}
.ss-section-header {
    padding:18px 24px;
    border-bottom:1.5px solid #f0f2f5;
    display:flex; align-items:center; justify-content:space-between;
    gap:12px;
}
.ss-section-icon {
    width:40px; height:40px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:1rem;
}
.ss-section-title { font-size:1rem; font-weight:700; color:#2d3748; margin:0; }
.ss-section-subtitle { font-size:0.75rem; color:#718096; margin:0; }
.ss-table { width:100%; border-collapse:collapse; }
.ss-table thead th {
    background:#f8fafc; padding:12px 24px;
    font-size:0.72rem; font-weight:700; text-transform:uppercase;
    letter-spacing:1px; color:#64748b;
    border-bottom:1.5px solid #e9ecef;
}
.ss-table thead th:nth-child(2) { text-align:right; }
.ss-table tbody tr {
    border-bottom:1px solid #f0f4f8;
    transition:background 0.15s;
}
.ss-table tbody tr:last-child { border-bottom:none; }
.ss-table tbody tr:hover { background:#fef9f4; }
.ss-table tbody td { padding:14px 24px; vertical-align:middle; }
.ss-allowance-name { font-weight:600; color:#2d3748; font-size:0.88rem; }
.ss-allowance-name small { display:block; font-weight:400; color:#94a3b8; font-size:0.72rem; }
.ss-input-wrap { display:flex; align-items:center; justify-content:flex-end; gap:6px; }
.ss-input {
    border:1.5px solid #e2e8f0; border-radius:8px;
    padding:8px 12px; font-size:0.88rem; color:#2d3748;
    background:#fafbfc; width:120px; text-align:right;
    transition:border-color 0.2s, box-shadow 0.2s;
    outline:none;
}
.ss-input:focus {
    border-color:#f6821f;
    box-shadow:0 0 0 3px rgba(246,130,31,0.13);
    background:#fff;
}
.ss-input-unit {
    font-size:0.75rem; font-weight:700; color:#94a3b8;
    min-width:24px; text-align:left;
}

/* Custom Row Styles */
.ss-custom-name {
    border:1.5px solid #e2e8f0; border-radius:8px;
    padding:8px 12px; font-size:0.88rem; font-weight:500;
    color:#2d3748; background:#fafbfc;
    width:100%; max-width:320px;
    transition:border-color 0.2s, box-shadow 0.2s;
    outline:none;
}
.ss-custom-name:focus {
    border-color:#f6821f;
    box-shadow:0 0 0 3px rgba(246,130,31,0.13);
    background:#fff;
}
.btn-remove-custom {
    color:#ef4444; border:none; background:transparent;
    font-size:0.95rem; padding:4px 8px; border-radius:6px;
    cursor:pointer; transition:transform 0.15s, color 0.15s, background 0.15s;
}
.btn-remove-custom:hover {
    color:#b91c1c; background:#fee2e2; transform:scale(1.1);
}
.ss-btn-add {
    font-size:0.8rem; font-weight:600; padding:6px 14px;
    border-radius:8px; border:1.5px solid currentColor;
    background:transparent; display:inline-flex; align-items:center; gap:6px;
    transition:all 0.2s;
}
.ss-btn-add-allowance { color:#10b981; }
.ss-btn-add-allowance:hover { background:#10b981; color:#fff; }
.ss-btn-add-deduction { color:#ef4444; }
.ss-btn-add-deduction:hover { background:#ef4444; color:#fff; }

/* Deduction row */
.deduct-icon { color:#e74a3b; }
.allow-icon  { color:#1cc88a; }

/* Submit bar */
.ss-submit-bar {
    background:#fff; border-radius:14px;
    border:1.5px solid #f0f2f5; box-shadow:0 4px 20px rgba(0,0,0,0.07);
    padding:20px 28px; display:flex; align-items:center; justify-content:flex-end; gap:12px;
}
.ss-btn-cancel {
    padding:10px 26px; border-radius:9px; font-weight:600;
    background:#f1f5f9; color:#64748b; border:none; font-size:0.88rem;
    transition:background 0.2s;
}
.ss-btn-cancel:hover { background:#e2e8f0; }
.ss-btn-save {
    padding:10px 32px; border-radius:9px; font-weight:700;
    background:linear-gradient(135deg,#f6821f,#e05c00); color:#fff;
    border:none; font-size:0.88rem;
    box-shadow:0 4px 14px rgba(246,130,31,0.35);
    transition:transform 0.15s, box-shadow 0.2s;
}
.ss-btn-save:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(246,130,31,0.4); }
</style>

<form method="POST" action="{{ route('setsalary.save-payroll', $employee->id) }}">
@csrf

{{-- ===== TOP: Employee Info + Set Salary Box ===== --}}
<div class="ss-page-header">
    <div class="ss-header-info">
        <p class="ss-employee-name">{{ $employee->name }}</p>
        <p class="ss-employee-sub">{{ $employee->email ?? 'N/A' }}
            @if($employee->department)
                &nbsp;&middot;&nbsp; {{ $employee->department->name }}
            @endif
        </p>
        <span class="ss-badge-id">
            <i class="fas fa-id-badge me-1"></i>
            {{ \Auth::user() ? \Auth::user()->employeeIdFormat($employee->employee_id) : $employee->employee_id }}
        </span>
    </div>

    <div class="ss-salary-box">
        <div class="d-flex align-items-center justify-content-between mb-1">
            <div class="ss-salary-input-label mb-0"><i class="fas fa-rupee-sign me-1"></i>Set Salary (Monthly)</div>
            <button type="button" class="btn btn-sm btn-outline-light ms-2 px-2 py-0" style="font-size:0.75rem; border-radius:6px;" onclick="autoCalculateBreakdown(false)" title="Calculate standard breakdown">
                <i class="fas fa-calculator me-1"></i>Auto Calculate
            </button>
        </div>
        <div class="ss-salary-input-wrap">
            <span class="ss-currency-symbol">₹</span>
            <input type="number" id="set_salary" name="set_salary" class="ss-salary-input"
                   placeholder="0.00" step="any" min="0"
                   value="{{ old('set_salary', $employee->set_salary ?? '') }}"
                   required>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    $customAllowances = old('custom_allowances', $payrollData->custom_allowances ?? []);
    if (is_string($customAllowances)) {
        $customAllowances = json_decode($customAllowances, true) ?: [];
    }
    $customDeductions = old('custom_deductions', $payrollData->custom_deductions ?? []);
    if (is_string($customDeductions)) {
        $customDeductions = json_decode($customDeductions, true) ?: [];
    }
@endphp

{{-- ===== TABLE 1: Allowances ===== --}}
<div class="ss-section-card">
    <div class="ss-section-header">
        <div class="d-flex align-items-center gap-3">
            <div class="ss-section-icon" style="background:rgba(28,200,138,0.12);">
                <i class="fas fa-plus-circle allow-icon"></i>
            </div>
            <div>
                <p class="ss-section-title">Allowances</p>
                <p class="ss-section-subtitle">Enter the fixed allowance amounts for each component</p>
            </div>
        </div>
        <button type="button" class="ss-btn-add ss-btn-add-allowance" id="btnAddAllowance">
            <i class="fas fa-plus"></i> Add Allowance
        </button>
    </div>
    <table class="ss-table" id="allowanceTable">
        <thead>
            <tr>
                <th>Allowance</th>
                <th>Amount (₹)</th>
                <th style="width: 44px; text-align: center;"></th>
            </tr>
        </thead>
        <tbody id="fixedAllowanceBody">
            @php
                $allowances = [
                    'basic'      => ['label' => 'Basic',       'desc' => 'Core salary component'],
                    'medical'    => ['label' => 'Medical',     'desc' => 'Medical reimbursement'],
                    'hra'        => ['label' => 'HRA',         'desc' => 'House Rent Allowance'],
                    'conveyance' => ['label' => 'Conveyance',  'desc' => 'Travel/transport allowance'],
                    'education'  => ['label' => 'Education',   'desc' => 'Education allowance'],
                    'executive'  => ['label' => 'Executive',   'desc' => 'Executive allowance'],
                ];
            @endphp

            @foreach($allowances as $field => $info)
            @php
                $savedVal = isset($payrollData->$field) ? (float) $payrollData->$field : null;
                $defaultVal = isset($defaultComponents[$field]) ? (float) $defaultComponents[$field] : null;
                $dispVal = '';
                if ($savedVal !== null && $savedVal > 0) {
                    $dispVal = number_format($savedVal, 2, '.', '');
                } elseif ($defaultVal !== null && $defaultVal > 0) {
                    $dispVal = number_format($defaultVal, 2, '.', '');
                }
            @endphp
            <tr>
                <td>
                    <div class="ss-allowance-name">
                        {{ $info['label'] }}
                        <small>{{ $info['desc'] }}</small>
                    </div>
                </td>
                <td>
                    <div class="ss-input-wrap">
                        <span class="ss-input-unit" style="margin-right:0;margin-left:0;color:#1cc88a;font-size:0.9rem;">₹</span>
                        <input type="number" id="{{ $field }}_amt" name="{{ $field }}"
                               class="ss-input allowance-amt" step="any" min="0"
                               placeholder="0.00"
                               value="{{ old($field, $dispVal) }}"
                               oninput="recalcAllowances()">
                    </div>
                </td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
        <tbody id="customAllowanceBody">
            @foreach($customAllowances as $idx => $cAllow)
            @php
                $cName = $cAllow['name'] ?? '';
                $cAmt = isset($cAllow['amount']) ? number_format((float) $cAllow['amount'], 2, '.', '') : '';
            @endphp
            <tr class="custom-allowance-row">
                <td>
                    <input type="text" name="custom_allowances[{{ $idx }}][name]"
                           class="ss-custom-name" placeholder="Allowance Name (e.g. Bonus, Fuel, Special)"
                           value="{{ $cName }}" required>
                </td>
                <td>
                    <div class="ss-input-wrap">
                        <span class="ss-input-unit" style="margin-right:0;margin-left:0;color:#1cc88a;font-size:0.9rem;">₹</span>
                        <input type="number" name="custom_allowances[{{ $idx }}][amount]"
                               class="ss-input allowance-amt custom-allowance-amt" step="any" min="0"
                               placeholder="0.00" value="{{ $cAmt }}"
                               oninput="recalcAllowances()" required>
                    </div>
                </td>
                <td style="text-align: center;">
                    <button type="button" class="btn-remove-custom" onclick="removeRow(this, 'allowance')" title="Remove">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#f8fafc; border-top:2px solid #e2e8f0;">
                <td style="font-weight:800; color:#2d3748; padding:14px 24px;">Total</td>
                <td style="font-weight:800; color:#10b981; padding:14px 24px; text-align:right;">₹<span id="allowance_total">0.00</span></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- ===== TABLE 2: Deductions ===== --}}
<div class="ss-section-card">
    <div class="ss-section-header">
        <div class="d-flex align-items-center gap-3">
            <div class="ss-section-icon" style="background:rgba(231,74,59,0.10);">
                <i class="fas fa-minus-circle deduct-icon"></i>
            </div>
            <div>
                <p class="ss-section-title">Deductions</p>
                <p class="ss-section-subtitle">Enter the fixed deduction amounts for statutory components</p>
            </div>
        </div>
        <button type="button" class="ss-btn-add ss-btn-add-deduction" id="btnAddDeduction">
            <i class="fas fa-plus"></i> Add Deduction
        </button>
    </div>
    <table class="ss-table" id="deductionTable">
        <thead>
            <tr>
                <th>Deduction</th>
                <th>Amount (₹)</th>
                <th style="width: 44px; text-align: center;"></th>
            </tr>
        </thead>
        <tbody id="fixedDeductionBody">
            @php
                $deductions = [
                    'esi'              => ['label' => 'ESI',               'desc' => 'Employee State Insurance'],
                    'pf'               => ['label' => 'PF',                'desc' => 'Provident Fund'],
                    'professional_tax' => ['label' => 'Professional Tax',  'desc' => 'State professional tax'],
                    'tds'              => ['label' => 'TDS',               'desc' => 'Tax Deducted at Source'],
                ];
            @endphp

            @foreach($deductions as $field => $info)
            @php
                $savedVal = isset($payrollData->$field) ? (float) $payrollData->$field : null;
                $defaultVal = isset($defaultComponents[$field]) ? (float) $defaultComponents[$field] : null;
                $dispVal = '';
                if ($savedVal !== null && $savedVal > 0) {
                    $dispVal = number_format($savedVal, 2, '.', '');
                } elseif ($defaultVal !== null && $defaultVal > 0) {
                    $dispVal = number_format($defaultVal, 2, '.', '');
                }
            @endphp
            <tr>
                <td>
                    <div class="ss-allowance-name">
                        {{ $info['label'] }}
                        <small>{{ $info['desc'] }}</small>
                    </div>
                </td>
                <td>
                    <div class="ss-input-wrap">
                        <span class="ss-input-unit" style="margin-right:0;margin-left:0;color:#e74a3b;font-size:0.9rem;">₹</span>
                        <input type="number" id="{{ $field }}_amt" name="{{ $field }}"
                               class="ss-input deduction-amt" step="any" min="0"
                               placeholder="0.00"
                               value="{{ old($field, $dispVal) }}"
                               oninput="recalcDeductions()">
                    </div>
                </td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
        <tbody id="customDeductionBody">
            @foreach($customDeductions as $idx => $cDeduct)
            @php
                $dName = $cDeduct['name'] ?? '';
                $dAmt = isset($cDeduct['amount']) ? number_format((float) $cDeduct['amount'], 2, '.', '') : '';
            @endphp
            <tr class="custom-deduction-row">
                <td>
                    <input type="text" name="custom_deductions[{{ $idx }}][name]"
                           class="ss-custom-name" placeholder="Deduction Name (e.g. MLWF, Loan, Other)"
                           value="{{ $dName }}" required>
                </td>
                <td>
                    <div class="ss-input-wrap">
                        <span class="ss-input-unit" style="margin-right:0;margin-left:0;color:#e74a3b;font-size:0.9rem;">₹</span>
                        <input type="number" name="custom_deductions[{{ $idx }}][amount]"
                               class="ss-input deduction-amt custom-deduction-amt" step="any" min="0"
                               placeholder="0.00" value="{{ $dAmt }}"
                               oninput="recalcDeductions()" required>
                    </div>
                </td>
                <td style="text-align: center;">
                    <button type="button" class="btn-remove-custom" onclick="removeRow(this, 'deduction')" title="Remove">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#f8fafc; border-top:2px solid #e2e8f0;">
                <td style="font-weight:800; color:#2d3748; padding:14px 24px;">Total</td>
                <td style="font-weight:800; color:#e74a3b; padding:14px 24px; text-align:right;">₹<span id="deduction_total">0.00</span></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- ===== SUBMIT BAR ===== --}}
<div class="ss-submit-bar">
    <a href="{{ route('setsalary.index') }}" class="ss-btn-cancel">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
    <button type="submit" class="ss-btn-save" id="saveBtn">
        <i class="fas fa-save me-2"></i>Save Salary & Payroll Data
    </button>
</div>

</form>

<script>
let customAllowanceCounter = {{ count($customAllowances) }};
let customDeductionCounter = {{ count($customDeductions) }};

function recalcAllowances() {
    var total = 0;
    document.querySelectorAll('.allowance-amt').forEach(function(input) {
        total += parseFloat(input.value) || 0;
    });
    var totalEl = document.getElementById('allowance_total');
    if (totalEl) {
        totalEl.textContent = total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}

function recalcDeductions() {
    var total = 0;
    document.querySelectorAll('.deduction-amt').forEach(function(input) {
        total += parseFloat(input.value) || 0;
    });
    var totalEl = document.getElementById('deduction_total');
    if (totalEl) {
        totalEl.textContent = total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}

function autoCalculateBreakdown(onlyIfEmpty = false) {
    var salary = parseFloat(document.getElementById('set_salary').value) || 0;
    if (salary <= 0) {
        if (!onlyIfEmpty) alert('Please enter a Set Salary amount first.');
        return;
    }

    var basic = Math.round(salary * 0.40 * 100) / 100;
    var hra = Math.round(salary * 0.16 * 100) / 100;
    var medical = Math.round(salary * 0.06 * 100) / 100;
    var conveyance = Math.round(salary * 0.04 * 100) / 100;
    var education = Math.round(salary * 0.04 * 100) / 100;
    var executive = Math.round(salary * 0.30 * 100) / 100;

    var basicEl = document.getElementById('basic_amt');
    if (!onlyIfEmpty || !basicEl || !basicEl.value || parseFloat(basicEl.value) === 0) {
        if (basicEl) basicEl.value = basic.toFixed(2);
        var hraEl = document.getElementById('hra_amt');
        if (hraEl) hraEl.value = hra.toFixed(2);
        var medEl = document.getElementById('medical_amt');
        if (medEl) medEl.value = medical.toFixed(2);
        var convEl = document.getElementById('conveyance_amt');
        if (convEl) convEl.value = conveyance.toFixed(2);
        var eduEl = document.getElementById('education_amt');
        if (eduEl) eduEl.value = education.toFixed(2);
        var execEl = document.getElementById('executive_amt');
        if (execEl) execEl.value = executive.toFixed(2);

        var pt = document.getElementById('professional_tax_amt');
        if (pt && (!onlyIfEmpty || !pt.value || parseFloat(pt.value) === 0)) {
            pt.value = (200).toFixed(2);
        }

        var pf = document.getElementById('pf_amt');
        if (pf && (!onlyIfEmpty || !pf.value || parseFloat(pf.value) === 0)) {
            var pfAmt = basic >= 15000 ? 1800 : Math.round(basic * 0.12 * 100) / 100;
            pf.value = pfAmt.toFixed(2);
        }

        var esi = document.getElementById('esi_amt');
        if (esi && (!onlyIfEmpty || !esi.value || parseFloat(esi.value) === 0)) {
            var esiAmt = salary <= 21000 ? Math.round(basic * 0.0075 * 100) / 100 : 0;
            esi.value = esiAmt.toFixed(2);
        }

        recalcAllowances();
        recalcDeductions();
    }
}

function removeRow(btn, type) {
    var row = btn.closest('tr');
    if (row) {
        row.remove();
        if (type === 'allowance') {
            recalcAllowances();
        } else {
            recalcDeductions();
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    recalcAllowances();
    recalcDeductions();

    // Add custom allowance row
    document.getElementById('btnAddAllowance').addEventListener('click', function() {
        var tbody = document.getElementById('customAllowanceBody');
        var idx = customAllowanceCounter++;
        var tr = document.createElement('tr');
        tr.className = 'custom-allowance-row';
        tr.innerHTML = `
            <td>
                <input type="text" name="custom_allowances[${idx}][name]"
                       class="ss-custom-name" placeholder="Allowance Name (e.g. Bonus, Fuel, Special)"
                       required>
            </td>
            <td>
                <div class="ss-input-wrap">
                    <span class="ss-input-unit" style="margin-right:0;margin-left:0;color:#1cc88a;font-size:0.9rem;">₹</span>
                    <input type="number" name="custom_allowances[${idx}][amount]"
                           class="ss-input allowance-amt custom-allowance-amt" step="any" min="0"
                           placeholder="0.00" oninput="recalcAllowances()" required>
                </div>
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn-remove-custom" onclick="removeRow(this, 'allowance')" title="Remove">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        tr.querySelector('.ss-custom-name').focus();
    });

    // Add custom deduction row
    document.getElementById('btnAddDeduction').addEventListener('click', function() {
        var tbody = document.getElementById('customDeductionBody');
        var idx = customDeductionCounter++;
        var tr = document.createElement('tr');
        tr.className = 'custom-deduction-row';
        tr.innerHTML = `
            <td>
                <input type="text" name="custom_deductions[${idx}][name]"
                       class="ss-custom-name" placeholder="Deduction Name (e.g. MLWF, Loan, Other)"
                       required>
            </td>
            <td>
                <div class="ss-input-wrap">
                    <span class="ss-input-unit" style="margin-right:0;margin-left:0;color:#e74a3b;font-size:0.9rem;">₹</span>
                    <input type="number" name="custom_deductions[${idx}][amount]"
                           class="ss-input deduction-amt custom-deduction-amt" step="any" min="0"
                           placeholder="0.00" oninput="recalcDeductions()" required>
                </div>
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn-remove-custom" onclick="removeRow(this, 'deduction')" title="Remove">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        tr.querySelector('.ss-custom-name').focus();
    });

    // Save button loading state
    document.querySelector('form').addEventListener('submit', function() {
        var btn = document.getElementById('saveBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        btn.disabled = true;
    });
});
</script>

@endsection
