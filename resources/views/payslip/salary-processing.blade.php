@extends('layouts.admin')

@section('page-title')
    {{ __('Salary Processing') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Salary Processing') }}</li>
@endsection

@section('content')
@php
    $isFinanceAccounts = false;
    if (\Auth::user()->type == 'employee') {
        $employee = \App\Models\Employee::where('user_id', \Auth::user()->id)->with('department')->first();
        if ($employee && $employee->department) {
            $deptName = strtolower(trim($employee->department->name));
            $isFinanceAccounts = (
                $deptName == 'finance & accounts' ||
                $deptName == 'finance and accounts' ||
                $deptName == 'finance & account' ||
                strpos($deptName, 'finance') !== false && strpos($deptName, 'account') !== false
            );
        }
    } elseif (\Auth::user()->type == 'company') {
        $isFinanceAccounts = true;
    }
@endphp

<div class="row">
    <div class="col-xl-12">
        <div class="card salary-processing-card">

            {{-- ===== CARD HEADER ===== --}}
            <div class="card-header salary-card-header">
                <div class="salary-header-top">
                    <div class="salary-title-wrap">
                        <div>
                            <h5 class="salary-title mb-0">{{ __('Salary Processing') }}</h5>
                            <p class="salary-subtitle mb-0">{{ __('View and manage monthly employee salaries') }}</p>
                        </div>
                    </div>

                    <div class="salary-filters-wrap">
                        <div class="salary-filter-group">
                            <label class="salary-filter-label">{{ __('Month') }}</label>
                            {{ Form::select('month', $month, date('m'), ['class' => 'form-select salary-filter-select month_date']) }}
                        </div>
                        <div class="salary-filter-group">
                            <label class="salary-filter-label">{{ __('Year') }}</label>
                            {{ Form::select('year', $year, date('Y'), ['class' => 'form-select salary-filter-select year_date']) }}
                        </div>
                        <div class="salary-filter-group">
                            <label class="salary-filter-label">{{ __('Department') }}</label>
                            {{ Form::select('department_id', $departments, '', ['class' => 'form-select salary-filter-select department_filter', 'placeholder' => __('All Departments')]) }}
                        </div>
                        @if(\Auth::user()->type == 'company' || \Gate::check('payroll.salary_processing.export.all'))
                        <div class="salary-filter-group salary-export-group">
                            <label class="salary-filter-label">&nbsp;</label>
                            {{ Form::open(['route' => ['salary-processing.export'], 'method' => 'POST', 'id' => 'salary_processing_export_form']) }}
                            <input type="hidden" name="datePicker" class="export_date_picker" value="">
                            <input type="hidden" name="department_id" class="export_department_id" value="">
                            <button type="submit" class="btn btn-primary salary-export-btn" id="export_btn">
                                <i class="ti ti-file-export me-1"></i>{{ __('Export') }}
                            </button>
                            {{ Form::close() }}
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Stats strip --}}
                <div class="salary-stats-strip" id="salary-stats-strip" style="display:none;">
                    <div class="salary-stat-item">
                        <span class="salary-stat-label">{{ __('Total Employees') }}</span>
                        <span class="salary-stat-value" id="stat-total">—</span>
                    </div>
                    <div class="salary-stat-divider"></div>
                    <div class="salary-stat-item">
                        <span class="salary-stat-label">{{ __('Paid') }}</span>
                        <span class="salary-stat-value text-success" id="stat-paid">—</span>
                    </div>
                    <div class="salary-stat-divider"></div>
                    <div class="salary-stat-item">
                        <span class="salary-stat-label">{{ __('Pending') }}</span>
                        <span class="salary-stat-value text-warning" id="stat-pending">—</span>
                    </div>
                </div>
            </div>

            {{-- ===== CARD BODY ===== --}}
            <div class="card-body p-0">

                {{-- Loading overlay --}}
                <div id="salary-loading" class="salary-loading-overlay">
                    <div class="salary-spinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="salary-loading-text">{{ __('Loading salary data…') }}</span>
                    </div>
                </div>

                {{-- Table wrapper --}}
                <div class="salary-table-wrapper">
                    <table class="table salary-table" id="pc-dt-render-column-cells">
                        <thead>
                            <tr class="salary-thead-single">
                                <th class="col-fixed col-srno">{{ __('SRNO') }}</th>
                                <th class="col-fixed col-empcode">{{ __('EMPCODE') }}</th>
                                <th class="col-fixed col-empname">{{ __('EMPNAME') }}</th>
                                <th class="col-dept">{{ __('DEPT') }}</th>
                                <th class="col-sm">{{ __('COST_CENT') }}</th>
                                <th class="col-sm">{{ __('AGE') }}</th>
                                <th class="col-sm">{{ __('SEX') }}</th>
                                <th class="col-doj">{{ __('DOJ') }}</th>

                                <th>{{ __('MIN_WAGE_R') }}</th>
                                <th>{{ __('PROD_PC_RT') }}</th>
                                <th>{{ __('BASIC_RT') }}</th>
                                <th>{{ __('MEDICAL_RT') }}</th>
                                <th>{{ __('HRA_RT') }}</th>
                                <th>{{ __('CONNVEYANC') }}</th>
                                <th>{{ __('EDUCATION_') }}</th>
                                <th>{{ __('EXECUTIVE_') }}</th>
                                <th>{{ __('LEAVE_ENCA') }}</th>
                                <th>{{ __('SITE EXPE') }}</th>
                                <th>{{ __('SPL ALLOW') }}</th>

                                <th>{{ __('PR_DAYS') }}</th>
                                <th>{{ __('WEEKLY_OFF') }}</th>
                                <th>{{ __('PH') }}</th>
                                <th>{{ __('LWP') }}</th>
                                <th>{{ __('LEAVE') }}</th>
                                <th>{{ __('PL') }}</th>
                                <th>{{ __('PAID_DAYS') }}</th>
                                <th>{{ __('OT_HRS') }}</th>

                                <th>{{ __('BASIC') }}</th>
                                <th>{{ __('MEDICAL') }}</th>
                                <th>{{ __('HRA') }}</th>
                                <th>{{ __('CONNVEYAN2') }}</th>
                                <th>{{ __('EDUCATION') }}</th>
                                <th>{{ __('EXECUTIVE') }}</th>
                                <th>{{ __('LEAVE_ENC2') }}</th>
                                <th>{{ __('SITE EXP2') }}</th>
                                <th>{{ __('SPL ALLO2') }}</th>
                                <th>{{ __('Arrears') }}</th>

                                <th>{{ __('GROSS') }}</th>

                                <th>{{ __('PF') }}</th>
                                <th>{{ __('PT') }}</th>
                                <th>{{ __('MLWF') }}</th>
                                <th>{{ __('ADVANCE') }}</th>
                                <th>{{ __('TDS') }}</th>
                                <th>{{ __('TELEPHONE') }}</th>

                                <th>{{ __('DEDUCTION') }}</th>
                                <th>{{ __('NET') }}</th>
                                <th>{{ __('PMT_DT') }}</th>
                                <th>{{ __('SIGNATURE') }}</th>
                                <th class="col-status">{{ __('STATUS') }}</th>
                            </tr>
                        </thead>
                        <tbody id="salary-tbody">
                            {{-- Filled by JS --}}
                        </tbody>
                    </table>
                </div>

            </div>{{-- /card-body --}}
        </div>{{-- /card --}}
    </div>
