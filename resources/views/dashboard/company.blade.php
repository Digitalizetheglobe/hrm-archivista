@extends('layouts.admin')

@section('page-title')
    {{ __('Dashboard') }}
@endsection

@php
    $setting = App\Models\Utility::settings();
@endphp

@section('content')
<style>

    .fc-prev-button, .fc-next-button {
        padding: 5px 8px !important; /* Smaller arrow buttons */
        font-size: 14px !important;
        background-color: #007bff !important; /* Bootstrap primary color */
        border-radius: 5px !important;
        border: none !important;
        color: white !important;
    }

    .fc-prev-button:hover, .fc-next-button:hover {
        background-color: #0056b3 !important;
    }

    #calendar {
        margin-bottom: 10px; /* Space between calendar and arrows */
    }

    .calendar-navigation {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
    }

    .events-container {
        max-height: 400px;
        overflow-y: auto;
    }

    .event-item {
        background-color: #f8f9fa;
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }

    .event-item:hover {
        background-color: #e9ecef;
        transform: translateX(2px);
    }

    .birthday-event {
        border-left-color: #007bff;
    }

    .anniversary-event {
        border-left-color: #28a745;
    }

    .event-avatar img {
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .event-icon {
        font-size: 1.2rem;
    }

    .birthday-icon, .anniversary-icon {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(255,255,255,0.8);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

</style>
<div>
    <div class="row">
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if (\Auth::user()->type == 'employee')
            <!-- Employee specific content -->
        @else
        


            <div class="col-xxl-9">
                <div class="row">
                    <!-- Left Side Cards -->
                    <div class="col-xl-12">
                        <div class="row">
                            <div class="col-xxl-12">
                                <div class="col-xl-12">
                                    <div class="row">
                                        <!-- first Card -->
                                            <div class="col-lg-4 col-md-6">
                                                <div class="card" style="border-radius: 10px; background-color: #fff;">
                                                    <div class="card-body" style="padding: 20px;">
                                                        <div class=" align-items-center">
                                                            <div class="col-auto">
                                                                <div style="background-color: #B55CC4; width: 50px; height: 50px; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                                                    <i class="fa-solid fa-user-tie" style="font-size: 25px; color: #fff;"></i>
                                                                </div>
                                                            </div><br>
                                                            <div class="col-auto" style="display: flex; align-items: center; gap: 5px;">
                                                                <h6 style="font-size: 14px; color: #515356; margin: 0;">Total,</h6>
                                                                <h4 class="m-0 text-primary" style="font-size: 15px; color:#555657 !important; font-weight: 800; margin: 0;">Employees</h4>
                                                            </div>

                                                            <div class="col-auto">
                                                                <h6 style="font-size: 14px; color: #0569a6;"> </h6>
                                                                <h4 class="m-0 text-primary" style="font-size: 30px; color : #000 !important; "> {{ $countUser + $countEmployee }}  </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <!-- second Card -->
                                            <div class="col-lg-4 col-md-6">
                                                <div class="card" style="border-radius: 10px; background-color: #fff;">
                                                    <div class="card-body" style="padding: 20px;">
                                                        <div class="align-items-center">
                                                            <div class="col-auto">
                                                                <div style="background-color: #299dc6; width: 50px; height: 50px; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                                                    <i class="fa-solid fa-clipboard-question" style="font-size: 25px; color: #fff;"></i>
                                                                </div>
                                                            </div><br>
                                                            <div class="col-auto" style="display: flex; align-items: center; gap: 5px;">
                                                                <h6 style="font-size: 14px; color: #515356; margin: 0;">Today,</h6>
                                                                <h4 class="m-0 text-primary" style="font-size: 15px; color: #555657 !important; font-weight: 800; margin: 0;">TimeSheet</h4>
                                                            </div>
                                                            <div class="col-auto">
                                                                <h6 style="font-size: 14px; color: #0569a6;"></h6>
                                                                <h4 class="m-0 text-primary" style="font-size: 30px; color : #000 !important;">{{ $todayEnquiryCount }}</h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Third Card -->
                                            <div class="col-lg-4 col-md-6">
                                                <div class="card" style="border-radius: 10px; background-color: #fff;">
                                                    <div class="card-body" style="padding: 20px;">
                                                        <div class="align-items-center">
                                                            <div class="col-auto">
                                                                <div style="background-color: #3B7080; width: 50px; height: 50px; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                                                    <i class="fa-solid fa-calendar-check" style="font-size: 25px; color: #fff;"></i>
                                                                </div>
                                                            </div><br>
                                                            <div class="col-auto" style="display: flex; align-items: center; gap: 5px;">
                                                                <h6 style="font-size: 14px; color: #515356; margin: 0;">Today,</h6>
                                                                <h4 class="m-0 text-primary" style="font-size: 15px; color: #555657 !important; font-weight: 800; margin: 0;">Leaves</h4>
                                                            </div>
                                                            <div class="col-auto">
                                                                <h6 style="font-size: 14px; color: #6c757d;"></h6>
                                                                <h4 class="m-0 text-primary" style="font-size: 30px; color:#000 !important;">{{$todayLeaves  }}</h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    </div>

                                    <div class="row">
                                         <!-- Fourth Card -->
                                        <div class="col-lg-4 col-md-6">
                                            <div class="card" style="border-radius: 10px; background-color: #fff;">
                                                    <div class="card-body" style="padding: 20px;">
                                                        <div class=" align-items-center">
                                                            <div class="col-auto">
                                                                <div style="background-color: #299dc6; width: 50px; height: 50px; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                                                    <i class="fa-solid fa-sitemap"  style="font-size: 25px; color: #fff;"></i>
                                                                </div>
                                                            </div><br>
                                                            <div class="col-auto" style="display: flex; align-items: center; gap: 5px;">
                                                                <h6 style="font-size: 14px; color: #515356; margin: 0;">Total,</h6>
                                                                <h4 class="m-0 text-primary" style="font-size: 15px; color: #555657 !important; font-weight: 800; margin: 0;">Department</h4>
                                                            </div>

                                                            <div class="col-auto">
                                                                <h6 style="font-size: 14px; color: #0569a6;"> </h6>
                                                                <h4 class="m-0 text-primary" style="font-size: 30px; color : #000 !important; "> {{ $totalDepartment }}  </h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </div>
                                        </div>
                                       
                                        <!-- fifth Card -->
                                        <div class="col-lg-4 col-md-6">
                                            <div class="card" style="border-radius: 10px; background-color: #fff;">
                                                <div class="card-body" style="padding: 20px;">
                                                    <div class=" align-items-center">
                                                        <div class="col-auto">
                                                            <div style="background-color: #F26522; width: 50px; height: 50px; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                                                <i class="fa-solid fa-diagram-project" style="font-size: 25px; color: #fff;"></i>
                                                            </div>
                                                        </div><br>
                                                        <div class="col-auto" style="display: flex; align-items: center; gap: 5px;">
                                                            <h6 style="font-size: 14px; color: #515356; margin: 0;">Total,</h6>
                                                            <h4 class="m-0 text-primary" style="font-size: 15px; color: #555657 !important; font-weight: 800; margin: 0;">Projects</h4>
                                                        </div>

                                                        <div class="col-auto">
                                                            <h6 style="font-size: 14px; color: #6c757d;"> </h6>
                                                            <h4 class="m-0 text-primary" style="font-size: 30px; color:#000 !important; "> {{ $totalProjects }}  </h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Six Card -->
                                        <div class="col-lg-4 col-md-6">
                                            <div class="card" style="border-radius: 10px; background-color: #fff; ">
                                                <div class="card-body" style="padding: 20px;">
                                                    <div class=" align-items-center">
                                                        <div class="col-auto">
                                                            <div style="background-color: #FD3995; width: 50px; height: 50px; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                                                <i class="fa-solid fa-ticket" style="font-size: 25px; color: #fff;"></i>
                                                            </div>
                                                        </div><br>
                                                        <div class="col-auto" style="display: flex; align-items: center; gap: 5px;">
                                                            <h6 style="font-size: 14px; color: #515356; margin: 0;">Total,</h6>
                                                            <h4 class="m-0 text-primary" style="font-size: 15px; color: #555657 !important; font-weight: 800; margin: 0;">Ticket</h4>
                                                        </div>

                                                        <div class="col-auto">
                                                            <h6 style="font-size: 14px; color: #6c757d;"> </h6>
                                                            <h4 class="m-0 text-primary" style="font-size: 30px; color:#000 !important; "> {{ $countTicket }}  </h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <!-- Additional Data Below Cards -->
                        <div class="row">
                            <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header card-body table-border-style d-flex justify-content-between align-items-center">
                                            <h5 style="font-size:20px; color:black; margin: 0;">{{ __('Today\'s Attendance') }}</h5>
                                        </div>
                                        <div class="card-body" style="height: 300px; overflow: auto; padding: 10px; padding-top:25px;">
                                            <div class="table-responsive" style="max-width:full">
                                                <table class="table table-bordered text-center">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('Employee Name') }}</th>
                                                                <th>{{ __('Clock-In Time') }}</th>
                                                                <th>{{ __('Clock-In Location') }}</th>
                                                                <th>{{ __('Clock-Out Time') }}</th>
                                                                <th>{{ __('Clock-Out Location') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($presentEmployeesWithClockIn as $data)
                                                                <tr>
                                                                    <td>{{ $data['employee']->name ?? 'N/A' }}</td>
                                                                    <td>{{ $data['clock_in'] ?? '--:--' }}</td>
                                                                    <td>
                                                                        @php
                                                                            $location = $data['clock_in_location'] ?? null;
                                                                            if ($location) {
                                                                                // Extract main location (city name) from full address
                                                                                $parts = explode(',', $location);
                                                                                $mainPlace = trim($parts[0]);
                                                                                echo $mainPlace;
                                                                            } else {
                                                                                echo '--:--';
                                                                            }
                                                                        @endphp
                                                                    </td>
                                                                    <td>{{ $data['clock_out'] ?? '--:--' }}</td>
                                                                    <td>
                                                                        @php
                                                                            $outLocation = $data['clock_out_location'] ?? null;
                                                                            if ($outLocation) {
                                                                                // Extract main location (city name) from full address
                                                                                $parts = explode(',', $outLocation);
                                                                                $mainPlace = trim($parts[0]);
                                                                                echo $mainPlace;
                                                                            } else {
                                                                                echo '--:--';
                                                                            }
                                                                        @endphp
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

                        @if(isset($hasTodaySiteVisits) && $hasTodaySiteVisits)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header card-body table-border-style d-flex justify-content-between align-items-center">
                                        <h5 style="font-size:20px; color:black; margin: 0;">{{ __('Today\'s Site Attendance') }}</h5>
                                    </div>
                                    <div class="card-body" style="height: 300px; overflow: auto; padding: 10px; padding-top:25px;">
                                        <div class="table-responsive">
                                            <table class="table table-bordered text-center">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Employee Name') }}</th>
                                                        <th>{{ __('Clock-In') }}</th>
                                                        <th>{{ __('Location') }}</th>
                                                        <th>{{ __('Site Visit In') }}</th>
                                                        <th>{{ __('Site In Loc') }}</th>
                                                        <th>{{ __('Site Visit Out') }}</th>
                                                        <th>{{ __('Site Out Loc') }}</th>
                                                        <th>{{ __('Punch Out') }}</th>
                                                        <th>{{ __('Punch Out Loc') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($siteAttendanceEmployees as $data)
                                                        <tr>
                                                            <td>{{ $data['employee']->name ?? 'N/A' }}</td>
                                                            <td>{{ $data['clock_in'] }}</td>
                                                            <td>
                                                                @php
                                                                    $loc = $data['clock_in_location'];
                                                                    if (!empty($loc) && $loc != '--:--') {
                                                                        $parts = explode(',', $loc);
                                                                        echo trim($parts[0]);
                                                                    } else {
                                                                        echo '--:--';
                                                                    }
                                                                @endphp
                                                            </td>
                                                            <td>{{ $data['clock_in_2'] }}</td>
                                                            <td>
                                                                @php
                                                                    $loc2 = $data['clock_in_2_location'];
                                                                    if (!empty($loc2) && $loc2 != '--:--') {
                                                                        $parts = explode(',', $loc2);
                                                                        echo trim($parts[0]);
                                                                    } else {
                                                                        echo '--:--';
                                                                    }
                                                                @endphp
                                                            </td>
                                                            <td>{{ $data['clock_out_2'] }}</td>
                                                            <td>
                                                                @php
                                                                    $outLoc2 = $data['clock_out_2_location'];
                                                                    if (!empty($outLoc2) && $outLoc2 != '--:--') {
                                                                        $parts = explode(',', $outLoc2);
                                                                        echo trim($parts[0]);
                                                                    } else {
                                                                        echo '--:--';
                                                                    }
                                                                @endphp
                                                            </td>
                                                            <td>{{ $data['clock_out'] }}</td>
                                                            <td>
                                                                @php
                                                                    $outLoc = $data['clock_out_location'];
                                                                    if (!empty($outLoc) && $outLoc != '--:--') {
                                                                        $parts = explode(',', $outLoc);
                                                                        echo trim($parts[0]);
                                                                    } else {
                                                                        echo '--:--';
                                                                    }
                                                                @endphp
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="9" class="text-center">{{ __('No employees have punched in yet for today\'s site visits.') }}</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header card-body table-border-style d-flex justify-content-between align-items-center">
                                        <h5 style="font-size:20px; color:black; margin: 0;">{{ __('Attendance Overview') }}</h5>
                                    </div>
                                    <div class="card-body" style="height: 300px; padding: 10px;">
                                        <div class="card shadow-none mt-3">
                                            <div class="card-body p-2">
                                                <div id="attendance-chart"></div>
                                            </div>
                                        </div>
                                        <div class="mt-0" style="display: flex; align-items: center; justify-content:center;">
                                            <ul style="list-style: none; padding: 0; display: flex; align-items: center; gap:50px;">
                                                <li style="display: flex; align-items: center; margin-bottom: 5px;">
                                                    <span style="width: 15px; height: 15px; background-color:#6dacaa; display: inline-block; margin-right: 10px; border-radius: 50%;"></span>
                                                    Present
                                                </li>
                                                <li style="display: flex; align-items: center; margin-bottom: 5px;">
                                                    <span style="width: 15px; height: 15px; background-color: #eef5ff; display: inline-block; margin-right: 10px; border-radius: 50%;"></span>
                                                    Absent
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header card-body table-border-style d-flex justify-content-between align-items-center">
                                        <h5 style="font-size:20px; color:black; margin: 0;">{{ __('Not Clock In employees') }}</h5>
                                    </div>
                                    <div class="card-body" style="height: 300px; overflow: auto; padding: 10px; padding-top:25px;">
                                        <div class="table-responsive" style="max-width:452px;">
                                            <table class="table table-bordered text-center">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Employee Name') }}</th>
                                                        <th>{{ __('Status') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($notClockIns as $employee)
                                                        <tr>
                                                            <td>{{ $employee->name ?? 'N/A' }}</td>
                                                            <td style="color: red;">Absent</td>
                                                        </tr>
                                                    @endforeach
                                                    @if($notClockIns->isEmpty())
                                                        <tr>
                                                            <td colspan="2">All employees are present</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header card-body table-border-style d-flex justify-content-between align-items-center">
                                        <h5 style="font-size:20px; color:black; margin: 0;">{{ __('Notices') }}</h5>
                                    </div>
                                    <div class="card-body" style="height: 300px; overflow: auto; padding: 10px; padding-top:25px;">
                                        <div class="table-responsive" ">
                                            <table class="table table-bordered text-center">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 60%;">Title</th>
                                                        <th style="width: 40%;">Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($notices as $notice)
                                                    <tr>
                                                        <td style="word-wrap: break-word; white-space: normal;">
                                                            {{ Str::limit($notice->title, 50, '...') }}
                                                        </td>
                                                        <td>
                                                            {{ \Carbon\Carbon::parse($notice->notice_startdate)->format('d M Y') }} - 
                                                            {{ \Carbon\Carbon::parse($notice->notice_enddate)->format('d M Y') }}
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
                </div>
            </div>

              <!-- Right Side Calendar -->

                <div class="col-xxl-3">
                    <div class="d-flex flex-column gap-2 sticky-top" style="top: 10px; height: 100vh; ">
                        
                        <div class="card flex-grow-1">
                            <div class="card-header">
                                <h5 style="font-size:20px;color:black">{{ __("This Month Event's") }}</h5>
                            </div>
                            <div class="card-body">
                                @if(isset($arrEvents) && count($arrEvents) > 0)
                                    <div class="events-container">
                                        @foreach($arrEvents as $event)
                                            <div class="event-item d-flex align-items-center mb-3 p-2 rounded {{ $event['type'] == 'birthday' ? 'birthday-event' : 'anniversary-event' }}">
                                                <div class="event-avatar me-3">
                                                    <img src="{{ asset('storage/uploads/avatar/' . $event['avatar']) }}" 
                                                         alt="{{ $event['employee_name'] }}" 
                                                         class="rounded-circle" 
                                                         width="45" 
                                                         height="45"
                                                         onerror="this.src='{{ asset('storage/avatars/avatar.png') }}'">
                                                </div>
                                                <div class="event-details flex-grow-1">
                                                    <h6 class="mb-1 fw-bold">{{ $event['employee_name'] }}</h6>
                                                    <p class="mb-1 {{ $event['type'] == 'birthday' ? 'text-primary' : 'text-success' }} fw-semibold">
                                                        {{ $event['message'] }}
                                                    </p>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar-alt me-1"></i>{{ $event['date'] }} • 
                                                        <i class="fas fa-building me-1"></i>{{ $event['department'] }}
                                                    </small>
                                                </div>
                                                <div class="event-icon">
                                                    @if($event['type'] == 'birthday')
                                                        <div class="birthday-icon">
                                                            <i class="fas fa-birthday-cake text-primary"></i>
                                                        </div>
                                                    @else
                                                        <div class="anniversary-icon">
                                                            <i class="fas fa-award text-success"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 2rem;"></i>
                                        <p class="text-muted mb-0">No events this month</p>
                                        <small class="text-muted">Check back next month for upcoming celebrations!</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="card flex-grow-1">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <h5>{{ __('Calendar') }}</h5>
                                        <input type="hidden" id="path_admin" value="{{ url('/') }}">
                                    </div>
                                    <div class="col-lg-6">
                                        @if (isset($setting['is_enabled']) && $setting['is_enabled'] == 'on')
                                            <select class="form-control" name="calender_type" id="calender_type"
                                                style="float: right; width: 1px;" onchange="get_data()">
                                                <option value="local_calender" selected="true">{{ __('Local Calendar') }}</option>
                                            </select>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body " style="padding-top:0px;">
                                <div id='calendar'  class='calendar'></div>
                            </div>
                        </div>

                    </div>
                </div>


                



              

                
  
        @endif
    </div>
</div>
@endsection

@push('script-page')
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>

    @if (Auth::user()->type == 'company' || Auth::user()->type == 'hr')
    <script type="text/javascript">
    $(document).ready(function() {
        get_data();
    });

    function get_data() {
        var calender_type = $('#calender_type :selected').val();

        $('#calendar').removeClass('local_calender google_calender');
        if (!calender_type) {
            calender_type = 'local_calender';
        }
        $('#calendar').addClass(calender_type);

        $.ajax({
            data: {
                "_token": "{{ csrf_token() }}",
                'calender_type': calender_type
            },
            success: function(data) {
                var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                    headerToolbar: {
                        left: 'prev', // Only navigation arrows
                        center: 'title',
                        right: 'next'
                    },
                    themeSystem: 'bootstrap',
                    slotDuration: '00:10:00',
                    allDaySlot: true,
                    navLinks: false,
                    droppable: true,
                    selectable: true,
                    selectMirror: true,
                    editable: true,
                    dayMaxEvents: true,
                    handleWindowResize: true,
                    height: '360px',
                });
                calendar.render();
            }
        });
    }
</script>

    @else
        <script>
            $(document).ready(function() {
                get_data();
            });

            function get_data() {
                var calender_type = $('#calender_type :selected').val();

                $('#event_calendar').removeClass('local_calender');
                $('#event_calendar').removeClass('google_calender');
                if (calender_type == undefined) {
                    calender_type = 'local_calender';
                }
                $('#event_calendar').addClass(calender_type);

                $.ajax({
                    url: $("#path_admin").val() + "/event/get_event_data",
                    method: "POST",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        'calender_type': calender_type
                    },
                    success: function(data) {
                        var etitle;
                        var etype;
                        var etypeclass;
                        var calendar = new FullCalendar.Calendar(document.getElementById('event_calendar'), {
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            buttonText: {
                                timeGridDay: "{{ __('Day') }}",
                                timeGridWeek: "{{ __('Week') }}",
                                // dayGridMonth: "{{ __('Month') }}"
                            },
                            // slotLabelFormat: {
                            //     hour: '2-digit',
                            //     minute: '2-digit',
                            //     hour12: false,
                            // },
                            themeSystem: 'tailwind',
                            slotDuration: '00:10:00',
                            allDaySlot: true,
                            navLinks: true,
                            droppable: true,
                            selectable: true,
                            selectMirror: true,
                            editable: true,
                            dayMaxEvents: true,
                            handleWindowResize: true,
                            events: data,
                            height: '400px',
                            // timeFormat: 'H(:mm)',

                        });

                        calendar.render();
                    }
                });
            };
        </script>
    @endif

    @if (\Auth::user()->type == 'company')
        <script>
            (function() {
                var totalEmployees = {{ $totalEmployees }};
                var presentEmployees = {{ count($presentEmployeesWithClockIn) }};
                var attendancePercentage = {{ round($attendancePercentage, 2) }};
                
                var options = {
                    series: [attendancePercentage],
                    chart: {
                        height: 380,
                        type: 'radialBar',
                        offsetY: -20,
                        sparkline: {
                            enabled: true
                        }
                    },
                    plotOptions: {
                        radialBar: {
                            startAngle: -90,
                            endAngle: 90,
                            track: {
                                background: "#eef5ff",
                                strokeWidth: '98%',
                                margin: 5,
                            
                            },
                            dataLabels: {
                                name: {
                                    show: true
                                },
                                value: {
                                    offsetY: -50,
                                    fontSize: '20px'
                                }
                            }
                        }
                    },
                    grid: {
                        padding: {
                            top: -10
                        }
                    },
                    colors: ["#68A288"],
                    labels: [''],
                    tooltip: {
                        enabled: true,
                        y: {
                            formatter: function(val) {
                                return `Out of ${totalEmployees} employees, ${presentEmployees} are present.`;
                            }
                        }
                    }
                };

                var chart = new ApexCharts(document.querySelector("#attendance-chart"), options);
                chart.render();
            })();
        </script>

        <style>
            .apexcharts-tooltip {
                background: #000 !important;
                color: #fff !important;
                border-radius: 8px;
                font-size: 14px;
            }
        </style>
    @endif
@endpush

@push('script-page')
<script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
<script>
    (function() {
        var options = {
            chart: {
                height: 265,
                type: 'bar',
                toolbar: {
                    show: false,
                },
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '50%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: 4,
                curve: 'smooth'
            },
            series: {!! json_encode($chartData['data']) !!},
            xaxis: {
                categories: {!! json_encode($chartData['labels']) !!},
            },
            colors: ['#b4d1c4', '#68a288'],
            fill: {
                type: 'solid',
            },
            grid: {
                strokeDashArray: 4,
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'right',
            },
            markers: {
                size: 4,
                colors: ['#000', '#FF3A6E'],
                opacity: 2.5,
                strokeWidth: 4,
                hover: {
                    size: 8,
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#income-expense-chart"), options);
        chart.render();
    })();
</script>
@endpush