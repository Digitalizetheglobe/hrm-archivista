@extends('layouts.admin')

@section('page-title')
    {{ __('Generate Letter') }}
@endsection

@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Generate Letter') }}</h5>
    </div>
@endsection

@section('breadcrumb-item')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('letter_templates.index') }}">{{ __('Letter Templates') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Generate Letter') }}</li>
@endsection

@push('script-page')
    <script type="text/javascript">
        $(document).ready(function() {
            // Auto-generate date field
            $('#auto_date').click(function() {
                var today = new Date();
                var formattedDate = today.toISOString().split('T')[0];
                $('#date').val(formattedDate);
            });

            // Handle form submission with AJAX
            $('#generateForm').submit(function(e) {
                e.preventDefault();
                
                var submitBtn = $(this).find('button[type="submit"]');
                var originalText = submitBtn.html();
                
                // Show loading state
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ __("Generating...") }}');
                
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            // Open PDF in new tab for preview and download
                            window.open(response.download_url, '_blank');
                            
                            // Show success message
                            alert('{{ __("PDF generated successfully! The PDF has been opened in a new tab where you can preview and download it.") }}');
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
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Generate Letter from Template') }}: {{ $letterTemplate->name }}</h6>
                        <a href="{{ route('letter_templates.index') }}" class="btn btn-secondary btn-sm float-right">
                            <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('Fill in the Variables') }}</h5>
                                
                                <form method="POST" action="{{ route('letter_templates.generatePdf', $letterTemplate->id) }}" id="generateForm">
                                    @csrf
                                    
                                    @if (count($variables) > 0)
                                        @foreach ($variables as $variable)
                                            <div class="form-group">
                                                <label for="{{ $variable }}">
                                                    {{ ucwords(str_replace('_', ' ', $variable)) }}
                                                    @if (in_array($variable, ['employee_name', 'department', 'date']))
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                                
                                                @if ($variable == 'date')
                                                    <div class="input-group">
                                                        <input type="date" class="form-control" id="{{ $variable }}" name="{{ $variable }}" {{ in_array($variable, ['employee_name', 'department', 'date']) ? 'required' : '' }}>
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary" id="auto_date">{{ __('Today') }}</button>
                                                        </div>
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
                                            <i class="fas fa-info-circle"></i> {{ __('No variables found in this template. You can add variables like {employee_name}, {department}, {date} etc. in the template content.') }}
                                        </div>
                                    @endif
                                    
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success btn-lg" {{ count($variables) == 0 ? 'disabled' : '' }}>
                                            <i class="fas fa-file-pdf"></i> {{ __('Generate PDF') }}
                                        </button>
                                        <a href="{{ route('letter_templates.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> {{ __('Cancel') }}
                                        </a>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('Letter Preview') }}</h5>
                                <div class="border p-3 bg-light" style="min-height: 500px; max-height: 600px; overflow-y: auto;">
                                    <div id="preview-content">
                                        {!! $letterTemplate->content !!}
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <strong>{{ __('Note') }}:</strong> {{ __('This is a preview of the template. Variables will be replaced with the actual values you enter on the left when you generate the PDF.') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-info">{{ __('Template Information') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>{{ __('Template Name') }}:</strong> {{ $letterTemplate->name }}</p>
                                <p><strong>{{ __('Source Letter') }}:</strong> {{ $letterTemplate->source_letter ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>{{ __('Created Date') }}:</strong> {{ \Carbon\Carbon::parse($letterTemplate->created_at)->format('d M Y H:i') }}</p>
                                <p><strong>{{ __('Last Updated') }}:</strong> {{ \Carbon\Carbon::parse($letterTemplate->updated_at)->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6>{{ __('Variables Found in Template') }}:</h6>
                            @if (count($variables) > 0)
                                <div class="row">
                                    @foreach ($variables as $variable)
                                        <div class="col-md-3 mb-2">
                                            <span class="badge badge-info"><code>{{ '{' . $variable . '}' }}</code></span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">{{ __('No variables found in this template.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
