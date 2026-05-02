    @extends('layouts.admin')

    @section('page-title')
        {{ __('Project List') }}
    @endsection

    @section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
        <li class="breadcrumb-item">{{ __('Project List') }}</li>
    @endsection 

    @section('action-button')
    @can('Create Employee')
        <a href="#" data-url="{{ route('projects.create') }}" data-ajax-popup="true"
            data-title="{{ __('Add New Project') }}" data-size="lg" data-bs-toggle="tooltip" title="Create"
            class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        
        {{-- Import Button with same popup style --}}

        <a href="#" data-url="{{ route('projects.import') }}" data-ajax-popup="true"
            data-title="{{ __('Import Projects') }}" data-size="md" data-bs-toggle="tooltip" title="Import"
            class="btn btn-sm btn-primary">
            <i class="ti ti-file-import"></i>
        </a>
        
        {{-- Export Button --}}
        <a href="{{ route('projects.export') }}" data-bs-toggle="tooltip" title="Export"
            class="btn btn-sm btn-primary">
            <i class="ti ti-file-export"></i>
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
                                    <th>{{ __('Project Name') }}</th>
                                    <th>{{ __('Client Name') }}</th>
                                    @if (Gate::check('Edit Meeting') || Gate::check('Delete Meeting'))
                                            <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($projects as $project)
                                    <tr>
                                        <td>{{ $project->project_name }}</td>
                                        <td>{{ $project->client->client_name ?? 'N/A' }}</td>                                       
                                        @if (Gate::check('Edit Meeting') || Gate::check('Delete Meeting'))

                                            <td class="Action">
                                                <span>
                                                    <!-- Edit Button -->
                                                    @can('Edit Meeting')
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" 
                                                            class="mx-3 btn btn-sm align-items-center" 
                                                            data-url="{{ route('projects.edit', $project->id) }}" 
                                                            data-ajax-popup="true" 
                                                            data-size="lg" 
                                                            data-bs-toggle="tooltip" 
                                                            data-title="{{ __('Edit Project') }}" 
                                                            data-bs-original-title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan

                                                    <!-- Delete Button -->
                                                    @can('Delete Meeting')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open([
                                                                'method' => 'DELETE', 
                                                                'route' => ['projects.destroy', $project->id], 
                                                                'style' => 'display:inline'
                                                            ]) !!}
                                                            <a href="#" 
                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para" 
                                                            data-bs-toggle="tooltip" 
                                                            title="{{ __('Delete Project') }}" 
                                                            data-bs-original-title="{{ __('Delete') }}" 
                                                            aria-label="{{ __('Delete') }}" 
                                                            onclick="event.preventDefault(); document.getElementById('delete-form-{{ $project->id }}').submit();">
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
                                        <td colspan="{{ Auth::user()->type != 'employee' ? '6' : '5' }}" class="text-center">{{ __('No projects found') }}</td>
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
