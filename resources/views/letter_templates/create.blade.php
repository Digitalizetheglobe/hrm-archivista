@extends('layouts.admin')

@section('page-title')
    {{ __('Create Letter Template') }}
@endsection

@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Create Letter Template') }}</h5>
    </div>
@endsection

@section('breadcrumb-item')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('letter_templates.index') }}">{{ __('Letter Templates') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Create') }}</li>
@endsection

@push('script-page')
    <!-- Include Summernote CSS and JS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
    
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize Summernote
            $('#content').summernote({
                height: 400,
                toolbar: [
                    ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['para', ['ul', 'ol', 'paragraph', 'height']],
                    ['insert', ['link', 'picture', 'video', 'table', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });

            // Load letter content when dropdown changes
            $('#source_letter').change(function() {
                var letterName = $(this).val();
                if (letterName) {
                    $.ajax({
                        url: '{{ route("letter_templates.loadContent") }}',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            letter_name: letterName
                        },
                        success: function(response) {
                            $('#content').summernote('code', response.content);
                        },
                        error: function(xhr) {
                            alert('Error loading letter content: ' + xhr.responseJSON.error);
                        }
                    });
                }
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
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Create Letter Template') }}</h6>
                        <a href="{{ route('letter_templates.index') }}" class="btn btn-secondary btn-sm float-right">
                            <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('letter_templates.store') }}">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">{{ __('Template Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="source_letter">{{ __('Select Letter Template (Optional)') }}</label>
                                        <select class="form-control" id="source_letter" name="source_letter">
                                            <option value="">{{ __('Select a letter to load content...') }}</option>
                                            @foreach ($letters as $letter)
                                                <option value="{{ $letter }}">{{ $letter }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">{{ __('Select a letter to load its content as a starting point') }}</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="content">{{ __('Template Content') }} <span class="text-danger">*</span></label>
                                <textarea id="content" name="content" class="form-control" required>{{ old('content') }}</textarea>
                                <small class="text-muted">
                                    {{ __('You can use variables like {employee_name}, {department}, {date}, etc. These will be replaced when generating letters.') }}
                                </small>
                                @error('content')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <div class="float-right">
                                    <a href="{{ route('letter_templates.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ __('Save Template') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-info">{{ __('Available Variables') }}</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">{{ __('You can use the following variables in your template. They will be replaced with actual values when generating letters:') }}</p>
                        <div class="row">
                            <div class="col-md-4">
                                <ul class="list-unstyled">
                                    <li><code>{employee_name}</code> - {{ __('Employee Name') }}</li>
                                    <li><code>{department}</code> - {{ __('Department') }}</li>
                                    <li><code>{designation}</code> - {{ __('Designation') }}</li>
                                    <li><code>{date}</code> - {{ __('Current Date') }}</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <ul class="list-unstyled">
                                    <li><code>{company_name}</code> - {{ __('Company Name') }}</li>
                                    <li><code>{address}</code> - {{ __('Address') }}</li>
                                    <li><code>{phone}</code> - {{ __('Phone Number') }}</li>
                                    <li><code>{email}</code> - {{ __('Email Address') }}</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <ul class="list-unstyled">
                                    <li><code>{salary}</code> - {{ __('Salary') }}</li>
                                    <li><code>{join_date}</code> - {{ __('Joining Date') }}</li>
                                    <li><code>{reference}</code> - {{ __('Reference Number') }}</li>
                                    <li><code>{custom_field}</code> - {{ __('Any custom field') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
