

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Suppliers List')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Suppliers')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <th>Name</th>
                                <th>Company Website</th>
                                <th>Plan Location</th>
                                <th>Category</th>
                                <th>Sub Category</th>
                                <th>Rate in Pure £</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($vendor->name); ?></td>
                                    <td>
                                        <?php if($vendor->company_website): ?>
                                            <a href="<?php echo e($vendor->company_website); ?>" target="_blank"><?php echo e($vendor->company_website); ?></a>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>                                    <td><?php echo e($vendor->plan_location); ?></td>
                                    <td><?php echo e($vendor->category ? $vendor->category->name : '-'); ?></td>
                                    <td><?php echo e($vendor->subCategory ? $vendor->subCategory->name : '-'); ?></td>
                                    <td><?php echo e($vendor->rate_in_pure); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('vendor.supplier.show', $vendor->id)); ?>" class="btn btn-sm btn-primary">Show</a>
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/vendors/supplier.blade.php ENDPATH**/ ?>