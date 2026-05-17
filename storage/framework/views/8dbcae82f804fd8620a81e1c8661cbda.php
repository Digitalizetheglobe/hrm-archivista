<?php
    $plan = Utility::getChatGPTSettings();
?>

<?php echo e(Form::model($leave, ['route' => ['leave.update', $leave->id], 'method' => 'PUT'])); ?>

<div class="modal-body">

    <?php if($plan->enable_chatgpt == 'on'): ?>
        <div class="card-footer text-end">
            <a href="#" class="btn btn-sm btn-primary" data-size="medium" data-ajax-popup-over="true"
                data-url="<?php echo e(route('generate', ['leave'])); ?>" data-bs-toggle="tooltip" data-bs-placement="top"
                title="<?php echo e(__('Generate')); ?>" data-title="<?php echo e(__('Generate Content With AI')); ?>">
                <i class="fas fa-robot"></i><?php echo e(__(' Generate With AI')); ?>

            </a>
        </div>
    <?php endif; ?>

    <?php if(\Auth::user()->type != 'employee'): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <?php echo e(Form::label('employee_id', __('Employee'), ['class' => 'col-form-label'])); ?>

                    <?php echo e(Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'placeholder' => __('Select Employee')])); ?>

                </div>
            </div>
        </div>
    <?php else: ?>
        <?php echo Form::hidden('employee_id', !empty($employees) ? $employees->id : 0, ['id' => 'employee_id']); ?>

    <?php endif; ?>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <?php echo e(Form::label('leave_type_id', __('Leave Type'), ['class' => 'col-form-label'])); ?><span class="text-danger pl-1">*</span>
                
                <select name="leave_type_id" id="leave_type_id" class="form-control select">
                    
                    <?php $__currentLoopData = $leavetypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($leave->id); ?>"><?php echo e($leave->title); ?> (<p class="float-right pr-5">
                                <?php echo e($leave->days); ?></p>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <?php echo e(Form::label('start_date', __('Start Date'), ['class' => 'col-form-label'])); ?>

                <?php echo e(Form::text('start_date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off'])); ?>

            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <?php echo e(Form::label('end_date', __('End Date'), ['class' => 'col-form-label'])); ?>

                <?php echo e(Form::text('end_date', null, ['class' => 'form-control d_week', 'autocomplete' => 'off'])); ?>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <?php echo e(Form::label('leave_reason', __('Leave Reason'), ['class' => 'col-form-label'])); ?>

                <?php echo e(Form::textarea('leave_reason', null, ['class' => 'form-control', 'placeholder' => __('Leave Reason'), 'rows' => '3'])); ?>

            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">

                <?php echo e(Form::label('remark', __('Remark'), ['class' => 'col-form-label'])); ?>


                <?php if($plan->enable_chatgpt == 'on'): ?>
                    <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true"
                        id="grammarCheck" data-url="<?php echo e(route('grammar', ['grammar'])); ?>" data-bs-placement="top"
                        data-title="<?php echo e(__('Grammar check with AI')); ?>">
                        <i class="ti ti-rotate"></i> <span><?php echo e(__('Grammar check with AI')); ?></span>
                    </a>
                <?php endif; ?>
                
                <?php echo e(Form::textarea('remark', null, ['class' => 'form-control grammer_textarea', 'placeholder' => __('Leave Remark'), 'rows' => '3'])); ?>

            </div>
        </div>
    </div>
    <?php if(\Spatie\Permission\PermissionServiceProvider::bladeMethodWrapper('hasRole', 'Company')): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <?php echo e(Form::label('status', __('Status'), ['class' => 'col-form-label'])); ?>

                    <select name="status" id="" class="form-control select2">
                        <option value=""><?php echo e(__('Select Status')); ?></option>
                        <option value="Pending" <?php if($leave->status == 'Pending'): ?> selected="" <?php endif; ?>><?php echo e(__('Pending')); ?>

                        </option>
                        <option value="Approved" <?php if($leave->status == 'Approved'): ?> selected="" <?php endif; ?>><?php echo e(__('Approved')); ?>

                        </option>
                        <option value="Reject" <?php if($leave->status == 'Reject'): ?> selected="" <?php endif; ?>><?php echo e(__('Reject')); ?>

                        </option>
                    </select>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
    <input type="submit" value="<?php echo e(__('Update')); ?>" class="btn  btn-primary">

</div>
<?php echo e(Form::close()); ?>


<script>
    $(document).ready(function() {
        setTimeout(() => {
            var employee_id = $('#employee_id').val();
            if (employee_id) {
                $('#employee_id').trigger('change');
            }
        }, 100);
    });
</script>
<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/leave/edit.blade.php ENDPATH**/ ?>