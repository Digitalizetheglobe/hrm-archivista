@extends('layouts.admin')

@section('page-title')
    {{ __('Employee-Wise Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee-Wise Report') }}</li>
@endsection

@push('script-page')
<script>
    $(document).ready(function() {
    console.log('Document ready - initializing employee report');

    // Branch change event
    $(document).on('change', 'select[name=branch_id]', function() {
        var branch_id = $(this).val();
        console.log('Branch changed:', branch_id);
        getDepartment(branch_id);
        // Reset employee and date fields
        $('#employee_filter').empty().append('<option value="">{{ __("All Employees") }}</option>').prop('disabled', true);
        $('#start_date, #end_date').prop('disabled', true);
        $('#searchBtn').prop('disabled', true);
    });

    // Department change event
    $(document).on('change', 'select[name=department_id]', function() {
        var department_id = $(this).val();
        console.log('Department changed:', department_id);
        if(department_id) {
            getEmployee(department_id);
        } else {
            $('#employee_filter').empty().append('<option value="">{{ __("All Employees") }}</option>').prop('disabled', true);
            $('#start_date, #end_date').prop('disabled', true);
            $('#searchBtn').prop('disabled', true);
        }
    });

    // Employee change event
    $(document).on('change', 'select[name=employee_id]', function() {
        var selectedEmployee = $(this).val();
        console.log('Employee selected:', selectedEmployee);
        if(selectedEmployee) {
            $('#start_date, #end_date').prop('disabled', false);
            $('#searchBtn').prop('disabled', false);
        } else {
            $('#start_date, #end_date').prop('disabled', true);
            $('#searchBtn').prop('disabled', true);
        }
    });

    function getDepartment(bid) {
        console.log('Fetching departments for branch:', bid);
        $.ajax({
            url: '{{ route("get.departments.by.branch") }}',
            type: 'POST',
            data: {
                "branch_id": bid,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {
                console.log('Departments received:', data);
                $('#department_filter').empty();
                $('#department_filter').append('<option value="">{{ __("All Departments") }}</option>');
                $.each(data, function(key, value) {
                    $('#department_filter').append('<option value="' + key + '">' + value + '</option>');
                });
                $('#department_filter').prop('disabled', false);
            },
            error: function(xhr) {
                console.error('Error fetching departments:', xhr.responseText);
            }
        });
    }

    function getEmployee(did) {
        console.log('Fetching employees for department:', did);
        $.ajax({
            url: '{{ route("get.employees.by.department") }}',
            type: 'POST',
            data: {
                "department_id": did,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {
                console.log('Employees received:', data);
                $('#employee_filter').empty();
                $('#employee_filter').append('<option value="">{{ __("All Employees") }}</option>');
                $.each(data, function(key, value) {
                    $('#employee_filter').append('<option value="' + key + '">' + value + '</option>');
                });
                $('#employee_filter').prop('disabled', false);
            },
            error: function(xhr) {
                console.error('Error fetching employees:', xhr.responseText);
            }
        });
    }

    // Form submission debug
    $('#filterForm').on('submit', function(e) {
        console.log('Form submitted with data:', {
            branch_id: $('select[name=branch_id]').val(),
            department_id: $('select[name=department_id]').val(),
            employee_id: $('select[name=employee_id]').val(),
            start_date: $('input[name=start_date]').val(),
            end_date: $('input[name=end_date]').val()
        });
    });

    // Initialize DataTable if table exists
    if ($('.table').length) {
        console.log('Initializing DataTable');
        $('.table').DataTable({
            responsive: true,
            dom: '<"row"<"col-md-6"B><"col-md-6"f>>rtlp',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            }
        });
    }
});
</script>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ route('reports.employee-wise') }}" id="filterForm">
                    <div class="d-flex flex-wrap justify-content-end gap-3">
                        <!-- Branch Selection -->
                        <div class="form-group" style="min-width: 200px;">
                            <label for="branch_filter" class="form-label">{{ __('Select Branch') }}</label>
                            <select class="form-control select w-100" id="branch_filter" name="branch_id">
                                <option value="">{{ __('All Branches') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Department Selection -->
                        <div class="form-group" style="min-width: 200px;">
                            <label for="department_filter" class="form-label">{{ __('Select Department') }}</label>
                            <select class="form-control select w-100" id="department_filter" name="department_id" {{ !request('branch_id') ? 'disabled' : '' }}>
                                <option value="">{{ __('All Departments') }}</option>
                                @if(request('branch_id') && $departments)
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Employee Selection -->
                        <div class="form-group" style="min-width: 200px;">
                            <label for="employee_filter" class="form-label">{{ __('Select Employee') }}</label>
                            <select class="form-control select w-100" id="employee_filter" name="employee_id" {{ !request('department_id') ? 'disabled' : '' }}>
                                <option value="">{{ __('All Employees') }}</option>
                                @if(request('department_id') && $employees)
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Start Date -->
                        <div class="form-group" style="min-width: 160px;">
                            <label for="start_date" class="form-label">{{ __('Start Date') }}</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ request('start_date') ?? date('Y-m-01') }}" {{ !request('employee_id') ? 'disabled' : '' }}>
                        </div>

                        <!-- End Date -->
                        <div class="form-group" style="min-width: 160px;">
                            <label for="end_date" class="form-label">{{ __('End Date') }}</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ request('end_date') ?? date('Y-m-t') }}" {{ !request('employee_id') ? 'disabled' : '' }}>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-sm btn-primary"
                                title="{{ __('Apply') }}" id="searchBtn" {{ !request('employee_id') ? 'disabled' : '' }}>
                                <i class="ti ti-search"></i>
                            </button>
                            <a href="{{ route('reports.employee-wise') }}" class="btn btn-sm btn-danger" title="{{ __('Reset') }}">
                                <i class="ti ti-trash-off"></i>
                            </a>
                            @if(request('employee_id'))
                            <a href="{{ route('employee.wise.export', request()->all()) }}" class="btn btn-sm btn-success"
                                data-bs-toggle="tooltip" title="{{ __('Download Excel') }}">
                                <i class="ti ti-download"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



@if(request('employee_id'))
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Total Projects Worked On') }}</h5>
                                <p class="card-text display-6">{{ $reportData['total_projects'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Total Time Worked') }}</h5>
                                <p class="card-text display-6">{{ number_format($reportData['total_hours'], 2) }} hrs</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">{{ __(' Hourly Rate') }}</h5>
                                <p class="card-text display-6">{{ number_format($reportData['total_hours'] > 0 ? $reportData['total_cost'] / $reportData['total_hours'] : 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body">
                                
                                <h5 class="card-title">{{ __('Total Cost') }}</h5>
                                <p class="card-text display-6">{{ number_format($reportData['total_cost'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(request('employee_id'))
    <script>
        // Debug: Output employee and reportData to browser console
        console.log('employee:', @json($employee));
        console.log('reportData:', @json($reportData));
    </script>
@endif
<div class="col-xl-12">
    <div class="card">
        <div class="card-header card-body table-border-style">
            <div class="table-responsive">
                <table class="table" id="pc-dt-simple">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Project') }}</th>
                            <th>{{ __('Time Spent') }}</th>
                            <th>{{ __('Description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timesheetDetails as $timesheet)
                            <tr>
                                <td>{{ \Auth::user()->dateFormat($timesheet->date) }}</td>
                                <td>{{ $timesheet->project->project_name ?? '' }}</td>
                                <td>{{ number_format($timesheet->total_time, 2) }} hrs</td>
                                <td>{{ $timesheet->narration }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection