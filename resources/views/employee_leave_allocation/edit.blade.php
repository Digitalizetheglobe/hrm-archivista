{{ Form::open(['route' => ['employee-leave-allocations.update', $employee->id], 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <p class="text-muted">
                {{ __('Enter the number of extra leaves to allocate to this employee\'s current balance. These extra leaves will carry forward according to your settings. Set to 0 or leave blank to remove extra leaves.') }}
            </p>
        </div>
        
        @foreach ($leaveTypes as $leaveType)
            <div class="form-group col-md-6">
                {{ Form::label('allocations[' . $leaveType->id . ']', 'Extra ' . $leaveType->title, ['class' => 'col-form-label']) }}
                <div class="input-group">
                    {{ Form::number('allocations[' . $leaveType->id . ']', $allocations[$leaveType->id] ?? '', ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'placeholder' => __('Enter extra days')]) }}
                    <span class="input-group-text">{{ __('Days') }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Save Allocations') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
