    
    <?php $__env->startSection('page-title'); ?>
        <?php echo e(__('Manage Attendance List')); ?>

    <?php $__env->stopSection(); ?>

    <?php $__env->startSection('breadcrumb'); ?>
        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
        <li class="breadcrumb-item"><?php echo e(__('Attendance List')); ?></li>
    <?php $__env->stopSection(); ?>


    <?php $__env->startPush('script-page'); ?>
        <script>
            $(document).ready(function () {

                // ─── View Type Toggle ──────────────────────────────────────────
                $('input[name="type"]').on('change', function () {
                    var type = $(this).val();
                    $('.filter-type-btn').removeClass('active');
                    $(this).closest('.filter-type-btn').addClass('active');
                    if (type === 'monthly') {
                        $('.month').show(); $('.date').hide();
                    } else {
                        $('.date').show(); $('.month').hide();
                    }
                });

                // Init on load
                var currentType = $('input[name="type"]:checked').val() || 'monthly';
                if (currentType === 'daily') { $('.date').show(); $('.month').hide(); }
                else { $('.month').show(); $('.date').hide(); }

                // ─── Cascading Dropdowns ───────────────────────────────────────

                // Initially hide department and employee cols
                $('#dept-col').hide();
                $('#emp-col').hide();

                // If branch is already selected (from query string), load departments
                var selectedBranch     = '<?php echo e(request('branch')); ?>';
                var selectedDepartment = '<?php echo e(request('department')); ?>';
                var selectedEmployee   = '<?php echo e(request('employee')); ?>';

                if (selectedBranch) {
                    loadDepartments(selectedBranch, selectedDepartment, selectedEmployee);
                }

                // ── Branch changes ──
                $(document).on('change', '#branch_id', function () {
                    var branchId = $(this).val();

                    // Reset downstream
                    $('#department_id').html('<option value="">— Select Department —</option>');
                    $('#employee_id').html('<option value="">— Select Employee —</option>');
                    $('#dept-col').hide();
                    $('#emp-col').hide();

                    if (!branchId) return;

                    loadDepartments(branchId, '', '');
                });

                // ── Department changes ──
                $(document).on('change', '#department_id', function () {
                    var deptId = $(this).val();

                    // Reset downstream
                    $('#employee_id').html('<option value="">— Select Employee —</option>');
                    $('#emp-col').hide();

                    if (!deptId) return;

                    loadEmployees(deptId, '');
                });

                // ─── Helpers ──────────────────────────────────────────────────

                function loadDepartments(branchId, preselectedDept, preselectedEmp) {
                    $.ajax({
                        url: '<?php echo e(route('monthly.getdepartment')); ?>',
                        type: 'POST',
                        data: { branch_id: branchId, _token: '<?php echo e(csrf_token()); ?>' },
                        success: function (data) {
                            var options = '<option value="">— Select Department —</option>';
                            $.each(data, function (id, name) {
                                var sel = (id == preselectedDept) ? 'selected' : '';
                                options += '<option value="' + id + '" ' + sel + '>' + name + '</option>';
                            });
                            $('#department_id').html(options);
                            $('#dept-col').show();

                            if (preselectedDept) {
                                loadEmployees(preselectedDept, preselectedEmp);
                            }
                        }
                    });
                }

                function loadEmployees(deptId, preselectedEmp) {
                    $.ajax({
                        url: '<?php echo e(route('monthly.getemployee')); ?>',
                        type: 'POST',
                        data: { department_id: deptId, _token: '<?php echo e(csrf_token()); ?>' },
                        success: function (data) {
                            var options = '<option value="">— All Employees —</option>';
                            $.each(data, function (id, name) {
                                var sel = (id == preselectedEmp) ? 'selected' : '';
                                options += '<option value="' + id + '" ' + sel + '>' + name + '</option>';
                            });
                            $('#employee_id').html(options);
                            $('#emp-col').show();
                        }
                    });
                }
            });
        </script>
    <?php $__env->stopPush(); ?>

    <?php $__env->startSection('action-button'); ?>
        <a href="<?php echo e(route('attendance.calendar')); ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="<?php echo e(__('Calendar View')); ?>">
            <i class="ti ti-calendar"></i>
        </a>
    <?php $__env->stopSection(); ?>
    <?php $__env->startSection('content'); ?>
        <?php if(session('status')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo session('   '); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <style>
            .filter-card {
                border: none;
                border-radius: 16px;
                box-shadow: 0 2px 16px rgba(253, 117, 35, 0.08);
                background: #fff;
                margin-bottom: 20px;
            }
            .filter-card .card-body {
                padding: 20px 24px 16px 24px;
            }
            .filter-header {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 16px;
                border-bottom: 2px solid #fff4ee;
                padding-bottom: 12px;
            }
            .filter-header .filter-title {
                font-size: 13px;
                font-weight: 700;
                color: #fd7523;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }
            .filter-header i {
                color: #fd7523;
                font-size: 17px;
            }
            .filter-type-group {
                display: flex;
                gap: 6px;
                flex-wrap: wrap;
            }
            .filter-type-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 7px 14px;
                border-radius: 8px;
                border: 1.5px solid #e0e3ee;
                background: #f8f9fc;
                color: #6c757d;
                font-size: 13px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.18s;
            }
            .filter-type-btn input[type="radio"] {
                display: none;
            }
            .filter-type-btn.active,
            .filter-type-btn:has(input:checked) {
                background: #fd7523;
                border-color: #fd7523;
                color: #fff;
                box-shadow: 0 3px 10px rgba(253,117,35,0.25);
            }
            .filter-type-btn:not(.active):hover {
                border-color: #fd7523;
                color: #fd7523;
                background: #fff4ee;
            }
            .filter-type-btn i { font-size: 14px; }
            .filter-label {
                font-size: 11px;
                font-weight: 700;
                color: #adb5bd;
                margin-bottom: 5px;
                letter-spacing: 0.06em;
                text-transform: uppercase;
            }
            .filter-control {
                border-radius: 8px;
                border: 1.5px solid #e0e3ee;
                font-size: 13px;
                color: #3a3b45;
                background: #f8f9fc;
                transition: border-color 0.18s, box-shadow 0.18s;
                padding: 7px 10px;
            }
            .filter-control:focus {
                border-color: #fd7523;
                box-shadow: 0 0 0 3px rgba(253,117,35,0.12);
                background: #fff;
                outline: none;
            }
            .filter-actions {
                display: flex;
                gap: 8px;
                align-items: flex-end;
                padding-bottom: 2px;
            }
            .btn-filter-apply {
                background: linear-gradient(135deg, #fd7523 0%, #e8621a 100%);
                border: none;
                border-radius: 8px;
                color: #fff;
                font-size: 13px;
                font-weight: 600;
                padding: 8px 18px;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: all 0.18s;
                white-space: nowrap;
                box-shadow: 0 3px 10px rgba(253,117,35,0.30);
            }
            .btn-filter-apply:hover {
                background: linear-gradient(135deg, #e8621a 0%, #d45510 100%);
                color: #fff;
                box-shadow: 0 6px 16px rgba(253,117,35,0.40);
                transform: translateY(-1px);
            }
            .btn-filter-reset {
                background: #fff;
                border: 1.5px solid #e0e3ee;
                border-radius: 8px;
                color: #6c757d;
                font-size: 13px;
                font-weight: 600;
                padding: 8px 14px;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: all 0.18s;
                white-space: nowrap;
            }
            .btn-filter-reset:hover {
                background: #fff4ee;
                border-color: #fd7523;
                color: #fd7523;
            }
            .btn-filter-import {
                background: #fff;
                border: 1.5px solid #fd7523;
                border-radius: 8px;
                color: #fd7523;
                font-size: 13px;
                font-weight: 600;
                padding: 8px 14px;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: all 0.18s;
                white-space: nowrap;
            }
            .btn-filter-import:hover {
                background: #fff4ee;
                border-color: #e8621a;
                color: #e8621a;
            }
            .filter-divider {
                width: 1px;
                height: 36px;
                background: #e0e3ee;
                margin: 0 4px;
                align-self: flex-end;
            }
        </style>

        <div class="row">
            <div class="col-sm-12">
                <div class="filter-card card mt-2" id="multiCollapseExample1">
                    <div class="card-body">
                        <?php echo e(Form::open(['route' => ['attendanceemployee.index'], 'method' => 'get', 'id' => 'attendanceemployee_filter'])); ?>


                        <div class="filter-header">
                            <i class="ti ti-adjustments-horizontal"></i>
                            <span class="filter-title"><?php echo e(__('Filter Attendance')); ?></span>
                        </div>

                        <div class="row g-3 align-items-end">

                            
                            <div class="col-xl-auto col-lg-auto col-md-6 col-12">
                                <div class="filter-label"><?php echo e(__('View Type')); ?></div>
                                <div class="filter-type-group">
                                    <label class="filter-type-btn <?php echo e((!isset($_GET['type']) || $_GET['type'] == 'monthly') ? 'active' : ''); ?>" id="label-monthly">
                                        <input type="radio" name="type" value="monthly" <?php echo e((!isset($_GET['type']) || $_GET['type'] == 'monthly') ? 'checked' : ''); ?>>
                                        <i class="ti ti-calendar-month"></i> <?php echo e(__('Monthly')); ?>

                                    </label>
                                    <label class="filter-type-btn <?php echo e(isset($_GET['type']) && $_GET['type'] == 'daily' ? 'active' : ''); ?>" id="label-daily">
                                        <input type="radio" name="type" value="daily" <?php echo e(isset($_GET['type']) && $_GET['type'] == 'daily' ? 'checked' : ''); ?>>
                                        <i class="ti ti-calendar-day"></i> <?php echo e(__('Daily')); ?>

                                    </label>
                                </div>
                            </div>

                            
                            <div class="col-xl col-lg col-md-6 col-12 month">
                                <div class="filter-label"><?php echo e(__('Month')); ?></div>
                                <?php echo e(Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class' => 'form-control filter-control'])); ?>

                            </div>

                            
                            <div class="col-xl col-lg col-md-6 col-12 date" style="display:none;">
                                <div class="filter-label"><?php echo e(__('Date')); ?></div>
                                <?php echo e(Form::date('date', isset($_GET['date']) ? $_GET['date'] : '', ['class' => 'form-control filter-control'])); ?>

                            </div>

                            <?php if(\Auth::user()->type != 'employee'): ?>
                                
                                <div class="col-xl col-lg col-md-6 col-12">
                                    <div class="filter-label"><?php echo e(__('Branch')); ?></div>
                                    <?php echo e(Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control filter-control select', 'id' => 'branch_id'])); ?>

                                </div>

                                
                                <div class="col-xl col-lg col-md-6 col-12" id="dept-col">
                                    <div class="filter-label"><?php echo e(__('Department')); ?></div>
                                    <select name="department" id="department_id" class="form-control filter-control">
                                        <option value="">— Select Department —</option>
                                    </select>
                                </div>

                                
                                <div class="col-xl col-lg col-md-6 col-12" id="emp-col">
                                    <div class="filter-label"><?php echo e(__('Employee')); ?></div>
                                    <select name="employee" id="employee_id" class="form-control filter-control">
                                        <option value="">— All Employees —</option>
                                    </select>
                                </div>
                            <?php endif; ?>

                            
                            <div class="col-xl-auto col-lg-auto col-md-12 col-12">
                                <div class="filter-actions">
                                    <a href="#" class="btn-filter-apply"
                                        onclick="document.getElementById('attendanceemployee_filter').submit(); return false;"
                                        data-bs-toggle="tooltip" title="<?php echo e(__('Apply Filter')); ?>">
                                        <i class="ti ti-search"></i>
                                    </a>
                                    <a href="<?php echo e(route('attendanceemployee.index')); ?>" class="btn-filter-reset"
                                        data-bs-toggle="tooltip" title="<?php echo e(__('Reset Filters')); ?>">
                                        <i class="ti ti-refresh"></i>
                                    </a>
                                    <a href="#" class="btn-filter-import"
                                        onclick="let form = document.getElementById('attendanceemployee_filter'); form.action = '<?php echo e(route('attendance.export')); ?>'; form.submit(); form.action = '<?php echo e(route('attendanceemployee.index')); ?>'; return false;"
                                        data-bs-toggle="tooltip" title="<?php echo e(__('Export Excel')); ?>">
                                        <i class="ti ti-file-export"></i>
                                    </a>
                                </div>
                            </div>

                        </div>

                        <?php echo e(Form::close()); ?>

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
                                        <?php if(\Auth::user()->type != 'employee'): ?>
                                            <th><?php echo e(__('Employee')); ?></th>
                                        <?php endif; ?>
                                        <th><?php echo e(__('Date')); ?></th>
                                        <th><?php echo e(__('Status')); ?></th>
                                        <th><?php echo e(__('Clock In')); ?></th>
                                        <th><?php echo e(__('Clock Out')); ?></th>
                                        <th><?php echo e(__('Clock In Location')); ?></th>
                                        <th><?php echo e(__('Clock Out Location')); ?></th>
                                        <th><?php echo e(__('Late')); ?></th>
                                        <th><?php echo e(__('Early Leaving')); ?></th>
                                        <th><?php echo e(__('Overtime')); ?></th>
                                        <?php if(Gate::check('Edit Attendance') || Gate::check('Delete Attendance')): ?>
                                            <th width="200px"><?php echo e(__('Action')); ?></th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $attendanceEmployee; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <?php if(\Auth::user()->type != 'employee'): ?>
                                                <td><?php echo e(!empty($attendance->employee) ? $attendance->employee->name : ''); ?></td>
                                            <?php endif; ?>
                                            <td><?php echo e(\Auth::user()->dateFormat($attendance->date)); ?></td>
                                            <td>
                                            <?php
                                                $isLate = !empty($attendance->late) && $attendance->late !== '00:00:00';
                                            ?>
                                            
                                            <?php switch($attendance->status):
                                                case ('Present'): ?>
                                                    <span class="badge bg-success me-1"><?php echo e($attendance->status); ?></span>
                                                    <?php if($isLate): ?>
                                                        <span class="badge bg-warning">Late</span>
                                                    <?php endif; ?>
                                                    <?php break; ?>
                                                <?php case ('Half Day'): ?>
                                                    <span class="badge bg-warning"><?php echo e($attendance->status); ?></span>
                                                    <?php break; ?>
                                                <?php case ('Early Leaving'): ?>
                                                    <span class="badge bg-info"><?php echo e($attendance->status); ?></span>
                                                    <?php break; ?>
                                                <?php case ('Single Punch In'): ?>
                                                    <span class="badge bg-secondary"><?php echo e($attendance->status); ?></span>
                                                    <?php break; ?>
                                                <?php case ('Leave'): ?>
                                                    <span class="badge bg-danger"><?php echo e($attendance->status); ?></span>
                                                    <?php break; ?>
                                                <?php default: ?>
                                                    <span class="badge bg-secondary"><?php echo e($attendance->status); ?></span>
                                            <?php endswitch; ?>
                                        </td>
                                            <td><?php echo e($attendance->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00'); ?>

                                            </td>
                                            <td><?php echo e($attendance->clock_out != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_out) : '00:00'); ?>

                                            </td>
                                            <td>
                                                <?php
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
                                                ?>
                                                
                                                <?php if(!empty($attendance->clock_in_location)): ?>
                                                    <small class="text-muted"><?php echo e($attendance->clock_in_location); ?></small><br>
                                                    <?php if(!empty($attendance->clock_in_latitude) && !empty($attendance->clock_in_longitude)): ?>
                                                        <a href="https://maps.google.com/?q=<?php echo e($attendance->clock_in_latitude); ?>,<?php echo e($attendance->clock_in_longitude); ?>" 
                                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="ti ti-map-pin"></i> View Map
                                                        </a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if(!empty($attendance->clock_out_location)): ?>
                                                    <small class="text-muted"><?php echo e($attendance->clock_out_location); ?></small><br>
                                                    <?php if(!empty($attendance->clock_out_latitude) && !empty($attendance->clock_out_longitude)): ?>
                                                        <a href="https://maps.google.com/?q=<?php echo e($attendance->clock_out_latitude); ?>,<?php echo e($attendance->clock_out_longitude); ?>" 
                                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="ti ti-map-pin"></i> View Map
                                                        </a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($attendance->late); ?></td>
                                            <td><?php echo e($attendance->early_leaving); ?></td>
                                            <td><?php echo e($attendance->overtime); ?></td>
                                            <?php if(Gate::check('Edit Attendance') || Gate::check('Delete Attendance')): ?>
                                                <td class="Action">
                                                    <span>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Edit Attendance')): ?>
                                                            <div class="action-btn bg-info ms-2">
                                                                <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                    data-size="lg"
                                                                    data-url="<?php echo e(URL::to('attendanceemployee/' . $attendance->id . '/edit')); ?>"
                                                                    data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                    title="" data-title="<?php echo e(__('Edit Attendance')); ?>"
                                                                    data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Delete Attendance')): ?>
                                                            <div class="action-btn bg-danger ms-2">
                                                                <?php echo Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['attendanceemployee.destroy', $attendance->id],
                                                                    'id' => 'delete-form-' . $attendance->id,
                                                                ]); ?>

                                                                <a href="#"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-bs-original-title="Delete" aria-label="Delete"><i
                                                                        class="ti ti-trash text-white text-white"></i></a>
                                                                </form>
                                                            </div>
                                                        <?php endif; ?>
                                                    </span>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/attendance/index.blade.php ENDPATH**/ ?>