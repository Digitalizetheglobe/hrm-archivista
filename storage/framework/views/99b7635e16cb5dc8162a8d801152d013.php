<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Create Letter Template')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('title'); ?>
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0"><?php echo e(__('Create Letter Template')); ?></h5>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb-item'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('letter_templates.index')); ?>"><?php echo e(__('Letter Templates')); ?></a></li>
    <li class="breadcrumb-item active"><?php echo e(__('Create')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <!-- Include Summernote CSS and JS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize Summernote
            $('#content').summernote({
                height: 400,
                toolbar: [
                    ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['para', ['ul', 'ol', 'paragraph', 'height']],
                    ['insert', ['link', 'picture', 'video', 'table', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });

            // Load letter content when dropdown changes
            $('#source_letter').change(function() {
                var letterName = $(this).val();
                if (letterName) {
                    $.ajax({
                        url: '<?php echo e(route("letter_templates.loadContent")); ?>',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            letter_name: letterName
                        },
                        success: function(response) {
                            $('#content').summernote('code', response.content);
                        },
                        error: function(xhr) {
                            alert('Error loading letter content: ' + xhr.responseJSON.error);
                        }
                    });
                }
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
                        <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('Create Letter Template')); ?></h6>
                        <a href="<?php echo e(route('letter_templates.index')); ?>" class="btn btn-secondary btn-sm float-right">
                            <i class="fas fa-arrow-left"></i> <?php echo e(__('Back')); ?>

                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo e(route('letter_templates.store')); ?>">
                            <?php echo csrf_field(); ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name"><?php echo e(__('Template Name')); ?> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo e(old('name')); ?>" required>
                                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="text-danger"><?php echo e($message); ?></span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="source_letter"><?php echo e(__('Select Letter Template (Optional)')); ?></label>
                                        <select class="form-control" id="source_letter" name="source_letter">
                                            <option value=""><?php echo e(__('Select a letter to load content...')); ?></option>
                                            <?php $__currentLoopData = $letters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $letter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($letter); ?>"><?php echo e($letter); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <small class="text-muted"><?php echo e(__('Select a letter to load its content as a starting point')); ?></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="content"><?php echo e(__('Template Content')); ?> <span class="text-danger">*</span></label>
                                <textarea id="content" name="content" class="form-control" required><?php echo e(old('content')); ?></textarea>
                                <small class="text-muted">
                                    <?php echo e(__('You can use variables like {employee_name}, {department}, {date}, etc. These will be replaced when generating letters.')); ?>

                                </small>
                                <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="form-group">
                                <div class="float-right">
                                    <a href="<?php echo e(route('letter_templates.index')); ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> <?php echo e(__('Cancel')); ?>

                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> <?php echo e(__('Save Template')); ?>

                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-info"><?php echo e(__('Available Variables')); ?></h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted"><?php echo e(__('You can use the following variables in your template. They will be replaced with actual values when generating letters:')); ?></p>
                        <div class="row">
                            <div class="col-md-4">
                                <ul class="list-unstyled">
                                    <li><code>{employee_name}</code> - <?php echo e(__('Employee Name')); ?></li>
                                    <li><code>{department}</code> - <?php echo e(__('Department')); ?></li>
                                    <li><code>{designation}</code> - <?php echo e(__('Designation')); ?></li>
                                    <li><code>{date}</code> - <?php echo e(__('Current Date')); ?></li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <ul class="list-unstyled">
                                    <li><code>{company_name}</code> - <?php echo e(__('Company Name')); ?></li>
                                    <li><code>{address}</code> - <?php echo e(__('Address')); ?></li>
                                    <li><code>{phone}</code> - <?php echo e(__('Phone Number')); ?></li>
                                    <li><code>{email}</code> - <?php echo e(__('Email Address')); ?></li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <ul class="list-unstyled">
                                    <li><code>{salary}</code> - <?php echo e(__('Salary')); ?></li>
                                    <li><code>{join_date}</code> - <?php echo e(__('Joining Date')); ?></li>
                                    <li><code>{reference}</code> - <?php echo e(__('Reference Number')); ?></li>
                                    <li><code>{custom_field}</code> - <?php echo e(__('Any custom field')); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/letter_templates/create.blade.php ENDPATH**/ ?>