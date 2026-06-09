@extends('layouts.admin')

@section('page-title')
    {{ __('Leave Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('leave.index') }}">{{ __('Leave') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leave Details') }}</li>
@endsection

@section('content')
<style>
    /* ── Filter Bar ── */
    .ld-filter-bar {
        background: #fff;
        border: 1px solid #edf0f5;
        border-radius: 14px;
        padding: 22px 28px;
        display: flex;
        align-items: flex-end;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 28px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    }
    .ld-filter-bar .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex: 0 0 220px;
    }
    .ld-filter-bar .filter-group label {
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        margin: 0;
    }
    .ld-filter-bar .filter-group .form-control {
        border-radius: 9px;
        border: 1.5px solid #e2e8f0;
        padding: 9px 14px;
        font-size: 14px;
        color: #3c4b64;
        background: #f8fafc;
        transition: border-color 0.2s, box-shadow 0.2s;
        height: auto;
    }
    .ld-filter-bar .filter-group .form-control:focus {
        border-color: #e8590c;
        box-shadow: 0 0 0 3px rgba(232,89,12,0.10);
        background: #fff;
        outline: none;
    }
    .search-input-wrap {
        position: relative;
    }
    .search-input-wrap .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 13px;
        pointer-events: none;
    }
    .search-input-wrap .form-control {
        padding-left: 34px !important;
    }

    /* ── Stat Cards ── */
    .ld-stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        margin-bottom: 32px;
    }
    @media (max-width: 992px) { .ld-stats-row { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 576px) { .ld-stats-row { grid-template-columns: 1fr; } }

    .stat-card {
        background: #fff;
        border-radius: 14px;
        padding: 22px 24px;
        display: flex;
        align-items: center;
        gap: 18px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        border: 1px solid #f0f3f8;
        position: relative;
        overflow: hidden;
        transition: box-shadow 0.22s ease, transform 0.22s ease;
    }
    .stat-card::after {
        content: '';
        position: absolute;
        top: -24px; right: -24px;
        width: 90px; height: 90px;
        border-radius: 50%;
        opacity: 0.07;
        pointer-events: none;
    }
    .stat-card.sc-primary::after { background: #e8590c; }
    .stat-card.sc-success::after { background: #22c55e; }
    .stat-card.sc-warning::after { background: #f59e0b; }
    .stat-card.sc-info::after    { background: #0ea5e9; }
    .stat-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.07); transform: translateY(-3px); }

    .stat-icon {
        width: 54px; height: 54px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 21px;
        color: white;
        flex-shrink: 0;
    }
    .stat-icon.ic-primary { background: linear-gradient(135deg, #e8590c, #ff7a3d); box-shadow: 0 4px 14px rgba(232,89,12,0.30); }
    .stat-icon.ic-success { background: linear-gradient(135deg, #22c55e, #4ade80); box-shadow: 0 4px 14px rgba(34,197,94,0.30); }
    .stat-icon.ic-warning { background: linear-gradient(135deg, #f59e0b, #fbbf24); box-shadow: 0 4px 14px rgba(245,158,11,0.30); }
    .stat-icon.ic-info    { background: linear-gradient(135deg, #0ea5e9, #38bdf8); box-shadow: 0 4px 14px rgba(14,165,233,0.30); }

    .stat-text .stat-label { font-size: 11px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; margin-bottom: 5px; }
    .stat-text .stat-value { font-size: 28px; font-weight: 800; color: #1e293b; line-height: 1; }
    .stat-text .stat-unit  { font-size: 12px; color: #94a3b8; font-weight: 600; margin-left: 2px; }

    /* ── Employee Category Sections ── */
    .category-section { margin-bottom: 36px; }
    .category-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 14px;
        border-bottom: 2px solid #f1f5f9;
    }
    .category-header .cat-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
        color: #fff;
        flex-shrink: 0;
    }
    .cat-icon.ci-orange { background: linear-gradient(135deg, #e8590c, #ff7a3d); }
    .cat-icon.ci-amber  { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .cat-icon.ci-green  { background: linear-gradient(135deg, #22c55e, #4ade80); }

    .category-header h4 {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        line-height: 1.2;
    }
    .category-header .cat-subtitle {
        font-size: 12px;
        color: #94a3b8;
        font-weight: 500;
        margin-top: 2px;
    }
    .cat-badge {
        margin-left: auto;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }

    /* ── Table ── */
    .ld-table-card {
        background: #fff;
        border: 1px solid #edf0f5;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    }
    .ld-table-card table { margin: 0; }
    .ld-table-card thead tr th {
        background: #f8fafc;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748b;
        padding: 14px 20px;
        border-bottom: 1px solid #edf0f5;
        border-top: none;
        white-space: nowrap;
    }
    .ld-table-card tbody tr td {
        padding: 13px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        color: #475569;
        vertical-align: middle;
    }
    .ld-table-card tbody tr:last-child td { border-bottom: none; }

    /* Employee row */
    .emp-row td {
        background: linear-gradient(90deg, rgba(232,89,12,0.04) 0%, rgba(232,89,12,0.01) 100%) !important;
        padding: 14px 20px !important;
        border-top: 1.5px solid rgba(232,89,12,0.15) !important;
        border-bottom: 1.5px solid rgba(232,89,12,0.08) !important;
    }
    .emp-avatar {
        width: 34px; height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e8590c, #ff7a3d);
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        flex-shrink: 0;
        margin-right: 10px;
    }
    .emp-name {
        font-weight: 700;
        font-size: 14px;
        color: #1e293b;
    }
    .emp-email {
        font-size: 12px;
        color: #94a3b8;
        font-weight: 500;
    }

    /* Leave type rows */
    .leave-type-name {
        font-weight: 600;
        color: #334155;
        font-size: 13.5px;
    }
    .days-value {
        font-weight: 700;
        font-size: 14px;
    }

    /* Empty state */
    .empty-state {
        background: #fff;
        border: 1px solid #edf0f5;
        border-radius: 14px;
        padding: 60px 30px;
        text-align: center;
    }
    .empty-state .empty-icon {
        width: 70px; height: 70px;
        background: #f1f5f9;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
        font-size: 26px;
        color: #94a3b8;
    }
</style>

{{-- ── FILTER BAR ── --}}
<div class="ld-filter-bar">
    <div class="filter-group">
        <label for="month_filter">{{ __('Select Month') }}</label>
        <input type="month" id="month_filter" name="month" class="form-control"
               value="{{ $selectedMonth }}" onchange="updateFilters()">
    </div>
    <div class="filter-group">
        <label for="employee_search">{{ __('Search Employee') }}</label>
        <div class="search-input-wrap">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="employee_search" name="search" class="form-control"
                   value="{{ request()->get('search') }}"
                   placeholder="{{ __('Search by name or email…') }}"
                   onkeyup="handleSearch(event)">
        </div>
    </div>
    @if(request()->get('search'))
        <div class="filter-group" style="justify-content: flex-end; flex: 0 0 auto;">
            <label>&nbsp;</label>
            <a href="?month={{ $selectedMonth }}"
               class="btn btn-sm"
               style="border: 1.5px solid #e2e8f0; border-radius: 9px; color: #64748b; background: #f8fafc; padding: 9px 16px; font-size: 13px; font-weight: 600; text-decoration: none;">
                <i class="fas fa-times me-1"></i>{{ __('Clear') }}
            </a>
        </div>
    @endif
</div>

{{-- ── STAT CARDS ── --}}
<div class="ld-stats-row">
    <div class="stat-card sc-primary">
        <div class="stat-icon ic-primary"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-text">
            <div class="stat-label">{{ __('Total Leaves') }}</div>
            <div class="stat-value">{{ $monthlySummary['total_leaves'] }}<span class="stat-unit">Days</span></div>
        </div>
    </div>
    <div class="stat-card sc-success">
        <div class="stat-icon ic-success"><i class="fas fa-plus-circle"></i></div>
        <div class="stat-text">
            <div class="stat-label">{{ __('Credited Leaves') }}</div>
            <div class="stat-value">{{ $monthlySummary['credited_leaves'] }}<span class="stat-unit">Days</span></div>
        </div>
    </div>
    <div class="stat-card sc-warning">
        <div class="stat-icon ic-warning"><i class="fas fa-minus-circle"></i></div>
        <div class="stat-text">
            <div class="stat-label">{{ __('Used Leaves') }}</div>
            <div class="stat-value">{{ $monthlySummary['used_leaves'] }}<span class="stat-unit">Days</span></div>
        </div>
    </div>
    <div class="stat-card sc-info">
        <div class="stat-icon ic-info"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-text">
            <div class="stat-label">{{ __('Remaining Leaves') }}</div>
            <div class="stat-value">{{ $monthlySummary['remaining_leaves'] }}<span class="stat-unit">Days</span></div>
        </div>
    </div>
</div>

{{-- ── CONTRACT (CONFIRM) EMPLOYEES ── --}}
@if($contractConfirmEmployees->count() > 0)
<div class="category-section">
    <div class="category-header">
        <div class="cat-icon ci-orange"><i class="fas fa-user-check"></i></div>
        <div>
            <h4>{{ __('Contract (Confirm) Employees') }}</h4>
            <div class="cat-subtitle">{{ __('Confirmed contract & consultant staff') }}</div>
        </div>
        <span class="cat-badge" style="background: rgba(232,89,12,0.1); color: #e8590c;">
            {{ $contractConfirmEmployees->count() }} {{ __('Employees') }}
        </span>
    </div>
    <div class="ld-table-card">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Leave Type') }}</th>
                    <th>{{ __('Allocated Days') }}</th>
                    <th>{{ __('Carried Forward') }}</th>
                    <th>{{ __('Used Days') }}</th>
                    <th>{{ __('Remaining Days') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaveDetails['contract_confirm'] as $employeeDetail)
                    <?php
                        $emp = $employeeDetail['employee'];
                        $leaveBalances = $employeeDetail['leave_balances'];
                        $initials = collect(explode(' ', $emp->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('');
                    ?>
                    <tr class="emp-row">
                        <td colspan="5">
                            <div class="d-flex align-items-center">
                                <div class="emp-avatar">{{ $initials }}</div>
                                <div>
                                    <div class="emp-name">{{ $emp->name }}</div>
                                    <div class="emp-email">{{ $emp->email }}</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @foreach($leaveBalances as $leaveBalance)
                        <tr>
                            <td style="padding-left: 28px !important;">
                                <span class="leave-type-name">{{ $leaveBalance['leave_type']->title }}</span>
                                @if($leaveBalance['is_unlimited'])
                                    <span class="badge" style="background: rgba(14,165,233,0.12); color: #0ea5e9; font-size: 10px; font-weight: 700; border-radius: 6px; padding: 3px 8px; margin-left: 6px;">{{ __('Unlimited') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($leaveBalance['is_unlimited'])
                                    <span class="text-muted">{{ __('N/A') }}</span>
                                @else
                                    <span class="days-value" style="color: #475569;">{{ $leaveBalance['allocated_days'] }}</span>
                                @endif
                            </td>
                            <td>
                                @if($leaveBalance['carried_forward_days'] > 0)
                                    <span class="days-value text-success">+{{ $leaveBalance['carried_forward_days'] }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><span class="days-value" style="color: #f59e0b;">{{ $leaveBalance['used_days'] }}</span></td>
                            <td>
                                @if($leaveBalance['is_unlimited'])
                                    <span class="days-value text-info">{{ __('Unlimited') }}</span>
                                @else
                                    <span class="days-value {{ $leaveBalance['remaining_days'] > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $leaveBalance['remaining_days'] }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── CONTRACT (NOT CONFIRM) EMPLOYEES ── --}}
@if($contractNotConfirmEmployees->count() > 0)
<div class="category-section">
    <div class="category-header">
        <div class="cat-icon ci-amber"><i class="fas fa-user-clock"></i></div>
        <div>
            <h4>{{ __('Contract (Not Confirm) Employees') }}</h4>
            <div class="cat-subtitle">{{ __('Unconfirmed contract & consultant staff') }}</div>
        </div>
        <span class="cat-badge" style="background: rgba(245,158,11,0.12); color: #d97706;">
            {{ $contractNotConfirmEmployees->count() }} {{ __('Employees') }}
        </span>
    </div>
    <div class="ld-table-card">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Leave Type') }}</th>
                    <th>{{ __('Allocated Days') }}</th>
                    <th>{{ __('Carried Forward') }}</th>
                    <th>{{ __('Used Days') }}</th>
                    <th>{{ __('Remaining Days') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaveDetails['contract_not_confirm'] as $employeeDetail)
                    <?php
                        $emp = $employeeDetail['employee'];
                        $leaveBalances = $employeeDetail['leave_balances'];
                        $initials = collect(explode(' ', $emp->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('');
                    ?>
                    <tr class="emp-row">
                        <td colspan="5">
                            <div class="d-flex align-items-center">
                                <div class="emp-avatar" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">{{ $initials }}</div>
                                <div>
                                    <div class="emp-name">{{ $emp->name }}</div>
                                    <div class="emp-email">{{ $emp->email }}</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @foreach($leaveBalances as $leaveBalance)
                        <tr>
                            <td style="padding-left: 28px !important;">
                                <span class="leave-type-name">{{ $leaveBalance['leave_type']->title }}</span>
                                @if($leaveBalance['is_unlimited'])
                                    <span class="badge" style="background: rgba(14,165,233,0.12); color: #0ea5e9; font-size: 10px; font-weight: 700; border-radius: 6px; padding: 3px 8px; margin-left: 6px;">{{ __('Unlimited') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($leaveBalance['is_unlimited'])
                                    <span class="text-muted">{{ __('N/A') }}</span>
                                @else
                                    <span class="days-value" style="color: #475569;">{{ $leaveBalance['allocated_days'] }}</span>
                                @endif
                            </td>
                            <td>
                                @if($leaveBalance['carried_forward_days'] > 0)
                                    <span class="days-value text-success">+{{ $leaveBalance['carried_forward_days'] }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><span class="days-value" style="color: #f59e0b;">{{ $leaveBalance['used_days'] }}</span></td>
                            <td>
                                @if($leaveBalance['is_unlimited'])
                                    <span class="days-value text-info">{{ __('Unlimited') }}</span>
                                @else
                                    <span class="days-value {{ $leaveBalance['remaining_days'] > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $leaveBalance['remaining_days'] }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── PAYROLL EMPLOYEES ── --}}
@if($payrollEmployees->count() > 0)
<div class="category-section">
    <div class="category-header">
        <div class="cat-icon ci-green"><i class="fas fa-users"></i></div>
        <div>
            <h4>{{ __('Payroll Employees') }}</h4>
            <div class="cat-subtitle">{{ __('Full-time payroll staff') }}</div>
        </div>
        <span class="cat-badge" style="background: rgba(34,197,94,0.12); color: #16a34a;">
            {{ $payrollEmployees->count() }} {{ __('Employees') }}
        </span>
    </div>
    <div class="ld-table-card">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Leave Type') }}</th>
                    <th>{{ __('Allocated Days') }}</th>
                    <th>{{ __('Carried Forward') }}</th>
                    <th>{{ __('Used Days') }}</th>
                    <th>{{ __('Remaining Days') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaveDetails['payroll'] as $employeeDetail)
                    <?php
                        $emp = $employeeDetail['employee'];
                        $leaveBalances = $employeeDetail['leave_balances'];
                        $initials = collect(explode(' ', $emp->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('');
                    ?>
                    <tr class="emp-row">
                        <td colspan="5">
                            <div class="d-flex align-items-center">
                                <div class="emp-avatar" style="background: linear-gradient(135deg, #22c55e, #4ade80);">{{ $initials }}</div>
                                <div>
                                    <div class="emp-name">{{ $emp->name }}</div>
                                    <div class="emp-email">{{ $emp->email }}</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @foreach($leaveBalances as $leaveBalance)
                        <tr>
                            <td style="padding-left: 28px !important;">
                                <span class="leave-type-name">{{ $leaveBalance['leave_type']->title }}</span>
                                @if($leaveBalance['is_unlimited'])
                                    <span class="badge" style="background: rgba(14,165,233,0.12); color: #0ea5e9; font-size: 10px; font-weight: 700; border-radius: 6px; padding: 3px 8px; margin-left: 6px;">{{ __('Unlimited') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($leaveBalance['is_unlimited'])
                                    <span class="text-muted">{{ __('N/A') }}</span>
                                @else
                                    <span class="days-value" style="color: #475569;">{{ $leaveBalance['allocated_days'] }}</span>
                                @endif
                            </td>
                            <td>
                                @if($leaveBalance['carried_forward_days'] > 0)
                                    <span class="days-value text-success">+{{ $leaveBalance['carried_forward_days'] }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><span class="days-value" style="color: #f59e0b;">{{ $leaveBalance['used_days'] }}</span></td>
                            <td>
                                @if($leaveBalance['is_unlimited'])
                                    <span class="days-value text-info">{{ __('Unlimited') }}</span>
                                @else
                                    <span class="days-value {{ $leaveBalance['remaining_days'] > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $leaveBalance['remaining_days'] }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── EMPTY STATE ── --}}
@if($contractConfirmEmployees->count() == 0 && $contractNotConfirmEmployees->count() == 0 && $payrollEmployees->count() == 0)
<div class="empty-state">
    <div class="empty-icon"><i class="fas fa-users"></i></div>
    @if(request()->get('search'))
        <h5 style="font-weight: 700; color: #1e293b;">{{ __('No results found') }}</h5>
        <p class="text-muted" style="max-width: 340px; margin: 8px auto 0;">{{ __('No employees matched your search. Try a different name or email.') }}</p>
        <a href="?month={{ $selectedMonth }}" class="btn btn-sm mt-3" style="border: 1.5px solid #e2e8f0; border-radius: 9px; color: #64748b; background: #f8fafc; padding: 8px 18px; font-size: 13px; font-weight: 600;">
            <i class="fas fa-times me-1"></i>{{ __('Clear Search') }}
        </a>
    @else
        <h5 style="font-weight: 700; color: #1e293b;">{{ __('No employees found') }}</h5>
        <p class="text-muted" style="max-width: 340px; margin: 8px auto 0;">{{ __('There are no employees in the system yet.') }}</p>
    @endif
</div>
@endif

@endsection

@push('scripts')
<script>
    function updateFilters() {
        const month = document.getElementById('month_filter').value;
        const search = document.getElementById('employee_search').value;
        const url = new URL(window.location.href);
        url.searchParams.set('month', month);
        if (search.trim()) {
            url.searchParams.set('search', search.trim());
        } else {
            url.searchParams.delete('search');
        }
        window.location.href = url.toString();
    }

    function handleSearch(event) {
        if (event.key === 'Enter') {
            updateFilters();
        }
    }
</script>
@endpush
