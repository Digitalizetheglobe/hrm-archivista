<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage TimeSheet')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('TimeSheet')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <a href="<?php echo e(route('timesheet.export')); ?>?<?php echo e(http_build_query(request()->query())); ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="<?php echo e(__('Export')); ?>">
        <i class="ti ti-file-export"></i>
    </a>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Create TimeSheet')): ?>
        <a href="#" data-url="<?php echo e(route('timesheet.create')); ?>" data-ajax-popup="true" data-size="xl"
            data-title="<?php echo e(__('Create New TimeSheet')); ?>" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="<?php echo e(__('Create')); ?>">
            <i class="ti ti-plus"></i>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2" id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    <?php echo e(Form::open(['route' => ['timesheet.index'], 'method' => 'get', 'id' => 'timesheet_filter'])); ?>

                    <div class="row align-items-end flex-nowrap">
                        <?php if(Auth::user()->type == 'employee'): ?>
                        <div class="col-auto">
                            <div class="card bg-primary mb-0 text-white">
                                <div class="card-body p-3">
                                    <h6 class="mb-0"><?php echo e(__('Total Hours')); ?></h6>
                                    <h3 class="mb-0"><?php echo e($totalTime ?? '0'); ?> hrs</h3>
                                    <?php if(request()->filled('start_date') || request()->filled('end_date')): ?>
                                        <small><?php echo e(\Auth::user()->dateFormat(request('start_date'))); ?> to <?php echo e(\Auth::user()->dateFormat(request('end_date'))); ?></small>
                                    <?php else: ?>
                                        <small><?php echo e(__('Today')); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-auto">
                            <div class="btn-box">
                                <?php echo e(Form::label('start_date', __('Start Date'), ['class' => 'form-label'])); ?>

                                <?php echo e(Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d'), ['class' => 'month-btn form-control current_date', 'autocomplete' => 'off'])); ?>

                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="btn-box">
                                <?php echo e(Form::label('end_date', __('End Date'), ['class' => 'form-label'])); ?>

                                <?php echo e(Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'), ['class' => 'month-btn form-control current_date', 'autocomplete' => 'off'])); ?>

                            </div>
                        </div>
                        
                        <?php if(\Auth::user()->type != 'employee'): ?>
                        <div class="col-auto">
                            <div class="btn-box">
                                <?php echo e(Form::label('employee', __('Employee'), ['class' => 'form-label'])); ?>

                                <?php echo e(Form::select('employee', $employeesList, isset($_GET['employee']) ? $_GET['employee'] : '', ['class' => 'form-control select ', 'id' => 'employee_id'])); ?>

                            </div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6">
                            <div class="btn-box">
                                <?php echo e(Form::label('client', __('Client'), ['class' => 'form-label'])); ?>

                                <?php echo e(Form::select('client', $clients, isset($_GET['client']) ? $_GET['client'] : '', ['class' => 'form-control select', 'id' => 'client_id'])); ?>

                            </div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6">
                            <div class="btn-box">
                                <?php echo e(Form::label('project', __('Project'), ['class' => 'form-label'])); ?>

                                <?php echo e(Form::select('project', $projects, isset($_GET['project']) ? $_GET['project'] : '', ['class' => 'form-control select', 'id' => 'project_id'])); ?>

                            </div>
                        </div>
                        <?php else: ?>
                            <?php echo Form::hidden('employee', !empty($employeesList) ? $employeesList->id : 0, ['id' => 'employee_id']); ?>

                        <?php endif; ?>
                        
                        <div class="col-auto">
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="document.getElementById('timesheet_filter').submit(); return false;"
                                data-bs-toggle="tooltip" title="" data-bs-original-title="apply">
                                <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                            </a>
                            <a href="<?php echo e(route('timesheet.index')); ?>" class="btn btn-sm btn-danger"
                                data-bs-toggle="tooltip" title="" data-bs-original-title="Reset">
                                <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                            </a>
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
            <div class="card-body py-0">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <?php if(\Auth::user()->type != 'employee'): ?>
                                    <th><?php echo e(__('Employee')); ?></th>
                                <?php endif; ?>
                                <th><?php echo e(__('Date')); ?></th>
                                <th><?php echo e(__('Total Time')); ?></th>
                                <?php if(\Auth::user()->type != 'employee'): ?>
                                    <th><?php echo e(__('Client')); ?></th>
                                    <th><?php echo e(__('Project')); ?></th>
                                    <th><?php echo e(__('Expense')); ?></th>
                                    <th><?php echo e(__('Location')); ?></th>
                                    <th><?php echo e(__('Narration')); ?></th>
                                    <th><?php echo e(__('Billable')); ?></th>
                                <?php else: ?>
                                    <th><?php echo e(__('Client Name')); ?></th>
                                    <th><?php echo e(__('Project Name')); ?></th>
                                    <th><?php echo e(__('Total Time')); ?></th>
                                    <th><?php echo e(__('Billable')); ?></th>
                                <?php endif; ?>
                                <th width="200px"><?php echo e(__('Action')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $timeSheets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $timeSheet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <?php if(\Auth::user()->type != 'employee'): ?>
                                        <td><?php echo e(!empty($timeSheet->employee) ? $timeSheet->employee->name : ''); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo e(\Auth::user()->dateFormat($timeSheet->date)); ?></td>
                                    <td><?php echo e($timeSheet->total_time); ?> hrs</td>
                                    
                                    <?php if(\Auth::user()->type != 'employee'): ?>
                                        <td><?php echo e($timeSheet->client->client_name ?? ''); ?></td>
                                        <td><?php echo e($timeSheet->project->project_name ?? ''); ?></td>
                                        <td><?php echo e($timeSheet->expense); ?></td>
                                        <td><?php echo e($timeSheet->location); ?></td>
                                        <td><?php echo e($timeSheet->narration); ?></td>
                                        <td><?php echo e($timeSheet->billable); ?></td>
                                    <?php else: ?>
                                        <td><?php echo e($timeSheet->client->client_name ?? ''); ?></td>
                                        <td><?php echo e($timeSheet->project->project_name ?? ''); ?></td>
                                        <td><?php echo e($timeSheet->total_time); ?></td>
                                        <td><?php echo e($timeSheet->billable); ?></td>
                                    <?php endif; ?>
                                    
                                    <td class="Action">
                                        <span>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Edit TimeSheet')): ?>
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                        data-url="<?php echo e(route('timesheet.edit', $timeSheet->id)); ?>"
                                                        data-ajax-popup="true" data-size="xl" data-bs-toggle="tooltip"
                                                        title="<?php echo e(__('Edit TimeSheet')); ?>">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Delete TimeSheet')): ?>
                                                <div class="action-btn bg-danger ms-2">
                                                    <?php echo Form::open([
                                                        'method' => 'DELETE',
                                                        'route' => ['timesheet.destroy', $timeSheet->id],
                                                        'id' => 'delete-form-' . $timeSheet->id,
                                                    ]); ?>

                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    <?php echo Form::close(); ?>

                                                </div>
                                            <?php endif; ?>
                                        </span>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
<script>
    $(document).ready(function() {
        // Set default dates to today if not already set
        if (!$('[name="start_date"]').val()) {
            $('[name="start_date"]').val(new Date().toISOString().split('T')[0]);
        }
        if (!$('[name="end_date"]').val()) {
            $('[name="end_date"]').val(new Date().toISOString().split('T')[0]);
        }

        // Auto-submit form when date range changes
        $('[name="start_date"], [name="end_date"]').on('change', function() {
            $('#timesheet_filter').submit();
        });

        // Load projects when client changes
        $('#client_id').on('change', function() {
            var clientId = $(this).val();
            if(clientId) {
                $.ajax({
            url: `/get-client-projects/${clientId}`,
                    type: 'GET',
                    data: {client_id: clientId},
                    success: function(data) {
                        $('#project_id').empty();
                        $('#project_id').append('<option value="All">All</option>');
                        $.each(data, function(key, value) {
                            $('#project_id').append('<option value="'+key+'">'+value+'</option>');
                        });
                    }
                });
            } else {
                $('#project_id').empty();
                $('#project_id').append('<option value="All">All</option>');
            }
        });

        // Submit form on filter change (for non-employee users)
        <?php if(\Auth::user()->type != 'employee'): ?>
            $('#employee_id, #client_id, #project_id').on('change', function() {
                $('#timesheet_filter').submit();
            });
        <?php endif; ?>
    });
</script>
<?php $__env->stopPush(); ?>    
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/timeSheet/index.blade.php ENDPATH**/ ?>