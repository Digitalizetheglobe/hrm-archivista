@extends('layouts.admin')

@section('page-title')
    {{ __('Project-Wise Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Project-Wise Report') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ route('project.wise.report') }}" id="filterForm">
                    <div class="d-flex flex-wrap gap-3">
                        
                        <div class="form-group">
                            <label for="project_filter">{{ __('Select Project') }}</label>
                            <select class="form-control select mt-1" id="project_filter" name="project_id">
                                <option value="">{{ __('All Projects') }}</option>
                                @foreach($allProjects as $project)
                                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->project_name }} ({{ $project->client->name ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_date">{{ __('Start Date') }}</label>
                            <input type="date" name="start_date" id="start_date" class="form-control mt-1"
                                value="{{ request('start_date') ?? date('Y-m-01') }}">
                        </div>

                        <div class="form-group">
                            <label for="end_date">{{ __('End Date') }}</label>
                            <input type="date" name="end_date" id="end_date" class="form-control mt-1"
                                value="{{ request('end_date') ?? date('Y-m-t') }}">
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="document.getElementById('filterForm').submit(); return false;"
                                data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                <i class="ti ti-search"></i>
                            </a>

                            <a href="{{ route('reports.project-wise') }}" class="btn btn-sm btn-danger"
                                data-bs-toggle="tooltip" title="{{ __('Reset') }}">
                                <i class="ti ti-trash-off text-white-off"></i>
                            </a>
                            
                            @if(request('project_id'))
                            <a href="{{ route('project.wise.export', request()->all()) }}" class="btn btn-sm btn-success"
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

@if(request('project_id'))
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Total Employees') }}</h5>
                                <p class="card-text display-6">{{ $selectedProjectData['total_employees'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Total Time Spent') }}</h5>
                                <p class="card-text display-6">{{ number_format($selectedProjectData['total_hours'], 2) }} hrs</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Total Expense') }}</h5>
                                <p class="card-text display-6">{{ number_format($selectedProjectData['total_expense'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Total Cost') }}</h5>
                                <p class="card-text display-6">{{ number_format($totalCost, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

    @if(request('project_id'))
    <script>
        // Debug: Output timesheetDetails to browser console
        console.log('timesheetDetails:', @json($timesheetDetails));
    </script>
@endif
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                {{-- <h5></h5> --}}
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Time Spent') }}</th>
                                    <th>{{ __('Hourly Rate') }}</th>
                                    <th>{{ __('Cost') }}</th>
                                    <th>{{ __('Expense') }}</th>
                                    <th>{{ __('Description') }}</th>
                                </tr>
                        </thead>
                        <tbody>
                                @foreach($timesheetDetails as $timesheet)
                                    <tr>
                                        <td>{{ \Auth::user()->dateFormat($timesheet->date) }}</td>
                                        <td>{{ $timesheet->employee->name ?? '' }}</td>
                                        <td>{{ number_format($timesheet->total_time, 2) }} hrs</td>
                                        <td>{{ $timesheet->employee->hourly_charged ?? 0 }}</td>
                                        <td>{{ number_format($timesheet->total_time * ($timesheet->employee->hourly_charged ?? 0), 2) }}</td>
                                        <td>{{ number_format($timesheet->expense, 2) }}</td>
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

@push('script-page')
<script>
    $(document).ready(function() {
        // Auto-submit form when project changes
        $('#project_filter').change(function() {
            $('#filterForm').submit();
        });

        // Initialize select2
        $('.select').select2({
            width: '100%'
        });
        
        // Make table responsive with DataTables (optional)
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
    });
</script>
@endpush