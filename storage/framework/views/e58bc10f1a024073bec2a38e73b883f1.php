<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Dashboard')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
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
        <?php if(session('status')): ?>
            <div class="alert alert-success" role="alert">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?>

        <?php if(\Auth::user()->type == 'employee'): ?>
            <div class="col-xxl-9">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card">  
                                    <div class="card-header d-flex align-items-center">
                                        <img src="<?php echo e(asset('https://connect360.in//storage/uploads/avatar/' . ($emp->user->avatar ?? 'default-avatar.png'))); ?>" 
                                            alt="Profile Image" 
                                            class="rounded-circle me-4" 
                                            width="60" 
                                            height="60">
                                        <div>
                                            <h4 class="mb-0" style="color:black;"><?php echo e($emp->name); ?></h4>
                                            <small style="font-size: 12px; color:black;"><?php echo e($emp->department->name ?? 'No Department'); ?> Team</small><small style="font-size:16px; color:black;"> &nbsp<?php echo e($emp->designation->name ?? 'No Designation'); ?>&nbsp</small><br>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Phone Number:<br></strong> <?php echo e($emp->phone ?? 'N/A'); ?></p><br>
                                        <p><strong>Email Address:<br></strong> <?php echo e($emp->email ?? 'N/A'); ?></p><br>
                                        <p><strong>Joined On:<br></strong> <?php echo e(\Carbon\Carbon::parse($emp->company_doj)->format('d M Y')); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card" style="height:395px;">
                                    <div class="card-header">
                                        <h5 style="font-size:20px;color:black"><?php echo e(__('Attendance')); ?></h5>
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
                                            <?php
                                                $siteVisit = \App\Models\SiteVisit::where('employee_id', $emp->id)->where('date', date('Y-m-d'))->where('status', 'Approved')->first();
                                            ?>

                                            <?php if(!isset($employeeAttendance) || !$employeeAttendance->clock_in): ?>
                                                <span class="text-primary"><i class="fas fa-fingerprint"></i> Not Punched In</span>
                                            <?php elseif($employeeAttendance->clock_out == '00:00:00' || !$employeeAttendance->clock_out): ?>
                                                <span class="text-success"><i class="fas fa-fingerprint"></i> Punched In at <?php echo e(\Carbon\Carbon::parse($employeeAttendance->clock_in)->format('h:i A')); ?></span>
                                            <?php elseif($siteVisit && (empty($employeeAttendance->clock_in_2) || $employeeAttendance->clock_in_2 == '00:00:00')): ?>
                                                <span class="text-warning"><i class="fas fa-map-marker-alt"></i> Punched Out (Site Visit Pending)</span>
                                            <?php elseif($siteVisit && (empty($employeeAttendance->clock_out_2) || $employeeAttendance->clock_out_2 == '00:00:00')): ?>
                                                <span class="text-success"><i class="fas fa-map-marker-alt"></i> Site Visit Punched In at <?php echo e(\Carbon\Carbon::parse($employeeAttendance->clock_in_2)->format('h:i A')); ?></span>
                                            <?php else: ?>
                                                <span class="text-danger"><i class="fas fa-sign-out-alt"></i> Punched Out at <?php echo e(\Carbon\Carbon::parse($employeeAttendance->clock_out_2 && $employeeAttendance->clock_out_2 != '00:00:00' ? $employeeAttendance->clock_out_2 : $employeeAttendance->clock_out)->format('h:i A')); ?></span>
                                            <?php endif; ?>
                                        </p>

                                        <?php echo e(Form::open(['url' => 'attendanceemployee/attendance', 'method' => 'post', 'id' => 'attendanceForm'])); ?>

                                            <!-- Hidden fields for location capture -->
                                            <input type="hidden" id="latitude" name="latitude">
                                            <input type="hidden" id="longitude" name="longitude">
                                            <input type="hidden" id="location" name="location">
                                            
                                            <?php if(empty($employeeAttendance) || (!$employeeAttendance->clock_in)): ?>
                                                <button type="submit" value="0" name="in" id="clock_in" class="btn btn-primary"><?php echo e(__('Punch In')); ?></button>
                                            <?php elseif($siteVisit && (empty($employeeAttendance->clock_in_2) || $employeeAttendance->clock_in_2 == '00:00:00')): ?>
                                                <button type="submit" value="0" name="in" id="clock_in_2" class="btn btn-warning"><?php echo e(__('Site Visit In')); ?></button>
                                            <?php elseif($siteVisit && (empty($employeeAttendance->clock_out_2) || $employeeAttendance->clock_out_2 == '00:00:00')): ?>
                                                <button type="button" value="1" name="out" id="clock_out_2" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmClockOutModal">
                                                    <?php echo e(__('Site Visit Out')); ?>

                                                </button>
                                            <?php elseif($employeeAttendance->clock_out == '00:00:00' || !$employeeAttendance->clock_out): ?>
                                                <button type="button" value="1" name="out" id="clock_out" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmClockOutModal">
                                                    <?php echo e(__('Punch Out')); ?>

                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-secondary" disabled><?php echo e(__('Completed')); ?></button>
                                            <?php endif; ?>
                                        <?php echo e(Form::close()); ?>

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
                                        <h5 style="font-size:20px; color:black; margin: 0;"><?php echo e(__('Notices')); ?></h5>
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
                                                    <?php $__currentLoopData = $notices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td style="word-wrap: break-word; white-space: normal;">
                                                            <?php echo e(Str::limit($notice->title, 50, '...')); ?>

                                                        </td>
                                                        <td>
                                                            <?php echo e(\Carbon\Carbon::parse($notice->notice_startdate)->format('d M Y')); ?> - 
                                                            <?php echo e(\Carbon\Carbon::parse($notice->notice_enddate)->format('d M Y')); ?>

                                                        </td>
                                                    </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header card-body table-border-style">
                                        <h5 style="font-size:20px;color:black"><?php echo e(__('TO-DO Lists')); ?></h5>
                                    </div>
                                    <div class="card-body" style="height: 324px; overflow:auto;">
                                        <div class="table-responsive"> 
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                    <th><?php echo e(__('Task Title')); ?></th>
                                                    <th><?php echo e(__('Priority')); ?></th>
                                                    <th><?php echo e(__('Due Date')); ?></th>
                                                    <th><?php echo e(__('Status')); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="list">
                                                    <?php $__currentLoopData = $todos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $todo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                            <td><?php echo e($todo->task); ?></td>
                                                            <td>
                                                                <?php if($todo->priority == 1): ?>
                                                                    <span class="badge bg-danger"><?php echo e(__('High')); ?></span>
                                                                <?php elseif($todo->priority == 2): ?>
                                                                    <span class="badge bg-warning"><?php echo e(__('Medium')); ?></span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-success"><?php echo e(__('Low')); ?></span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo e(\Carbon\Carbon::parse($todo->expires_at)->format('d M Y')); ?></td>
                                                            <td>
                                                                <?php if($todo->is_completed): ?>
                                                                    <span class="badge bg-success"><?php echo e(__('Completed')); ?></span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-danger"><?php echo e(__('Pending')); ?></span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <h5 style="font-size:20px;color:black"><?php echo e(__("This Month Event's")); ?></h5>
                        </div>
                        <div class="card-body">
                            <?php if(isset($monthlyEvents) && count($monthlyEvents) > 0): ?>
                                <div class="events-container">
                                    <?php $__currentLoopData = $monthlyEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="event-item d-flex align-items-center mb-3 p-2 rounded <?php echo e($event['type'] == 'birthday' ? 'birthday-event' : 'anniversary-event'); ?>">
                                            <div class="event-avatar me-3">
                                                <img src="<?php echo e(asset('storage/uploads/avatar/' . $event['avatar'])); ?>" 
                                                     alt="<?php echo e($event['employee_name']); ?>" 
                                                     class="rounded-circle" 
                                                     width="45" 
                                                     height="45"
                                                     onerror="this.src='<?php echo e(asset('storage/avatars/avatar.png')); ?>'">
                                            </div>
                                            <div class="event-details flex-grow-1">
                                                <h6 class="mb-1 fw-bold"><?php echo e($event['employee_name']); ?></h6>
                                                <p class="mb-1 <?php echo e($event['type'] == 'birthday' ? 'text-primary' : 'text-success'); ?> fw-semibold">
                                                    <?php echo e($event['message']); ?>

                                                </p>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i><?php echo e($event['date']); ?> • 
                                                    <i class="fas fa-building me-1"></i><?php echo e($event['department']); ?>

                                                </small>
                                            </div>
                                            <div class="event-icon">
                                                <?php if($event['type'] == 'birthday'): ?>
                                                    <div class="birthday-icon">
                                                        <i class="fas fa-birthday-cake text-primary"></i>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="anniversary-icon">
                                                        <i class="fas fa-award text-success"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 2rem;"></i>
                                    <p class="text-muted mb-0">No events this month</p>
                                    <small class="text-muted">Check back next month for upcoming celebrations!</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card flex-grow-1">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-6">
                                    <h5><?php echo e(__('Calendar')); ?></h5>
                                    <!-- <input type="hidden" id="path_admin" value="<?php echo e(url('/')); ?>"> -->
                                </div>
                                
                            </div>
                        </div>
                        <div class="card-body" style="padding-top:0px;">
                            <div id='calendar' class='calendar'></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script src="<?php echo e(asset('assets/js/plugins/main.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/apexcharts.min.js')); ?>"></script>

    <?php if(Auth::user()->type == 'employee'): ?>
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
                "_token": "<?php echo e(csrf_token()); ?>",
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
    <?php endif; ?>

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

            <?php if(isset($employeeAttendance) && $employeeAttendance->clock_in): ?>
                if (!clockInTime) {
                    clockInTime = new Date("<?php echo e(\Carbon\Carbon::parse($employeeAttendance->clock_in)->toIso8601String()); ?>");
                    localStorage.setItem("clockInTime", clockInTime.toISOString());
                }
            <?php endif; ?>

            <?php if(isset($employeeAttendance) && $employeeAttendance->clock_out && $employeeAttendance->clock_out !== '00:00:00'): ?>
                if (!clockOutTime) {
                    clockOutTime = new Date("<?php echo e(\Carbon\Carbon::parse($employeeAttendance->clock_out)->toIso8601String()); ?>");
                    localStorage.setItem("clockOutTime", clockOutTime.toISOString());
                    localStorage.setItem("isPunchedOut", "true");
                    localStorage.setItem("lastClockOutDate", new Date().toLocaleDateString());
                    isPunchedOut = true;
                }
            <?php endif; ?>

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
                // Only disable the clock out button if the user is actually punched out
                // (has clock_out time in database and localStorage)
                if (clockOutButton && isPunchedOut && clockOutTime) {
                    clockOutButton.disabled = true;
                    clockOutButton.classList.add("opacity-50", "cursor-not-allowed");
                } else if (clockOutButton && !isPunchedOut) {
                    // Enable the button if user is not punched out
                    clockOutButton.disabled = false;
                    clockOutButton.classList.remove("opacity-50", "cursor-not-allowed");
                }
            }

            updateTimeDisplay();
            updateUI();
            updateProgress();

            // SIMPLE: Always enable the punch out button if it exists
            if (clockOutButton) {
                clockOutButton.disabled = false;
                clockOutButton.classList.remove("opacity-50", "cursor-not-allowed");
                console.log('Punch Out button enabled');
            }

            // ONE-TIME PERMISSION CHECK ON PAGE LOAD
            checkLocationPermissionOnLoad();

            if (!isPunchedOut) {
                setInterval(updateProgress, 1000);
            }

            if (clockInButton) {
                clockInButton.addEventListener("click", function (e) {
                    e.preventDefault();
                    console.log('Punch In button clicked!');
                    handleLocationRequest('punch_in', clockInButton);
                });
            }

            // NATIVE LOCATION PERMISSION SYSTEM
            let locationPermissionGranted = false;
            let locationPermissionChecked = false;

            function handleLocationRequest(action, button) {
                const originalText = button.innerText;
                button.disabled = true;
                button.innerText = "Getting location...";

                // Check if we already know the permission status
                if (locationPermissionGranted) {
                    // Permission already granted, get location directly
                    getLocationAndSubmit(action, button);
                    return;
                }

                // Check permission status first
                if (navigator.permissions) {
                    navigator.permissions.query({ name: 'geolocation' }).then(result => {
                        locationPermissionChecked = true;
                        
                        if (result.state === 'granted') {
                            locationPermissionGranted = true;
                            getLocationAndSubmit(action, button);
                        } else if (result.state === 'prompt') {
                            // Will trigger native browser permission dialog
                            getLocationAndSubmit(action, button);
                        } else if (result.state === 'denied') {
                            // Permission denied, show simple error
                            showSimpleLocationError(action, 'Location permission was denied. Please enable location access in your browser settings.');
                            button.disabled = false;
                            button.innerText = originalText;
                        }
                    }).catch(() => {
                        // Permissions API not supported, try directly
                        getLocationAndSubmit(action, button);
                    });
                } else {
                    // Fallback: try directly (will show native permission if needed)
                    getLocationAndSubmit(action, button);
                }
            }

            function getLocationAndSubmit(action, button) {
                if (!navigator.geolocation) {
                    showSimpleLocationError(action, 'Your browser does not support location services. Please use a modern browser.');
                    button.disabled = false;
                    button.innerText = action === 'punch_in' ? 'Punch In' : 'Punch Out';
                    return;
                }

                // Fast options for quick location
                const fastOptions = {
                    enableHighAccuracy: false,  // Faster but less accurate
                    timeout: 3000,             // Reduced to 3s for speed
                    maximumAge: 120000         // Allow cached location (2 minutes)
                };

                let locationTimeout;
                let locationFound = false;

                // Set a timeout to prevent hanging
                locationTimeout = setTimeout(() => {
                    if (!locationFound) {
                        console.log('Fast location timeout, trying fallback...');
                        tryFallbackLocation(action, button);
                    }
                }, 2500); // 2.5s fallback

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        locationFound = true;
                        clearTimeout(locationTimeout);
                        
                        console.log('Fast location captured:', position.coords.latitude, position.coords.longitude);
                        
                        // Mark permission as granted for future use
                        locationPermissionGranted = true;
                        locationPermissionChecked = true;
                        
                        button.innerText = "Submitting...";
                        
                        // Set coordinates immediately
                        document.getElementById('latitude').value = position.coords.latitude;
                        document.getElementById('longitude').value = position.coords.longitude;
                        document.getElementById('location').value = "Location captured";
                        
                        // Submit immediately for speed
                        submitAttendance(action);
                        
                        // Get address in background (non-blocking)
                        getAddressInBackground(position.coords.latitude, position.coords.longitude);
                    },
                    function(error) {
                        locationFound = true;
                        clearTimeout(locationTimeout);
                        
                        console.log('Fast location failed:', error.code);
                        
                        // Try fallback for certain errors
                        if (error.code === error.TIMEOUT || error.code === error.POSITION_UNAVAILABLE) {
                            tryFallbackLocation(action, button);
                        } else {
                            handleLocationError(action, button, error);
                        }
                    },
                    fastOptions
                );
            }

            function tryFallbackLocation(action, button) {
                console.log('Trying fallback location method...');
                
                // Very fast options for fallback
                const fallbackOptions = {
                    enableHighAccuracy: false,
                    timeout: 2000,
                    maximumAge: 300000  // 5 minutes cached
                };

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        console.log('Fallback location captured:', position.coords.latitude, position.coords.longitude);
                        
                        button.innerText = "Submitting...";
                        
                        // Set coordinates immediately
                        document.getElementById('latitude').value = position.coords.latitude;
                        document.getElementById('longitude').value = position.coords.longitude;
                        document.getElementById('location').value = "Location captured";
                        
                        // Submit immediately
                        submitAttendance(action);
                        
                        // Get address in background
                        getAddressInBackground(position.coords.latitude, position.coords.longitude);
                    },
                    function(error) {
                        console.log('Fallback location failed:', error.code);
                        handleLocationError(action, button, error);
                    },
                    fallbackOptions
                );
            }

            function handleLocationError(action, button, error) {
                let errorMessage = '';
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Location permission was denied. Please enable location access in your browser settings.';
                        locationPermissionGranted = false;
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'Location information unavailable. Please ensure GPS/location services are enabled.';
                        break;
                    case error.TIMEOUT:
                        errorMessage = 'Location request timed out. Please check your connection and try again.';
                        break;
                    default:
                        errorMessage = 'Unable to get location. Please try again.';
                        break;
                }
                
                showSimpleLocationError(action, errorMessage);
                button.disabled = false;
                button.innerText = action === 'punch_in' ? 'Punch In' : 'Punch Out';
            }

            function getAddressInBackground(lat, lon) {
                // Get address in background without blocking
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
                    .then(response => response.json())
                    .then(data => {
                        // Update location field if still on page
                        const locationField = document.getElementById('location');
                        if (locationField && locationField.value === "Location captured") {
                            locationField.value = data.display_name || "Location found";
                        }
                    })
                    .catch(error => {
                        console.log("Background geocoding skipped:", error.message);
                    });
            }

            function showSimpleLocationError(action, message) {
                // Simple alert instead of custom modal for native feel
                alert(message);
            }

            function checkLocationPermissionOnLoad() {
                // Check permission status on page load (silent)
                if (navigator.permissions) {
                    navigator.permissions.query({ name: 'geolocation' }).then(result => {
                        locationPermissionChecked = true;
                        
                        if (result.state === 'granted') {
                            locationPermissionGranted = true;
                            console.log('Location permission already granted');
                        } else if (result.state === 'prompt') {
                            console.log('Location permission not yet requested');
                            // Don't request on page load, wait for user action
                        } else if (result.state === 'denied') {
                            locationPermissionGranted = false;
                            console.log('Location permission denied');
                        }
                    }).catch(() => {
                        console.log('Permissions API not supported');
                    });
                } else {
                    console.log('Permissions API not available');
                }
            }

            function submitAttendance(action) {
                // Update localStorage
                let now = new Date();
                
                if (action === 'punch_in') {
                    localStorage.setItem("clockInTime", now.toISOString());
                    localStorage.removeItem("isPunchedOut");
                    localStorage.removeItem("clockOutTime");
                    localStorage.removeItem("lastClockOutDate");
                    clockInTime = now;
                    clockOutTime = null;
                    isPunchedOut = false;
                } else {
                    localStorage.removeItem("clockInTime");
                    localStorage.removeItem("clockOutTime");
                    localStorage.removeItem("isPunchedOut");
                    localStorage.setItem("lastClockOutDate", now.toLocaleDateString());
                    clockOutTime = now;
                    isPunchedOut = true;
                }
                
                updateUI();
                
                // Submit the form
                setTimeout(() => {
                    document.getElementById('attendanceForm').submit();
                }, 500);
            }

            // PUNCH OUT HANDLER
            if (confirmClockOutBtn) {
                confirmClockOutBtn.addEventListener("click", function () {
                    console.log('Confirm Clock Out button clicked!');
                    handleLocationRequest('punch_out', confirmClockOutBtn);
                });
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

    <?php $__env->stopPush(); ?>

<style>
/* Simple modal styling */
#confirmClockOutModal {
    display: none;
}

#confirmClockOutModal.show {
    display: block !important;
}

.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.5);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1040;
}

#confirmClockOutBtn {
    min-width: 120px;
}
</style>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/dashboard/dashboard.blade.php ENDPATH**/ ?>