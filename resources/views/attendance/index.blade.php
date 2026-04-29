    @extends('layouts.admin')
    @section('page-title')
        {{ __('Manage Attendance List') }}
    @endsection

    @section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
        <li class="breadcrumb-item">{{ __('Attendance List') }}</li>
    @endsection


    @push('script-page')
        <script>
            $('input[name="type"]:radio').on('change', function(e) {
                var type = $(this).val();

                if (type == 'monthly') {
                    $('.month').addClass('d-block');
                    $('.month').removeClass('d-none');
                    $('.date').addClass('d-none');
                    $('.date').removeClass('d-block');
                } else {
                    $('.date').addClass('d-block');
                    $('.date').removeClass('d-none');
                    $('.month').addClass('d-none');
                    $('.month').removeClass('d-block');
                }
            });

            $('input[name="type"]:radio:checked').trigger('change');
        </script>

        <script>
            $(document).ready(function() {
                var b_id = $('#branch_id').val();
                // getDepartment(b_id);
            });
            $(document).on('change', 'select[name=branch]', function() {
                var branch_id = $(this).val();

                getDepartment(branch_id);
            });

            function getDepartment(bid) {

                $.ajax({
                    url: '{{ route('monthly.getdepartment') }}',
                    type: 'POST',
                    data: {
                        "branch_id": bid,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {

                        $('.department_id').empty();
                        var emp_selct = `<select class="form-control department_id" name="department_id" id="choices-multiple"
                                                placeholder="Select Department" >
                                                </select>`;
                        $('.department_div').html(emp_selct);

                        $('.department_id').append('<option value=""> {{ __('Select Department') }} </option>');
                        $.each(data, function(key, value) {
                            $('.department_id').append('<option value="' + key + '">' + value +
                                '</option>');
                        });
                        new Choices('#choices-multiple', {
                            removeItemButton: true,
                        });
                    }
                });
            }
        </script>
    @endpush
    @section('action-button')
    @endsection
    @section('content')
        @if (session('status'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {!! session('   ') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="row">
            <div class="col-sm-12">
                <div class=" mt-2 " id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body">
                            {{ Form::open(['route' => ['attendanceemployee.index'], 'method' => 'get', 'id' => 'attendanceemployee_filter']) }}
                            <div class="row align-items-center justify-content-end">
                                <div class="col-xl-10">
                                    <div class="row">

                                        <div class="col-3">
                                            <label class="form-label">{{ __('Type') }}</label> <br>

                                            <div class="form-check form-check-inline form-group">
                                                <input type="radio" id="monthly" value="monthly" name="type"
                                                    class="form-check-input"
                                                    {{ isset($_GET['type']) && $_GET['type'] == 'monthly' ? 'checked' : 'checked' }}>
                                                <label class="form-check-label" for="monthly">{{ __('Monthly') }}</label>
                                            </div>
                                            <div class="form-check form-check-inline form-group">
                                                <input type="radio" id="daily" value="daily" name="type"
                                                    class="form-check-input"
                                                    {{ isset($_GET['type']) && $_GET['type'] == 'daily' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="daily">{{ __('Daily') }}</label>
                                            </div>

                                        </div>

                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                            <div class="btn-box">
                                                {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}
                                                {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class' => 'month-btn form-control month-btn']) }}
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 date">
                                            <div class="btn-box">
                                                {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                                {{ Form::date('date', isset($_GET['date']) ? $_GET['date'] : '', ['class' => 'form-control month-btn']) }}
                                            </div>
                                        </div>
                                        @if (\Auth::user()->type != 'employee')
                                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                                <div class="btn-box">
                                                    {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                                                    {{ Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control select branch_id', 'id' => 'branch_id']) }}
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                                <div class="btn-box">
                                                    {{ Form::label('department', __('department'), ['class' => 'form-label']) }}
                                                    {{ Form::select('department', $department, isset($_GET['department']) ? $_GET['department'] : '', ['class' => 'form-control select department_id', 'id' => 'department_id']) }}
                                                </div>

                                                {{-- <div class="form-icon-user" id="department_div">
                                                    {{ Form::label('department', __('Department'), ['class' => 'form-label']) }}
                                                    <select class="form-control select department_id" name="department_id"
                                                        id="department_id" placeholder="Select Department">
                                                    </select>
                                                </div> --}}

                                            </div>
                                        @endif

                                    </div>
                                </div>
                                <div class="col-auto mt-4">
                                    <div class="row">
                                        <div class="col-auto">

                                            <a href="#" class="btn btn-sm btn-primary"
                                                onclick="document.getElementById('attendanceemployee_filter').submit(); return false;"
                                                data-bs-toggle="tooltip" title="{{ __('Apply') }}"
                                                data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                            </a>

                                            <a href="{{ route('attendanceemployee.index') }}" class="btn btn-sm btn-danger "
                                                data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                                data-original-title="{{ __('Reset') }}">
                                                <span class="btn-inner--icon"><i
                                                        class="ti ti-trash-off text-white-off "></i></span>
                                            </a>

                                            <a href="#" data-url="{{ route('attendance.file.import') }}"
                                                data-ajax-popup="true" data-title="{{ __('Import  Attendance CSV File') }}"
                                                data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
                                                data-bs-original-title="{{ __('Import') }}">
                                                <i class="ti ti-file"></i>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
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
                                        @if (\Auth::user()->type != 'employee')
                                            <th>{{ __('Employee') }}</th>
                                        @endif
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Clock In') }}</th>
                                        <th>{{ __('Clock Out') }}</th>
                                        <th>{{ __('Clock In Location') }}</th>
                                        <th>{{ __('Clock Out Location') }}</th>
                                        <th>{{ __('Late') }}</th>
                                        <th>{{ __('Early Leaving') }}</th>
                                        <th>{{ __('Overtime') }}</th>
                                        @if (Gate::check('Edit Attendance') || Gate::check('Delete Attendance'))
                                            <th width="200px">{{ __('Action') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attendanceEmployee as $attendance)
                                        <tr>
                                            @if (\Auth::user()->type != 'employee')
                                                <td>{{ !empty($attendance->employee) ? $attendance->employee->name : '' }}</td>
                                            @endif
                                            <td>{{ \Auth::user()->dateFormat($attendance->date) }}</td>
                                            <td>
                                            @php
                                                $isLate = !empty($attendance->late) && $attendance->late !== '00:00:00';
                                            @endphp
                                            
                                            @switch($attendance->status)
                                                @case('Present')
                                                    <span class="badge bg-success me-1">{{ $attendance->status }}</span>
                                                    @if($isLate)
                                                        <span class="badge bg-warning">Late</span>
                                                    @endif
                                                    @break
                                                @case('Half Day')
                                                    <span class="badge bg-warning">{{ $attendance->status }}</span>
                                                    @break
                                                @case('Early Leaving')
                                                    <span class="badge bg-info">{{ $attendance->status }}</span>
                                                    @break
                                                @case('Single Punch In')
                                                    <span class="badge bg-secondary">{{ $attendance->status }}</span>
                                                    @break
                                                @case('Leave')
                                                    <span class="badge bg-danger">{{ $attendance->status }}</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $attendance->status }}</span>
                                            @endswitch
                                        </td>
                                            <td>{{ $attendance->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00' }}
                                            </td>
                                            <td>{{ $attendance->clock_out != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_out) : '00:00' }}
                                            </td>
                                            <td>
                                                @php
                                                    // Debug: Check if location data exists
                                                    $hasClockInLocation = !empty($attendance->clock_in_location);
                                                    $hasClockOutLocation = !empty($attendance->clock_out_location);
                                                    // Log debug info
                                                    if(!$hasClockInLocation) {
                                                        \Log::warning('No clock_in_location for attendance ID: ' . $attendance->id);
                                                    }
                                                    if(!$hasClockOutLocation) {
                                                        \Log::warning('No clock_out_location for attendance ID: ' . $attendance->id);
                                                    }
                                                @endphp
                                                
                                                @if(!empty($attendance->clock_in_location))
                                                    <small class="text-muted">{{ $attendance->clock_in_location }}</small><br>
                                                    @if(!empty($attendance->clock_in_latitude) && !empty($attendance->clock_in_longitude))
                                                        <a href="https://maps.google.com/?q={{ $attendance->clock_in_latitude }},{{ $attendance->clock_in_longitude }}" 
                                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="ti ti-map-pin"></i> View Map
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($attendance->clock_out_location))
                                                    <small class="text-muted">{{ $attendance->clock_out_location }}</small><br>
                                                    @if(!empty($attendance->clock_out_latitude) && !empty($attendance->clock_out_longitude))
                                                        <a href="https://maps.google.com/?q={{ $attendance->clock_out_latitude }},{{ $attendance->clock_out_longitude }}" 
                                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="ti ti-map-pin"></i> View Map
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $attendance->late }}</td>
                                            <td>{{ $attendance->early_leaving }}</td>
                                            <td>{{ $attendance->overtime }}</td>
                                            @if (Gate::check('Edit Attendance') || Gate::check('Delete Attendance'))
                                                <td class="Action">
                                                    <span>
                                                        @can('Edit Attendance')
                                                            <div class="action-btn bg-info ms-2">
                                                                <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                    data-size="lg"
                                                                    data-url="{{ URL::to('attendanceemployee/' . $attendance->id . '/edit') }}"
                                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                    title="" data-title="{{ __('Edit Attendance') }}"
                                                                    data-bs-original-title="{{ __('Edit') }}">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endcan

                                                        @can('Delete Attendance')
                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['attendanceemployee.destroy', $attendance->id],
                                                                    'id' => 'delete-form-' . $attendance->id,
                                                                ]) !!}
                                                                <a href="#"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-bs-original-title="Delete" aria-label="Delete"><i
                                                                        class="ti ti-trash text-white text-white"></i></a>
                                                                </form>
                                                            </div>
                                                        @endcan
                                                    </span>
                                                </td>
                                            @endif
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
