<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Bulk Attendance')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Bulk Attendance')); ?></li>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('script-page'); ?>
    <script>
        $('#present_all').click(function(event) {
            // alert('hiii');
            if (this.checked) {
                $('.present').each(function() {
                    this.checked = true;
                });

                $('.present_check_in').removeClass('d-none');
                $('.present_check_in').addClass('d-block');

            } else {
                $('.present').each(function() {
                    this.checked = false;
                });
                $('.present_check_in').removeClass('d-block');
                $('.present_check_in').addClass('d-none');

            }
        });

        $('.present').click(function(event) {
            var div = $(this).parent().parent().parent().parent().find('.present_check_in');

            if (this.checked) {
                div.removeClass('d-none');
                div.addClass('d-block');

            } else {
                div.removeClass('d-block');
                div.addClass('d-none');
            }

        });
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
                url: '<?php echo e(route('monthly.getdepartment')); ?>',
                type: 'POST',
                data: {
                    "branch_id": bid,
                    "_token": "<?php echo e(csrf_token()); ?>",
                },
                success: function(data) {

                    $('.department_id').empty();
                    var emp_selct = `<select class="form-control department_id" name="department_id" id="choices-multiple"
                                            placeholder="Select Department" >
                                            </select>`;
                    $('.department_div').html(emp_selct);

                    $('.department_id').append('<option value=""> <?php echo e(__('Select Department')); ?> </option>');
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
    <style>
        .bulk-attendance-table th {
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border-color);
        }
        .bulk-attendance-table td {
            vertical-align: middle;
            padding: 15px 10px;
        }
        .emp-name {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
        }
        .emp-badge {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            background: rgba(34,197,94,0.1);
            color: #16a34a;
        }
        .time-group {
            background: var(--surface-2);
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }
        .time-group:hover {
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }
        .time-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 4px;
            display: block;
        }
        .time-input {
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            padding: 6px 10px;
            font-size: 13px;
            width: 100%;
            transition: border-color 0.15s ease;
        }
        .time-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
        }
        .custom-control-label {
            font-weight: 600;
            cursor: pointer;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('action-button'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        <?php echo e(Form::open(['route' => ['attendanceemployee.bulkattendance'], 'method' => 'get', 'id' => 'bulkattendance_filter'])); ?>

                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box"></div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            <?php echo e(Form::label('date', __('Date'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Form::text('date', isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'), ['class' => 'month-btn form-control d_week ', 'autocomplete' => 'off'])); ?>

                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            <?php echo e(Form::label('branch', __('branch'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control select branch_id', 'id' => 'branch_id'])); ?>

                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            <?php echo e(Form::label('department', __('department'), ['class' => 'form-label'])); ?>

                                            <?php echo e(Form::select('department', $department, isset($_GET['department']) ? $_GET['department'] : '', ['class' => 'form-control select department_id', 'id' => 'department_id'])); ?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">

                                        <a href="#" class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('bulkattendance_filter').submit(); return false;"
                                            data-bs-toggle="tooltip" title="" data-bs-original-title="apply">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="<?php echo e(route('attendanceemployee.bulkattendance')); ?>"
                                            class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title=""
                                            data-bs-original-title="Reset">
                                            <span class="btn-inner--icon"><i
                                                    class="ti ti-trash-off text-white-off "></i></span>
                                        </a>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php echo e(Form::close()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <?php echo e(Form::open(['route' => ['attendanceemployee.bulkattendance'], 'method' => 'post'])); ?>

                <div class="table-responsive">
                    <table class="table bulk-attendance-table" id="">
                        <thead>
                            <tr>
                                <th width="10%"><?php echo e(__('Employee Id')); ?></th>
                                <th><?php echo e(__('Employee')); ?></th>
                                <th><?php echo e(__('Branch')); ?></th>
                                <th><?php echo e(__('Department')); ?></th>
                                <th>
                                    <div class="form-group my-auto">
                                        <div class="custom-control ">
                                            <input class="form-check-input" type="checkbox" name="present_all"
                                                id="present_all" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                                            <label class="custom-control-label" for="present_all">
                                                <?php echo e(__('Attendance')); ?></label>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $attendance = $employee->present_status($employee->id, isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'));
                                ?>
                                <tr>
                                    <td class="Id">
                                        <input type="hidden" value="<?php echo e($employee->id); ?>" name="employee_id[]">
                                        <a href="<?php echo e(route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id))); ?>"
                                            class="btn btn-outline-primary btn-sm"><?php echo e(\Auth::user()->employeeIdFormat($employee->employee_id)); ?></a>
                                    </td>
                                    <td>
                                        <div class="emp-name"><?php echo e($employee->name); ?></div>
                                    </td>
                                    <td><span class="badge bg-secondary"><?php echo e(!empty($employee->branch) ? $employee->branch->name : ''); ?></span></td>
                                    <td><span class="badge bg-info"><?php echo e(!empty($employee->department) ? $employee->department->name : ''); ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-4">
                                            <div class="form-check custom-checkbox">
                                                <input class="form-check-input present" type="checkbox"
                                                    name="present-<?php echo e($employee->id); ?>"
                                                    id="present<?php echo e($employee->id); ?>"
                                                    <?php echo e(!empty($attendance) && in_array($attendance->status, ['Present', 'Half Day']) ? 'checked' : ''); ?>>
                                                <label class="form-check-label custom-control-label"
                                                    for="present<?php echo e($employee->id); ?>">Present</label>
                                            </div>
                                            
                                            <div class="present_check_in <?php echo e(empty($attendance) ? 'd-none' : ''); ?> flex-grow-1">
                                                <div class="d-flex gap-3">
                                                    <div class="time-group flex-grow-1">
                                                        <label class="time-label"><i class="ti ti-clock me-1"></i> <?php echo e(__('In Time')); ?></label>
                                                        <input type="time" class="time-input"
                                                            name="in-<?php echo e($employee->id); ?>"
                                                            id="in-<?php echo e($employee->id); ?>"
                                                            value="<?php echo e(!empty($attendance) && $attendance->clock_in != '00:00:00' ? $attendance->clock_in : \Utility::getValByName('company_start_time')); ?>">
                                                        
                                                        <!-- Hidden fields for location -->
                                                        <input type="hidden" name="clock_in_latitude_<?php echo e($employee->id); ?>" id="clock_in_latitude_<?php echo e($employee->id); ?>" value="<?php echo e($attendance->clock_in_latitude ?? ''); ?>">
                                                        <input type="hidden" name="clock_in_longitude_<?php echo e($employee->id); ?>" id="clock_in_longitude_<?php echo e($employee->id); ?>" value="<?php echo e($attendance->clock_in_longitude ?? ''); ?>">
                                                        <input type="hidden" name="clock_in_location_<?php echo e($employee->id); ?>" id="clock_in_location_<?php echo e($employee->id); ?>" value="<?php echo e($attendance->clock_in_location ?? ''); ?>">
                                                        <?php if(!empty($attendance) && !empty($attendance->clock_in_location)): ?>
                                                            <div class="mt-1 text-muted" style="font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;" title="<?php echo e($attendance->clock_in_location); ?>">
                                                                <i class="ti ti-map-pin text-primary"></i> <?php echo e($attendance->clock_in_location); ?>

                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="time-group flex-grow-1">
                                                        <label class="time-label"><i class="ti ti-clock me-1"></i> <?php echo e(__('Out Time')); ?></label>
                                                        <input type="time" class="time-input"
                                                            name="out-<?php echo e($employee->id); ?>"
                                                            id="out-<?php echo e($employee->id); ?>"
                                                            value="<?php echo e(!empty($attendance) && $attendance->clock_out != '00:00:00' ? $attendance->clock_out : ''); ?>">
                                                        
                                                        <!-- Hidden fields for location -->
                                                        <input type="hidden" name="clock_out_latitude_<?php echo e($employee->id); ?>" id="clock_out_latitude_<?php echo e($employee->id); ?>" value="<?php echo e($attendance->clock_out_latitude ?? ''); ?>">
                                                        <input type="hidden" name="clock_out_longitude_<?php echo e($employee->id); ?>" id="clock_out_longitude_<?php echo e($employee->id); ?>" value="<?php echo e($attendance->clock_out_longitude ?? ''); ?>">
                                                        <input type="hidden" name="clock_out_location_<?php echo e($employee->id); ?>" id="clock_out_location_<?php echo e($employee->id); ?>" value="<?php echo e($attendance->clock_out_location ?? ''); ?>">
                                                        <?php if(!empty($attendance) && !empty($attendance->clock_out_location)): ?>
                                                            <div class="mt-1 text-muted" style="font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;" title="<?php echo e($attendance->clock_out_location); ?>">
                                                                <i class="ti ti-map-pin text-danger"></i> <?php echo e($attendance->clock_out_location); ?>

                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="attendance-btn float-end pt-4">
                    <input type="hidden" value="<?php echo e(isset($_GET['date']) ? $_GET['date'] : date('Y-m-d')); ?>"
                        name="date">
                    <input type="hidden" value="<?php echo e(isset($_GET['branch']) ? $_GET['branch'] : ''); ?>" name="branch">
                    <input type="hidden" value="<?php echo e(isset($_GET['department']) ? $_GET['department'] : ''); ?>"
                        name="department">
                    <?php echo e(Form::submit(__('Update'), ['class' => 'btn btn-primary'])); ?>

                </div>
                <?php echo e(Form::close()); ?>

            </div>
        </div>
    </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script>
        $(document).ready(function() {
            if ($('.daterangepicker').length > 0) {
                $('.daterangepicker').daterangepicker({
                    format: 'yyyy-mm-dd',
                    locale: {
                        format: 'YYYY-MM-DD'
                    },
                });
            }
        });
        
        // Geolocation capture functions for bulk attendance
        function captureBulkLocation(employeeId, type) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        // Set hidden field values
                        document.getElementById(type + '_latitude_' + employeeId).value = lat;
                        document.getElementById(type + '_longitude_' + employeeId).value = lng;
                        
                        // Get address using reverse geocoding (optional)
                        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                            .then(response => response.json())
                            .then(data => {
                                const address = data.display_name || `Lat: ${lat}, Lng: ${lng}`;
                                document.getElementById(type + '_location_' + employeeId).value = address;
                                document.getElementById(type + '_location_text_' + employeeId).textContent = '📍 ' + address;
                            })
                            .catch(error => {
                                // Fallback to coordinates
                                const address = `Lat: ${lat}, Lng: ${lng}`;
                                document.getElementById(type + '_location_' + employeeId).value = address;
                                document.getElementById(type + '_location_text_' + employeeId).textContent = '📍 ' + address;
                            });
                    },
                    function(error) {
                        console.error('Error getting location:', error);
                        document.getElementById(type + '_location_text_' + employeeId).textContent = '❌ Location access denied';
                    }
                );
            } else {
                document.getElementById(type + '_location_text_' + employeeId).textContent = '❌ Geolocation not supported';
            }
        }

        // Add event listeners for all employee time fields
        document.addEventListener('change', function(e) {
            if (e.target && e.target.id && e.target.id.startsWith('in-')) {
                const employeeId = e.target.id.replace('in-', '');
                captureBulkLocation(employeeId, 'clock_in');
            }
            if (e.target && e.target.id && e.target.id.startsWith('out-')) {
                const employeeId = e.target.id.replace('out-', '');
                captureBulkLocation(employeeId, 'clock_out');
            }
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/attendance/bulk.blade.php ENDPATH**/ ?>