</div>
@endsection

@push('script-page')
<script>
$(document).ready(function () {

    callback();

    // ── Trigger on filter change ──────────────────────────────────────────────
    $(document).on('change', '.month_date,.year_date,.department_filter', function () {
        callback();
        updateExportForm();
    });

    updateExportForm();
    $(document).on('change', '.month_date,.year_date,.department_filter', updateExportForm);

    // ── Main data loader ──────────────────────────────────────────────────────
    function callback() {
        var month = $('.month_date').val();
        var year  = $('.year_date').val();
        if (month === '' || month === '--') {
            month = '{{ date('m') }}';
            year  = '{{ date('Y') }}';
        }
        var datePicker   = year + '-' + month;
        var departmentId = $('.department_filter').val();

        var ajaxData = { datePicker: datePicker, _token: '{{ csrf_token() }}' };
        if (departmentId && departmentId !== '' && departmentId !== '0') {
            ajaxData.department_id = departmentId;
        }

        showLoading();

        $.ajax({
            url  : '{{ route('salary-processing.search_json') }}',
            type : 'POST',
            data : ajaxData,
            success: function (data) {
                hideLoading();
                renderTable(data);
            },
            error: function (xhr) {
                hideLoading();
                renderError();
                console.error('Salary Processing Error:', xhr);
            }
        });
    }

    // ── Render table rows ─────────────────────────────────────────────────────
    function renderTable(data) {
        var tr = '';
        var totalPaid    = 0;
        var totalPending = 0;

        if (data && data.length > 0) {
            $.each(data, function (i, v) {
                var url_employee = v['url'];

                var employeeId = v[0];
                var srno       = v[2];
                var empCode    = v[3];
                var empName    = v[4];
                var dept       = v[5];
                var costCent   = v[6];
                var age        = v[7];
                var sex        = v[8];
                var doj        = v[9];

                var minWageR  = v[10]; var prodPcRt  = v[11]; var basicRt  = v[12];
                var medicalRt = v[13]; var hraRt     = v[14]; var conveyance = v[15];
                var education = v[16]; var executive = v[17]; var leaveEnca  = v[18];
                var siteExpe  = v[19]; var splAllow  = v[20];

                var prDays   = v[21]; var weeklyOff = v[22]; var ph      = v[23];
                var lwp      = v[24]; var leave     = v[25]; var pl      = v[26];
                var paidDays = v[27]; var otHrs     = v[28];

                var basic     = v[29]; var medical   = v[30]; var hra       = v[31];
                var conveyan2 = v[32]; var education2= v[33]; var executive2= v[34];
                var leaveEnc2 = v[35]; var siteExp2  = v[36]; var splAllo2  = v[37];
                var arrears   = v[38];

                var gross = v[39];

                var pf = v[40]; var pt = v[41]; var mlwf = v[42];
                var advance = v[43]; var tds = v[44]; var telephone = v[45];

                var deduction = v[46];
                var net       = v[47];
                var pmtDt     = v[48];
                var signature = v[49];
                var status    = v[50] || 'Pending';

                if (status === 'Done') { totalPaid++; } else { totalPending++; }

                var statusCell = buildStatusCell(status, employeeId, empName);

                // Row alternating class
                var rowClass = (i % 2 === 0) ? '' : 'row-alt';

                tr +=
                    '<tr class="salary-row ' + rowClass + '">' +
                    '<td class="td-center td-srno">' + srno + '</td>' +
                    '<td class="td-empcode"><a class="emp-code-link" href="' + url_employee + '">' + empCode + '</a></td>' +
                    '<td class="td-empname"><span class="emp-name">' + empName + '</span></td>' +
                    '<td class="td-dept"><span class="dept-badge">' + (dept || '—') + '</span></td>' +
                    '<td class="td-center">' + (costCent || '—') + '</td>' +
                    '<td class="td-center">' + (age || '—') + '</td>' +
                    '<td class="td-center">' + (sex || '—') + '</td>' +
                    '<td class="td-center">' + (doj || '—') + '</td>' +

                    // Rates
                    td(fc(minWageR), 'td-rates') + td(fc(prodPcRt), 'td-rates') +
                    td(fc(basicRt), 'td-rates') + td(fc(medicalRt), 'td-rates') +
                    td(fc(hraRt), 'td-rates') + td(fc(conveyance), 'td-rates') +
                    td(fc(education), 'td-rates') + td(fc(executive), 'td-rates') +
                    td(fc(leaveEnca), 'td-rates') + td(fc(siteExpe), 'td-rates') +
                    td(fc(splAllow), 'td-rates') +

                    // Attendance
                    td(fn(prDays), 'td-attendance') + td(fn(weeklyOff), 'td-attendance') +
                    td(fn(ph), 'td-attendance') + td(fn(lwp), 'td-attendance') +
                    td(fn(leave), 'td-attendance') + td(fn(pl), 'td-attendance') +
                    '<td class="td-attendance td-bold">' + fn(paidDays) + '</td>' +
                    td(fn(otHrs), 'td-attendance') +

                    // Earnings
                    td(fc(basic), 'td-earnings') + td(fc(medical), 'td-earnings') +
                    td(fc(hra), 'td-earnings') + td(fc(conveyan2), 'td-earnings') +
                    td(fc(education2), 'td-earnings') + td(fc(executive2), 'td-earnings') +
                    td(fc(leaveEnc2), 'td-earnings') + td(fc(siteExp2), 'td-earnings') +
                    td(fc(splAllo2), 'td-earnings') + td(fc(arrears), 'td-earnings') +

                    // Gross
                    '<td class="td-amount td-gross td-bold">' + fc(gross) + '</td>' +

                    // Deductions
                    td(fc(pf), 'td-deductions') + td(fc(pt), 'td-deductions') +
                    td(fc(mlwf), 'td-deductions') + td(fc(advance), 'td-deductions') +
                    td(fc(tds), 'td-deductions') + td(fc(telephone), 'td-deductions') +

                    // Totals
                    '<td class="td-amount td-bold">' + fc(deduction) + '</td>' +
                    '<td class="td-amount td-net td-bold">' + fc(net) + '</td>' +
                    '<td class="td-center">' + (pmtDt || '—') + '</td>' +
                    '<td class="td-center">' + (signature || '—') + '</td>' +
                    statusCell +
                    '</tr>';
            });

            // Update stat strip
            $('#stat-total').text(data.length);
            $('#stat-paid').text(totalPaid);
            $('#stat-pending').text(totalPending);
            $('#salary-stats-strip').slideDown(200);

        } else {
            tr = '<tr><td class="salary-empty-row" colspan="49">' +
                 '<div class="salary-empty-state"><i class="ti ti-mood-empty"></i>' +
                 '<span>{{ __('No salary records found for the selected period.') }}</span></div></td></tr>';
            $('#salary-stats-strip').slideUp(200);
        }

        $('#salary-tbody').html(tr);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function td(content, cls) {
        return '<td class="' + cls + '">' + content + '</td>';
    }

    function fc(num) {
        if (num === '' || num === null || num === undefined || isNaN(num)) return '<span class="text-muted">—</span>';
        var n = parseFloat(num);
        if (n === 0) return '<span class="zero-val">0.00</span>';
        return n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fn(num) {
        if (num === '' || num === null || num === undefined || isNaN(num)) return '<span class="text-muted">—</span>';
        var n = parseFloat(num);
        if (n === 0) return '<span class="zero-val">0</span>';
        return n.toLocaleString('en-IN');
    }

    function buildStatusCell(status, employeeId, empName) {
        @if ($isFinanceAccounts)
            var cls = status === 'Done' ? 'bg-success' : 'bg-warning text-dark';
            if (status === 'Done') {
                return '<td class="td-status"><span class="badge salary-badge ' + cls + '"><i class="ti ti-check me-1"></i>' + status + '</span></td>';
            } else {
                return '<td class="td-status">' +
                    '<span class="badge salary-badge ' + cls + ' me-1"><i class="ti ti-clock me-1"></i>' + status + '</span>' +
                    '<button type="button" class="btn btn-xs btn-success mark-payment-btn" ' +
                    'data-employee-id="' + employeeId + '" data-employee-name="' + empName + '" ' +
                    'data-current-status="' + status + '" data-new-status="Done">' +
                    '<i class="ti ti-check"></i> Pay</button></td>';
            }
        @else
            var cls = status === 'Done' ? 'bg-success' : 'bg-warning text-dark';
            return '<td class="td-status"><span class="badge salary-badge ' + cls + '">' + status + '</span></td>';
        @endif
    }

    function renderError() {
        $('#salary-tbody').html(
            '<tr><td class="salary-empty-row" colspan="49">' +
            '<div class="salary-empty-state text-danger"><i class="ti ti-alert-circle"></i>' +
            '<span>{{ __('Failed to load data. Please try again.') }}</span></div></td></tr>'
        );
    }

    function showLoading() {
        $('#salary-loading').fadeIn(150);
        $('#salary-tbody').css('opacity', 0.4);
    }

    function hideLoading() {
        $('#salary-loading').fadeOut(150);
        $('#salary-tbody').css('opacity', 1);
    }

    // ── Export form sync ──────────────────────────────────────────────────────
    function updateExportForm() {
        var month = $('.month_date').val();
        var year  = $('.year_date').val();
        if (month === '' || month === '--') {
            month = '{{ date('m') }}';
            year  = '{{ date('Y') }}';
        }
        $('.export_date_picker').val(year + '-' + month);
        $('.export_department_id').val($('.department_filter').val() || '');
    }

    // ── Mark as Paid ──────────────────────────────────────────────────────────
    @if ($isFinanceAccounts)
    $(document).on('click', '.mark-payment-btn', function (e) {
        e.preventDefault();
        var $btn         = $(this);
        var employeeId   = $btn.data('employee-id');
        var employeeName = $btn.data('employee-name');
        var currentStatus= $btn.data('current-status');
        var newStatus    = $btn.data('new-status');
        var month        = $('.month_date').val() || '{{ date('m') }}';
        var year         = $('.year_date').val()  || '{{ date('Y') }}';
        var monthNames   = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
        var monthName    = monthNames[parseInt(month) - 1];
        var statusText   = newStatus === 'Done' ? 'Paid' : 'Pending';

        const swal = Swal.mixin({
            customClass: { confirmButton: 'btn btn-success', cancelButton: 'btn btn-danger' },
            buttonsStyling: false
        });

        swal.fire({
            title: 'Confirm Payment',
            html : '<div class="text-start">' +
                   '<p><strong>Employee:</strong> ' + employeeName + '</p>' +
                   '<p><strong>Period:</strong> ' + monthName + ' ' + year + '</p>' +
                   '<p><strong>Current:</strong> <span class="badge bg-warning text-dark">' + currentStatus + '</span></p>' +
                   '<p><strong>New:</strong> <span class="badge bg-success">' + statusText + '</span></p>' +
                   '<hr><p class="text-danger fw-semibold mb-0">Are you sure the payment has been completed?</p></div>',
            icon              : 'question',
            showCancelButton  : true,
            confirmButtonText : '<i class="ti ti-check"></i> Yes, Confirm',
            cancelButtonText  : '<i class="ti ti-x"></i> Cancel',
            reverseButtons    : true,
            focusCancel       : true
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $btn.prop('disabled', true).html('<i class="ti ti-loader-2 spin"></i> Processing…');
            $.ajax({
                url  : '{{ route('salary-processing.update-status') }}',
                type : 'POST',
                data : { employee_id: employeeId, year: year, month: month, status: newStatus, _token: '{{ csrf_token() }}' },
                success: function (response) {
                    if (response.success) {
                        swal.fire({ title: 'Done!', text: 'Payment status updated.', icon: 'success', confirmButtonText: 'OK' })
                            .then(callback);
                    } else {
                        swal.fire({ title: 'Error', text: response.message || 'Failed to update.', icon: 'error' });
                        $btn.prop('disabled', false).html('<i class="ti ti-check"></i> Pay');
                    }
                },
                error: function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Failed to update.';
                    swal.fire({ title: 'Error', text: msg, icon: 'error' });
                    $btn.prop('disabled', false).html('<i class="ti ti-check"></i> Pay');
                }
            });
        });
    });
    @endif

});
</script>

<style>
/* ═══════════════════════════════════════════════════════════════════════════
   SALARY PROCESSING – UI STYLES
   Colors are preserved from original; only layout/spacing/polish updated
═══════════════════════════════════════════════════════════════════════════ */

/* ─── Card ─────────────────────────────────────────────────────────────── */
.salary-processing-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 16px rgba(0,0,0,.07);
    overflow: hidden;
}

