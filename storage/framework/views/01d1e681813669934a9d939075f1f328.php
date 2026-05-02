<?php echo e(Form::open(['url' => route('projects.process-import'), 'method' => 'post', 'enctype' => 'multipart/form-data'])); ?>

<div class="modal-body">
    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <p><?php echo e(__('Your Excel file should have the following columns:')); ?></p>
                <ul>
                    <li><strong>project_name</strong> (required)</li>
                    <li><strong>client_id</strong> (required, must exist in clients table)</li>
                </ul>
                <p class="mt-2"><strong>Note:</strong> Rows with missing or invalid data will be skipped.</p>
            </div>
        </div>

        <div class="col-12">
            <div class="form-group">
                <?php echo e(Form::label('file', __('Select Excel File'), ['class' => 'form-label'])); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::file('file', [
                        'class' => 'form-control',
                        'required' => 'required',
                        'accept' => '.xlsx,.xls,.csv'
                    ])); ?>

                </div>
                <small class="text-muted"><?php echo e(__('Supported formats: .xlsx, .xls, .csv')); ?></small>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="<?php echo e(__('Cancel')); ?>" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Import')); ?>" class="btn btn-primary">
</div>
<?php echo e(Form::close()); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/projects/import.blade.php ENDPATH**/ ?>