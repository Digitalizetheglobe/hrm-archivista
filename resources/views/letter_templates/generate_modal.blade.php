<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <h5 class="mb-3">{{ __('Fill in the Variables') }}</h5>
            
            <form method="POST" action="{{ route('letter_templates.generatePdf', $letterTemplate->id) }}" id="generateFormModal">
                @csrf
                
                @if (count($variables) > 0)
                    @foreach ($variables as $variable)
                        <div class="form-group mb-3">
                            <label for="{{ $variable }}" class="form-label">
                                {{ ucwords(str_replace('_', ' ', $variable)) }}
                                @if (in_array($variable, ['employee_name', 'department', 'date']))
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            
                            @if ($variable == 'date')
                                <div class="input-group">
                                    <input type="date" class="form-control" id="{{ $variable }}" name="{{ $variable }}" {{ in_array($variable, ['employee_name', 'department', 'date']) ? 'required' : '' }}>
                                    <button type="button" class="btn btn-outline-secondary" id="auto_date_modal">{{ __('Today') }}</button>
                                </div>
                            @elseif ($variable == 'email')
                                <input type="email" class="form-control" id="{{ $variable }}" name="{{ $variable }}" {{ in_array($variable, ['employee_name', 'department', 'date']) ? 'required' : '' }}>
                            @elseif ($variable == 'phone')
                                <input type="tel" class="form-control" id="{{ $variable }}" name="{{ $variable }}" {{ in_array($variable, ['employee_name', 'department', 'date']) ? 'required' : '' }}>
                            @elseif (in_array($variable, ['address', 'notes', 'description']))
                                <textarea class="form-control" id="{{ $variable }}" name="{{ $variable }}" rows="3" {{ in_array($variable, ['employee_name', 'department', 'date']) ? 'required' : '' }}></textarea>
                            @else
                                <input type="text" class="form-control" id="{{ $variable }}" name="{{ $variable }}" {{ in_array($variable, ['employee_name', 'department', 'date']) ? 'required' : '' }}>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> {{ __('No variables found in this template.') }}
                    </div>
                @endif
                
                <div class="modal-footer px-0 pb-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="generateBtnModal" {{ count($variables) == 0 ? 'disabled' : '' }}>
                        <i class="fas fa-file-pdf me-1"></i> {{ __('Generate PDF') }}
                    </button>
                </div>
            </form>
        </div>
        
        <div class="col-md-6">
            <h5 class="mb-3">{{ __('Letter Preview') }}</h5>
            <div class="border p-3 bg-light" style="min-height: 400px; max-height: 500px; overflow-y: auto; font-size: 0.9rem;">
                <div id="preview-content">
                    {!! $letterTemplate->content !!}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Use a namespaced event to avoid double-binding if modal is re-opened
        $('#auto_date_modal').off('click').on('click', function() {
            var today = new Date();
            var formattedDate = today.toISOString().split('T')[0];
            $('#date').val(formattedDate);
        });

        // Use delegated event listener on form to prevent multiple bindings issues
        $('#generateFormModal').off('submit').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var submitBtn = form.find('#generateBtnModal');
            var originalText = submitBtn.html();
            
            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Generating...") }}');
            
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        // Create a hidden link to trigger direct download without new tab
                        var downloadLink = document.createElement("a");
                        downloadLink.href = response.download_url;
                        downloadLink.download = response.filename || 'letter.pdf';
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                        
                        // Close the modal
                        $('#commonModal').modal('hide');
                        
                        // Show success message using Toastr if available, or fallback
                        if (window.show_toastr) {
                            show_toastr('Success', '{{ __("Letter generated successfully!") }}', 'success');
                        } else {
                            alert('{{ __("Letter generated successfully!") }}');
                        }
                    }
                },
                error: function(xhr) {
                    var error = xhr.responseJSON ? xhr.responseJSON.error : '{{ __("An error occurred while generating the PDF.") }}';
                    alert(error);
                },
                complete: function() {
                    // Restore button state
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
</script>
