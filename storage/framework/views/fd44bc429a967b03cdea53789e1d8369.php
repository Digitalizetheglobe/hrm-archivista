<?php
    $setting = App\Models\Utility::settings();
    $plan = Utility::getChatGPTSettings();
    $compOffBalance = $compOffBalance ?? 0;
?>

<style>
/* ===== Premium Leave Form Styles (Branded Orange) ===== */
.lf-section-label {
    font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1.2px; color: #8c92a4; margin-bottom: 8px;
}
.lf-input-icon-wrap { position: relative; }
.lf-input-icon-wrap .lf-icon {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: #a0aec0; font-size: 0.85rem; pointer-events: none;
}
.lf-input-icon-wrap .form-control,
.lf-input-icon-wrap .form-select { padding-left: 34px; }
.lf-form-control {
    border: 1.5px solid #e2e8f0; border-radius: 10px;
    padding: 10px 14px; font-size: 0.88rem; color: #2d3748;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    background: #fafbfc;
}
.lf-form-control:focus {
    border-color: #f6821f; box-shadow: 0 0 0 3px rgba(246,130,31,0.12);
    background: #fff; outline: none;
}
/* Pill Toggle */
.lf-pill-group { display: flex; gap: 10px; flex-wrap: wrap; }
.lf-pill-group input[type="radio"] { display: none; }
.lf-pill-group label {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 8px 20px; border-radius: 30px; font-size: 0.83rem;
    font-weight: 600; cursor: pointer; border: 2px solid #e2e8f0;
    background: #f7f8fa; color: #64748b;
    transition: all 0.2s ease; user-select: none;
}
.lf-pill-group label i { font-size: 0.85rem; }
.lf-pill-group input[type="radio"]:checked + label {
    border-color: #f6821f; background: linear-gradient(135deg,#f6821f,#fb923c);
    color: #fff; box-shadow: 0 4px 12px rgba(246,130,31,0.3);
}
.lf-pill-group.orange input[type="radio"]:checked + label {
    border-color: #f6821f; background: linear-gradient(135deg,#f6821f,#e67e22);
    box-shadow: 0 4px 12px rgba(246,130,31,0.3);
}
/* Balance badge */
.lf-balance-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: linear-gradient(135deg,#fff7ed,#ffedd5);
    border: 1px solid #fed7aa; color: #9a3412;
    border-radius: 20px; padding: 4px 12px; font-size: 0.75rem; font-weight: 600;
}
/* Divider */
.lf-divider { border: none; border-top: 1.5px dashed #e9ecef; margin: 8px 0 16px; }
/* Section card */
.lf-section {
    background: #fff; border-radius: 12px;
    border: 1.5px solid #f0f2f5; padding: 18px 18px 10px;
    margin-bottom: 14px;
}
.lf-section-title {
    font-size: 0.78rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1px; color: #f6821f; margin-bottom: 14px;
    display: flex; align-items: center; gap: 7px;
}
</style>

<?php echo e(Form::open(['url' => 'leave', 'method' => 'post'])); ?>

<div class="modal-body" style="padding: 20px 24px;">


    
    <?php if(\Auth::user()->type != 'employee'): ?>
        <div class="lf-section">
            <div class="lf-section-title"><i class="fas fa-user-tie"></i> Employee</div>
            <div class="lf-input-icon-wrap">
                <i class="fas fa-user lf-icon"></i>
                <?php echo e(Form::select('employee_id', $employees, null, ['class' => 'form-control lf-form-control select2', 'id' => 'employee_id', 'placeholder' => __('Select Employee')])); ?>

            </div>
        </div>
    <?php else: ?>
        <?php echo Form::hidden('employee_id', !empty($employees) ? $employees->id : 0, ['id' => 'employee_id']); ?>

    <?php endif; ?>

    
    <?php if($compOffBalance > 0): ?>
        <div class="mb-3">
            <span class="lf-balance-badge"><i class="fas fa-exchange-alt"></i> Comp-Off Balance: <?php echo e($compOffBalance); ?></span>
        </div>
    <?php endif; ?>

    
    <div class="lf-section">
        <div class="lf-section-title"><i class="fas fa-calendar-alt"></i> Leave Details</div>
        <div class="mb-3">
            <div class="lf-section-label">Leave Type <span class="text-danger">*</span></div>
            <div class="lf-input-icon-wrap">
                <i class="fas fa-list lf-icon"></i>
                <select name="leave_type_id" id="leave_type_id" class="form-control lf-form-control" style="padding-left:34px;">
                    <option value=""><?php echo e(__('Select Leave Type')); ?></option>
                    <?php $__currentLoopData = $leavetypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($leave->title == 'LWP' || $leave->title == 'WFH'): ?>
                            <option value="<?php echo e($leave->id); ?>" data-unlimited="true"><?php echo e($leave->title); ?> (Unlimited)</option>
                        <?php else: ?>
                            <option value="<?php echo e($leave->id); ?>" data-unlimited="false" data-period="<?php echo e($leave->type); ?>" data-carry-forward="<?php echo e($leave->carry_forward_enabled ? 'true' : 'false'); ?>">
                                <?php echo e($leave->title); ?>

                                <?php if(strtolower(trim($leave->title)) === 'comp-off'): ?>
                                    (<?php echo e(\App\Http\Controllers\LeaveController::getCompOffBalance(($employees instanceof \App\Models\Employee) ? $employees->id : 0)); ?> <?php echo e(__('Days Available')); ?>)
                                <?php elseif($leave->type == 'monthly'): ?> (<?php echo e($leave->days); ?> <?php echo e(__('Days/Month')); ?>)
                                <?php else: ?> <?php endif; ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div id="leave_balance_info" class="mt-2" style="font-size:0.78rem;color:#667eea;font-weight:600;"></div>
        </div>

        
        <div class="mb-3">
            <div class="lf-section-label">Leave Duration</div>
            <div class="lf-pill-group">
                <input type="radio" name="leave_duration" id="full_day" value="full_day" checked>
                <label for="full_day"><i class="fas fa-sun"></i> Full Day</label>
                <input type="radio" name="leave_duration" id="half_day" value="half_day">
                <label for="half_day"><i class="fas fa-adjust"></i> Half Day</label>
            </div>
        </div>

        
        <div id="half_day_type_container" style="display:none;" class="mb-3">
            <div class="lf-section-label">Half Day Session</div>
            <div class="lf-pill-group orange">
                <input type="radio" name="half_day_type" id="first_half" value="first_half">
                <label for="first_half"><i class="fas fa-cloud-sun"></i> First Half</label>
                <input type="radio" name="half_day_type" id="second_half" value="second_half">
                <label for="second_half"><i class="fas fa-cloud-moon"></i> Second Half</label>
            </div>
        </div>

        
        <div class="row g-3">
            <div class="col-md-6" id="start_date_container">
                <div class="lf-section-label">Start Date</div>
                <div class="lf-input-icon-wrap">
                    <i class="fas fa-calendar-day lf-icon"></i>
                    <?php echo e(Form::text('start_date', null, ['class' => 'form-control lf-form-control d_week current_date', 'autocomplete' => 'off', 'id' => 'start_date', 'placeholder' => 'YYYY-MM-DD'])); ?>

                </div>
            </div>
            <div class="col-md-6" id="end_date_container">
                <div class="lf-section-label">End Date</div>
                <div class="lf-input-icon-wrap">
                    <i class="fas fa-calendar-check lf-icon"></i>
                    <?php echo e(Form::text('end_date', null, ['class' => 'form-control lf-form-control d_week current_date', 'autocomplete' => 'off', 'id' => 'end_date', 'placeholder' => 'YYYY-MM-DD'])); ?>

                </div>
            </div>
        </div>
    </div>

    
    <div class="lf-section">
        <div class="lf-section-title"><i class="fas fa-pen-nib"></i> Reason & Remarks</div>
        <div class="mb-3">
            <div class="lf-section-label">Leave Reason <span class="text-danger">*</span></div>
            <?php echo e(Form::textarea('leave_reason', null, ['class' => 'form-control lf-form-control', 'required' => 'required', 'placeholder' => __('Describe your leave reason...'), 'rows' => '3'])); ?>

        </div>
    </div>

    
    <?php if(isset($setting['is_enabled']) && $setting['is_enabled'] == 'on'): ?>
        <div class="lf-section" style="padding: 12px 18px;">
            <div class="d-flex align-items-center justify-content-between">
                <div style="font-size:0.82rem; font-weight:600; color:#4a5568;">
                    <i class="fas fa-calendar-alt me-2" style="color:#667eea;"></i>
                    <?php echo e(__('Sync with Google Calendar?')); ?>

                </div>
                <div class="form-switch">
                    <input type="checkbox" class="form-check-input" name="synchronize_type" id="switch-shadow" value="google_calendar">
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="modal-footer" style="border-top: 1.5px solid #f0f2f5; padding: 14px 24px; gap: 10px;">
    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius:8px; font-weight:600;"><?php echo e(__('Cancel')); ?></button>
    <button type="submit" class="btn btn-primary px-5" style="border-radius:8px; font-weight:600; background:linear-gradient(135deg,#f6821f,#e67e22); border:none;">
        <i class="fas fa-paper-plane me-2"></i><?php echo e(__('Submit Leave')); ?>

    </button>
</div>
<?php echo e(Form::close()); ?>



<script>
    $(document).ready(function() {
        var now = new Date();
        var month = (now.getMonth() + 1);
        var day = now.getDate();
        if (month < 10) month = "0" + month;
        if (day < 10) day = "0" + day;
        var today = now.getFullYear() + '-' + month + '-' + day;
        $('.current_date').val(today);

        // Leave Duration Logic
        $('input[name="leave_duration"]').on('change', function() {
            var duration = $(this).val();
            if (duration == 'half_day') {
                $('#half_day_type_container').fadeIn();
                $('#end_date_container').hide();
                // Set first half as default if none selected
                if (!$('input[name="half_day_type"]:checked').val()) {
                    $('#first_half').prop('checked', true);
                }
                // Sync end date with start date
                $('#end_date').val($('#start_date').val());
            } else {
                $('#half_day_type_container').fadeOut();
                $('#end_date_container').fadeIn();
            }
        });

        // Sync end date if it's a half day and start date changes
        $('#start_date').on('change', function() {
            if ($('input[name="leave_duration"]:checked').val() == 'half_day') {
                $('#end_date').val($(this).val());
            }
        });
        
        // Debug: Check current state of options
        console.log('=== DEBUG: Leave Type Options ===');
        $('#leave_type_id option').each(function() {
            var $option = $(this);
            console.log('Option:', {
                value: $option.val(),
                text: $option.text(),
                dataUnlimited: $option.data('unlimited'),
                disabled: $option.prop('disabled'),
                hidden: $option.is(':hidden'),
                html: $option.html()
            });
        });
        
        // Track selection changes
        $('#leave_type_id').on('change', function() {
            var selectedValue = $(this).val();
            var selectedText = $(this).find('option:selected').text();
            var employeeId = $('#employee_id').val();
            
            console.log('=== SELECTION CHANGED ===');
            console.log('Selected Value:', selectedValue);
            console.log('Selected Text:', selectedText);
            console.log('Employee ID:', employeeId);
            
            // Check if LWP or WFH was selected
            if (selectedValue == '3' || selectedValue == '4') {
                console.log('Unlimited leave type selected!');
                $('#leave_balance_info').text('Unlimited leave - no balance restrictions');
            } else if (selectedValue && employeeId) {
                // Fetch leave balance for the selected employee and leave type
                $.get('/leave/get-leave-balance/' + employeeId + '/' + selectedValue, function(data) {
                    var balanceText = 'Available: ' + data.available_days + ' days';
                    if ((data.employee_type === 'Contract' || data.employee_type === 'Consultant') && data.confirm_of_employment) {
                        balanceText += ' (Confirmed ' + data.employee_type + ' Employee)';
                    } else if (data.employee_type === 'Contract' || data.employee_type === 'Consultant') {
                        balanceText += ' (Unconfirmed ' + data.employee_type + ' Employee)';
                    }
                    $('#leave_balance_info').text(balanceText);
                }).fail(function() {
                    $('#leave_balance_info').text('Fetch balance information');
                });
            } else {
                $('#leave_balance_info').text('');
            }
        });
        
        // Track click events on options
        $('#leave_type_id option').on('click', function() {
            console.log('Option clicked:', $(this).val(), $(this).text());
        });
        
        // Aggressively protect unlimited leave types
        function protectUnlimitedLeaveTypes() {
            $('#leave_type_id option').each(function() {
                var $option = $(this);
                var value = $option.val();
                var currentText = $option.text();
                
                // Ensure LWP and WFH are enabled and visible
                if (value == '3' || value == '4') { // LWP or WFH
                    $option.prop('disabled', false);
                    $option.show();
                    $option.css('display', 'block');
                    $option.removeAttr('disabled');
                    
                    // Force unlimited text
                    if (!currentText.includes('(Unlimited)')) {
                        var title = currentText.split(' (')[0];
                        $option.text(title + ' (Unlimited)');
                        console.log('Forced', title, 'to Unlimited');
                    }
                }
            });
        }
        
        // Force selection to work for unlimited types
        $('#leave_type_id').on('click', function(e) {
            var clickedOption = $(e.target);
            var value = clickedOption.val();
            
            if (value == '3' || value == '4') {
                e.preventDefault();
                e.stopPropagation();
                
                // Force selection
                $('#leave_type_id').val(value);
                clickedOption.prop('selected', true);
                
                console.log('Forced selection of unlimited leave type:', value);
                
                // Trigger change event
                $('#leave_type_id').trigger('change');
                
                return false;
            }
        });
        
        // Initial protection
        protectUnlimitedLeaveTypes();
        
        // Protect against any future modifications - more frequent
        setInterval(protectUnlimitedLeaveTypes, 500);
        
        // Override any jQuery that might modify the dropdown
        $('#leave_type_id').on('DOMSubtreeModified', function() {
            setTimeout(protectUnlimitedLeaveTypes, 50);
        });
        
        // Handle employee selection change for admin users
        $('#employee_id').on('change', function() {
            var employeeId = $(this).val();
            if (employeeId && employeeId !== '') {
                // Fetch filtered leave types for the selected employee
                $.get('/leave/get-leave-types/' + employeeId, function(data) {
                    updateLeaveTypeDropdown(data);
                }).fail(function() {
                    console.error('Failed to fetch leave types for employee');
                });
            } else {
                // Reset to all leave types if no employee selected
                location.reload(); // Simple reload to reset
            }
        });
        
        function updateLeaveTypeDropdown(leaveTypes) {
            var $select = $('#leave_type_id');
            var currentValue = $select.val();
            
            // Clear existing options except the first one
            $select.find('option:not(:first)').remove();
            
            // Add new options based on filtered leave types
            $.each(leaveTypes, function(index, leave) {
                var $option = $('<option></option>');
                $option.attr('value', leave.id);
                
                if (leave.title == 'LWP' || leave.title == 'WFH') {
                    $option.attr('data-unlimited', 'true');
                    $option.text(leave.title + ' (Unlimited)');
                } else {
                    $option.attr('data-unlimited', 'false');
                    $option.attr('data-period', leave.type);
                    $option.attr('data-carry-forward', leave.carry_forward_enabled ? 'true' : 'false');
                    
                    var text = leave.title;
                    if (leave.title.toLowerCase().trim() === 'comp-off') {
                        text += ' (' + leave.days + ' Days Available)';
                    } else if (leave.type == 'monthly') {
                        text += ' (' + leave.days + ' Days/Month)';
                    } else {
                        text += ' (' + leave.days + ' Days/Year)';
                    }
                    if (leave.carry_forward_enabled && leave.type == 'monthly') {
                        text += ' + Carry Forward';
                    }
                    $option.text(text);
                }
                
                $select.append($option);
            });
            
            // Try to restore previous selection if it's still available
            if (currentValue) {
                $select.val(currentValue);
            }
            
            // Re-apply protection for unlimited types
            protectUnlimitedLeaveTypes();
        }
    });

    setTimeout(() => {
        var employee_id = $('#employee_id').val();
        if (employee_id) {
            $('#employee_id').trigger('change');
        }
    }, 100);
</script>
<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/leave/create.blade.php ENDPATH**/ ?>