/* ─── Card Header ──────────────────────────────────────────────────────── */
.salary-card-header {
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    padding: 20px 24px 0;
}

.salary-header-top {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    padding-bottom: 16px;
}

/* Title */
.salary-title-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
}
.salary-title-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: linear-gradient(135deg, #6FD943 0%, #3a8a1e 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 22px;
    flex-shrink: 0;
}
.salary-title {
    font-size: 18px;
    font-weight: 700;
    color: #1a1f36;
    line-height: 1.2;
}
.salary-subtitle {
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
}

/* Filters */
.salary-filters-wrap {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 10px;
}
.salary-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.salary-filter-label {
    font-size: 11px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: .4px;
    margin: 0;
}
.salary-filter-select {
    min-width: 110px;
    height: 36px;
    font-size: 13px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    padding: 4px 10px;
    transition: border-color .2s;
}
.salary-filter-select:focus {
    border-color: #6FD943;
    box-shadow: 0 0 0 3px rgba(111,217,67,.18);
}
.salary-export-btn {
    height: 36px;
    font-size: 13px;
    border-radius: 8px;
    padding: 0 16px;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Stats Strip */
.salary-stats-strip {
    display: flex;
    align-items: center;
    gap: 0;
    padding: 10px 0;
    border-top: 1px solid #f0f0f0;
}
.salary-stat-item {
    display: flex;
    flex-direction: column;
    padding: 0 20px;
}
.salary-stat-label {
    font-size: 11px;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: .4px;
    font-weight: 600;
}
.salary-stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #1a1f36;
    line-height: 1.2;
}
.salary-stat-divider {
    width: 1px;
    height: 32px;
    background: #e5e7eb;
}

