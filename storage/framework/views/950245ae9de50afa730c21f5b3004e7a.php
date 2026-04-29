<?php
    $setting = App\Models\Utility::settings(); // Optional if you're using settings
?>

<?php echo e(Form::open(['route' => 'categories.store', 'method' => 'post'])); ?>

<div class="modal-body">
    <div class="row">

        <!-- Category Name -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('name', __('Category Name'), ['class' => 'form-label'])); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::text('name', null, ['class' => 'form-control', 'required' => true, 'placeholder' => 'Enter Category Name'])); ?>

                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('description', __('Description'), ['class' => 'form-label'])); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Optional Description'])); ?>

                </div>
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <a href="<?php echo e(route('categories.index')); ?>" class="btn btn-light me-2"><?php echo e(__('Cancel')); ?></a>
    <button type="submit" class="btn btn-primary"><?php echo e(__('Create')); ?></button>
</div>

<?php echo e(Form::close()); ?>

<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/category/create.blade.php ENDPATH**/ ?>