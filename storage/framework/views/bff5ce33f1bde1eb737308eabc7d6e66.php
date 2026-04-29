<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Generate Letter')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('title'); ?>
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0"><?php echo e(__('Generate Letter')); ?></h5>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb-item'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('letter_templates.index')); ?>"><?php echo e(__('Letter Templates')); ?></a></li>
    <li class="breadcrumb-item active"><?php echo e(__('Generate Letter')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script type="text/javascript">
        $(document).ready(function() {
            // Auto-generate date field
            $('#auto_date').click(function() {
                var today = new Date();
                var formattedDate = today.toISOString().split('T')[0];
                $('#date').val(formattedDate);
            });

            // Handle form submission with AJAX
            $('#generateForm').submit(function(e) {
                e.preventDefault();
                
                var submitBtn = $(this).find('button[type="submit"]');
                var originalText = submitBtn.html();
                
                // Show loading state
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> <?php echo e(__("Generating...")); ?>');
                
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            // Open PDF in new tab for preview and download
                            window.open(response.download_url, '_blank');
                            
                            // Show success message
                            alert('<?php echo e(__("PDF generated successfully! The PDF has been opened in a new tab where you can preview and download it.")); ?>');
                        }
                    },
                    error: function(xhr) {
                        var error = xhr.responseJSON ? xhr.responseJSON.error : '<?php echo e(__("An error occurred while generating the PDF.")); ?>';
                        alert(error);
                    },
                    complete: function() {
                        // Restore button state
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('Generate Letter from Template')); ?>: <?php echo e($letterTemplate->name); ?></h6>
                        <a href="<?php echo e(route('letter_templates.index')); ?>" class="btn btn-secondary btn-sm float-right">
                            <i class="fas fa-arrow-left"></i> <?php echo e(__('Back')); ?>

                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3"><?php echo e(__('Fill in the Variables')); ?></h5>
                                
                                <form method="POST" action="<?php echo e(route('letter_templates.generatePdf', $letterTemplate->id)); ?>" id="generateForm">
                                    <?php echo csrf_field(); ?>
                                    
                                    <?php if(count($variables) > 0): ?>
                                        <?php $__currentLoopData = $variables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="form-group">
                                                <label for="<?php echo e($variable); ?>">
                                                    <?php echo e(ucwords(str_replace('_', ' ', $variable))); ?>

                                                    <?php if(in_array($variable, ['employee_name', 'department', 'date'])): ?>
                                                        <span class="text-danger">*</span>
                                                    <?php endif; ?>
                                                </label>
                                                
                                                <?php if($variable == 'date'): ?>
                                                    <div class="input-group">
                                                        <input type="date" class="form-control" id="<?php echo e($variable); ?>" name="<?php echo e($variable); ?>" <?php echo e(in_array($variable, ['employee_name', 'department', 'date']) ? 'required' : ''); ?>>
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary" id="auto_date"><?php echo e(__('Today')); ?></button>
                                                        </div>
                                                    </div>
                                                <?php elseif($variable == 'email'): ?>
                                                    <input type="email" class="form-control" id="<?php echo e($variable); ?>" name="<?php echo e($variable); ?>" <?php echo e(in_array($variable, ['employee_name', 'department', 'date']) ? 'required' : ''); ?>>
                                                <?php elseif($variable == 'phone'): ?>
                                                    <input type="tel" class="form-control" id="<?php echo e($variable); ?>" name="<?php echo e($variable); ?>" <?php echo e(in_array($variable, ['employee_name', 'department', 'date']) ? 'required' : ''); ?>>
                                                <?php elseif(in_array($variable, ['address', 'notes', 'description'])): ?>
                                                    <textarea class="form-control" id="<?php echo e($variable); ?>" name="<?php echo e($variable); ?>" rows="3" <?php echo e(in_array($variable, ['employee_name', 'department', 'date']) ? 'required' : ''); ?>></textarea>
                                                <?php else: ?>
                                                    <input type="text" class="form-control" id="<?php echo e($variable); ?>" name="<?php echo e($variable); ?>" <?php echo e(in_array($variable, ['employee_name', 'department', 'date']) ? 'required' : ''); ?>>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> <?php echo e(__('No variables found in this template. You can add variables like {employee_name}, {department}, {date} etc. in the template content.')); ?>

                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success btn-lg" <?php echo e(count($variables) == 0 ? 'disabled' : ''); ?>>
                                            <i class="fas fa-file-pdf"></i> <?php echo e(__('Generate PDF')); ?>

                                        </button>
                                        <a href="<?php echo e(route('letter_templates.index')); ?>" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> <?php echo e(__('Cancel')); ?>

                                        </a>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="col-md-6">
                                <h5 class="mb-3"><?php echo e(__('Letter Preview')); ?></h5>
                                <div class="border p-3 bg-light" style="min-height: 500px; max-height: 600px; overflow-y: auto;">
                                    <div id="preview-content">
                                        <?php echo $letterTemplate->content; ?>

                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <strong><?php echo e(__('Note')); ?>:</strong> <?php echo e(__('This is a preview of the template. Variables will be replaced with the actual values you enter on the left when you generate the PDF.')); ?>

                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-info"><?php echo e(__('Template Information')); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><?php echo e(__('Template Name')); ?>:</strong> <?php echo e($letterTemplate->name); ?></p>
                                <p><strong><?php echo e(__('Source Letter')); ?>:</strong> <?php echo e($letterTemplate->source_letter ?? '-'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><?php echo e(__('Created Date')); ?>:</strong> <?php echo e(\Carbon\Carbon::parse($letterTemplate->created_at)->format('d M Y H:i')); ?></p>
                                <p><strong><?php echo e(__('Last Updated')); ?>:</strong> <?php echo e(\Carbon\Carbon::parse($letterTemplate->updated_at)->format('d M Y H:i')); ?></p>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6><?php echo e(__('Variables Found in Template')); ?>:</h6>
                            <?php if(count($variables) > 0): ?>
                                <div class="row">
                                    <?php $__currentLoopData = $variables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-3 mb-2">
                                            <span class="badge badge-info"><code><?php echo e('{' . $variable . '}'); ?></code></span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted"><?php echo e(__('No variables found in this template.')); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/letter_templates/generate.blade.php ENDPATH**/ ?>