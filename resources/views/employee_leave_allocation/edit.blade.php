{{ Form::open(['route' => ['employee-leave-allocations.update', $employee->id], 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <p class="text-muted">
                {{ __('Enter a number to ADD extra leaves to this employee\'s current balance. These extra leaves will carry forward according to your settings. Leave blank if no extra leaves are needed.') }}
            </p>
        </div>
        
        @foreach ($leaveTypes as $leaveType)
            <div class="form-group col-md-6">
                {{ Form::label('allocations[' . $leaveType->id . ']', 'Add Extra ' . $leaveType->title, ['class' => 'col-form-label']) }}
                <div class="input-group">
                    {{ Form::number('allocations[' . $leaveType->id . ']', '', ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'placeholder' => __('Enter extra days to add')]) }}
                    <span class="input-group-text">{{ __('Days') }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Add Extra Leaves') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