/* ─── Loading Overlay ──────────────────────────────────────────────────── */
.salary-table-wrapper {
    position: relative;
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
}
.salary-loading-overlay {
    display: none;
    position: absolute;
    inset: 0;
    z-index: 10;
    background: rgba(255,255,255,.75);
    backdrop-filter: blur(2px);
    align-items: center;
    justify-content: center;
    flex-direction: column;
}
.salary-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    margin-top: 60px;
}
.salary-loading-text {
    font-size: 13px;
    color: #6b7280;
    font-weight: 500;
}

/* ─── Table ────────────────────────────────────────────────────────────── */
.salary-table {
    width: max-content;
    min-width: 100%;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 12.5px;
}

/* ── Sticky first 3 columns ── */
.salary-table thead th.col-fixed {
    position: sticky;
    z-index: 6;
    background: #f9fafb;
}
.salary-table tbody td.td-srno,
.salary-table tbody td.td-empcode,
.salary-table tbody td.td-empname {
    position: sticky;
    z-index: 4;
    background: #fff;
}
.salary-row.row-alt td.td-srno,
.salary-row.row-alt td.td-empcode,
.salary-row.row-alt td.td-empname {
    background: #fafbfc;
}
.salary-row:hover td.td-srno,
.salary-row:hover td.td-empcode,
.salary-row:hover td.td-empname {
    background: #f4fdf0 !important;
}

