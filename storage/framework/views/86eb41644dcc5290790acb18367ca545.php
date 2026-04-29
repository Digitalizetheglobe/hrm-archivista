
<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Add Sub-Category')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Employee')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <a href="#" data-url="<?php echo e(route('subcategories.create')); ?>" data-ajax-popup="true"
        data-title="<?php echo e(__('Create New Category')); ?>" data-size="lg" data-bs-toggle="tooltip" title="Create"
        class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i>
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Category Title')); ?></th>
                                <th><?php echo e(__('Sub-Category Title')); ?></th>
                                <th><?php echo e(__('Sub-Category Description')); ?></th>
                                <th width="130px"><?php echo e(__('Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($subcategory->category->name ?? '-'); ?></td>
                                    <td><?php echo e($subcategory->name); ?></td>
                                    <td><?php echo e($subcategory->description); ?></td>
                                    <td>
                                        <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                data-url="<?php echo e(route('subcategories.edit', $subcategory->id)); ?>"
                                                data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
                                                data-title="<?php echo e(__('Edit Category')); ?>" data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                        <div class="action-btn bg-danger ms-2">
                                            <?php echo Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['subcategories.destroy', $subcategory->id],
                                            ]); ?>

                                            <a href="#"
                                                class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                data-bs-toggle="tooltip" title="<?php echo e(__('Delete Task')); ?>"
                                                data-bs-original-title="<?php echo e(__('Delete Category')); ?>" aria-label="<?php echo e(__('Delete')); ?>"
                                                onclick="event.preventDefault(); document.getElementById('delete-form-<?php echo e($subcategory->id); ?>').submit();">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                            <?php echo Form::close(); ?>

                                        </div>
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/subcategory/index.blade.php ENDPATH**/ ?>