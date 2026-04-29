@extends('layouts.admin')

@section('page-title')
   {{ __('Manage Leave Type') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leave Type') }}</li>
@endsection

@section('action-button')
    @can('Create Branch')
        <a href="#" data-url="{{ route('leavetype.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Leave Type') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection


@section('content')
<div class="row">

        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">

                    <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Leave Type') }}</th>
                                <th>{{ __('Period') }}</th>
                                <th>{{ __('Days') }}</th>
                                <th>{{ __('Eligible Employees') }}</th>
                                <th width="200px">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leavetypes as $leavetype)
                                <tr>
                                    <td>{{ $leavetype->title }}</td>
                                    <td><span class="badge bg-{{ $leavetype->type == 'monthly' ? 'info' : 'primary' }}">{{ __(ucfirst($leavetype->type)) }}</span></td>
                                    <td>
                                        @if($leavetype->is_unlimited)
                                            <span class="badge bg-success">{{ __('Unlimited') }}</span>
                                        @else
                                            {{ $leavetype->days }} 
                                            @if($leavetype->type == 'monthly')
                                                <small class="text-muted">{{ __('/month') }}</small>
                                            @else
                                                <small class="text-muted">{{ __('/year') }}</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($leavetype->eligible_employee_types && count($leavetype->eligible_employee_types) > 0)
                                            @foreach($leavetype->eligible_employee_types as $type)
                                                @switch($type)
                                                    @case('payroll_confirm')
                                                        <span class="badge bg-success">{{ __('Payroll - Confirm') }}</span>
                                                        @break
                                                    @case('payroll_not_confirm')
                                                        <span class="badge bg-secondary">{{ __('Payroll - Not Confirm') }}</span>
                                                        @break
                                                    @case('contract_confirm')
                                                        <span class="badge bg-info">{{ __('Contract - Confirm') }}</span>
                                                        @break
                                                    @case('contract_not_confirm')
                                                        <span class="badge bg-warning">{{ __('Contract - Not Confirm') }}</span>
                                                        @break
                                                @endswitch
                                            @endforeach
                                        @else
                                            <span class="text-muted">{{ __('All Employees') }}</span>
                                        @endif
                                    </td>
                                    <td class="Action">
                                        <span>
                                            @can('Edit Leave Type')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                        data-url="{{ URL::to('leavetype/' . $leavetype->id . '/edit') }}"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title=""
                                                        data-title="{{ __('Edit Leave Type') }}"
                                                        data-bs-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('Delete Leave Type')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['leavetype.destroy', $leavetype->id], 'id' => 'delete-form-' . $leavetype->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                        aria-label="Delete"><i
                                                            class="ti ti-trash text-white "></i></a>
                                                    </form>
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
