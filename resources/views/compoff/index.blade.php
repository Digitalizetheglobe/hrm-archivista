@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Comp-Off') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Manage Comp-Off') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('compoff.create') }}" data-ajax-popup="false" data-size="md"
        data-title="{{ __('Create New Comp-Off') }}" data-bs-toggle="tooltip" title="Create"
        class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i>
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mb-3 mb-sm-0">
                        <div class="d-flex align-items-center">
                            <div class="theme-avtar bg-primary" style="border-radius: 12px; padding: 12px;">
                                <i class="ti ti-list text-white" style="font-size: 24px;"></i>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted">{{ __('Total') }}</small>
                                <h6 class="m-0 font-weight-bold">{{ __('Comp-Off Entries') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0 font-weight-bold text-primary">{{ count($comp_offs) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table align-items-center" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Departments') }}</th>
                                <th>{{ __('Selected Dates') }}</th>
                                <th>{{ __('Employees Count') }}</th>
                                <th>{{ __('Created At') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($comp_offs as $comp_off)
                                @php
                                    $deptIds = json_decode($comp_off->department_ids, true) ?? [];
                                    $empIds = json_decode($comp_off->employee_ids, true) ?? [];
                                    $dates = json_decode($comp_off->dates, true) ?? [];
                                    
                                    $depts = \App\Models\Department::whereIn('id', $deptIds)->pluck('name')->toArray();
                                @endphp
                                <tr>
                                    <td class="font-weight-bold">{{ !empty($comp_off->branch) ? $comp_off->branch->name : '-' }}</td>
                                    <td>
                                        @foreach($depts as $deptName)
                                            <span class="badge bg-light text-dark border me-1">{{ $deptName }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($dates as $date)
                                            <span class="badge bg-soft-primary text-primary me-1">{{ \Auth::user()->dateFormat($date) }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="badge bg-info px-3 py-2 rounded-pill font-weight-bold">{{ count($empIds) }} {{ __('Employees') }}</span>
                                    </td>
                                    <td>{{ \Auth::user()->dateFormat($comp_off->created_at) }}</td>
                                    <td class="Action">
                                        <span>  
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{ route('compoff.show', $comp_off->id) }}"
                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="{{ __('Comp-Off Detail') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>

                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('compoff.edit', $comp_off->id) }}" class="mx-3 btn btn-sm align-items-center"
                                                    data-ajax-popup="false" data-title="{{ __('Edit Comp-Off') }}"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>

                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['compoff.destroy', $comp_off->id], 'id' => 'delete-form-' . $comp_off->id]) !!}
                                                <a href="#!" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                    data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                    title="{{ __('Delete') }}">
                                                    <i class="ti ti-trash text-white"></i></a>
                                                {!! Form::close() !!}
                                            </div>
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
