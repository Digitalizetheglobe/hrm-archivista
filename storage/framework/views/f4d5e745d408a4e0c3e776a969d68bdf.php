<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Site Visit')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Site Visit')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <div class="float-end">
        <?php if(Auth::user()->type == 'employee' || \Auth::user()->can('Create Attendance')): ?>
            <a href="#" data-url="<?php echo e(route('site-visit.create')); ?>" data-ajax-popup="true" data-title="<?php echo e(__('Create Site Visit')); ?>" data-bs-toggle="tooltip" title="<?php echo e(__('Create')); ?>" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <?php if(Auth::user()->type != 'employee'): ?>
                                        <th><?php echo e(__('Employee')); ?></th>
                                    <?php endif; ?>
                                    <th><?php echo e(__('Date')); ?></th>
                                    <th><?php echo e(__('Location')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                    <th width="200px"><?php echo e(__('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $siteVisits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $siteVisit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <?php if(Auth::user()->type != 'employee'): ?>
                                            <td><?php echo e(!empty($siteVisit->employee) ? $siteVisit->employee->name : ''); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo e(Auth::user()->dateFormat($siteVisit->date)); ?></td>
                                        <td><?php echo e($siteVisit->location); ?></td>
                                        <td>
                                            <?php if($siteVisit->status == 'Pending'): ?>
                                                <div class="status_badge badge bg-warning p-2 px-3 rounded"><?php echo e(__($siteVisit->status)); ?></div>
                                            <?php elseif($siteVisit->status == 'Approved'): ?>
                                                <div class="status_badge badge bg-success p-2 px-3 rounded"><?php echo e(__($siteVisit->status)); ?></div>
                                            <?php else: ?>
                                                <div class="status_badge badge bg-danger p-2 px-3 rounded"><?php echo e(__($siteVisit->status)); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" data-url="<?php echo e(route('site-visit.show', $siteVisit->id)); ?>" data-ajax-popup="true" data-title="<?php echo e(__('View Site Visit')); ?>" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="<?php echo e(__('View')); ?>">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                                <?php if(Auth::user()->type != 'employee' && $siteVisit->status == 'Pending'): ?>
                                                    <div class="action-btn bg-success ms-2">
                                                        <?php echo Form::open(['method' => 'POST', 'route' => ['site-visit.approve', $siteVisit->id], 'id' => 'approve-form-' . $siteVisit->id]); ?>

                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Approve')); ?>" onclick="document.getElementById('approve-form-<?php echo e($siteVisit->id); ?>').submit();">
                                                                <i class="ti ti-check text-white"></i>
                                                            </a>
                                                        <?php echo Form::close(); ?>

                                                    </div>
                                                    <div class="action-btn bg-danger ms-2">
                                                        <?php echo Form::open(['method' => 'POST', 'route' => ['site-visit.reject', $siteVisit->id], 'id' => 'reject-form-' . $siteVisit->id]); ?>

                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Reject')); ?>" onclick="document.getElementById('reject-form-<?php echo e($siteVisit->id); ?>').submit();">
                                                                <i class="ti ti-x text-white"></i>
                                                            </a>
                                                        <?php echo Form::close(); ?>

                                                    </div>
                                                <?php endif; ?>
                                                <div class="action-btn bg-danger ms-2">
                                                    <?php echo Form::open(['method' => 'DELETE', 'route' => ['site-visit.destroy', $siteVisit->id], 'id' => 'delete-form-' . $siteVisit->id]); ?>

                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>" data-confirm="<?php echo e(__('Are You Sure?') . '|' . __('This action cannot be undone. Do you want to continue?')); ?>" data-confirm-yes="document.getElementById('delete-form-<?php echo e($siteVisit->id); ?>').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/site_visit/index.blade.php ENDPATH**/ ?>