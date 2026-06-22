<?php echo e(Form::model($leavetype, ['route' => ['leavetype.update', $leavetype->id], 'method' => 'PUT'])); ?>

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
                    <?php echo e(Form::select('type', ['monthly' => __('Monthly'), 'yearly' => __('Yearly')], $leavetype->type, ['class' => 'form-control', 'required'=>'required'])); ?>

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
                    <?php echo e(Form::checkbox('is_unlimited', 1, $leavetype->is_unlimited, ['class' => 'form-check-input', 'id' => 'is_unlimited'])); ?>

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

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                <?php echo e(Form::label('more_than_3_5_leaves', __('More than 3.5 Leaves'), ['class' => 'form-label'])); ?>

                <div class="form-check">
                    <?php echo e(Form::checkbox('more_than_3_5_leaves', 1, $leavetype->more_than_3_5_leaves, ['class' => 'form-check-input', 'id' => 'more_than_3_5_leaves'])); ?>

                    <?php echo e(Form::label('more_than_3_5_leaves', __('Check if this rule applies to more than 3.5 leaves'), ['class' => 'form-check-label'])); ?>

                </div>
                <?php $__errorArgs = ['more_than_3_5_leaves'];
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
                <?php echo e(Form::label('is_lwp', __('Leave Without Pay (LWP)'), ['class' => 'form-label'])); ?>

                <div class="form-check">
                    <?php echo e(Form::checkbox('is_lwp', 1, $leavetype->is_lwp, ['class' => 'form-check-input', 'id' => 'is_lwp'])); ?>

                    <?php echo e(Form::label('is_lwp', __('This leave is LWP (for all employees, unlimited days, no carry forward)'), ['class' => 'form-check-label text-danger'])); ?>

                </div>
                <?php $__errorArgs = ['is_lwp'];
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
                    <?php echo e(Form::checkbox('carry_forward_enabled', 1, $leavetype->carry_forward_enabled ?? false, ['class' => 'form-check-input', 'id' => 'carry_forward_enabled'])); ?>

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



        <div class="col-lg-12 col-md-12 col-sm-12" id="days_field">
            <div class="form-group">
                <?php echo e(Form::label('days', __('Days Per Period'), ['class' => 'form-label'])); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::number('days', null, ['class' => 'form-control', 'placeholder' => __('Enter Days per Period'), 'min'=>'0', 'step'=>'0.01', 'id' => 'days_input'])); ?>

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
                                'contract_confirm' => 'Contract/Consultant - Confirm',
                                'contract_not_confirm' => 'Contract/Consultant - Not Confirm'
                            ];
                            $selectedTypes = $leavetype->eligible_employee_types ?? [];
                        ?>
                        <?php $__currentLoopData = $employeeTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-check">
                                <?php echo e(Form::checkbox('eligible_employee_types[]', $value, in_array($value, $selectedTypes), ['class' => 'form-check-input', 'id' => 'edit_employee_type_' . $value])); ?>

                                <?php echo e(Form::label('edit_employee_type_' . $value, $label, ['class' => 'form-check-label'])); ?>

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
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" class="btn btn-primary"><?php echo e(__('Update')); ?></button>
</div>
<?php echo e(Form::close()); ?>


<script>
(function() {
    const unlimitedCheckbox = document.getElementById('is_unlimited');
    const lwpCheckbox = document.getElementById('is_lwp');
    const daysField = document.getElementById('days_field');
    const daysInput = document.getElementById('days_input');
    const carryForwardCheckbox = document.getElementById('carry_forward_enabled');
    const employeeTypeCheckboxes = document.querySelectorAll('input[name="eligible_employee_types[]"]');
    
    function toggleFields() {
        if (!unlimitedCheckbox) return;
        
        let isUnlimited = unlimitedCheckbox.checked;
        const isLwp = lwpCheckbox ? lwpCheckbox.checked : false;
        
        if (isLwp) {
            isUnlimited = true;
            unlimitedCheckbox.checked = true;
            unlimitedCheckbox.disabled = true;
            
            carryForwardCheckbox.checked = false;
            document.getElementById('carry_forward_section').style.display = 'none';
            
            employeeTypeCheckboxes.forEach(cb => {
                cb.checked = true;
                cb.disabled = true;
            });
            // We need to add a hidden input since disabled checkboxes don't submit
            let hiddenContainer = document.getElementById('hidden_employee_types_container');
            if (!hiddenContainer) {
                hiddenContainer = document.createElement('div');
                hiddenContainer.id = 'hidden_employee_types_container';
                employeeTypeCheckboxes[0].closest('.form-group').appendChild(hiddenContainer);
            }
            hiddenContainer.innerHTML = '';
            employeeTypeCheckboxes.forEach(cb => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'eligible_employee_types[]';
                hiddenInput.value = cb.value;
                hiddenContainer.appendChild(hiddenInput);
            });
        } else {
            unlimitedCheckbox.disabled = false;
            const hiddenContainer = document.getElementById('hidden_employee_types_container');
            if (hiddenContainer) {
                hiddenContainer.innerHTML = '';
            }
            employeeTypeCheckboxes.forEach(cb => {
                cb.disabled = false;
            });
        }

        // Toggle days field based on unlimited
        if (isUnlimited) {
            daysField.style.display = 'none';
            daysInput.removeAttribute('required');
            if(isLwp) {
                daysInput.value = 0;
            }
        } else {
            daysField.style.display = 'block';
            daysInput.setAttribute('required', 'required');
        }
        
        // Hide carry forward section for unlimited leaves
        if (isUnlimited && !isLwp) {
            document.getElementById('carry_forward_section').style.display = 'none';
            carryForwardCheckbox.checked = false;
        } else if (!isLwp) {
            document.getElementById('carry_forward_section').style.display = 'block';
        }
    }
    
    if (unlimitedCheckbox) unlimitedCheckbox.addEventListener('change', toggleFields);
    if (lwpCheckbox) lwpCheckbox.addEventListener('change', toggleFields);
    if (carryForwardCheckbox) carryForwardCheckbox.addEventListener('change', toggleFields);
    
    toggleFields(); // Initialize on page load
})();
</script>




<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/leavetype/edit.blade.php ENDPATH**/ ?>