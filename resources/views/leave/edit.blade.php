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
        <div class="mb-3">
            <div class="lf-section-label">Leave Type <span class="text-danger">*</span></div>
            <div class="lf-input-icon-wrap">
                <i class="fas fa-list lf-icon"></i>
                <select name="leave_type_id" id="leave_type_id" class="form-control lf-form-control">
                    @foreach ($leavetypes as $type)
                        <option value="{{ $type->id }}" @if($leave->leave_type_id == $type->id) selected @endif>
                            {{ $type->title }} ({{ $type->days }} days)
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Leave Duration Pills --}}
        <div class="mb-3">
            <div class="lf-section-label">Leave Duration</div>
            <div class="lf-pill-group">
                <input type="radio" name="leave_duration" id="full_day" value="full_day" @if($leave->leave_duration == 'full_day' || empty($leave->leave_duration)) checked @endif>
                <label for="full_day"><i class="fas fa-sun"></i> Full Day</label>
                <input type="radio" name="leave_duration" id="half_day" value="half_day" @if($leave->leave_duration == 'half_day') checked @endif>
                <label for="half_day"><i class="fas fa-adjust"></i> Half Day</label>
            </div>
        </div>

        {{-- Half Day Option --}}
        <div id="half_day_type_container" style="{{ $leave->leave_duration == 'half_day' ? '' : 'display: none;' }}" class="mb-3">
            <div class="lf-section-label">Half Day Session</div>
            <div class="lf-pill-group orange">
                <input type="radio" name="half_day_type" id="first_half" value="first_half" @if($leave->half_day_type == 'first_half') checked @endif>
                <label for="first_half"><i class="fas fa-cloud-sun"></i> First Half</label>
                <input type="radio" name="half_day_type" id="second_half" value="second_half" @if($leave->half_day_type == 'second_half') checked @endif>
                <label for="second_half"><i class="fas fa-cloud-moon"></i> Second Half</label>
            </div>
        </div>

        {{-- Dates --}}
        <div class="row g-3">
            <div class="col-md-6" id="start_date_container">
                <div class="lf-section-label">Start Date</div>
                <div class="lf-input-icon-wrap">
                    <i class="fas fa-calendar-day lf-icon"></i>
                    {{ Form::text('start_date', null, ['class' => 'form-control lf-form-control d_week', 'autocomplete' => 'off', 'id' => 'start_date']) }}
                </div>
            </div>
            <div class="col-md-6" id="end_date_container" style="{{ $leave->leave_duration == 'half_day' ? 'display: none;' : '' }}">
                <div class="lf-section-label">End Date</div>
                <div class="lf-input-icon-wrap">
                    <i class="fas fa-calendar-check lf-icon"></i>
                    {{ Form::text('end_date', null, ['class' => 'form-control lf-form-control d_week', 'autocomplete' => 'off', 'id' => 'end_date']) }}
                </div>
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
        <div class="mb-2">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <div class="lf-section-label mb-0">Remark <span class="text-danger">*</span></div>
                @if ($plan->enable_chatgpt == 'on')
                    <a href="#" data-size="md" class="btn btn-outline-primary btn-sm px-2 py-1" data-ajax-popup-over="true"
                        id="grammarCheck" data-url="{{ route('grammar', ['grammar']) }}" data-bs-placement="top"
                        data-title="{{ __('Grammar check with AI') }}" style="font-size:0.72rem;">
                        <i class="ti ti-rotate me-1"></i>{{ __('Grammar check') }}
                    </a>
                @endif
            </div>
            {{ Form::textarea('remark', null, ['class' => 'form-control lf-form-control grammer_textarea', 'required' => 'required', 'placeholder' => __('Additional remarks...'), 'rows' => '2']) }}
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

        setTimeout(() => {
            var employee_id = $('#employee_id').val();
            if (employee_id) {
                $('#employee_id').trigger('change');
            }
        }, 100);
    });
</script>
