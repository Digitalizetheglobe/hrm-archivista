<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Letter Templates')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Letter Templates')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <a href="<?php echo e(route('letter_templates.generated.index')); ?>" 
        class="btn btn-sm btn-info flex items-center space-x-2 mr-2">
        <i class="ti ti-file-text"></i>
        <span><?php echo e(__('Generated Letters')); ?></span>
    </a>
    <a href="<?php echo e(route('letter_templates.create')); ?>" 
        data-title="<?php echo e(__('Create Letter Template')); ?>" 
        class="btn btn-sm btn-primary flex items-center space-x-2">
        <i class="ti ti-plus"></i>
        <span><?php echo e(__('Create')); ?></span>
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5><?php echo e(__('Letter Templates')); ?></h5>
                    <br>
                    <div class="table-responsive">
                        <?php if($letterTemplates->count() > 0): ?>
                            <table class="table" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('Template Name')); ?></th>
                                        <th><?php echo e(__('Source Letter')); ?></th>
                                        <th><?php echo e(__('Created Date')); ?></th>
                                        <th class="text-right"><?php echo e(__('Action')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $letterTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($template->name); ?></td>
                                            <td><?php echo e($template->source_letter ?? '-'); ?></td>
                                            <td><?php echo e(\Carbon\Carbon::parse($template->created_at)->format('d M Y')); ?></td>
                                            <td class="text-right">
                                                <?php if(\Auth::user()->type != 'employee'): ?>
                                                    <div class="action-btn bg-primary">
                                                        <a href="<?php echo e(route('letter_templates.edit', $template->id)); ?>" 
                                                            class="mx-3 btn btn-sm align-items-center text-white"
                                                            data-size="lg"
                                                            data-url="<?php echo e(route('letter_templates.edit', $template->id)); ?>"
                                                            data-ajax-popup="true" 
                                                            data-size="md" 
                                                            data-bs-toggle="tooltip"
                                                            title="" 
                                                            data-title="<?php echo e(__('Edit Template')); ?>"
                                                            data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                    
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="<?php echo e(route('letter_templates.generate', $template->id)); ?>" 
                                                            class="mx-3 btn btn-sm align-items-center text-white"
                                                            data-size="lg"
                                                            data-url="<?php echo e(route('letter_templates.generate', $template->id)); ?>"
                                                            data-ajax-popup="true" 
                                                            data-size="md" 
                                                            data-bs-toggle="tooltip"
                                                            title="" 
                                                            data-title="<?php echo e(__('Generate Letter')); ?>"
                                                            data-bs-original-title="<?php echo e(__('Generate')); ?>">
                                                            <i class="ti ti-file text-white"></i>
                                                        </a>
                                                    </div>
                                                    
                                                    <div class="action-btn bg-danger ms-2">
                                                        <a href="#" 
                                                            class="mx-3 btn btn-sm align-items-center text-white"
                                                            data-size="lg"
                                                            data-url="<?php echo e(route('letter_templates.destroy', $template->id)); ?>"
                                                            data-ajax-popup="true" 
                                                            data-size="md" 
                                                            data-bs-toggle="tooltip"
                                                            title="" 
                                                            data-title="<?php echo e(__('Delete Template')); ?>"
                                                            data-bs-original-title="<?php echo e(__('Delete')); ?>"
                                                            onclick="if(confirm('<?php echo e(__('Are you sure to delete this template?')); ?>')) { 
                                                                var form = document.createElement('form');
                                                                form.method = 'POST';
                                                                form.action = '<?php echo e(route('letter_templates.destroy', $template->id)); ?>';
                                                                var csrf = document.createElement('input');
                                                                csrf.type = 'hidden';
                                                                csrf.name = '_token';
                                                                csrf.value = '<?php echo e(csrf_token()); ?>';
                                                                form.appendChild(csrf);
                                                                var method = document.createElement('input');
                                                                method.type = 'hidden';
                                                                method.name = '_method';
                                                                method.value = 'DELETE';
                                                                form.appendChild(method);
                                                                document.body.appendChild(form);
                                                                form.submit();
                                                            }">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="ti ti-file text-gray-300 fa-3x mb-3"></i>
                                <h5 class="text-gray-400"><?php echo e(__('No letter templates found.')); ?></h5>
                                <p class="text-gray-400"><?php echo e(__('Create your first letter template to get started.')); ?></p>
                                <a href="<?php echo e(route('letter_templates.create')); ?>" class="btn btn-primary">
                                    <i class="ti ti-plus"></i> <?php echo e(__('Create Letter Template')); ?>

                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/letter_templates/index.blade.php ENDPATH**/ ?>