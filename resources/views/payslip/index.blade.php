@extends('layouts.admin')

@section('page-title')
    {{ __('Payslip') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('payslip') }}</li>
@endsection

@section('content')
    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
        <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12 mt-4">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['payslip.store'], 'method' => 'POST', 'id' => 'payslip_form']) }}
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mx-2">
                            <div class="btn-box">
                                {{ Form::label('month', __('Select Month'), ['class' => 'form-label']) }}
                                {{ Form::select('month', $month, date('m'), ['class' => 'form-control select', 'id' => 'month']) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mx-2">
                            <div class="btn-box">
                                {{ Form::label('year', __('Select Year'), ['class' => 'form-label']) }}
                                {{ Form::select('year', $year, date('Y'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn  btn-primary"
                                onclick="document.getElementById('payslip_form').submit(); return false;"
                                data-bs-toggle="tooltip" title="{{ __('payslip') }}"
                                data-original-title="{{ __('payslip') }}">{{ __('Generate Payslip') }}
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    @endif

    <div class="col-12">
        <div class="card">
            <div class="card-header payslip-find-header">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h5 class="mb-0">{{ __('Find Employee Payslip') }}</h5>
                    <div class="d-flex align-items-center gap-2">
                        @if (Auth::user()->type == 'company' || Auth::user()->type == 'hr')
                            {{ Form::open(['route' => ['payslip.export'], 'method' => 'POST', 'id' => 'payslip_export_form', 'class' => 'mb-0']) }}
                            <input type="hidden" name="filter_month" class="filter_month">
                            <input type="hidden" name="filter_year" class="filter_year">
                            <input type="hidden" name="filter_employee" class="filter_employee">
                            <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" title="{{ __('Export') }}">
                                <i class="ti ti-file-export"></i>
                            </button>
                            {{ Form::close() }}
                        @endif
                        @can('Create Pay Slip')
                            <button type="button" class="btn btn-primary" id="bulk_payment" data-bs-toggle="tooltip" title="{{ __('Bulk Payment') }}">
                                <i class="ti ti-cash"></i>
                            </button>
                        @endcan
                    </div>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-lg-6 col-md-12">
                        <label class="form-label" for="payslip_emp_search">{{ __('Select Employee') }}</label>
                        <div class="payslip-emp-combo">
                            <input type="text" class="form-control" id="payslip_emp_search" placeholder="{{ __('Search employee...') }}" autocomplete="off">
                            <div class="payslip-emp-menu" id="payslip_emp_menu" hidden></div>
                            <select class="employee_id d-none" id="payslip_employee_id" aria-hidden="true">
                                @foreach ($employeeList as $id => $name)
                                    <option value="{{ $id }}" {{ (isset($_GET['employee_id']) && $_GET['employee_id'] == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6">
                        <label class="form-label">{{ __('Select Month') }}</label>
                        <select class="form-control month_date">
                            @foreach ($month as $k => $mon)
                                <option value="{{ $k }}" {{ date('m') == $k ? 'selected' : '' }}>{{ $mon }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6">
                        <label class="form-label">{{ __('Select Year') }}</label>
                        {{ Form::select('year', $year, date('Y'), ['class' => 'form-control year_date']) }}
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-render-column-cells">
                        <thead>
                            <tr>
                                <th>{{ __('Employee Id') }}</th>
                                @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Name') }}</th>
                                @endif

                                <th>{{ __('Salary') }}</th>
                                <th>{{ __('Net Salary') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css-page')
<style>
    .payslip-find-header {
        overflow: visible;
    }
    .payslip-find-header .form-control,
    .payslip-find-header select.form-control {
        height: 42px;
        min-height: 42px;
        line-height: 1.5;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 14px;
    }
    .payslip-emp-combo {
        position: relative;
    }
    .payslip-emp-menu {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 4px);
        z-index: 3000;
        max-height: 280px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 6px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }
    .payslip-emp-menu button {
        display: block;
        width: 100%;
        text-align: left;
        background: none;
        border: 0;
        padding: 9px 12px;
        font-size: 14px;
        line-height: 1.4;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #212529;
        cursor: pointer;
    }
    .payslip-emp-menu button:hover,
    .payslip-emp-menu button.is-active {
        background: #fff4e8;
        color: #d35400;
    }
    .payslip-find-header .btn-primary {
        height: 42px;
        min-width: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 14px;
    }
</style>
@endpush

@push('script-page')
    <script>
        $(document).ready(function() {
            var $empSelect = $('#payslip_employee_id');
            var $empSearch = $('#payslip_emp_search');
            var $empMenu = $('#payslip_emp_menu');

            function selectedEmployeeLabel() {
                var label = $.trim($empSelect.find('option:selected').text());
                return label || '{{ __('All') }}';
            }

            function renderEmployeeMenu(filter) {
                var q = (filter || '').toLowerCase();
                var html = '';
                $empSelect.find('option').each(function() {
                    var text = $.trim($(this).text());
                    if (q && text.toLowerCase().indexOf(q) === -1) {
                        return;
                    }
                    html += '<button type="button" data-value="' + $(this).attr('value') + '">' + text + '</button>';
                });
                if (!html) {
                    html = '<button type="button" disabled>{{ __('No employees found') }}</button>';
                }
                $empMenu.html(html).prop('hidden', false);
            }

            $empSearch.val(selectedEmployeeLabel());

            $empSearch.on('focus click', function() {
                if (this.value === selectedEmployeeLabel()) {
                    this.select();
                }
                renderEmployeeMenu($empSearch.val() === selectedEmployeeLabel() ? '' : $empSearch.val());
            });

            $empSearch.on('input', function() {
                renderEmployeeMenu(this.value);
            });

            $empMenu.on('click', 'button[data-value]', function() {
                var value = $(this).attr('data-value');
                var text = $.trim($(this).text());
                $empSelect.val(value).trigger('change');
                $empSearch.val(text);
                $empMenu.prop('hidden', true);
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.payslip-emp-combo').length) {
                    $empMenu.prop('hidden', true);
                    if (!$empSearch.val()) {
                        $empSearch.val(selectedEmployeeLabel());
                    }
                }
            });

            callback();

            function callback() {
                var month = $(".month_date").val();
                var year = $(".year_date").val();
                var employee_id = $(".employee_id").val();

                $('.filter_month').val(month);
                $('.filter_year').val(year);
                $('.filter_employee').val(employee_id);

                if (month == '') {
                    month = '{{ date('m', strtotime('last month')) }}';
                    year = '{{ date('Y') }}';

                    $('.filter_month').val(month);
                    $('.filter_year').val(year);
                }

                var datePicker = year + '-' + month;

                $.ajax({
                    url: '{{ route('payslip.search_json') }}',
                    type: 'POST',
                    data: {
                        "datePicker": datePicker,
                        "employee_id": employee_id,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        var datatable_data = {
                            data: data
                        };

                        function renderstatus(data, cell, row) {
                            if (data == 'Paid')
                                return '<div class="badge bg-success p-2 px-3 rounded"><a href="#" class="text-white">' +
                                    data + '</a></div>';
                            else
                                return '<div class="badge bg-danger p-2 px-3 rounded"><a href="#" class="text-white">' +
                                    data + '</a></div>';
                        }

                        function renderButton(data, cell, row) {

                            var $div = $(row);
                            employee_id = $div.find('td:eq(0)').text();
                            status = $div.find('td:eq(6)').text();

                            var month = $(".month_date").val();
                            var year = $(".year_date").val();
                            var id = employee_id;
                            var payslip_id = data;
                            var clickToPaid = '';
                            var payslip = '';
                            var view = '';
                            var edit = '';
                            var deleted = '';
                            var form = '';

                            if (data != 0) {
                                var payslip =
                                    '<a href="#" data-url="{{ url('payslip/pdf/') }}/' + id +
                                    '/' + datePicker +
                                    '" data-size="md-pdf"  data-ajax-popup="true" class="btn btn-primary" data-title="{{ __('Employee Payslip') }}">' +
                                    '{{ __('Payslip') }}' + '</a> ';
                            }

                            if (status == "UnPaid" && data != 0) {
                                clickToPaid = '<a href="{{ url('payslip/paysalary/') }}/' + id +
                                    '/' + datePicker + '"  class="view-btn primary-bg btn-sm">' +
                                    '{{ __('Click To Paid') }}' + '</a>  ';
                            }

                            if (data != 0) {
                                view =
                                    '<a href="#" data-url="{{ url('payslip/showemployee/') }}/' +
                                    payslip_id +
                                    '"  data-ajax-popup="true" class="view-btn gray-bg" data-title="{{ __('View Employee Detail') }}">' +
                                    '{{ __('View') }}' + '</a>';
                            }

                            if (data != 0 && status == "UnPaid") {
                                edit =
                                    '<a href="#" data-url="{{ url('payslip/editemployee/') }}/' +
                                    payslip_id +
                                    '"  data-ajax-popup="true" class="view-btn blue-bg" data-title="{{ __('Edit Employee salary') }}">' +
                                    '{{ __('Edit') }}' + '</a>';
                            }

                            var url = '{{ route('payslip.delete', ':id') }}';
                            url = url.replace(':id', payslip_id);

                            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                                if (data != 0) {
                                    deleted = '<a href="#"  data-url="' + url +
                                        '" class="payslip_delete view-btn red-bg" >' +
                                        '{{ __('Delete') }}' + '</a>';
                                }
                            @endif

                            return view + payslip + clickToPaid + edit + deleted + form;
                        }

                        console.clear();
                        var tr = '';
                        if (data.length > 0) {
                            $.each(data, function(indexInArray, valueOfElement) {
                                var status =
                                    '<div class="badge bg-danger p-2 px-3 rounded"><a href="#" class="text-white">' +
                                    valueOfElement[5] + '</a></div>';
                                if (valueOfElement[5] == 'Paid' || valueOfElement[5] ==
                                    'paid') {
                                    var status =
                                        '<div class="badge bg-success p-2 px-3 rounded"><a href="#" class="text-white">' +
                                        valueOfElement[5] + '</a></div>';
                                }
                                var id = valueOfElement[0];
                                var employee_id = valueOfElement[1];
                                var payslip_id = valueOfElement[6];

                                if (valueOfElement[6] != 0) {
                                    var payslip =
                                        '<a href="#" data-url="{{ url('payslip/pdf/') }}/' +
                                        id + '/' + datePicker +
                                        '" data-size="lg"  data-ajax-popup="true" class="btn-sm btn btn-warning" data-bs-toggle="tooltip" title="{{ __('Payslip') }}" data-title="{{ __('Employee Payslip') }}">' +
                                        '<i class="ti ti-download text-white"></i>' + '</a> ';
                                }
                                var clickToPaid = '';
                                var edit = '';

                                var url = '{{ route('payslip.delete', ':id') }}';
                                url = url.replace(':id', payslip_id);

                                @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
                                    var deleted = '<a href="#"  data-url="' + url +
                                        '" class="payslip_delete view-btn btn btn-danger ms-1 btn-sm" data-bs-toggle="tooltip" title="{{ __('Delete') }}" >' +
                                        '<i class="ti ti-trash text-white"></i>' + '</a>';
                                @else
                                    var deleted = '';
                                @endif

                                var url_employee = valueOfElement['url'];
                                @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
                                    tr +=
                                        '<tr>' +
                                        '<td> <a class="btn btn-outline-primary" href="' +
                                        url_employee + '">' + valueOfElement[1] + '</a></td> ' +
                                        '<td style="text-transform: uppercase;">' + valueOfElement[2] + '</td> ' +
                                        '<td>' + valueOfElement[3] + '</td>' +
                                        '<td>' + valueOfElement[4] + '</td>' +
                                        '<td>' + payslip + clickToPaid + edit + deleted +
                                        '</td>' +
                                        '</tr>';
                                @else
                                    tr +=
                                        '<tr>' +
                                        '<td> <a class="btn btn-outline-primary" href="' +
                                        url_employee + '">' + valueOfElement[1] + '</a></td> ' +
                                        '<td>' + valueOfElement[3] + '</td>' +
                                        '<td>' + valueOfElement[4] + '</td>' +
                                        '<td>' + payslip + clickToPaid + edit + deleted +
                                        '</td>' +
                                        '</tr>';
                                @endif
                            });
                        } else {
                            var colspan = $('#pc-dt-render-column-cells thead tr th').length;
                            var tr = '<tr><td class="dataTables-empty" colspan="' + colspan +
                                '">{{ __('No entries found') }}</td></tr>';
                        }

                        $('#pc-dt-render-column-cells tbody').html(tr);
                        var table = document.querySelector("#pc-dt-render-column-cells");
                        var datatable = new simpleDatatables.DataTable(table);
                    },
                    error: function(data) {

                    }
                });
            }

            $(document).on("change", ".month_date,.year_date,.employee_id", function() {
                callback();
            });

            //bulkpayment Click
            $(document).on("click", "#bulk_payment", function() {
                var month = $(".month_date").val();
                var year = $(".year_date").val();
                var datePicker = year + '_' + month;

            });
            $(document).on('click', '#bulk_payment',
                'a[data-ajax-popup="true"], button[data-ajax-popup="true"], div[data-ajax-popup="true"]',
                function() {
                    var month = $(".month_date").val();
                    var year = $(".year_date").val();
                    var datePicker = year + '-' + month;

                    var title = 'Bulk Payment';
                    var size = 'md';
                    var url = 'payslip/bulk_pay_create/' + datePicker;

                    // return false;

                    $("#commonModal .modal-title").html(title);
                    $("#commonModal .modal-dialog").addClass('modal-' + size);
                    $.ajax({
                        url: url,
                        success: function(data) {
                            // alert(data);
                            // return false;
                            if (data.length) {
                                $('#commonModal .body').html(data);
                                $("#commonModal").modal('show');
                                // common_bind();
                            } else {
                                show_toastr('error', 'Permission denied.');
                                $("#commonModal").modal('hide');
                            }
                        },
                        error: function(data) {
                            data = data.responseJSON;
                            show_toastr('error', data.error);
                        }
                    });
                });

            $(document).on("click", ".payslip_delete", function() {
                var confirmation = confirm("are you sure you want to delete this payslip?");
                var url = $(this).data('url');

                if (confirmation) {
                    $.ajax({
                        type: "GET",
                        url: url,
                        dataType: "JSON",
                        success: function(data) {
                            // show_toastr(data.status, data.msg, 'data.status');
                            show_toastr('error', 'Payslip Deleted Successfully', 'success');

                            setTimeout(function() {
                                location.reload();
                            }, 800)
                        },
                    });
                }
            });
        });
    </script>
@endpush
