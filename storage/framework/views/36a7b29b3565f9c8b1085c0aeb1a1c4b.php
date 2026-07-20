

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Attendance Regularisation')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Attendance Regularisation')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <?php if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee' || (!request('own') && Gate::check('attendance.regularisation.create.all')) || (request('own') && Gate::check('attendance.regularisation.create.own'))): ?>
        <a href="#" data-url="<?php echo e(route('attendance-regularisation.create', request('own') ? ['own' => 1] : [])); ?>" data-ajax-popup="true"
            data-title="<?php echo e(__('Create Attendance Regularisation')); ?>" data-size="lg"
            class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-border-style">
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <?php if(\Auth::user()->type != 'employee'): ?>
                                        <th class="text-start"><?php echo e(__('Employee Name')); ?></th>
                                    <?php endif; ?>
                                    <th class="text-start"><?php echo e(__('Date')); ?></th>
                                    <th class="text-start"><?php echo e(__('In Time')); ?></th>
                                    <th class="text-start"><?php echo e(__('Out Time')); ?></th>
                                    <th class="text-start"><?php echo e(__('Reason')); ?></th>
                                    <th class="text-start"><?php echo e(__('Remark')); ?></th>
                                    <th class="text-start"><?php echo e(__('Status')); ?></th>
                                    <th class="text-center" width="200px"><?php echo e(__('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $regularisations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $regularisation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <?php if(\Auth::user()->type != 'employee'): ?>
                                            <td class="text-start"><?php echo e(!empty($regularisation->employee) ? $regularisation->employee->name : __('N/A')); ?></td>
                                        <?php endif; ?>
                                        <td class="text-start"><?php echo e(\Auth::user()->dateFormat($regularisation->missed_attendance_date)); ?></td>
                                        <td class="text-start"><?php echo e(\Auth::user()->timeFormat($regularisation->punch_in_time)); ?></td>
                                        <td class="text-start"><?php echo e(\Auth::user()->timeFormat($regularisation->punch_out_time)); ?></td>
                                        <td class="text-start"><?php echo e($regularisation->reason); ?></td>
                                        <td class="text-start"><?php echo e(\Illuminate\Support\Str::limit($regularisation->remark, 30)); ?></td>
                                        <td class="text-start">
                                            <?php if($regularisation->status == 'Pending'): ?>
                                                <span class="badge bg-warning"><?php echo e(__('Pending')); ?></span>
                                            <?php elseif($regularisation->status == 'Approved'): ?>
                                                <span class="badge bg-success"><?php echo e(__('Approved')); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><?php echo e(__('Rejected')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="Action" style="vertical-align: middle;">
                                            <span class="d-flex justify-content-end align-items-center gap-1">
                                                
                                                
                                                <div class="action-btn bg-info">
                                                    <a href="#" class="btn btn-sm"
                                                        data-url="<?php echo e(route('attendance-regularisation.show', $regularisation->id)); ?>"
                                                        data-ajax-popup="true" data-size="lg" data-title="<?php echo e(__('View Details')); ?>">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>

                                                <?php if($regularisation->status == 'Pending'): ?>
                                                    
                                                    <?php if(\Auth::user()->type == 'company' || Gate::check('attendance.regularisation.action.all')): ?>
                                                        <div class="action-btn bg-success">
                                                            <a href="#" class="btn btn-sm"
                                                                data-url="<?php echo e(route('attendance-regularisation.action', $regularisation->id)); ?>"
                                                                data-ajax-popup="true" data-size="lg" data-title="<?php echo e(__('Attendance Regularisation Action')); ?>">
                                                                <i class="ti ti-caret-right text-white"></i>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>

                                                    
                                                    <?php if(\Auth::user()->type == 'company' || Gate::check('attendance.regularisation.edit.all') || (Gate::check('attendance.regularisation.edit.own') && $regularisation->employee_id == (\Auth::user()->employee ? \Auth::user()->employee->id : 0))): ?>
                                                        <div class="action-btn bg-info">
                                                            <a href="#" class="btn btn-sm"
                                                                data-url="<?php echo e(route('attendance-regularisation.edit', $regularisation->id)); ?>"
                                                                data-ajax-popup="true" data-size="lg" data-title="<?php echo e(__('Edit Regularisation')); ?>">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                
                                                <?php if(\Auth::user()->type == 'company' || Gate::check('attendance.regularisation.delete.all') || (Gate::check('attendance.regularisation.delete.own') && $regularisation->employee_id == (\Auth::user()->employee ? \Auth::user()->employee->id : 0))): ?>
                                                    <div class="action-btn bg-danger">
                                                        <?php echo Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['attendance-regularisation.destroy', $regularisation->id],
                                                            'id' => 'delete-form-' . $regularisation->id,
                                                        ]); ?>

                                                        <a href="#"
                                                            class="btn btn-sm bs-pass-para" aria-label="Delete"
                                                            data-confirm="<?php echo e(__('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?')); ?>"
                                                            data-confirm-yes="document.getElementById('delete-form-<?php echo e($regularisation->id); ?>').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        <?php echo Form::close(); ?>

                                                    </div>
                                                <?php endif; ?>

                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="<?php echo e(\Auth::user()->type != 'employee' ? '8' : '7'); ?>" class="text-center">
                                            <?php echo e(__('No attendance regularisation requests found.')); ?>

                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <style>
        .table th {
            white-space: nowrap !important;
            text-align: left !important;
            vertical-align: middle !important;
            padding: 0.75rem !important;
            position: relative;
        }
        
        .table td {
            vertical-align: middle !important;
        }

        /* Ensure proper column width alignment */
        #pc-dt-simple th {
            min-width: 120px;
        }
        
        @media (max-width: 768px) {
            #pc-dt-simple th {
                min-width: 140px !important;
            }
        }
    </style>
    

<?php $__env->stopPush(); ?>



<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/attendance/regularisation/index.blade.php ENDPATH**/ ?>