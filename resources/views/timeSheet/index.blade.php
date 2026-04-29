@extends('layouts.admin')
@section('page-title')
    {{ __('Manage TimeSheet') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('TimeSheet') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('timesheet.export') }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a>

    @can('Create TimeSheet')
        <a href="#" data-url="{{ route('timesheet.create') }}" data-ajax-popup="true" data-size="xl"
            data-title="{{ __('Create New TimeSheet') }}" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2" id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['timesheet.index'], 'method' => 'get', 'id' => 'timesheet_filter']) }}
                    <div class="row align-items-end flex-nowrap">
                        @if(Auth::user()->type == 'employee')
                        <div class="col-auto">
                            <div class="card bg-primary mb-0 text-white">
                                <div class="card-body p-3">
                                    <h6 class="mb-0">{{ __('Total Hours') }}</h6>
                                    <h3 class="mb-0">{{ $totalTime ?? '0' }} hrs</h3>
                                    @if(request()->filled('start_date') || request()->filled('end_date'))
                                        <small>{{ \Auth::user()->dateFormat(request('start_date')) }} to {{ \Auth::user()->dateFormat(request('end_date')) }}</small>
                                    @else
                                        <small>{{ __('Today') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="col-auto">
                            <div class="btn-box">
                                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d'), ['class' => 'month-btn form-control current_date', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="btn-box">
                                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'), ['class' => 'month-btn form-control current_date', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                        
                        @if (\Auth::user()->type != 'employee')
                        <div class="col-auto">
                            <div class="btn-box">
                                {{ Form::label('employee', __('Employee'), ['class' => 'form-label']) }}
                                {{ Form::select('employee', $employeesList, isset($_GET['employee']) ? $_GET['employee'] : '', ['class' => 'form-control select ', 'id' => 'employee_id']) }}
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6">
                            <div class="btn-box">
                                {{ Form::label('client', __('Client'), ['class' => 'form-label']) }}
                                {{ Form::select('client', $clients, isset($_GET['client']) ? $_GET['client'] : '', ['class' => 'form-control select', 'id' => 'client_id']) }}
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6">
                            <div class="btn-box">
                                {{ Form::label('project', __('Project'), ['class' => 'form-label']) }}
                                {{ Form::select('project', $projects, isset($_GET['project']) ? $_GET['project'] : '', ['class' => 'form-control select', 'id' => 'project_id']) }}
                            </div>
                        </div>
                        @else
                            {!! Form::hidden('employee', !empty($employeesList) ? $employeesList->id : 0, ['id' => 'employee_id']) !!}
                        @endif
                        
                        <div class="col-auto">
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="document.getElementById('timesheet_filter').submit(); return false;"
                                data-bs-toggle="tooltip" title="" data-bs-original-title="apply">
                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                            </a>
                            <a href="{{ route('timesheet.index') }}" class="btn btn-sm btn-danger"
                                data-bs-toggle="tooltip" title="" data-bs-original-title="Reset">
                                <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-12">
    <div class="card">
        <div class="card-header card-body table-border-style">
            <div class="card-body py-0">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Employee') }}</th>
                                @endif
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Total Time') }}</th>
                                @if (\Auth::user()->type != 'employee')
                                    <th>{{ __('Client') }}</th>
                                    <th>{{ __('Project') }}</th>
                                    <th>{{ __('Expense') }}</th>
                                    <th>{{ __('Location') }}</th>
                                    <th>{{ __('Narration') }}</th>
                                    <th>{{ __('Billable') }}</th>
                                @else
                                    <th>{{ __('Client Name') }}</th>
                                    <th>{{ __('Project Name') }}</th>
                                    <th>{{ __('Total Time') }}</th>
                                    <th>{{ __('Billable') }}</th>
                                @endif
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($timeSheets as $timeSheet)
                                <tr>
                                    @if (\Auth::user()->type != 'employee')
                                        <td>{{ !empty($timeSheet->employee) ? $timeSheet->employee->name : '' }}</td>
                                    @endif
                                    <td>{{ \Auth::user()->dateFormat($timeSheet->date) }}</td>
                                    <td>{{ $timeSheet->total_time }} hrs</td>
                                    
                                    @if (\Auth::user()->type != 'employee')
                                        <td>{{ $timeSheet->client->client_name ?? '' }}</td>
                                        <td>{{ $timeSheet->project->project_name ?? '' }}</td>
                                        <td>{{ $timeSheet->expense }}</td>
                                        <td>{{ $timeSheet->location }}</td>
                                        <td>{{ $timeSheet->narration }}</td>
                                        <td>{{ $timeSheet->billable }}</td>
                                    @else
                                        <td>{{ $timeSheet->client->client_name ?? '' }}</td>
                                        <td>{{ $timeSheet->project->project_name ?? '' }}</td>
                                        <td>{{ $timeSheet->total_time }}</td>
                                        <td>{{ $timeSheet->billable }}</td>
                                    @endif
                                    
                                    <td class="Action">
                                        <span>
                                            @can('Edit TimeSheet')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                        data-url="{{ route('timesheet.edit', $timeSheet->id) }}"
                                                        data-ajax-popup="true" data-size="xl" data-bs-toggle="tooltip"
                                                        title="{{ __('Edit TimeSheet') }}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('Delete TimeSheet')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open([
                                                        'method' => 'DELETE',
                                                        'route' => ['timesheet.destroy', $timeSheet->id],
                                                        'id' => 'delete-form-' . $timeSheet->id,
                                                    ]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
    $(document).ready(function() {
        // Set default dates to today if not already set
        if (!$('[name="start_date"]').val()) {
            $('[name="start_date"]').val(new Date().toISOString().split('T')[0]);
        }
        if (!$('[name="end_date"]').val()) {
            $('[name="end_date"]').val(new Date().toISOString().split('T')[0]);
        }

        // Auto-submit form when date range changes
        $('[name="start_date"], [name="end_date"]').on('change', function() {
            $('#timesheet_filter').submit();
        });

        // Load projects when client changes
        $('#client_id').on('change', function() {
            var clientId = $(this).val();
            if(clientId) {
                $.ajax({
            url: `/get-client-projects/${clientId}`,
                    type: 'GET',
                    data: {client_id: clientId},
                    success: function(data) {
                        $('#project_id').empty();
                        $('#project_id').append('<option value="All">All</option>');
                        $.each(data, function(key, value) {
                            $('#project_id').append('<option value="'+key+'">'+value+'</option>');
                        });
                    }
                });
            } else {
                $('#project_id').empty();
                $('#project_id').append('<option value="All">All</option>');
            }
        });

        // Submit form on filter change (for non-employee users)
        @if (\Auth::user()->type != 'employee')
            $('#employee_id, #client_id, #project_id').on('change', function() {
                $('#timesheet_filter').submit();
            });
        @endif
    });
</script>
@endpush    