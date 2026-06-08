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
        @if(\Auth::user()->type == 'company')
            <a href="#" data-bs-toggle="modal" data-bs-target="#customEmailModal" class="btn btn-sm btn-info" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Custom Approval Email') }}">
                <i class="ti ti-mail"></i>
            </a>
        @endif
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
                    <ul class="nav nav-tabs mb-3" id="siteVisitTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab" aria-controls="approved" aria-selected="true">
                                {{ __('Approved') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="false">
                                {{ __('Pending') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab" aria-controls="rejected" aria-selected="false">
                                {{ __('Rejected') }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="siteVisitTabsContent">
                        <!-- Approved Tab -->
                        <div class="tab-pane fade show active" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                            <div class="table-responsive">
                                <table class="table pc-dt-simple">
                                    <thead>
                                        <tr>
                                            @if(Auth::user()->type != 'employee')
                                                <th>{{ __('Employee') }}</th>
                                            @endif
                                            <th>{{ __('Start Date') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Location') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th width="200px">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($siteVisits as $siteVisit)
                                            @if($siteVisit->status == 'Approved')
                                            <tr>
                                                @if(Auth::user()->type != 'employee')
                                                    <td>{{ !empty($siteVisit->employee) ? $siteVisit->employee->name : '' }}</td>
                                                @endif
                                                <td>{{ Auth::user()->dateFormat($siteVisit->start_date) }}</td>
                                                <td>{{ Auth::user()->dateFormat($siteVisit->end_date) }}</td>
                                                <td>{{ $siteVisit->location }}</td>
                                                <td>
                                                    <div class="status_badge badge bg-success p-2 px-3 rounded">{{ __($siteVisit->status) }}</div>
                                                </td>
                                                <td class="Action">
                                                    <span>
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" data-url="{{ route('site-visit.show', $siteVisit->id) }}" data-ajax-popup="true" data-title="{{ __('View Site Visit') }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
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
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Pending Tab -->
                        <div class="tab-pane fade" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                            <div class="table-responsive">
                                <table class="table pc-dt-simple">
                                    <thead>
                                        <tr>
                                            @if(Auth::user()->type != 'employee')
                                                <th>{{ __('Employee') }}</th>
                                            @endif
                                            <th>{{ __('Start Date') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Location') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th width="200px">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($siteVisits as $siteVisit)
                                            @if($siteVisit->status == 'Pending')
                                            <tr>
                                                @if(Auth::user()->type != 'employee')
                                                    <td>{{ !empty($siteVisit->employee) ? $siteVisit->employee->name : '' }}</td>
                                                @endif
                                                <td>{{ Auth::user()->dateFormat($siteVisit->start_date) }}</td>
                                                <td>{{ Auth::user()->dateFormat($siteVisit->end_date) }}</td>
                                                <td>{{ $siteVisit->location }}</td>
                                                <td>
                                                    <div class="status_badge badge bg-warning p-2 px-3 rounded">{{ __($siteVisit->status) }}</div>
                                                </td>
                                                <td class="Action">
                                                    <span>
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" data-url="{{ route('site-visit.show', $siteVisit->id) }}" data-ajax-popup="true" data-title="{{ __('View Site Visit') }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
                                                        @if(Auth::user()->type != 'employee')
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
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Rejected Tab -->
                        <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
                            <div class="table-responsive">
                                <table class="table pc-dt-simple">
                                    <thead>
                                        <tr>
                                            @if(Auth::user()->type != 'employee')
                                                <th>{{ __('Employee') }}</th>
                                            @endif
                                            <th>{{ __('Start Date') }}</th>
                                            <th>{{ __('End Date') }}</th>
                                            <th>{{ __('Location') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th width="200px">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($siteVisits as $siteVisit)
                                            @if($siteVisit->status == 'Rejected')
                                            <tr>
                                                @if(Auth::user()->type != 'employee')
                                                    <td>{{ !empty($siteVisit->employee) ? $siteVisit->employee->name : '' }}</td>
                                                @endif
                                                <td>{{ Auth::user()->dateFormat($siteVisit->start_date) }}</td>
                                                <td>{{ Auth::user()->dateFormat($siteVisit->end_date) }}</td>
                                                <td>{{ $siteVisit->location }}</td>
                                                <td>
                                                    <div class="status_badge badge bg-danger p-2 px-3 rounded">{{ __($siteVisit->status) }}</div>
                                                </td>
                                                <td class="Action">
                                                    <span>
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" data-url="{{ route('site-visit.show', $siteVisit->id) }}" data-ajax-popup="true" data-title="{{ __('View Site Visit') }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
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
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(\Auth::user()->type == 'company')
    <!-- Custom Email Modal -->
    <div class="modal fade" id="customEmailModal" tabindex="-1" aria-labelledby="customEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customEmailModalLabel">{{ __('Custom Site Visit Approval Email') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                {{ Form::open(['route' => ['site-visit.save_custom_email'], 'method' => 'POST']) }}
                <div class="modal-body">
                    @php
                        $settings = \App\Models\Utility::settings();
                    @endphp
                    <div class="alert alert-info">
                        <strong>{{ __('Available Placeholders:') }}</strong><br>
                        <code>{employee_name}</code> - Employee Name<br>
                        <code>{status}</code> - Status (Approved)<br>
                        <code>{location}</code> - Location<br>
                        <code>{start_date}</code> - Start Date<br>
                        <code>{end_date}</code> - End Date<br>
                        <code>{app_name}</code> - App Name
                    </div>
                    <div class="form-group">
                        {{ Form::label('subject', __('Email Subject'), ['class' => 'col-form-label']) }}
                        {{ Form::text('subject', $settings['custom_site_visit_approve_subject'] ?? '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter email subject')]) }}
                    </div>
                    <div class="form-group">
                        {{ Form::label('body', __('Email Body (HTML supported)'), ['class' => 'col-form-label']) }}
                        {{ Form::textarea('body', $settings['custom_site_visit_approve_body'] ?? '', ['class' => 'form-control', 'rows' => '8', 'required' => 'required', 'placeholder' => __('Enter email body...')]) }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
    @endif
@endsection
