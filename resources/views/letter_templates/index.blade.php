@extends('layouts.admin')

@section('page-title')
    {{ __('Letter Templates') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Letter Templates') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('letter_templates.generated.index') }}" 
        class="btn btn-sm btn-info flex items-center space-x-2 mr-2">
        <i class="ti ti-file-text"></i>
        <span>{{ __('Generated Letters') }}</span>
    </a>
    <a href="{{ route('letter_templates.create') }}" 
        data-title="{{ __('Create Letter Template') }}" 
        class="btn btn-sm btn-primary flex items-center space-x-2">
        <i class="ti ti-plus"></i>
        <span>{{ __('Create') }}</span>
    </a>
@endsection

@section('content')
    <div class="row">
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __('Letter Templates') }}</h5>
                    <br>
                    <div class="table-responsive">
                        @if ($letterTemplates->count() > 0)
                            <table class="table" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>{{ __('Template Name') }}</th>
                                        <th>{{ __('Source Letter') }}</th>
                                        <th>{{ __('Created Date') }}</th>
                                        <th class="text-right">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($letterTemplates as $template)
                                        <tr>
                                            <td>{{ $template->name }}</td>
                                            <td>{{ $template->source_letter ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($template->created_at)->format('d M Y') }}</td>
                                            <td class="text-right">
                                                @if (\Auth::user()->type != 'employee')
                                                    <div class="action-btn bg-primary">
                                                        <a href="{{ route('letter_templates.edit', $template->id) }}" 
                                                            class="mx-3 btn btn-sm align-items-center text-white"
                                                            data-size="lg"
                                                            data-url="{{ route('letter_templates.edit', $template->id) }}"
                                                            data-ajax-popup="true" 
                                                            data-size="md" 
                                                            data-bs-toggle="tooltip"
                                                            title="" 
                                                            data-title="{{ __('Edit Template') }}"
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                    
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="{{ route('letter_templates.generate', $template->id) }}" 
                                                            class="mx-3 btn btn-sm align-items-center text-white"
                                                            data-size="lg"
                                                            data-url="{{ route('letter_templates.generate', $template->id) }}"
                                                            data-ajax-popup="true" 
                                                            data-size="md" 
                                                            data-bs-toggle="tooltip"
                                                            title="" 
                                                            data-title="{{ __('Generate Letter') }}"
                                                            data-bs-original-title="{{ __('Generate') }}">
                                                            <i class="ti ti-file text-white"></i>
                                                        </a>
                                                    </div>
                                                    
                                                    <div class="action-btn bg-danger ms-2">
                                                        <a href="#" 
                                                            class="mx-3 btn btn-sm align-items-center text-white"
                                                            data-size="lg"
                                                            data-url="{{ route('letter_templates.destroy', $template->id) }}"
                                                            data-ajax-popup="true" 
                                                            data-size="md" 
                                                            data-bs-toggle="tooltip"
                                                            title="" 
                                                            data-title="{{ __('Delete Template') }}"
                                                            data-bs-original-title="{{ __('Delete') }}"
                                                            onclick="if(confirm('{{ __('Are you sure to delete this template?') }}')) { 
                                                                var form = document.createElement('form');
                                                                form.method = 'POST';
                                                                form.action = '{{ route('letter_templates.destroy', $template->id) }}';
                                                                var csrf = document.createElement('input');
                                                                csrf.type = 'hidden';
                                                                csrf.name = '_token';
                                                                csrf.value = '{{ csrf_token() }}';
                                                                form.appendChild(csrf);
                                                                var method = document.createElement('input');
                                                                method.type = 'hidden';
                                                                method.name = '_method';
                                                                method.value = 'DELETE';
                                                                form.appendChild(method);
                                                                document.body.appendChild(form);
                                                                form.submit();
                                                            }">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-center py-8">
                                <i class="ti ti-file text-gray-300 fa-3x mb-3"></i>
                                <h5 class="text-gray-400">{{ __('No letter templates found.') }}</h5>
                                <p class="text-gray-400">{{ __('Create your first letter template to get started.') }}</p>
                                <a href="{{ route('letter_templates.create') }}" class="btn btn-primary">
                                    <i class="ti ti-plus"></i> {{ __('Create Letter Template') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
