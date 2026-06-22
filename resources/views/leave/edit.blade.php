@php
    $plan = Utility::getChatGPTSettings();
@endphp

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

{{ Form::model($leave, ['route' => ['leave.update', $leave->id], 'method' => 'PUT']) }}
<div class="modal-body" style="padding: 20px 24px;">


    {{-- Employee Selector (Admin only) --}}
    @if (\Auth::user()->type != 'employee')
        <div class="lf-section">
            <div class="lf-section-title"><i class="fas fa-user-tie"></i> Employee</div>
            <div class="lf-input-icon-wrap">
                <i class="fas fa-user lf-icon"></i>
                {{ Form::select('employee_id', $employees, null, ['class' => 'form-control lf-form-control select2', 'id' => 'employee_id', 'placeholder' => __('Select Employee')]) }}
            </div>
        </div>
    @else
        {!! Form::hidden('employee_id', !empty($employees) ? $employees->id : 0, ['id' => 'employee_id']) !!}
    @endif

    {{-- Leave Type & Duration --}}
    <div class="lf-section">
        <div class="lf-section-title"><i class="fas fa-calendar-alt"></i> Leave Details</div>
        {{-- Dates --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6" id="start_date_container">
                <div class="lf-section-label">Start Date</div>
                <div class="lf-input-icon-wrap">
                    <i class="fas fa-calendar-day lf-icon"></i>
                    {{ Form::text('start_date', null, ['class' => 'form-control lf-form-control d_week', 'autocomplete' => 'off', 'id' => 'start_date']) }}
                </div>
            </div>
            <div class="col-md-6" id="end_date_container">
                <div class="lf-section-label">End Date</div>
                <div class="lf-input-icon-wrap">
                    <i class="fas fa-calendar-check lf-icon"></i>
                    {{ Form::text('end_date', null, ['class' => 'form-control lf-form-control d_week', 'autocomplete' => 'off', 'id' => 'end_date']) }}
                </div>
            </div>
        </div>

        <div class="mb-3">
            <div class="lf-section-label">Does this leave include a half day?</div>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="has_half_day" name="has_half_day" value="1" style="width: 2.5em; height: 1.25em; cursor: pointer;" @if($leave->leave_duration == 'half_day') checked @endif>
                <label class="form-check-label ms-2" for="has_half_day" style="padding-top: 2px; font-weight: 500; cursor: pointer;">Yes, subtracts 0.5 days from total</label>
            </div>
        </div>

        {{-- Half Day Option (hidden by default) --}}
        <div id="half_day_type_container" style="{{ $leave->leave_duration == 'half_day' ? '' : 'display: none;' }}" class="mb-3">
            <div class="lf-section-label">Half Day Session</div>
            <div class="lf-pill-group orange">
                <input type="radio" name="half_day_type" id="first_half" value="first_half" @if($leave->half_day_type == 'first_half') checked @endif>
                <label for="first_half"><i class="fas fa-cloud-sun"></i> First Half</label>
                <input type="radio" name="half_day_type" id="second_half" value="second_half" @if($leave->half_day_type == 'second_half') checked @endif>
                <label for="second_half"><i class="fas fa-cloud-moon"></i> Second Half</label>
            </div>
        </div>

        <div class="mb-3 p-2 bg-light rounded border">
            <span class="lf-section-label mb-0" style="font-size: 0.8rem;">Calculated Leave Days: <strong id="calculated_days_display" class="text-primary" style="font-size: 1rem;">0</strong></span>
        </div>

        <div class="mb-3">
            <div class="lf-section-label">Leave Type <span class="text-danger">*</span></div>
            <div class="lf-input-icon-wrap">
                <i class="fas fa-list lf-icon"></i>
                <select name="leave_type_id" id="leave_type_id" class="form-control lf-form-control">
                    @foreach ($leavetypes as $type)
                        <option value="{{ $type->id }}" data-more-than-3-5="{{ $type->more_than_3_5_leaves ? 'true' : 'false' }}" @if($leave->leave_type_id == $type->id) selected @endif>
                            {{ $type->title }}
                            @if(strtolower(trim($type->title)) === 'comp-off')
                                ({{ \App\Http\Controllers\LeaveController::getCompOffBalance($leave->employee_id) }} {{ __('Days Available') }})
                            @elseif($type->title == 'LWP' || $type->title == 'WFH')
                                (Unlimited)
                            @else
                                ({{ $type->days }} days)
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Leave Reason & Remark --}}
    <div class="lf-section">
        <div class="lf-section-title"><i class="fas fa-pen-nib"></i> Reason & Remarks</div>
        <div class="mb-3">
            <div class="lf-section-label">Leave Reason <span class="text-danger">*</span></div>
            {{ Form::textarea('leave_reason', null, ['class' => 'form-control lf-form-control', 'required' => 'required', 'placeholder' => __('Describe your leave reason...'), 'rows' => '3']) }}
        </div>

    </div>

    {{-- Status (Company Role Only) --}}
    @role('Company')
        <div class="lf-section">
            <div class="lf-section-title"><i class="fas fa-check-circle"></i> Status Management</div>
            <div class="lf-input-icon-wrap">
                <i class="fas fa-info-circle lf-icon"></i>
                <select name="status" class="form-control lf-form-control select2">
                    <option value="Pending" @if ($leave->status == 'Pending') selected @endif>{{ __('Pending') }}</option>
                    <option value="Approved" @if ($leave->status == 'Approved') selected @endif>{{ __('Approved') }}</option>
                    <option value="Reject" @if ($leave->status == 'Reject') selected @endif>{{ __('Reject') }}</option>
                </select>
            </div>
        </div>
    @endrole
