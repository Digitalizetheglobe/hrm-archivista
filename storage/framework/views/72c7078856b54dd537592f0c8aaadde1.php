<?php echo e(Form::open(['route' => ['employee-leave-allocations.update', $employee->id], 'method' => 'post'])); ?>

<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <p class="text-muted">
                <?php echo e(__('Enter a number to ADD extra leaves to this employee\'s current balance. These extra leaves will carry forward according to your settings. Leave blank if no extra leaves are needed.')); ?>

            </p>
        </div>
        
        <?php $__currentLoopData = $leaveTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leaveType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="form-group col-md-6">
                <?php echo e(Form::label('allocations[' . $leaveType->id . ']', 'Add Extra ' . $leaveType->title, ['class' => 'col-form-label'])); ?>

                <div class="input-group">
                    <?php echo e(Form::number('allocations[' . $leaveType->id . ']', '', ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'placeholder' => __('Enter extra days to add')])); ?>

                    <span class="input-group-text"><?php echo e(__('Days')); ?></span>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Add Extra Leaves')); ?>" class="btn btn-primary">
</div>
<?php echo e(Form::close()); ?>

<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/employee_leave_allocation/edit.blade.php ENDPATH**/ ?>