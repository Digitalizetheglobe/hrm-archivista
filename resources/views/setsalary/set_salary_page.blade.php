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
    display:flex; align-items:center; gap:12px;
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
.ss-table thead th:last-child { text-align:right; }
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
.ss-computed-amount {
    font-size:0.75rem; color:#64748b; margin-top:4px;
    text-align:right;
}
.ss-computed-amount span { font-weight:600; color:#f6821f; }

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
            {{ \Auth::user()->employeeIdFormat($employee->employee_id) }}
        </span>
    </div>

    <div class="ss-salary-box">
        <div class="ss-salary-input-label"><i class="fas fa-rupee-sign me-1"></i>Set Salary (Monthly)</div>
        <div class="ss-salary-input-wrap">
            <span class="ss-currency-symbol">₹</span>
            <input type="number" id="set_salary" name="set_salary" class="ss-salary-input"
                   placeholder="0.00" step="0.01" min="0"
                   value="{{ old('set_salary', $employee->set_salary ?? '') }}"
                   required oninput="recalcDeductions()">
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ===== TABLE 1: Allowances ===== --}}
<div class="ss-section-card">
    <div class="ss-section-header">
        <div class="ss-section-icon" style="background:rgba(28,200,138,0.12);">
            <i class="fas fa-plus-circle allow-icon"></i>
        </div>
        <div>
            <p class="ss-section-title">Allowances</p>
            <p class="ss-section-subtitle">Enter percentage of the gross salary for each allowance component</p>
        </div>
    </div>
    <table class="ss-table">
        <thead>
            <tr>
                <th>Allowance</th>
                <th>Percentage (%)</th>
            </tr>
        </thead>
        <tbody>
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
            <tr>
                <td>
                    <div class="ss-allowance-name">
                        {{ $info['label'] }}
                        <small>{{ $info['desc'] }}</small>
                    </div>
                </td>
                <td>
                    <div class="ss-input-wrap">
                        <input type="number" id="{{ $field }}_pct" name="{{ $field }}"
                               class="ss-input allowance-pct" step="0.01" min="0" max="100"
                               placeholder="0.00"
                               value="{{ old($field, $payrollData?->$field ?? '') }}"
                               oninput="recalcDeductions()">
                        <span class="ss-input-unit">%</span>
                    </div>
                    <div class="ss-computed-amount" id="{{ $field }}_amount">
                        &nbsp;
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#fef9f4;">
                <td style="color:#d97706; padding:14px 24px;">
                    <div class="ss-allowance-name" style="color:#d97706;">
                        Special Allowance (Auto-balance)
                        <small style="color:#f59e0b;">Adjusts automatically to match exactly 100% of Set Salary</small>
                    </div>
                </td>
                <td>
                    <div class="ss-input-wrap">
                        <span id="special_allowance_pct" style="font-size:0.95rem; font-weight:700; color:#d97706;">0.00</span>
                        <span class="ss-input-unit" style="color:#d97706;">%</span>
                    </div>
                    <div class="ss-computed-amount" id="special_allowance_amount">
                        &nbsp;
                    </div>
                </td>
            </tr>
            <tr style="background:#f8fafc; border-top:2px solid #e2e8f0;">
                <td style="font-weight:800; color:#2d3748; padding:14px 24px;">Total Breakdown (100% of Set Salary)</td>
                <td>
                    <div class="ss-input-wrap">
                        <span id="total_allowance_pct" style="font-size:1.1rem; font-weight:800; color:#10b981;">100.00</span>
                        <span class="ss-input-unit" style="color:#10b981;">%</span>
                    </div>
                    <div class="ss-computed-amount" style="font-size:0.9rem;" id="total_allowance_amount">
                        &nbsp;
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- ===== TABLE 2: Deductions ===== --}}
<div class="ss-section-card">
    <div class="ss-section-header">
        <div class="ss-section-icon" style="background:rgba(231,74,59,0.10);">
            <i class="fas fa-minus-circle deduct-icon"></i>
        </div>
        <div>
            <p class="ss-section-title">Deductions</p>
            <p class="ss-section-subtitle">Enter the fixed deduction amounts for statutory components</p>
        </div>
    </div>
    <table class="ss-table">
        <thead>
            <tr>
                <th>Deduction</th>
                <th>Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $deductions = [
                    'esi'              => ['label' => 'ESI',               'desc' => 'Employee State Insurance'],
                    'pf'               => ['label' => 'PF',                'desc' => 'Provident Fund'],
                    'professional_tax' => ['label' => 'Professional Tax',  'desc' => 'State professional tax'],
                ];
            @endphp

            @foreach($deductions as $field => $info)
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
                        <input type="number" name="{{ $field }}"
                               class="ss-input deduction-amt" step="0.01" min="0"
                               placeholder="0.00"
                               value="{{ old($field, $payrollData?->$field ?? '') }}">
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
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
function recalcDeductions() {
    var salary = parseFloat(document.getElementById('set_salary').value) || 0;
    var totalPct = 0;

    @foreach($allowances as $field => $info)
    (function() {
        var pct = parseFloat(document.getElementById('{{ $field }}_pct').value) || 0;
        totalPct += pct;
        var amount = (salary * pct / 100).toFixed(2);
        var el = document.getElementById('{{ $field }}_amount');
        if (pct > 0 && salary > 0) {
            el.innerHTML = '= <span>₹' + parseFloat(amount).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</span>';
        } else {
            el.innerHTML = '&nbsp;';
        }
    })();
    @endforeach

    var totalPctEl = document.getElementById('total_allowance_pct');
    var totalAmountEl = document.getElementById('total_allowance_amount');
    var specialPctEl = document.getElementById('special_allowance_pct');
    var specialAmountEl = document.getElementById('special_allowance_amount');
    
    if (totalPctEl && totalAmountEl && specialPctEl && specialAmountEl) {
        var specialPct = 100 - totalPct;
        var specialAmount = (salary * specialPct / 100).toFixed(2);
        
        specialPctEl.innerText = specialPct.toFixed(2);
        if (salary > 0 && specialPct !== 0) {
            specialAmountEl.innerHTML = '= <span style="color:#d97706;">₹' + parseFloat(specialAmount).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</span>';
        } else {
            specialAmountEl.innerHTML = '&nbsp;';
        }
        
        totalPctEl.innerText = '100.00';
        if (salary > 0) {
            totalAmountEl.innerHTML = '= <span style="color:#10b981;">₹' + parseFloat(salary.toFixed(2)).toLocaleString('en-IN', {minimumFractionDigits: 2}) + '</span>';
        } else {
            totalAmountEl.innerHTML = '&nbsp;';
        }
    }
}

// Run on page load to show computed amounts for existing data
document.addEventListener('DOMContentLoaded', function() {
    recalcDeductions();

    // Save button loading state
    document.querySelector('form').addEventListener('submit', function() {
        var btn = document.getElementById('saveBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        btn.disabled = true;
    });
});
</script>

@endsection