</div>

<div class="modal-footer" style="border-top: 1.5px solid #f0f2f5; padding: 14px 24px; gap: 10px;">
    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius:8px; font-weight:600;">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary px-5" style="border-radius:8px; font-weight:600; background:linear-gradient(135deg,#f6821f,#e67e22); border:none;">
        <i class="fas fa-save me-2"></i>{{ __('Update Leave') }}
    </button>
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        // Leave Duration Logic
        $('#has_half_day').on('change', function() {
            if ($(this).is(':checked')) {
                $('#half_day_type_container').fadeIn();
                if (!$('input[name="half_day_type"]:checked').val()) {
                    $('#second_half').prop('checked', true);
                }
            } else {
                $('#half_day_type_container').fadeOut();
            }
            calculateDays();
        });

        $('#start_date, #end_date').on('change', function() {
            calculateDays();
        });

        function calculateDays() {
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();
            var hasHalfDay = $('#has_half_day').is(':checked') ? 1 : 0;

            if (startDate && endDate) {
                $.ajax({
                    url: '{{ route('leave.calculate_days') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        start_date: startDate,
                        end_date: endDate,
                        has_half_day: hasHalfDay
                    },
                    success: function(response) {
                        var totalDays = parseFloat(response.total_days);
                        $('#calculated_days_display').text(totalDays);
                        filterLeaveTypes(totalDays);
                    }
                });
            }
        }

        function filterLeaveTypes(totalDays) {
            var $select = $('#leave_type_id');
            var currentValue = $select.val();
            
            $select.find('option').each(function() {
                if ($(this).val() === '') return;
                
                var requiresMoreThan35 = $(this).attr('data-more-than-3-5') === 'true';
                
                if (requiresMoreThan35 && totalDays <= 3.5) {
                    $(this).prop('disabled', true).hide();
                    if (currentValue === $(this).val()) {
                        $select.val(''); // clear selection
                    }
                } else {
                    $(this).prop('disabled', false).show();
                }
            });
        }
        
        // Initial calculation
        setTimeout(calculateDays, 500);

        setTimeout(() => {
            var employee_id = $('#employee_id').val();
            if (employee_id) {
                $('#employee_id').trigger('change');
            }
        }, 100);
    });
</script>
