<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Generated Letters')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('letter_templates.index')); ?>"><?php echo e(__('Letter Templates')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Generated Letters')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <a href="<?php echo e(route('letter_templates.index')); ?>" 
        class="btn btn-sm btn-secondary flex items-center space-x-2 mr-2">
        <i class="ti ti-arrow-left"></i>
        <span><?php echo e(__('Back to Templates')); ?></span>
    </a>
    <a href="<?php echo e(route('letter_templates.create')); ?>" 
        data-title="<?php echo e(__('Create Letter Template')); ?>" 
        class="btn btn-sm btn-primary flex items-center space-x-2">
        <i class="ti ti-plus"></i>
        <span><?php echo e(__('Generate Letter')); ?></span>
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5><?php echo e(__('Generated Letters')); ?></h5>
                    <br>
                    
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <form method="GET" action="<?php echo e(route('letter_templates.generated.index')); ?>">
                                <div class="form-group">
                                    <select name="letter_template_id" id="letter_template_id" class="form-control" onchange="this.form.submit()">
                                        <option value=""><?php echo e(__('All Templates')); ?></option>
                                        <?php $__currentLoopData = $letterTemplatesWithGenerated; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($id); ?>" <?php echo e(request('letter_template_id') == $id ? 'selected' : ''); ?>>
                                                <?php echo e($name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <?php if($generatedLetters->count() > 0): ?>
                            <table class="table" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('ID')); ?></th>
                                        <th><?php echo e(__('Letter Template')); ?></th>
                                        <th><?php echo e(__('Recipient Name')); ?></th>
                                        <th><?php echo e(__('Letter Date')); ?></th>
                                        <th><?php echo e(__('Generated Date')); ?></th>
                                        <th class="text-right"><?php echo e(__('Action')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $generatedLetters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $letter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($letter->id); ?></td>
                                            <td>
                                                <strong><?php echo e($letter->letterTemplate->name); ?></strong>
                                                <?php if($letter->letterTemplate->source_letter): ?>
                                                    <br><small class="text-muted"><?php echo e(__('Source')); ?>: <?php echo e($letter->letterTemplate->source_letter); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($letter->recipient_name); ?></td>
                                            <td><?php echo e(\Carbon\Carbon::parse($letter->letter_date)->format('d M Y')); ?></td>
                                            <td><?php echo e(\Carbon\Carbon::parse($letter->created_at)->format('d M Y H:i')); ?></td>
                                            <td class="text-right">
                                                <?php if(\Auth::user()->type != 'employee'): ?>
                                                    <div class="action-btn bg-info">
                                                        <a href="<?php echo e(route('letter_templates.generated.view', $letter->id)); ?>" 
                                                            class="mx-3 btn btn-sm align-items-center text-white"
                                                            target="_blank"
                                                            data-size="lg"
                                                            data-url="<?php echo e(route('letter_templates.generated.view', $letter->id)); ?>"
                                                            data-ajax-popup="true" 
                                                            data-size="md" 
                                                            data-bs-toggle="tooltip"
                                                            title="" 
                                                            data-title="<?php echo e(__('View Letter')); ?>"
                                                            data-bs-original-title="<?php echo e(__('View')); ?>">
                                                            <i class="ti ti-eye text-white"></i>
                                                        </a>
                                                    </div>
                                                    
                                                    <div class="action-btn bg-danger ms-2">
                                                        <a href="#" 
                                                            class="mx-3 btn btn-sm align-items-center text-white"
                                                            data-size="lg"
                                                            data-url="<?php echo e(route('letter_templates.generated.delete', $letter->id)); ?>"
                                                            data-ajax-popup="true" 
                                                            data-size="md" 
                                                            data-bs-toggle="tooltip"
                                                            title="" 
                                                            data-title="<?php echo e(__('Delete Letter')); ?>"
                                                            data-bs-original-title="<?php echo e(__('Delete')); ?>"
                                                            onclick="if(confirm('<?php echo e(__('Are you sure to delete this generated letter?')); ?>')) { 
                                                                var form = document.createElement('form');
                                                                form.method = 'POST';
                                                                form.action = '<?php echo e(route('letter_templates.generated.delete', $letter->id)); ?>';
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
                                <i class="ti ti-file-text text-gray-300 fa-3x mb-3"></i>
                                <h5 class="text-gray-400"><?php echo e(__('No generated letters found.')); ?></h5>
                                <p class="text-gray-400"><?php echo e(__('Generate letters from templates to see them here.')); ?></p>
                                <a href="<?php echo e(route('letter_templates.index')); ?>" class="btn btn-primary">
                                    <i class="ti ti-plus"></i> <?php echo e(__('Generate Letter')); ?>

                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/letter_templates/generated_index.blade.php ENDPATH**/ ?>