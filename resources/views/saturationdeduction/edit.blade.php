<form method="POST" action="{{ route('saturationdeduction.update', $saturationdeduction) }}">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="employee_id" class="form-label">{{ __('Employee Name') }} <span class="text-danger">*</span></label>
                <select name="employee_id" id="employee_id" class="form-control" required>
                    <option value="">{{ __('Select Employee') }}</option>
                    @if(isset($saturationdeduction->employee))
                        <option value="{{ $saturationdeduction->employee_id }}" selected>{{ $saturationdeduction->employee->name }}</option>
                    @endif
                    @if(isset($employees))
                        @foreach($employees as $id => $name)
                            <option value="{{ $id }}" {{ old('employee_id', $saturationdeduction->employee_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    @endif
                </select>
                @error('employee_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="deduction_option" class="form-label">{{ __('Deduction Type') }} <span class="text-danger">*</span></label>
                <select name="deduction_option" id="deduction_option" class="form-control" required>
                    <option value="">{{ __('Select Type') }}</option>
                    @if(isset($deduction_options))
                        @foreach($deduction_options as $id => $name)
                            <option value="{{ $id }}" {{ old('deduction_option', $saturationdeduction->deduction_option) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    @endif
                </select>
                @error('deduction_option')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="title" class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $saturationdeduction->title) }}" required>
                @error('title')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="type" class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
                <select name="type" id="type" class="form-control" required>
                    <option value="">{{ __('Select Type') }}</option>
                    @if(isset($saturationdeduc))
                        @foreach($saturationdeduc as $value => $label)
                            <option value="{{ $value }}" {{ old('type', $saturationdeduction->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    @endif
                </select>
                @error('type')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="amount" class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0" value="{{ old('amount', $saturationdeduction->amount) }}" required>
                @error('amount')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
    </div>
</form>
