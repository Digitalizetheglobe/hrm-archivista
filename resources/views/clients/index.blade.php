@extends('layouts.admin')

@section('page-title')
    {{ __('Client List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Client List') }}</li>
@endsection 

@section('action-button')
    @can('Create Employee')
        <a href="#" data-url="{{ route('clients.create') }}" data-ajax-popup="true"
            data-title="{{ __('Add New Client') }}" data-size="lg" data-bs-toggle="tooltip" title="Create"
            class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        
        {{-- Import Button with same popup style --}}
        <a href="#" data-url="{{ route('clients.import') }}" data-ajax-popup="true"
            data-title="{{ __('Import Clients') }}" data-size="md" data-bs-toggle="tooltip" title="Import"
            class="btn btn-sm btn-primary">
            <i class="ti ti-file-import"></i>
        </a>
    @endcan
@endsection 

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Client Code') }}</th>
                                <th>{{ __('Client Name') }}</th>
                                <th>{{ __('Company Email') }}</th>
                                <th>{{ __('Company Phone') }}</th>
                                @if (Gate::check('Edit Employee') || Gate::check('Delete Employee'))
                                    <th width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clients as $client)
                                <tr>
                                    <td>{{ $client->client_code }}</td>
                                    <td>{{ $client->client_name }}</td>
                                    <td>{{ $client->company_email }}</td>
                                    <td>{{ $client->company_phone }}</td>
                                    @if (Gate::check('Edit Employee') || Gate::check('Delete Employee'))
                                        <td class="Action">
                                            <span>
                                                <!-- Edit Button -->
                                                @can('Edit Employee')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" 
                                                        class="mx-3 btn btn-sm align-items-center" 
                                                        data-url="{{ route('clients.edit', $client->id) }}" 
                                                        data-ajax-popup="true" 
                                                        data-size="lg" 
                                                        data-bs-toggle="tooltip" 
                                                        data-title="{{ __('Edit Client') }}" 
                                                        data-bs-original-title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan

                                                <!-- Delete Button -->
                                                @can('Delete Employee')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open([
                                                            'method' => 'DELETE', 
                                                            'route' => ['clients.destroy', $client->id], 
                                                            'style' => 'display:inline'
                                                        ]) !!}
                                                        <a href="#" 
                                                        class="mx-3 btn btn-sm align-items-center bs-pass-para" 
                                                        data-bs-toggle="tooltip" 
                                                        title="{{ __('Delete Client') }}" 
                                                        data-bs-original-title="{{ __('Delete') }}" 
                                                        aria-label="{{ __('Delete') }}" 
                                                        onclick="event.preventDefault(); document.getElementById('delete-form-{{ $client->id }}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">{{ __('No clients found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection