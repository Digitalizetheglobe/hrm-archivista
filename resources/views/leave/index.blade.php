@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Leave') }}
@endsection


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leave ') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('leave.export') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Export') }}">
        <i class="ti ti-file-export"></i>
    </a>

    <a href="{{ route('leave.calender') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Calendar View') }}">
        <i class="ti ti-calendar"></i>
    </a>

    @can('Create Leave')
        <a href="#" data-url="{{ route('leave.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Leave') }}" data-size="lg" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    @endcan
@endsection

@section('content')
    <style>
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        .border-left-secondary {
            border-left: 0.25rem solid #858796 !important;
        }
        .shadow {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
        }
        .card-body {
            flex: 1 1 auto;
            min-height: 1px;
            padding: 1.25rem;
        }
        .text-xs {
            font-size: 0.7rem;
        }
        .font-weight-bold {
            font-weight: 700 !important;
        }
        .text-uppercase {
            text-transform: uppercase !important;
        }
        .mb-1 {
            margin-bottom: 0.25rem !important;
        }
        .h5 {
            font-size: 1.25rem;
        }
        .mb-0 {
            margin-bottom: 0 !important;
        }
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        .text-muted {
            color: #858796 !important;
        }
        .mr-2 {
            margin-right: 0.5rem !important;
        }
        .col-auto {
            flex: 0 0 auto;
            width: auto;
            max-width: 100%;
        }
        .fa-2x {
            font-size: 2rem;
        }
        .text-gray-300 {
            color: #dddfeb !important;
        }
        .mt-2 {
            margin-top: 0.5rem !important;
        }
        .progress {
            display: flex;
            height: 1rem;
            overflow: hidden;
            font-size: 0.75rem;
            background-color: #e9ecef;
            border-radius: 0.35rem;
        }
        .progress-bar {
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            background-color: #4e73df;
            transition: width 0.6s ease;
        }
        .bg-primary {
            background-color: #4e73df !important;
        }
        .bg-info {
            background-color: #36b9cc !important;
        }
        .small {
            font-size: 80%;
            font-weight: 400;
        }
    </style>
    
    {{-- Leave Balance Dashboard --}}
    @if (\Auth::user()->type == 'employee' && !empty($leaveBalances))
        <div class="row mb-4 mt-2">
            {{-- Special handling for Payroll employees - show specific cards only --}}
            @if(\Auth::user()->type == 'employee' && isset($employee) && $employee->employee_type === 'Payroll')
                {{-- Paid Leave Card --}}
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        {{ __('Paid Leave') }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        @php
                                            $paidLeaveBalance = null;
                                            $paidKeys = ['paid leave', 'paid', 'paidleave'];
                                            foreach ($paidKeys as $key) {
                                                if (isset($leaveBalances[$key])) {
                                                    $paidLeaveBalance = $leaveBalances[$key];
                                                    break;
                                                }
                                            }
                                        @endphp
                                        
                                        @if($paidLeaveBalance)
                                            {{ $paidLeaveBalance['available'] }} 
                                            <span class="text-xs text-muted">
                                                {{ __('Days') }}
                                                @if($paidLeaveBalance['carried_forward'] > 0)
                                                    <br>
                                                    <small class="text-success">+{{ $paidLeaveBalance['carried_forward'] }} {{ __('Carried Forward') }}</small>
                                                @endif
                                            </span>
                                        @else
                                            0 {{ __('Days') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                            @if($paidLeaveBalance)
                                <div class="mt-2">
                                    <div class="progress" style="height: 4px;">
                                        <?php 
                                        $percentage = $paidLeaveBalance['days_per_period'] > 0 ? 
                                            (($paidLeaveBalance['days_per_period'] - $paidLeaveBalance['available']) / $paidLeaveBalance['days_per_period']) * 100 : 0;
                                        ?>
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, $percentage) }}%"></div>
                                    </div>
                                    <small class="text-muted">
                                        {{ __('Used') }}: {{ $paidLeaveBalance['total_used'] }} / {{ $paidLeaveBalance['days_per_period'] }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Casual Leave Card --}}
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        {{ __('Casual Leave') }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        @php
                                            $casualLeaveBalance = null;
                                            $casualKeys = ['casual leaves', 'casual leave', 'casual', 'casualleaves'];
                                            foreach ($casualKeys as $key) {
                                                if (isset($leaveBalances[$key])) {
                                                    $casualLeaveBalance = $leaveBalances[$key];
                                                    break;
                                                }
                                            }
                                        @endphp
                                        
                                        @if($casualLeaveBalance)
                                            {{ $casualLeaveBalance['total_allocated'] }} 
                                            <span class="text-xs text-muted">{{ __('Days') }}</span>
                                        @else
                                            0 {{ __('Days') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                </div>
                            </div>
                            @if($casualLeaveBalance)
                                <div class="mt-2">
                                    <div class="progress" style="height: 4px;">
                                        <?php 
                                        $percentage = $casualLeaveBalance['days_per_period'] > 0 ? 
                                            (($casualLeaveBalance['days_per_period'] - $casualLeaveBalance['available']) / $casualLeaveBalance['days_per_period']) * 100 : 0;
                                        ?>
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, $percentage) }}%"></div>
                                    </div>
                                    <small class="text-muted">
                                        {{ __('Used') }}: {{ $casualLeaveBalance['total_used'] }} / {{ $casualLeaveBalance['days_per_period'] }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Remaining Casual Leave Card --}}
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        {{ __('Remaining Casual Leave') }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        @if($casualLeaveBalance)
                                            {{ $casualLeaveBalance['available'] }} 
                                            <span class="text-xs text-muted">{{ __('Days') }}</span>
                                        @else
                                            0 {{ __('Days') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                                </div>
                            </div>
                            @if($casualLeaveBalance)
                                <div class="mt-2">
                                    <div class="progress" style="height: 4px;">
                                        <?php 
                                        $percentage = $casualLeaveBalance['days_per_period'] > 0 ? 
                                            (($casualLeaveBalance['days_per_period'] - $casualLeaveBalance['available']) / $casualLeaveBalance['days_per_period']) * 100 : 0;
                                        ?>
                                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ min(100, $percentage) }}%"></div>
                                    </div>
                                    <small class="text-muted">
                                        {{ __('Used') }}: {{ $casualLeaveBalance['total_used'] }} / {{ $casualLeaveBalance['days_per_period'] }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Total Leaves This Month Card --}}
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        {{ __('Total Leaves (This Month)') }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $leaveBalances['total_leaves_this_month'] ?? 0 }} 
                                        <span class="text-xs text-muted">{{ __('Days') }}</span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    {{ __('Month') }}: {{ date('F Y') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                {{-- Dynamic Leave Type Cards for all other employee types - Only show eligible leave types --}}
                @if(isset($leaveTypes) && $leaveTypes->count() > 0)
                    @foreach($leaveTypes as $index => $leaveType)
                        @php
                            // Find balance for this leave type
                            $leaveBalance = null;
                            $leaveTypeName = strtolower(trim($leaveType->title));
                            
                            // Try to find balance by exact title first
                            if (isset($leaveBalances[$leaveTypeName])) {
                                $leaveBalance = $leaveBalances[$leaveTypeName];
                            } else {
                                // Try variations
                                $possibleKeys = [
                                    $leaveTypeName,
                                    str_replace(' ', '', $leaveTypeName),
                                    str_replace(' ', '_', $leaveTypeName),
                                    ucfirst($leaveTypeName),
                                    ucwords($leaveTypeName)
                                ];
                                
                                foreach ($possibleKeys as $key) {
                                    if (isset($leaveBalances[$key])) {
                                        $leaveBalance = $leaveBalances[$key];
                                        break;
                                    }
                                }
                            }
                            
                            // Determine card color based on index
                            $borderColors = ['primary', 'success', 'info', 'warning', 'danger'];
                            $borderColor = $borderColors[$index % count($borderColors)];
                            $icons = ['fa-calendar-check', 'fa-calendar-day', 'fa-calendar-alt', 'fa-calendar-week', 'fa-calendar'];
                            $icon = $icons[$index % count($icons)];
                        @endphp
                        
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                            <div class="card border-left-{{ $borderColor }} shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-{{ $borderColor }} text-uppercase mb-1">
                                                {{ $leaveType->title }}
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                @if($leaveBalance)
                                                    @if($leaveType->is_unlimited)
                                                        {{-- For unlimited leave types, show total used days --}}
                                                        {{ $leaveBalance['total_used'] }} 
                                                        <span class="text-xs text-muted">{{ __('Days Used') }}</span>
                                                    @else
                                                        {{-- For limited leave types, show available days --}}
                                                        {{ $leaveBalance['available'] }} 
                                                        <span class="text-xs text-muted">
                                                            {{ __('Days') }}
                                                            @if($leaveBalance['carried_forward'] > 0)
                                                                <br>
                                                                <small class="text-success">+{{ $leaveBalance['carried_forward'] }} {{ __('Carried Forward') }}</small>
                                                            @endif
                                                        </span>
                                                    @endif
                                                @else
                                                    @if($leaveType->is_unlimited)
                                                        0 {{ __('Days Used') }}
                                                    @else
                                                        0 {{ __('Days') }}
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas {{ $icon }} fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                    @if($leaveBalance)
                                        <div class="mt-2">
                                            @if($leaveType->is_unlimited)
                                                {{-- For unlimited leave types, show usage info without progress bar --}}
                                                <small class="text-muted">
                                                    {{ __('Total Used') }}: {{ $leaveBalance['total_used'] }} {{ __('Days') }}
                                                    @if($leaveBalance['used_this_month'] > 0)
                                                        <br>{{ __('This Month') }}: {{ $leaveBalance['used_this_month'] }} {{ __('Days') }}
                                                    @endif
                                                </small>
                                            @else
                                                {{-- For limited leave types, show progress bar --}}
                                                <div class="progress" style="height: 4px;">
                                                    <?php 
                                                    $percentage = $leaveBalance['days_per_period'] > 0 ? 
                                                        (($leaveBalance['days_per_period'] - $leaveBalance['available']) / $leaveBalance['days_per_period']) * 100 : 0;
                                                    ?>
                                                    <div class="progress-bar bg-{{ $borderColor }}" role="progressbar" style="width: {{ min(100, $percentage) }}%"></div>
                                                </div>
                                                <small class="text-muted">
                                                    {{ __('Used') }}: {{ $leaveBalance['total_used'] }} / {{ $leaveBalance['days_per_period'] }}
                                                </small>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    {{-- Total Leaves This Month Box --}}
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            {{ __('Total Leaves (This Month)') }}
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $leaveBalances['total_leaves_this_month'] ?? 0 }} 
                                            <span class="text-xs text-muted">{{ __('Days') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        {{ __('Month') }}: {{ date('F Y') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    @endif

    <div class="row">

        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    {{-- <h5> </h5> --}}
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    @if (\Auth::user()->type != 'employee')
                                        <th>{{ __('Employee') }}</th>
                                    @endif
                                    <th>{{ __('Leave Type') }}</th>
                                    <th>{{ __('Applied On') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Total Days') }}</th>
                                    <th>{{ __('Leave Reason') }}</th>
                                    <th>{{ __('status') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leaves as $leave)
                                    <tr>
                                        @if (\Auth::user()->type != 'employee')
                                            <td>{{ !empty($leave->employee_id) ? $leave->employees->name : '' }}
                                            </td>
                                        @endif
                                        <td>{{ !empty($leave->leave_type_id) ? $leave->leaveType->title : '' }}
                                        </td>
                                        <td>{{ \Auth::user()->dateFormat($leave->applied_on) }}</td>
                                        <td>{{ \Auth::user()->dateFormat($leave->start_date) }}</td>
                                        <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td>

                                        <td>{{ $leave->total_leave_days }}</td>
                                        <td>{{ $leave->leave_reason }}</td>
                                        <td>
                                            @if ($leave->status == 'Pending')
                                                <div class="badge bg-warning p-2 px-3 rounded status-badge5">
                                                    {{ $leave->status }}</div>
                                            @elseif($leave->status == 'Approved')
                                                <div class="badge bg-success p-2 px-3 rounded status-badge5">
                                                    {{ $leave->status }}</div>
                                            @elseif($leave->status == 'Reject')
                                                <div class="badge bg-danger p-2 px-3 rounded status-badge5">
                                                    {{ $leave->status }}</div>
                                            @endif
                                        </td>

                                        <td class="Action">

                                            <span>
                                                @if (\Auth::user()->type != 'employee')
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                            data-size="lg"
                                                            data-url="{{ URL::to('leave/' . $leave->id . '/action') }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Leave Action') }}"
                                                            data-bs-original-title="{{ __('Manage Leave') }}">
                                                            <i class="ti ti-caret-right text-white"></i>
                                                        </a>
                                                    </div>
                                                    @can('Edit Leave')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                data-size="lg"
                                                                data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="{{ __('Edit Leave') }}"
                                                                data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('Delete Leave')
                                                        @if (\Auth::user()->type != 'employee')
                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['leave.destroy', $leave->id],
                                                                    'id' => 'delete-form-' . $leave->id,
                                                                ]) !!}
                                                                <a href="#"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-bs-original-title="Delete" aria-label="Delete"><i
                                                                        class="ti ti-trash text-white text-white"></i></a>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    @endcan
                                                @else
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                            data-size="lg"
                                                            data-url="{{ URL::to('leave/' . $leave->id . '/action') }}"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="{{ __('Leave Action') }}"
                                                            data-bs-original-title="{{ __('Manage Leave') }}">
                                                            <i class="ti ti-caret-right text-white"></i>
                                                        </a>
                                                    </div>
                                                @endif

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
    </div>
@endsection

@push('script-page')
    <script>
        $(document).on('change', '#employee_id', function() {
            var employee_id = $(this).val();

            $.ajax({
                url: '{{ route('leave.jsoncount') }}',
                type: 'POST',
                data: {
                    "employee_id": employee_id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    var oldval = $('#leave_type_id').val();
                    $('#leave_type_id').empty();
                    $('#leave_type_id').append(
                        '<option value="">{{ __('Select Leave Type') }}</option>');

                    $.each(data, function(key, value) {

                        if (value.total_leave == value.days) {
                            $('#leave_type_id').append('<option value="' + value.id +
                                '" disabled>' + value.title + '&nbsp(' + value.total_leave +
                                '/' + value.days + ')</option>');
                        } else {
                            $('#leave_type_id').append('<option value="' + value.id + '">' +
                                value.title + '&nbsp(' + value.total_leave + '/' + value
                                .days + ')</option>');
                        }
                        if (oldval) {
                            if (oldval == value.id) {
                                $("#leave_type_id option[value=" + oldval + "]").attr(
                                    "selected", "selected");
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush
