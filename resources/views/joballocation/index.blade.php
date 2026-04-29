@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Job Allocation') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Manage Job Allocation') }}</li>
@endsection

@section('action-button')
        <a href="{{ route('joballocation.create') }}" data-ajax-popup="false" data-size="md"
            data-title="{{ __('Create New Job Allocation') }}" data-bs-toggle="tooltip" title="Create"
            class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mb-3 mb-sm-0">
                        <div class="d-flex align-items-center">
                            <div class="theme-avtar bg-primary">
                                <i class="ti ti-list"></i>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted">{{__('Total')}}</small>
                                <h6 class="m-0">{{__('Allocations')}}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{$data['total']}}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mb-3 mb-sm-0">
                        <div class="d-flex align-items-center">
                            <div class="theme-avtar bg-info">
                                <i class="ti ti-clock"></i>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted">{{__('Ongoing')}}</small>
                                <h6 class="m-0">{{__('Allocations')}}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{$data['Ongoing']}}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mb-3 mb-sm-0">
                        <div class="d-flex align-items-center">
                            <div class="theme-avtar bg-success">
                                <i class="ti ti-check"></i>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted">{{__('Completed')}}</small>
                                <h6 class="m-0">{{__('Allocations')}}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0">{{$data['Completed']}}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Client') }}</th>
                                <th>{{ __('Project') }}</th>
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('End Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allocations as $allocation)
                                <tr>
                                    <td>{{ !empty($allocation->client) ? $allocation->client->client_name : '-' }}</td>
                                    <td>{{ !empty($allocation->project) ? $allocation->project->project_name : '-' }}</td>
                                    <td>{{ \Auth::user()->dateFormat($allocation->start_date) }}</td>
                                    <td>{{ \Auth::user()->dateFormat($allocation->end_date) }}</td>
                                    <td>
                                        @if ($allocation->status == 'Approved')
                                            <span class="badge bg-success p-2 px-3 rounded">{{ $allocation->status }}</span>
                                        @elseif($allocation->status == 'Rejected')
                                            <span class="badge bg-danger p-2 px-3 rounded">{{ $allocation->status }}</span>
                                        @else
                                            <span class="badge bg-warning p-2 px-3 rounded">{{ $allocation->status }}</span>
                                        @endif
                                    </td>
                                    <td class="Action">
                                        <span>  
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{ route('joballocation.show', $allocation->id) }}"
                                                    class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="{{ __('Job Allocation Detail') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>

                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ route('joballocation.edit', $allocation->id) }}" class="mx-3 btn btn-sm  align-items-center"
                                                        data-ajax-popup="false" data-title="{{ __('Edit Job Allocation') }}"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>

                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['joballocation.destroy', $allocation->id], 'id' => 'delete-form-' . $allocation->id]) !!}
                                                    <a href="#!" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
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