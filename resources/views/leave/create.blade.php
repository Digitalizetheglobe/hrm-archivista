@php
    $setting = App\Models\Utility::settings();
    $plan = Utility::getChatGPTSettings();
    $compOffBalance = $compOffBalance ?? 0; // Default value if not defined
@endphp
{{ Form::open(['url' => 'leave', 'method' => 'post']) }}
<div class="modal-body">

    @if ($plan->enable_chatgpt == 'on')
        <div class="card-footer text-end">
            <a href="#" class="btn btn-sm btn-primary" data-size="medium" data-ajax-popup-over="true"
                data-url="{{ route('generate', ['leave']) }}" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Generate') }}" data-title="{{ __('Generate Content With AI') }}">
                <i class="fas fa-robot"></i>{{ __(' Generate With AI') }}
            </a>
        </div>
    @endif

    @if (\Auth::user()->type != 'employee')
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                    {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
                </div>
            </div>
        </div>
    @else
        {!! Form::hidden('employee_id', !empty($employees) ? $employees->id : 0, ['id' => 'employee_id']) !!}
    @endif

    {{-- Show Available Comp-Off Leaves for Employees --}}
    @if (\Auth::user()->type == 'employee' && isset($compOffLeaves) && $compOffLeaves > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <p>You have <strong>{{ $compOffLeaves }}</strong> Comp-Off Leave(s) available.</p>
                    <label>
                        <input type="checkbox" name="use_comp_off" value="1"> Use Comp-Off Leave
                    </label>
                </div>
            </div>
        </div>
    @endif

    <p>Comp-Off Balance: {{ $compOffBalance }}</p>

    {{-- Leave Type Selection --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_type_id', __('Leave Type'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                <select name="leave_type_id" id="leave_type_id" class="form-control select">
                    <option value="">{{ __('Select Leave Type') }}</option>
                    @foreach ($leavetypes as $leave)
                        @if ($leave->title === 'Comp-Off' && $compOffLeaves === 0)
                            @continue
                        @endif
                        @if($leave->title == 'LWP' || $leave->title == 'WFH')
                            <option value="{{ $leave->id }}" data-unlimited="true">
                                {{ $leave->title }} (Unlimited)
                            </option>
                        @else
                            <option value="{{ $leave->id }}" data-unlimited="false" data-period="{{ $leave->type }}" data-carry-forward="{{ $leave->carry_forward_enabled ? 'true' : 'false' }}">
                                {{ $leave->title }} 
                                @if($leave->type == 'monthly')
                                    ({{ $leave->days }} {{ __('Days/Month') }})
                                @else
                                    ({{ $leave->days }} {{ __('Days/Year') }})
                                @endif
                                @if($leave->carry_forward_enabled && $leave->type == 'monthly')
                                    <small class="text-success">+ Carry Forward</small>
                                @endif
                            </option>
                        @endif
                    @endforeach
                    @if($compOffBalance > 0)
                        <option value="comp_off">{{ __('Comp-Off Leave') }} ({{ $compOffBalance }} available)</option>
                    @endif
                </select>
                <div id="leave_balance_info" class="mt-2 text-info small"></div>
            </div>
        </div>
    </div>

    {{-- Start and End Date --}}
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}
                {{ Form::text('start_date', null, ['class' => 'form-control d_week current_date', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}
                {{ Form::text('end_date', null, ['class' => 'form-control d_week current_date', 'autocomplete' => 'off']) }}
            </div>
        </div>
    </div>

    {{-- Leave Reason --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('leave_reason', __('Leave Reason'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('leave_reason', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Leave Reason'), 'rows' => '3']) }}
            </div>
        </div>
    </div>

    {{-- Remarks and AI Grammar Check --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remark'), ['class' => 'col-form-label']) }}
                @if ($plan->enable_chatgpt == 'on')
                    <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true"
                        id="grammarCheck" data-url="{{ route('grammar', ['grammar']) }}" data-bs-placement="top"
                        data-title="{{ __('Grammar check with AI') }}">
                        <i class="ti ti-rotate"></i> <span>{{ __('Grammar check with AI') }}</span>
                    </a>
                @endif
                {{ Form::textarea('remark', null, ['class' => 'form-control grammer_textarea', 'required' => 'required', 'placeholder' => __('Leave Remark'), 'rows' => '3']) }}
            </div>
        </div>
    </div>

    {{-- Google Calendar Sync --}}
    @if (isset($setting['is_enabled']) && $setting['is_enabled'] == 'on')
        <div class="form-group col-md-6">
            {{ Form::label('synchronize_type', __('Synchronize in Google Calendar?'), ['class' => 'form-label']) }}
            <div class="form-switch">
                <input type="checkbox" class="form-check-input mt-2" name="synchronize_type" id="switch-shadow" value="google_calendar">
                <label class="form-check-label" for="switch-shadow"></label>
            </div>
        </div>
    @endif
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        var now = new Date();
        var month = (now.getMonth() + 1);
        var day = now.getDate();
        if (month < 10) month = "0" + month;
        if (day < 10) day = "0" + day;
        var today = now.getFullYear() + '-' + month + '-' + day;
        $('.current_date').val(today);
        
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
                    if (data.employee_type === 'Contract' && data.confirm_of_employment) {
                        balanceText += ' (Confirmed Contract Employee)';
                    } else if (data.employee_type === 'Contract') {
                        balanceText += ' (Unconfirmed Contract Employee)';
                    }
                    $('#leave_balance_info').text(balanceText);
                }).fail(function() {
                    $('#leave_balance_info').text('Unable to fetch balance information');
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
                    if (leave.type == 'monthly') {
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
