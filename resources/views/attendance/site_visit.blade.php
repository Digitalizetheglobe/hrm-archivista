    @extends('layouts.admin')
    @section('page-title')
        {{ __('Manage Site Visit Attendance List') }}
    @endsection

    @section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
        <li class="breadcrumb-item">{{ __('Site Visit Attendance List') }}</li>
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
                            {{ Form::open(['route' => ['attendanceemployee.sitevisit'], 'method' => 'get', 'id' => 'attendanceemployee_filter']) }}
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

                                            <a href="{{ route('attendanceemployee.sitevisit') }}" class="btn btn-sm btn-danger "
                                                data-bs-toggle="tooltip" title="{{ __('Reset') }}"
                                                data-original-title="{{ __('Reset') }}">
                                                <span class="btn-inner--icon"><i
                                                        class="ti ti-trash-off text-white-off "></i></span>
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
                                        <th>{{ __('Site In') }}</th>
                                        <th>{{ __('Site Out') }}</th>
                                        <th>{{ __('Site In Location') }}</th>
                                        <th>{{ __('Site Out Location') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attendanceEmployee as $attendance)
                                        <tr>
                                            @if (\Auth::user()->type != 'employee')
                                                <td>{{ !empty($attendance->employee) ? $attendance->employee->name : '' }}</td>
                                            @endif
                                            <td>{{ \Auth::user()->dateFormat($attendance->date) }}</td>
                                            <td>{{ $attendance->clock_in_2 != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in_2) : '00:00' }}</td>
                                            <td>{{ $attendance->clock_out_2 != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_out_2) : '00:00' }}</td>
                                            <td>
                                                @if(!empty($attendance->clock_in_2_location))
                                                    <small class="text-muted">{{ $attendance->clock_in_2_location }}</small><br>
                                                    @if(!empty($attendance->clock_in_2_latitude) && !empty($attendance->clock_in_2_longitude))
                                                        <a href="https://maps.google.com/?q={{ $attendance->clock_in_2_latitude }},{{ $attendance->clock_in_2_longitude }}" 
                                                           target="_blank" class="btn btn-sm btn-outline-warning">
                                                            <i class="ti ti-map-pin"></i> View Map
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($attendance->clock_out_2_location))
                                                    <small class="text-muted">{{ $attendance->clock_out_2_location }}</small><br>
                                                    @if(!empty($attendance->clock_out_2_latitude) && !empty($attendance->clock_out_2_longitude))
                                                        <a href="https://maps.google.com/?q={{ $attendance->clock_out_2_latitude }},{{ $attendance->clock_out_2_longitude }}" 
                                                           target="_blank" class="btn btn-sm btn-outline-warning">
                                                            <i class="ti ti-map-pin"></i> View Map
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
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