.salary-table thead th.col-srno,
.salary-table tbody td.td-srno   { left: 0;    min-width: 50px;  }
.salary-table thead th.col-empcode,
.salary-table tbody td.td-empcode { left: 50px; min-width: 110px; }
.salary-table thead th.col-empname,
.salary-table tbody td.td-empname {
    left: 160px;
    min-width: 180px;
    box-shadow: 3px 0 8px rgba(0,0,0,.08);
}

/* ── Header rows ── */
.salary-table thead {
    position: sticky;
    top: 0;
    z-index: 5;
}

.salary-thead-single th {
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    color: #374151;
    padding: 10px 10px 8px;
    white-space: nowrap;
    border-bottom: 2px solid #e5e7eb;
    vertical-align: middle;
    background: #f9fafb;
    border-right: 1px solid #e9ecef;
}



/* ── Body rows ── */
.salary-row td {
    padding: 9px 10px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    color: #374151;
    white-space: nowrap;
}
.salary-row:hover td {
    background-color: #f4fdf0 !important;
}
.row-alt td { background: #fafbfc; }

/* Typed cells */
.td-center    { text-align: center; }
.td-rates     { text-align: right; background-color: #f5fdf6; font-family: 'Courier New', monospace; font-size: 12px; }
.td-attendance{ text-align: center; background-color: #fffdf7; }
.td-earnings  { text-align: right; background-color: #f4fdf0; font-family: 'Courier New', monospace; font-size: 12px; }
.td-deductions{ text-align: right; background-color: #fff8f8; font-family: 'Courier New', monospace; font-size: 12px; }
.td-amount    { text-align: right; font-family: 'Courier New', monospace; font-size: 12.5px; }
.td-bold      { font-weight: 700; }
.td-net       { color: #1a6b32; }
.td-gross     { background-color: #f0f7f1; }
.td-status    { text-align: center; }

/* EmpCode link */
.emp-code-link {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 6px;
    border: 1px solid #c5edac;
    font-size: 12px;
    font-weight: 600;
    color: #3a8a1e;
    text-decoration: none;
    transition: all .15s;
    background: #edfae3;
}
.emp-code-link:hover {
    background: #6FD943;
    color: #fff;
    border-color: #6FD943;
}

/* Emp Name */
.emp-name { font-weight: 600; color: #1a1f36; }

/* Dept badge */
.dept-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    background: #f3f4f6;
    color: #374151;
    font-size: 11.5px;
    font-weight: 500;
    white-space: nowrap;
}

/* Salary badge */
.salary-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .3px;
}

/* Zero values */
.zero-val { color: #c4c4c4; }

/* Mark as Paid button */
.btn-xs {
    padding: 3px 8px;
    font-size: 11px;
    border-radius: 5px;
    font-weight: 600;
}

/* Spin animation */
@keyframes spin { 0%{transform:rotate(0)} 100%{transform:rotate(360deg)} }
.spin { display: inline-block; animation: spin .7s linear infinite; }

/* Empty / error state */
.salary-empty-row { text-align: center; padding: 48px !important; }
.salary-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: #9ca3af;
    font-size: 14px;
}
.salary-empty-state i { font-size: 40px; opacity: .5; }

/* Column widths */
.col-srno   { min-width: 50px;  text-align: center; }
.col-empcode{ min-width: 110px; }
.col-empname{ min-width: 180px; }
.col-dept   { min-width: 120px; }
.col-sm     { min-width: 60px;  text-align: center; }
.col-doj    { min-width: 100px; text-align: center; }
.col-amount { min-width: 110px; text-align: right; }
.col-status { min-width: 120px; text-align: center; }
.col-gross  { min-width: 120px; }
.td-dept    { min-width: 120px; }
.td-empcode { min-width: 110px; }
.td-empname { min-width: 180px; }
.td-srno    { min-width: 50px; text-align: center; }
</style>
@endpush
