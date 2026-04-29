<?php $__env->startSection('page-title'); ?>
   <?php echo e(__('Manage Leave Type')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Leave Type')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Create Branch')): ?>
        <a href="#" data-url="<?php echo e(route('leavetype.create')); ?>" data-ajax-popup="true"
            data-title="<?php echo e(__('Create New Leave Type')); ?>" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="<?php echo e(__('Create')); ?>">
            <i class="ti ti-plus"></i>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<div class="row">

        <div class="col-3">
            <?php echo $__env->make('layouts.hrm_setup', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">

                    <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Leave Type')); ?></th>
                                <th><?php echo e(__('Period')); ?></th>
                                <th><?php echo e(__('Days')); ?></th>
                                <th><?php echo e(__('Eligible Employees')); ?></th>
                                <th width="200px"><?php echo e(__('Action')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $leavetypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leavetype): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($leavetype->title); ?></td>
                                    <td><span class="badge bg-<?php echo e($leavetype->type == 'monthly' ? 'info' : 'primary'); ?>"><?php echo e(__(ucfirst($leavetype->type))); ?></span></td>
                                    <td>
                                        <?php if($leavetype->is_unlimited): ?>
                                            <span class="badge bg-success"><?php echo e(__('Unlimited')); ?></span>
                                        <?php else: ?>
                                            <?php echo e($leavetype->days); ?> 
                                            <?php if($leavetype->type == 'monthly'): ?>
                                                <small class="text-muted"><?php echo e(__('/month')); ?></small>
                                            <?php else: ?>
                                                <small class="text-muted"><?php echo e(__('/year')); ?></small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($leavetype->eligible_employee_types && count($leavetype->eligible_employee_types) > 0): ?>
                                            <?php $__currentLoopData = $leavetype->eligible_employee_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php switch($type):
                                                    case ('payroll_confirm'): ?>
                                                        <span class="badge bg-success"><?php echo e(__('Payroll - Confirm')); ?></span>
                                                        <?php break; ?>
                                                    <?php case ('payroll_not_confirm'): ?>
                                                        <span class="badge bg-secondary"><?php echo e(__('Payroll - Not Confirm')); ?></span>
                                                        <?php break; ?>
                                                    <?php case ('contract_confirm'): ?>
                                                        <span class="badge bg-info"><?php echo e(__('Contract - Confirm')); ?></span>
                                                        <?php break; ?>
                                                    <?php case ('contract_not_confirm'): ?>
                                                        <span class="badge bg-warning"><?php echo e(__('Contract - Not Confirm')); ?></span>
                                                        <?php break; ?>
                                                <?php endswitch; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php else: ?>
                                            <span class="text-muted"><?php echo e(__('All Employees')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="Action">
                                        <span>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Edit Leave Type')): ?>
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                        data-url="<?php echo e(URL::to('leavetype/' . $leavetype->id . '/edit')); ?>"
                                                        data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title=""
                                                        data-title="<?php echo e(__('Edit Leave Type')); ?>"
                                                        data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Delete Leave Type')): ?>
                                                <div class="action-btn bg-danger ms-2">
                                                    <?php echo Form::open(['method' => 'DELETE', 'route' => ['leavetype.destroy', $leavetype->id], 'id' => 'delete-form-' . $leavetype->id]); ?>

                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                        data-bs-toggle="tooltip" title="" data-bs-original-title="Delete"
                                                        aria-label="Delete"><i
                                                            class="ti ti-trash text-white "></i></a>
                                                    </form>
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/leavetype/index.blade.php ENDPATH**/ ?>