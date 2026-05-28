<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Comp-Off')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Manage Comp-Off')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <a href="<?php echo e(route('compoff.create')); ?>" data-ajax-popup="false" data-size="md"
        data-title="<?php echo e(__('Create New Comp-Off')); ?>" data-bs-toggle="tooltip" title="Create"
        class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i>
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mb-3 mb-sm-0">
                        <div class="d-flex align-items-center">
                            <div class="theme-avtar bg-primary" style="border-radius: 12px; padding: 12px;">
                                <i class="ti ti-list text-white" style="font-size: 24px;"></i>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted"><?php echo e(__('Total')); ?></small>
                                <h6 class="m-0 font-weight-bold"><?php echo e(__('Comp-Off Entries')); ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0 font-weight-bold text-primary"><?php echo e(count($comp_offs)); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table align-items-center" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Branch')); ?></th>
                                <th><?php echo e(__('Departments')); ?></th>
                                <th><?php echo e(__('Selected Dates')); ?></th>
                                <th><?php echo e(__('Employees Count')); ?></th>
                                <th><?php echo e(__('Created At')); ?></th>
                                <th width="200px"><?php echo e(__('Action')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $comp_offs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp_off): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $deptIds = json_decode($comp_off->department_ids, true) ?? [];
                                    $empIds = json_decode($comp_off->employee_ids, true) ?? [];
                                    $dates = json_decode($comp_off->dates, true) ?? [];
                                    
                                    $depts = \App\Models\Department::whereIn('id', $deptIds)->pluck('name')->toArray();
                                ?>
                                <tr>
                                    <td class="font-weight-bold"><?php echo e(!empty($comp_off->branch) ? $comp_off->branch->name : '-'); ?></td>
                                    <td>
                                        <?php $__currentLoopData = $depts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deptName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="badge bg-light text-dark border me-1"><?php echo e($deptName); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </td>
                                    <td>
                                        <?php $__currentLoopData = $dates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="badge bg-soft-primary text-primary me-1"><?php echo e(\Auth::user()->dateFormat($date)); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info px-3 py-2 rounded-pill font-weight-bold"><?php echo e(count($empIds)); ?> <?php echo e(__('Employees')); ?></span>
                                    </td>
                                    <td><?php echo e(\Auth::user()->dateFormat($comp_off->created_at)); ?></td>
                                    <td class="Action">
                                        <span>  
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="<?php echo e(route('compoff.show', $comp_off->id)); ?>"
                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="<?php echo e(__('Comp-Off Detail')); ?>">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>

                                            <div class="action-btn bg-info ms-2">
                                                <a href="<?php echo e(route('compoff.edit', $comp_off->id)); ?>" class="mx-3 btn btn-sm align-items-center"
                                                    data-ajax-popup="false" data-title="<?php echo e(__('Edit Comp-Off')); ?>"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="<?php echo e(__('Edit')); ?>">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>

                                            <div class="action-btn bg-danger ms-2">
                                                <?php echo Form::open(['method' => 'DELETE', 'route' => ['compoff.destroy', $comp_off->id], 'id' => 'delete-form-' . $comp_off->id]); ?>

                                                <a href="#!" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                    data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                    title="<?php echo e(__('Delete')); ?>">
                                                    <i class="ti ti-trash text-white"></i></a>
                                                <?php echo Form::close(); ?>

                                            </div>
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/compoff/index.blade.php ENDPATH**/ ?>