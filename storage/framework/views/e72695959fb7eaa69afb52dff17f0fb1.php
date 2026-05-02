
<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Job Allocation')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Manage Job Allocation')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
        <a href="<?php echo e(route('joballocation.create')); ?>" data-ajax-popup="false" data-size="md"
            data-title="<?php echo e(__('Create New Job Allocation')); ?>" data-bs-toggle="tooltip" title="Create"
            class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mb-3 mb-sm-0">
                        <div class="d-flex align-items-center">
                            <div class="theme-avtar bg-primary">
                                <i class="ti ti-list"></i>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted"><?php echo e(__('Total')); ?></small>
                                <h6 class="m-0"><?php echo e(__('Allocations')); ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0"><?php echo e($data['total']); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mb-3 mb-sm-0">
                        <div class="d-flex align-items-center">
                            <div class="theme-avtar bg-info">
                                <i class="ti ti-clock"></i>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted"><?php echo e(__('Ongoing')); ?></small>
                                <h6 class="m-0"><?php echo e(__('Allocations')); ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0"><?php echo e($data['Ongoing']); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mb-3 mb-sm-0">
                        <div class="d-flex align-items-center">
                            <div class="theme-avtar bg-success">
                                <i class="ti ti-check"></i>
                            </div>
                            <div class="ms-3">
                                <small class="text-muted"><?php echo e(__('Completed')); ?></small>
                                <h6 class="m-0"><?php echo e(__('Allocations')); ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto text-end">
                        <h4 class="m-0"><?php echo e($data['Completed']); ?></h4>
                    </div>
                </div>
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
                                <th><?php echo e(__('Client')); ?></th>
                                <th><?php echo e(__('Project')); ?></th>
                                <th><?php echo e(__('Start Date')); ?></th>
                                <th><?php echo e(__('End Date')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <th width="200px"><?php echo e(__('Action')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $allocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $allocation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e(!empty($allocation->client) ? $allocation->client->client_name : '-'); ?></td>
                                    <td><?php echo e(!empty($allocation->project) ? $allocation->project->project_name : '-'); ?></td>
                                    <td><?php echo e(\Auth::user()->dateFormat($allocation->start_date)); ?></td>
                                    <td><?php echo e(\Auth::user()->dateFormat($allocation->end_date)); ?></td>
                                    <td>
                                        <?php if($allocation->status == 'Approved'): ?>
                                            <span class="badge bg-success p-2 px-3 rounded"><?php echo e($allocation->status); ?></span>
                                        <?php elseif($allocation->status == 'Rejected'): ?>
                                            <span class="badge bg-danger p-2 px-3 rounded"><?php echo e($allocation->status); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning p-2 px-3 rounded"><?php echo e($allocation->status); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="Action">
                                        <span>  
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="<?php echo e(route('joballocation.show', $allocation->id)); ?>"
                                                    class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="<?php echo e(__('Job Allocation Detail')); ?>">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>

                                                <div class="action-btn bg-info ms-2">
                                                    <a href="<?php echo e(route('joballocation.edit', $allocation->id)); ?>" class="mx-3 btn btn-sm  align-items-center"
                                                        data-ajax-popup="false" data-title="<?php echo e(__('Edit Job Allocation')); ?>"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="<?php echo e(__('Edit')); ?>">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>

                                                <div class="action-btn bg-danger ms-2">
                                                    <?php echo Form::open(['method' => 'DELETE', 'route' => ['joballocation.destroy', $allocation->id], 'id' => 'delete-form-' . $allocation->id]); ?>

                                                    <a href="#!" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
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
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/joballocation/index.blade.php ENDPATH**/ ?>