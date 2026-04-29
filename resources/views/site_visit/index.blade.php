@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Site Visit') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Site Visit') }}</li>
@endsection

@section('action-button')
    <div class="float-end">
        @if(Auth::user()->type == 'employee' || \Auth::user()->can('Create Attendance'))
            <a href="#" data-url="{{ route('site-visit.create') }}" data-ajax-popup="true" data-title="{{ __('Create Site Visit') }}" data-bs-toggle="tooltip" title="{{ __('Create') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endif
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    @if(Auth::user()->type != 'employee')
                                        <th>{{ __('Employee') }}</th>
                                    @endif
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Location') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($siteVisits as $siteVisit)
                                    <tr>
                                        @if(Auth::user()->type != 'employee')
                                            <td>{{ !empty($siteVisit->employee) ? $siteVisit->employee->name : '' }}</td>
                                        @endif
                                        <td>{{ Auth::user()->dateFormat($siteVisit->date) }}</td>
                                        <td>{{ $siteVisit->location }}</td>
                                        <td>
                                            @if($siteVisit->status == 'Pending')
                                                <div class="status_badge badge bg-warning p-2 px-3 rounded">{{ __($siteVisit->status) }}</div>
                                            @elseif($siteVisit->status == 'Approved')
                                                <div class="status_badge badge bg-success p-2 px-3 rounded">{{ __($siteVisit->status) }}</div>
                                            @else
                                                <div class="status_badge badge bg-danger p-2 px-3 rounded">{{ __($siteVisit->status) }}</div>
                                            @endif
                                        </td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" data-url="{{ route('site-visit.show', $siteVisit->id) }}" data-ajax-popup="true" data-title="{{ __('View Site Visit') }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                                @if(Auth::user()->type != 'employee' && $siteVisit->status == 'Pending')
                                                    <div class="action-btn bg-success ms-2">
                                                        {!! Form::open(['method' => 'POST', 'route' => ['site-visit.approve', $siteVisit->id], 'id' => 'approve-form-' . $siteVisit->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Approve') }}" onclick="document.getElementById('approve-form-{{ $siteVisit->id }}').submit();">
                                                                <i class="ti ti-check text-white"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'POST', 'route' => ['site-visit.reject', $siteVisit->id], 'id' => 'reject-form-' . $siteVisit->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Reject') }}" onclick="document.getElementById('reject-form-{{ $siteVisit->id }}').submit();">
                                                                <i class="ti ti-x text-white"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endif
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['site-visit.destroy', $siteVisit->id], 'id' => 'delete-form-' . $siteVisit->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-confirm="{{ __('Are You Sure?') . '|' . __('This action cannot be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{ $siteVisit->id }}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
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
