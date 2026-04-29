<?php echo e(Form::open(['url' => 'leavetype', 'method' => 'post'])); ?>

<div class="modal-body">

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                <?php echo e(Form::label('title', __('Name'), ['class' => 'form-label'])); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::text('title', null, ['class' => 'form-control', 'required'=>'required', 'placeholder' => __('Enter Leave Type Name')])); ?>

                </div>
                <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger"><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>


        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                <?php echo e(Form::label('type', __('Leave Type Period'), ['class' => 'form-label'])); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::select('type', ['monthly' => __('Monthly'), 'yearly' => __('Yearly')], 'yearly', ['class' => 'form-control', 'required'=>'required'])); ?>

                </div>
                <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger"><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                <?php echo e(Form::label('is_unlimited', __('Unlimited Leave'), ['class' => 'form-label'])); ?>

                <div class="form-check">
                    <?php echo e(Form::checkbox('is_unlimited', 1, false, ['class' => 'form-check-input', 'id' => 'is_unlimited'])); ?>

                    <?php echo e(Form::label('is_unlimited', __('Check if this leave type has unlimited days'), ['class' => 'form-check-label'])); ?>

                </div>
                <?php $__errorArgs = ['is_unlimited'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger"><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12" id="carry_forward_section">
            <div class="form-group">
                <?php echo e(Form::label('carry_forward_enabled', __('Carry Forward'), ['class' => 'form-label'])); ?>

                <div class="form-check">
                    <?php echo e(Form::checkbox('carry_forward_enabled', 1, false, ['class' => 'form-check-input', 'id' => 'carry_forward_enabled'])); ?>

                    <?php echo e(Form::label('carry_forward_enabled', __('Enable carry forward for this leave type'), ['class' => 'form-check-label'])); ?>

                </div>
                <?php $__errorArgs = ['carry_forward_enabled'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger"><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12" id="max_carry_forward_field" style="display: none;">
            <div class="form-group">
                <?php echo e(Form::label('max_carry_forward_days', __('Max Carry Forward Days'), ['class' => 'form-label'])); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::number('max_carry_forward_days', null, ['class' => 'form-control', 'placeholder' => __('Enter maximum carry forward days'),'min'=>'0.01', 'step'=>'0.01', 'id' => 'max_carry_forward_input'])); ?>

                </div>
                <small class="form-text text-muted"><?php echo e(__('Maximum days that can be carried forward to next period')); ?></small>
                <?php $__errorArgs = ['max_carry_forward_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger"><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12" id="days_field">
            <div class="form-group">
                <?php echo e(Form::label('days', __('Days Per Period'), ['class' => 'form-label'])); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::number('days', null, ['class' => 'form-control', 'placeholder' => __('Enter Days per Period'),'min'=>'0', 'step'=>'0.01', 'id' => 'days_input'])); ?>

                </div>
                <?php $__errorArgs = ['days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger"><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                <?php echo e(Form::label('eligible_employee_types', __('Eligible Employee Types'), ['class' => 'form-label'])); ?>

                <div class="form-icon-user">
                    <div class="form-check-group">
                        <?php
                            $employeeTypes = [
                                'payroll_confirm' => 'Payroll - Confirm',
                                'payroll_not_confirm' => 'Payroll - Not Confirm',
                                'contract_confirm' => 'Contract - Confirm',
                                'contract_not_confirm' => 'Contract - Not Confirm'
                            ];
                        ?>
                        <?php $__currentLoopData = $employeeTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-check">
                                <?php echo e(Form::checkbox('eligible_employee_types[]', $value, false, ['class' => 'form-check-input', 'id' => 'employee_type_' . $value])); ?>

                                <?php echo e(Form::label('employee_type_' . $value, $label, ['class' => 'form-check-label'])); ?>

                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <small class="form-text text-muted"><?php echo e(__('Select which employee types can use this leave type. You can select one or multiple types.')); ?></small>
                <?php $__errorArgs = ['eligible_employee_types'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger"><?php echo e($message); ?></strong>
                    </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Create')); ?>" class="btn btn-primary">
</div>
<?php echo e(Form::close()); ?>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const unlimitedCheckbox = document.getElementById('is_unlimited');
    const daysField = document.getElementById('days_field');
    const daysInput = document.getElementById('days_input');
    const carryForwardCheckbox = document.getElementById('carry_forward_enabled');
    const maxCarryForwardField = document.getElementById('max_carry_forward_field');
    const maxCarryForwardInput = document.getElementById('max_carry_forward_input');
    const typeSelect = document.getElementById('type');
    
    function toggleFields() {
        const isUnlimited = unlimitedCheckbox.checked;
        const isCarryForwardEnabled = carryForwardCheckbox.checked;
        
        // Toggle days field based on unlimited
        if (isUnlimited) {
            daysField.style.display = 'none';
            daysInput.removeAttribute('required');
        } else {
            daysField.style.display = 'block';
            daysInput.setAttribute('required', 'required');
        }
        
        // Hide carry forward section for unlimited leaves
        if (isUnlimited) {
            document.getElementById('carry_forward_section').style.display = 'none';
            carryForwardCheckbox.checked = false;
            maxCarryForwardField.style.display = 'none';
            maxCarryForwardInput.removeAttribute('required');
        } else {
            document.getElementById('carry_forward_section').style.display = 'block';
        }
        
        // Toggle max carry forward field
        if (isCarryForwardEnabled && !isUnlimited) {
            maxCarryForwardField.style.display = 'block';
            maxCarryForwardInput.setAttribute('required', 'required');
        } else {
            maxCarryForwardField.style.display = 'none';
            maxCarryForwardInput.removeAttribute('required');
        }
    }
    
    unlimitedCheckbox.addEventListener('change', toggleFields);
    carryForwardCheckbox.addEventListener('change', toggleFields);
    
    toggleFields(); // Initialize on page load
});
</script>




<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/leavetype/create.blade.php ENDPATH**/ ?>