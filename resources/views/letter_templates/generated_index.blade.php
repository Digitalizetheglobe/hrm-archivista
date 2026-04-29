@extends('layouts.admin')

@section('page-title')
    {{ __('Generated Letters') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('letter_templates.index') }}">{{ __('Letter Templates') }}</a></li>
    <li class="breadcrumb-item">{{ __('Generated Letters') }}</li>
@endsection

@section('action-button')
    <a href="{{ route('letter_templates.index') }}" 
        class="btn btn-sm btn-secondary flex items-center space-x-2 mr-2">
        <i class="ti ti-arrow-left"></i>
        <span>{{ __('Back to Templates') }}</span>
    </a>
    <a href="{{ route('letter_templates.create') }}" 
        data-title="{{ __('Create Letter Template') }}" 
        class="btn btn-sm btn-primary flex items-center space-x-2">
        <i class="ti ti-plus"></i>
        <span>{{ __('Generate Letter') }}</span>
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <h5>{{ __('Generated Letters') }}</h5>
                    <br>
                    
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <form method="GET" action="{{ route('letter_templates.generated.index') }}">
                                <div class="form-group">
                                    <select name="letter_template_id" id="letter_template_id" class="form-control" onchange="this.form.submit()">
                                        <option value="">{{ __('All Templates') }}</option>
                                        @foreach($letterTemplatesWithGenerated as $id => $name)
                                            <option value="{{ $id }}" {{ request('letter_template_id') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        @if ($generatedLetters->count() > 0)
                            <table class="table" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>{{ __('ID') }}</th>
                                        <th>{{ __('Letter Template') }}</th>
                                        <th>{{ __('Recipient Name') }}</th>
                                        <th>{{ __('Letter Date') }}</th>
                                        <th>{{ __('Generated Date') }}</th>
                                        <th class="text-right">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($generatedLetters as $letter)
                                        <tr>
                                            <td>{{ $letter->id }}</td>
                                            <td>
                                                <strong>{{ $letter->letterTemplate->name }}</strong>
                                                @if ($letter->letterTemplate->source_letter)
                                                    <br><small class="text-muted">{{ __('Source') }}: {{ $letter->letterTemplate->source_letter }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $letter->recipient_name }}</td>
                                            <td>{{ \Carbon\Carbon::parse($letter->letter_date)->format('d M Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($letter->created_at)->format('d M Y H:i') }}</td>
                                            <td class="text-right">
                                                @if (\Auth::user()->type != 'employee')
                                                    <div class="action-btn bg-info">
                                                        <a href="{{ route('letter_templates.generated.view', $letter->id) }}" 
                                                            class="mx-3 btn btn-sm align-items-center text-white"
                                                            target="_blank"
                                                            data-size="lg"
                                                            data-url="{{ route('letter_templates.generated.view', $letter->id) }}"
                                                            data-ajax-popup="true" 
                                                            data-size="md" 
                                                            data-bs-toggle="tooltip"
                                                            title="" 
                                                            data-title="{{ __('View Letter') }}"
                                                            data-bs-original-title="{{ __('View') }}">
                                                            <i class="ti ti-eye text-white"></i>
                                                        </a>
                                                    </div>
                                                    
                                                    <div class="action-btn bg-danger ms-2">
                                                        <a href="#" 
                                                            class="mx-3 btn btn-sm align-items-center text-white"
                                                            data-size="lg"
                                                            data-url="{{ route('letter_templates.generated.delete', $letter->id) }}"
                                                            data-ajax-popup="true" 
                                                            data-size="md" 
                                                            data-bs-toggle="tooltip"
                                                            title="" 
                                                            data-title="{{ __('Delete Letter') }}"
                                                            data-bs-original-title="{{ __('Delete') }}"
                                                            onclick="if(confirm('{{ __('Are you sure to delete this generated letter?') }}')) { 
                                                                var form = document.createElement('form');
                                                                form.method = 'POST';
                                                                form.action = '{{ route('letter_templates.generated.delete', $letter->id) }}';
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
                                <i class="ti ti-file-text text-gray-300 fa-3x mb-3"></i>
                                <h5 class="text-gray-400">{{ __('No generated letters found.') }}</h5>
                                <p class="text-gray-400">{{ __('Generate letters from templates to see them here.') }}</p>
                                <a href="{{ route('letter_templates.index') }}" class="btn btn-primary">
                                    <i class="ti ti-plus"></i> {{ __('Generate Letter') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
