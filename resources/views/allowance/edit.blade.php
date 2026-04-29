<form method="POST" action="{{ route('allowance.update', $allowance) }}">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="employee_id" class="form-label">{{ __('Employee Name') }} <span class="text-danger">*</span></label>
                    <select name="employee_id" id="employee_id" class="form-control" required>
                        <option value="">{{ __('Select Employee') }}</option>
                        @foreach($employees as $id => $name)
                            <option value="{{ $id }}" {{ old('employee_id', $allowance->employee_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            new Choices('#employee_id', {
                                searchEnabled: true,
                                searchPlaceholderText: '{{ __("Search Employee...") }}',
                                noResultsText: '{{ __("No results found") }}',
                                itemSelectText: '{{ __("Press to select") }}',
                            });
                        });
                    </script>
                    @error('employee_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="allowance_type" class="form-label">{{ __('Allowance Type') }} <span class="text-danger">*</span></label>
                    <select name="allowance_type" id="allowance_type" class="form-control" required>
                        <option value="">{{ __('Select Type') }}</option>
                        @foreach($allowanceTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('allowance_type', $allowance->allowance_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('allowance_type')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="month" class="form-label">{{ __('Month') }} <span class="text-danger">*</span></label>
                    <select name="month" id="month" class="form-control" required>
                        <option value="">{{ __('Select Month') }}</option>
                        @foreach($monthOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('month', $allowance->month) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('month')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="amount" class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                    <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0" value="{{ old('amount', $allowance->amount) }}" required>
                    @error('amount')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="form-group">
                    <label for="remark" class="form-label">{{ __('Remark') }} ({{ __('Optional') }})</label>
                    <textarea name="remark" id="remark" class="form-control" rows="3">{{ old('remark', $allowance->remark) }}</textarea>
                    @error('remark')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
    </div>
</form>
