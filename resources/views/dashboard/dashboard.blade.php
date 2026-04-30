@extends('layouts.admin')

@section('page-title')
    {{ __('Dashboard') }}
@endsection

@section('content')
<style>
    .fc-prev-button, .fc-next-button {
        padding: 5px 8px !important;
        font-size: 14px !important;
        background-color: #007bff !important;
        border-radius: 5px !important;  
        border: none !important;
        color: white !important;
    }

    .fc-prev-button:hover, .fc-next-button:hover {
        background-color: #0056b3 !important;
    }

    #calendar {
        margin-bottom: 10px;
    }

    .calendar-navigation {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
    }

    /* Events Styling */
    .events-container {
        max-height: 400px;
        overflow-y: auto;
    }

    .event-item {
        background: #f8f9fa;
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }

    .event-item:hover {
        background: #e9ecef;
        transform: translateX(2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .birthday-event {
        border-left-color: #007bff;
        background: linear-gradient(90deg, rgba(0,123,255,0.05) 0%, #f8f9fa 100%);
    }

    .birthday-event:hover {
        background: linear-gradient(90deg, rgba(0,123,255,0.1) 0%, #e9ecef 100%);
    }

    .anniversary-event {
        border-left-color: #28a745;
        background: linear-gradient(90deg, rgba(40,167,69,0.05) 0%, #f8f9fa 100%);
    }

    .anniversary-event:hover {
        background: linear-gradient(90deg, rgba(40,167,69,0.1) 0%, #e9ecef 100%);
    }

    .event-avatar img {
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .birthday-event .event-avatar img {
        border-color: #007bff;
    }

    .anniversary-event .event-avatar img {
        border-color: #28a745;
    }

    .event-icon {
        font-size: 1.5rem;
        opacity: 0.8;
        transition: opacity 0.3s ease;
    }

    .event-item:hover .event-icon {
        opacity: 1;
    }

    .events-container::-webkit-scrollbar {
        width: 6px;
    }

    .events-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .events-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .events-container::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
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
            <div class="col-xxl-9">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card">  
                                    <div class="card-header d-flex align-items-center">
                                        <img src="{{ asset('https://connect360.in//storage/uploads/avatar/' . ($emp->user->avatar ?? 'default-avatar.png')) }}" 
                                            alt="Profile Image" 
                                            class="rounded-circle me-4" 
                                            width="60" 
                                            height="60">
                                        <div>
                                            <h4 class="mb-0" style="color:black;">{{ $emp->name }}</h4>
                                            <small style="font-size: 12px; color:black;">{{ $emp->department->name ?? 'No Department' }} Team</small><small style="font-size:16px; color:black;"> &nbsp{{ $emp->designation->name ?? 'No Designation' }}&nbsp</small><br>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Phone Number:<br></strong> {{ $emp->phone ?? 'N/A' }}</p><br>
                                        <p><strong>Email Address:<br></strong> {{ $emp->email ?? 'N/A' }}</p><br>
                                        <p><strong>Joined On:<br></strong> {{ \Carbon\Carbon::parse($emp->company_doj)->format('d M Y') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card" style="height:395px;">
                                    <div class="card-header">
                                        <h5 style="font-size:20px;color:black">{{ __('Attendance') }}</h5>
                                        <p id="currentDateTime"></p>
                                    </div>
                                    <div class="card-body text-center p-1">
                                        <div class="progress-container">
                                            <svg width="140" height="170" viewBox="0 0 100 100">
                                                <circle cx="50" cy="50" r="45" stroke="#e0e0e0" stroke-width="8" fill="none"></circle>
                                                <circle id="progressCircle" cx="50" cy="50" r="45" 
                                                    stroke="#4CAF50" stroke-width="7" fill="none"
                                                    stroke-dasharray="283" stroke-dashoffset="283"
                                                    stroke-linecap="round">
                                                </circle>
                                                <text id="progressTime" x="50" y="55" font-size="12" text-anchor="middle" fill="#333">0:00:00</text>
                                            </svg>
                                        </div>

                                        <p id="attendanceStatus" class="font-bold">
                                            @php
                                                $siteVisit = \App\Models\SiteVisit::where('employee_id', $emp->id)->where('date', date('Y-m-d'))->where('status', 'Approved')->first();
                                            @endphp

                                            @if (!isset($employeeAttendance) || !$employeeAttendance->clock_in)
                                                <span class="text-primary"><i class="fas fa-fingerprint"></i> Not Punched In</span>
                                            @elseif ($employeeAttendance->clock_out == '00:00:00' || !$employeeAttendance->clock_out)
                                                <span class="text-success"><i class="fas fa-fingerprint"></i> Punched In at {{ \Carbon\Carbon::parse($employeeAttendance->clock_in)->format('h:i A') }}</span>
                                            @elseif ($siteVisit && (empty($employeeAttendance->clock_in_2) || $employeeAttendance->clock_in_2 == '00:00:00'))
                                                <span class="text-warning"><i class="fas fa-map-marker-alt"></i> Punched Out (Site Visit Pending)</span>
                                            @elseif ($siteVisit && (empty($employeeAttendance->clock_out_2) || $employeeAttendance->clock_out_2 == '00:00:00'))
                                                <span class="text-success"><i class="fas fa-map-marker-alt"></i> Site Visit Punched In at {{ \Carbon\Carbon::parse($employeeAttendance->clock_in_2)->format('h:i A') }}</span>
                                            @else
                                                <span class="text-danger"><i class="fas fa-sign-out-alt"></i> Punched Out at {{ \Carbon\Carbon::parse($employeeAttendance->clock_out_2 && $employeeAttendance->clock_out_2 != '00:00:00' ? $employeeAttendance->clock_out_2 : $employeeAttendance->clock_out)->format('h:i A') }}</span>
                                            @endif
                                        </p>

                                        {{ Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post', 'id' => 'attendanceForm']) }}
                                            <!-- Hidden fields for location capture -->
                                            <input type="hidden" id="latitude" name="latitude">
                                            <input type="hidden" id="longitude" name="longitude">
                                            <input type="hidden" id="location" name="location">
                                            
                                            @if (empty($employeeAttendance) || (!$employeeAttendance->clock_in))
                                                <button type="submit" value="0" name="in" id="clock_in" class="btn btn-primary">{{ __('Punch In') }}</button>
                                            @elseif ($siteVisit && (empty($employeeAttendance->clock_in_2) || $employeeAttendance->clock_in_2 == '00:00:00'))
                                                <button type="submit" value="0" name="in" id="clock_in_2" class="btn btn-warning">{{ __('Site Visit In') }}</button>
                                            @elseif ($siteVisit && (empty($employeeAttendance->clock_out_2) || $employeeAttendance->clock_out_2 == '00:00:00'))
                                                <button type="button" value="1" name="out" id="clock_out_2" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmClockOutModal">
                                                    {{ __('Site Visit Out') }}
                                                </button>
                                            @elseif ($employeeAttendance->clock_out == '00:00:00' || !$employeeAttendance->clock_out)
                                                <button type="button" value="1" name="out" id="clock_out" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmClockOutModal">
                                                    {{ __('Punch Out') }}
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-secondary" disabled>{{ __('Completed') }}</button>
                                            @endif
                                        {{ Form::close() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header card-body table-border-style d-flex justify-content-between align-items-center">
                                        <h5 style="font-size:20px; color:black; margin: 0;">{{ __('Notices') }}</h5>
                                    </div>
                                    <div class="card-body" style="height: 325px; overflow: auto; padding: 10px; padding-top:25px;">
                                        <div class="table-responsive" style="max-width:452px;">
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

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header card-body table-border-style">
                                        <h5 style="font-size:20px;color:black">{{ __('TO-DO Lists') }}</h5>
                                    </div>
                                    <div class="card-body" style="height: 324px; overflow:auto;">
                                        <div class="table-responsive"> 
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                    <th>{{ __('Task Title') }}</th>
                                                    <th>{{ __('Priority') }}</th>
                                                    <th>{{ __('Due Date') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="list">
                                                    @foreach ($todos as $todo)
                                                        <tr>
                                                            <td>{{ $todo->task }}</td>
                                                            <td>
                                                                @if($todo->priority == 1)
                                                                    <span class="badge bg-danger">{{ __('High') }}</span>
                                                                @elseif($todo->priority == 2)
                                                                    <span class="badge bg-warning">{{ __('Medium') }}</span>
                                                                @else
                                                                    <span class="badge bg-success">{{ __('Low') }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ \Carbon\Carbon::parse($todo->expires_at)->format('d M Y') }}</td>
                                                            <td>
                                                                @if($todo->is_completed)
                                                                    <span class="badge bg-success">{{ __('Completed') }}</span>
                                                                @else
                                                                    <span class="badge bg-danger">{{ __('Pending') }}</span>
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
                    </div>

                </div>
            </div>

            <!-- Right Side Calendar -->
            <div class="col-xxl-3">
                <div class="d-flex flex-column gap-2 sticky-top" style="top: 10px; height: 100vh;">
                    
                    <div class="card flex-grow-1">
                        <div class="card-header">
                            <h5 style="font-size:20px;color:black">{{ __("This Month Event's") }}</h5>
                        </div>
                        <div class="card-body">
                            @if(isset($monthlyEvents) && count($monthlyEvents) > 0)
                                <div class="events-container">
                                    @foreach($monthlyEvents as $event)
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
                                    <!-- <input type="hidden" id="path_admin" value="{{ url('/') }}"> -->
                                </div>
                                
                            </div>
                        </div>
                        <div class="card-body" style="padding-top:0px;">
                            <div id='calendar' class='calendar'></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Bootstrap Modal -->
<div class="modal fade" id="confirmClockOutModal" tabindex="-1" aria-labelledby="confirmClockOutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmClockOutModalLabel">Confirm Clock Out</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to clock out?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmClockOutBtn">Yes, Clock Out</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
    <script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>

    @if (Auth::user()->type == 'employee')
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
                        left: 'prev',
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
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let progressCircle = document.getElementById("progressCircle");
            let progressTime = document.getElementById("progressTime");
            let clockInButton = document.getElementById("clock_in");
            let clockOutButton = document.getElementById("clock_out");
            let currentTimeElement = document.getElementById("currentDateTime");
            let confirmClockOutBtn = document.getElementById("confirmClockOutBtn");
            let attendanceStatus = document.getElementById("attendanceStatus");

            function isNewDay() {
                const lastClockOutDate = localStorage.getItem("lastClockOutDate");
                if (!lastClockOutDate) return false;
                
                const today = new Date().toLocaleDateString();
                return lastClockOutDate !== today;
            }

            if (isNewDay()) {
                localStorage.removeItem("clockInTime");
                localStorage.removeItem("clockOutTime");
                localStorage.removeItem("isPunchedOut");
            }

            let clockInTime = localStorage.getItem("clockInTime") && !isNewDay() ? new Date(localStorage.getItem("clockInTime")) : null;
            let clockOutTime = localStorage.getItem("clockOutTime") && !isNewDay() ? new Date(localStorage.getItem("clockOutTime")) : null;
            let isPunchedOut = localStorage.getItem("isPunchedOut") === "true" && !isNewDay();

            @if(isset($employeeAttendance) && $employeeAttendance->clock_in)
                if (!clockInTime) {
                    clockInTime = new Date("{{ \Carbon\Carbon::parse($employeeAttendance->clock_in)->toIso8601String() }}");
                    localStorage.setItem("clockInTime", clockInTime.toISOString());
                }
            @endif

            @if(isset($employeeAttendance) && $employeeAttendance->clock_out && $employeeAttendance->clock_out !== '00:00:00')
                if (!clockOutTime) {
                    clockOutTime = new Date("{{ \Carbon\Carbon::parse($employeeAttendance->clock_out)->toIso8601String() }}");
                    localStorage.setItem("clockOutTime", clockOutTime.toISOString());
                    localStorage.setItem("isPunchedOut", "true");
                    localStorage.setItem("lastClockOutDate", new Date().toLocaleDateString());
                    isPunchedOut = true;
                }
            @endif

            function updateTimeDisplay() {
                let now = new Date();
                currentTimeElement.textContent = now.toLocaleString("en-US", {
                    hour: "2-digit", minute: "2-digit", second: "2-digit", hour12: true, day: "2-digit", month: "short", year: "numeric"
                });
            }

            function updateProgress() {
                if (!clockInTime) {
                    progressCircle.style.strokeDashoffset = 283;
                    progressTime.textContent = "0:00:00";
                    return;
                }
                
                let elapsedSeconds;
                
                if (clockOutTime) {
                    elapsedSeconds = Math.floor((clockOutTime - clockInTime) / 1000);
                } else {
                    let now = new Date();
                    elapsedSeconds = Math.floor((now - clockInTime) / 1000);
                }

                elapsedSeconds = Math.min(elapsedSeconds, 10 * 60 * 60);
                let percentage = (elapsedSeconds / (10 * 60 * 60)) * 100;
                progressCircle.style.strokeDashoffset = 283 - (percentage * 283) / 100;

                let hours = Math.floor(elapsedSeconds / 3600);
                let minutes = Math.floor((elapsedSeconds % 3600) / 60);
                let seconds = elapsedSeconds % 60;
                progressTime.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                if (isPunchedOut) return;
            }

            function updateUI() {
                if (clockOutButton && isPunchedOut) {
                    clockOutButton.disabled = true;
                    clockOutButton.classList.add("opacity-50", "cursor-not-allowed");
                }
            }

            updateTimeDisplay();
            updateUI();
            updateProgress();

            if (!isPunchedOut) {
                setInterval(updateProgress, 1000);
            }

            if (clockInButton) {
                clockInButton.addEventListener("click", function (e) {
                    let now = new Date();
                    localStorage.setItem("clockInTime", now.toISOString());
                    localStorage.removeItem("isPunchedOut");
                    localStorage.removeItem("clockOutTime");
                    localStorage.removeItem("lastClockOutDate");
                    clockInTime = now;
                    clockOutTime = null;
                    isPunchedOut = false;
                    updateUI();
                });
            }

            // In your clock out confirmation handler:
            if (confirmClockOutBtn) {
                confirmClockOutBtn.addEventListener("click", function () {
                    const originalText = confirmClockOutBtn.innerText;
                    confirmClockOutBtn.disabled = true;
                    confirmClockOutBtn.innerText = "Capturing Location...";

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        document.getElementById('latitude').value = position.coords.latitude;
                                        document.getElementById('longitude').value = position.coords.longitude;
                                        document.getElementById('location').value = data.display_name || "Unknown Location";

                                        confirmClockOutBtn.innerText = "Submitting...";
                                        performClockOut();
                                    })
                                    .catch(error => {
                                        console.error("Clock Out Geocoding Error:", error);
                                        document.getElementById('latitude').value = position.coords.latitude;
                                        document.getElementById('longitude').value = position.coords.longitude;
                                        document.getElementById('location').value = "Location found, address unavailable";

                                        confirmClockOutBtn.innerText = "Submitting...";
                                        performClockOut();
                                    });
                            },
                            function(error) {
                                let errorMsg = "Could not access location.";
                                switch(error.code) {
                                    case error.PERMISSION_DENIED:
                                        errorMsg = "Location permission denied. Please allow location access.";
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        errorMsg = "Location information is unavailable. Please ensure GPS is ON.";
                                        break;
                                    case error.TIMEOUT:
                                        errorMsg = "Location request timed out. Proceeding with clock out.";
                                        break;
                                }
                                alert(errorMsg);
                                
                                if (error.code === error.TIMEOUT) {
                                    performClockOut();
                                } else {
                                    confirmClockOutBtn.disabled = false;
                                    confirmClockOutBtn.innerText = originalText;
                                }
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 15000,
                                maximumAge: 0
                            }
                        );
                    } else {
                        alert("Geolocation not supported by this browser. Proceeding with clock out.");
                        performClockOut();
                    }
                });
            }

            function performClockOut() {
                let now = new Date();
                
                // Clear all attendance-related localStorage data
                localStorage.removeItem("clockInTime");
                localStorage.removeItem("clockOutTime");
                localStorage.removeItem("isPunchedOut");
                localStorage.setItem("lastClockOutDate", now.toLocaleDateString());
                
                // Update variables
                clockOutTime = now;
                isPunchedOut = true;
                updateUI();
                
                // Submit the form
                document.getElementById('attendanceForm').submit();
                
                // Close the modal
                var modal = bootstrap.Modal.getInstance(document.getElementById('confirmClockOutModal'));
                modal.hide();
                
                // Update status and reload
                attendanceStatus.innerHTML = '<span class="text-danger"><i class="fas fa-sign-out-alt"></i> Punched Out at ' + now.toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit', hour12: true}) + '</span>';
                
                setTimeout(function() {
                    location.reload(true);
                }, 1000);
            }

            // At the start of your script, add this check:
            function isNewDay() {
                const lastClockOutDate = localStorage.getItem("lastClockOutDate");
                if (!lastClockOutDate) return false;
                
                const today = new Date().toLocaleDateString();
                return lastClockOutDate !== today;
            }

            if (isNewDay()) {
                localStorage.removeItem("clockInTime");
                localStorage.removeItem("clockOutTime");
                localStorage.removeItem("isPunchedOut");
            }

            setInterval(updateTimeDisplay, 1000);
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const clockInButton = document.getElementById("clock_in");
            const clockInButton2 = document.getElementById("clock_in_2");
            const clockOutButton2 = document.getElementById("clock_out_2");

            function handlePunch(btn, type) {
                if (btn) {
                    btn.addEventListener("click", function (e) {
                        e.preventDefault();
                        
                        // Disable button and show loading
                        const originalText = btn.innerText;
                        btn.disabled = true;
                        btn.innerText = "Capturing Location...";

                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                function(position) {
                                    document.getElementById('latitude').value = position.coords.latitude;
                                    document.getElementById('longitude').value = position.coords.longitude;
                                    
                                    // Submit form
                                    btn.innerText = "Submitting...";
                                    document.getElementById('attendanceForm').submit();
                                },
                                function(error) {
                                    let errorMsg = "Could not access location.";
                                    switch(error.code) {
                                        case error.PERMISSION_DENIED:
                                            errorMsg = "Location permission denied. Please allow location access in your app settings.";
                                            break;
                                        case error.POSITION_UNAVAILABLE:
                                            errorMsg = "Location information is unavailable. Please ensure your GPS is turned ON.";
                                            break;
                                        case error.TIMEOUT:
                                            errorMsg = "The request to get user location timed out. Submitting without location.";
                                            break;
                                    }
                                    
                                    alert(errorMsg);
                                    
                                    // Restore button if needed, but the user asked to submit anyway in previous version
                                    // Given the backend requirement, we should probably NOT submit if we need location
                                    // But I'll follow the existing pattern of "Submit anyway" if it's a timeout
                                    if (error.code === error.TIMEOUT) {
                                        document.getElementById("attendanceForm").submit();
                                    } else {
                                        btn.disabled = false;
                                        btn.innerText = originalText;
                                    }
                                },
                                { 
                                    enableHighAccuracy: true, 
                                    timeout: 15000, 
                                    maximumAge: 0 
                                }
                            );
                        } else {
                            alert("Geolocation is not supported by this device.");
                            document.getElementById("attendanceForm").submit();
                        }
                    });
                }
            }

            handlePunch(clockInButton, 'in');
            handlePunch(clockInButton2, 'in');
        });
    </script>
@endpush

<style>
#confirmClockOutModal {
    display: none;
}
</style>