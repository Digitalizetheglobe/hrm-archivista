@extends('layouts.admin')
@section('page-title')
    {{ __('Comp-Off Detail') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('compoff.index') }}">{{ __('Comp-Off') }}</a></li>
    <li class="breadcrumb-item">{{ __('Detail') }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Main Left Column: Comp-Off Metadata -->
    <div class="col-md-5">
        <div class="card shadow-lg border-0 mb-4" style="border-radius: 15px;">
            <div class="card-header bg-primary text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 text-white font-weight-bold"><i class="ti ti-info-circle me-2"></i>{{ __('Comp-Off Information') }}</h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-4 text-center">
                    <div class="bg-soft-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background-color: #e3f2fd; color: #0d6efd;">
                        <i class="ti ti-calendar-event" style="font-size: 36px;"></i>
                    </div>
                    <h5 class="font-weight-bold text-dark mb-1">{{ $comp_off->branch->name ?? '-' }}</h5>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill"><i class="ti ti-git-branch me-1"></i>{{ __('Branch') }}</span>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <label class="text-muted font-weight-bold mb-1">{{ __('Selected Departments') }}</label>
                    <div>
                        @foreach ($departments as $dept)
                            <span class="badge bg-soft-primary text-primary px-3 py-2 me-1 mb-1" style="font-size: 0.85rem;">{{ $dept }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted font-weight-bold mb-1">{{ __('Total Selected Dates') }}</label>
                    <div class="font-weight-bold text-dark" style="font-size: 1.1rem;">
                        <span class="badge bg-info px-3 py-2 rounded-pill"><i class="ti ti-calendar me-1"></i>{{ count($dates) }} {{ __('Dates') }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted font-weight-bold mb-1">{{ __('Created By') }}</label>
                    <div class="text-dark font-weight-bold">
                        <i class="ti ti-user me-1 text-primary"></i>{{ $comp_off->createdBy->name ?? '-' }}
                    </div>
                </div>

                <div class="mb-0">
                    <label class="text-muted font-weight-bold mb-1">{{ __('Created At') }}</label>
                    <div class="text-dark">
                        <i class="ti ti-clock me-1 text-primary"></i>{{ \Auth::user()->dateFormat($comp_off->created_at) }} {{ \Auth::user()->timeFormat($comp_off->created_at) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Dates Card -->
        <div class="card shadow-lg border-0 mb-4" style="border-radius: 15px;">
            <div class="card-header bg-primary text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px; background-color: #6c757d !important;">
                <h5 class="mb-0 text-white font-weight-bold"><i class="ti ti-calendar-stats me-2"></i>{{ __('Comp-Off Dates List') }}</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($dates as $date)
                        <div class="p-3 bg-light border rounded text-center" style="min-width: 110px; border-radius: 10px !important;">
                            <i class="ti ti-calendar text-primary mb-1 d-block" style="font-size: 20px;"></i>
                            <span class="font-weight-bold text-dark d-block" style="font-size: 0.9rem;">{{ \Auth::user()->dateFormat($date) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Main Right Column: Assigned Employees List -->
    <div class="col-md-7">
        <div class="card shadow-lg border-0" style="border-radius: 15px;">
            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between" style="border-top-left-radius: 15px; border-top-right-radius: 15px; background-color: #28a745 !important;">
                <h5 class="mb-0 text-white font-weight-bold"><i class="ti ti-users me-2"></i>{{ __('Assigned Employees') }}</h5>
                <span class="badge bg-white text-primary font-weight-bold px-3 py-2 rounded-pill">{{ count($employees) }} {{ __('Employees') }}</span>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Department') }}</th>
                                <th>{{ __('Email') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-container me-3 bg-soft-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: #e2f0d9; color: #28a745; font-weight: bold;">
                                                {{ strtoupper(substr($employee->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-dark font-weight-bold">{{ $employee->name }}</h6>
                                                <small class="text-muted">{{ $employee->employee_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $employee->department->name ?? '-' }}</span>
                                    </td>
                                    <td>{{ $employee->email }}</td>
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
