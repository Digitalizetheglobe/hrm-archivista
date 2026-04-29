{{ Form::model($leavetype, ['route' => ['leavetype.update', $leavetype->id], 'method' => 'PUT']) }}
<div class="modal-body">

    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('title', __('Name'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('title', null, ['class' => 'form-control', 'required'=>'required', 'placeholder' => __('Enter Leave Type Name')]) }}
                </div>
                @error('title')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>


        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('type', __('Leave Type Period'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::select('type', ['monthly' => __('Monthly'), 'yearly' => __('Yearly')], $leavetype->type, ['class' => 'form-control', 'required'=>'required']) }}
                </div>
                @error('type')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('is_unlimited', __('Unlimited Leave'), ['class' => 'form-label']) }}
                <div class="form-check">
                    {{ Form::checkbox('is_unlimited', 1, $leavetype->is_unlimited, ['class' => 'form-check-input', 'id' => 'is_unlimited']) }}
                    {{ Form::label('is_unlimited', __('Check if this leave type has unlimited days'), ['class' => 'form-check-label']) }}
                </div>
                @error('is_unlimited')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12" id="carry_forward_section">
            <div class="form-group">
                {{ Form::label('carry_forward_enabled', __('Carry Forward'), ['class' => 'form-label']) }}
                <div class="form-check">
                    {{ Form::checkbox('carry_forward_enabled', 1, $leavetype->carry_forward_enabled ?? false, ['class' => 'form-check-input', 'id' => 'carry_forward_enabled']) }}
                    {{ Form::label('carry_forward_enabled', __('Enable carry forward for this leave type'), ['class' => 'form-check-label']) }}
                </div>
                @error('carry_forward_enabled')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12" id="max_carry_forward_field" style="display: none;">
            <div class="form-group">
                {{ Form::label('max_carry_forward_days', __('Max Carry Forward Days'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::number('max_carry_forward_days', null, ['class' => 'form-control', 'placeholder' => __('Enter maximum carry forward days'),'min'=>'0.01', 'step'=>'0.01', 'id' => 'max_carry_forward_input']) }}
                </div>
                <small class="form-text text-muted">{{ __('Maximum days that can be carried forward to next period') }}</small>
                @error('max_carry_forward_days')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12" id="days_field">
            <div class="form-group">
                {{ Form::label('days', __('Days Per Period'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::number('days', null, ['class' => 'form-control', 'placeholder' => __('Enter Days per Period'), 'min'=>'0', 'step'=>'0.01', 'id' => 'days_input']) }}
                </div>
                @error('days')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('eligible_employee_types', __('Eligible Employee Types'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    <div class="form-check-group">
                        @php
                            $employeeTypes = [
                                'payroll_confirm' => 'Payroll - Confirm',
                                'payroll_not_confirm' => 'Payroll - Not Confirm',
                                'contract_confirm' => 'Contract - Confirm',
                                'contract_not_confirm' => 'Contract - Not Confirm'
                            ];
                            $selectedTypes = $leavetype->eligible_employee_types ?? [];
                        @endphp
                        @foreach($employeeTypes as $value => $label)
                            <div class="form-check">
                                {{ Form::checkbox('eligible_employee_types[]', $value, in_array($value, $selectedTypes), ['class' => 'form-check-input', 'id' => 'edit_employee_type_' . $value]) }}
                                {{ Form::label('edit_employee_type_' . $value, $label, ['class' => 'form-check-label']) }}
                            </div>
                        @endforeach
                    </div>
                </div>
                <small class="form-text text-muted">{{ __('Select which employee types can use this leave type. You can select one or multiple types.') }}</small>
                @error('eligible_employee_types')
                    <span class="invalid-name" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
</div>
{{ Form::close() }}

<script>
document.addEventListener('DOMContentLoaded', function() {
    const unlimitedCheckbox = document.getElementById('is_unlimited');
    const daysField = document.getElementById('days_field');
    const daysInput = document.getElementById('days_input');
    const carryForwardCheckbox = document.getElementById('carry_forward_enabled');
    const maxCarryForwardField = document.getElementById('max_carry_forward_field');
    const maxCarryForwardInput = document.getElementById('max_carry_forward_input');
    
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




