<div class="modal fade" id="createAllowanceModal" tabindex="-1" role="dialog" aria-labelledby="createAllowanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createAllowanceModalLabel">{{ __('Create Allowance') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createAllowanceForm" method="POST" action="{{ route('allowance.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="form-label">{{ __('Employee') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="employee_id" name="employee_id" required>
                                <option value="">{{ __('Select Employee') }}</option>
                                @foreach($employees as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('Select an employee') }}</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">{{ __('Allowance Type') }} <span class="text-danger">*</span></label>
                            <select class="form-control" id="allowance_type" name="allowance_type" required>
                                <option value="">{{ __('Select Type') }}</option>
                                @foreach($allowanceTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('Select allowance type') }}</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">{{ __('Month') }} <span class="text-danger">*</span></label>
                            <input type="month" class="form-control" id="month" name="month" required>
                            <small class="text-muted">{{ __('Select month for allowance') }}</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ \Auth::user()->currencySymbol() }}</span>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required placeholder="{{ __('Enter amount') }}">
                            </div>
                            <small class="text-muted">{{ __('Enter allowance amount') }}</small>
                        </div>
                        <div class="form-group col-md-12">
                            <label class="form-label">{{ __('Remark') }}</label>
                            <textarea class="form-control" id="remark" name="remark" rows="3" placeholder="{{ __('Enter remark (optional)') }}"></textarea>
                            <small class="text-muted">{{ __('Optional remark for this allowance') }}</small>
                        </div>
                    </div>
                    <div id="formErrors" class="alert alert-danger" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Choices('#employee_id', {
            searchEnabled: true,
            searchPlaceholderText: '{{ __("Search Employee...") }}',
            noResultsText: '{{ __("No results found") }}',
            itemSelectText: '{{ __("Press to select") }}',
        });
        
        // Form submission
        document.getElementById('createAllowanceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const errorDiv = document.getElementById('formErrors');
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Creating...") }}';
            errorDiv.style.display = 'none';
            
            fetch('{{ route("allowance.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                console.log('Response status:', response.status); // Debug log
                
                // Close modal immediately
                const modal = bootstrap.Modal.getInstance(document.getElementById('createAllowanceModal'));
                modal.hide();
                
                // Always reload the page after form submission
                // This ensures the new data is visible immediately
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                errorDiv.innerHTML = '{{ __("An error occurred. Please try again.") }}';
                errorDiv.style.display = 'block';
                
                // Fallback: reload page anyway to show any data that might have been saved
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = '{{ __("Create") }}';
            });
        });
    });
</script>
