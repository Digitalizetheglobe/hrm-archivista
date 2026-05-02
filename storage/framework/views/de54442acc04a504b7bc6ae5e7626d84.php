<?php echo e(Form::model($project, ['route' => ['projects.update', $project->id], 'method' => 'PUT'])); ?>

<div class="modal-body">
    <div class="row">
        <!-- Project Name Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('project_name', __('Project Name'), ['class' => 'form-label'])); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::text('project_name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Project Name')])); ?>

                </div>
            </div>
        </div>

        <!-- Client Name Dropdown -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                <?php echo e(Form::label('client_id', __('Client'), ['class' => 'form-label'])); ?>

                <div class="form-icon-user">
                    <select class="form-control" name="client_id" required>
                        <option value=""><?php echo e(__('Select Client')); ?></option>
                        <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $client_name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                         <option value="<?php echo e($id); ?>" <?php echo e($project->client_id == $id ? 'selected' : ''); ?>><?php echo e($client_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Update')); ?>" class="btn btn-primary">
</div>
<?php echo e(Form::close()); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/projects/edit.blade.php ENDPATH**/ ?>