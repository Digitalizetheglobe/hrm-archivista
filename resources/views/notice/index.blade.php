@extends('layouts.admin')

@section('page-title')
    {{ __('Notice List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Notice List') }}</li>
@endsection


@if(auth()->user()->type != 'employee')
    @section('action-button')
        <div class="row align-items-center m-1">
            @if(auth()->user()->type == 'company')
                <a href="#" data-size="lg" data-url="{{ route('notices.create') }}" data-ajax-popup="true"
                    data-bs-toggle="tooltip" title="{{ __('Create New Notice') }}" data-title="{{ __('Add New Notice') }}"
                    class="btn btn-sm btn-primary">
                    <i class="ti ti-plus"></i>
                </a>
            @endif
        </div>
    @endsection
@endif  


@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th width="130px">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notices as $notice)
                                <tr>
                                    <td>{{ $notice->title }}</td>
                                    <td>{{ Str::limit($notice->description, 50) }}</td>
                                    <td class="d-flex gap-2">
                                        <!-- View Button -->
                                        <a href="#" 
                                            class="btn btn-sm btn-primary text-white" 
                                            data-bs-toggle="tooltip" 
                                            title="{{ __('View Notice') }}"
                                            onclick="event.preventDefault(); showNoticePopup('{{ $notice->title }}', '{{ $notice->description }}');">
                                            <i class="ti ti-eye"></i>
                                        </a>

                                        @if(auth()->user()->type == 'company')
                                            <!-- Edit Button -->
                                            <a href="#" 
                                                class="btn btn-sm btn-info text-white" 
                                                data-url="{{ route('notices.edit', $notice->id) }}" 
                                                data-ajax-popup="true" 
                                                data-size="lg" 
                                                data-bs-toggle="tooltip" 
                                                data-title="{{ __('Edit Notice') }}" 
                                                data-bs-original-title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil"></i>
                                            </a>

                                            <!-- Delete Button with Form -->
                                            <form id="delete-form-{{ $notice->id }}" method="POST" action="{{ route('notices.destroy', $notice->id) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                            </form>

                                            <a href="#" class="btn btn-sm btn-danger text-white"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Delete Notice') }}"
                                                onclick="event.preventDefault();  document.getElementById('delete-form-{{ $notice->id }}').submit();">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                        @endif
  
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
    function showNoticePopup(title, description) {
        // Create modal HTML
        const modalHtml = `
            <div class="modal fade" id="noticeModal" tabindex="-1" aria-labelledby="noticeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="noticeModalLabel">{{ __('Notice Details') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong>{{ __('Title') }}:</strong>
                                <p>` + title + `</p>
                            </div>
                            <div class="mb-3">
                                <strong>{{ __('Description') }}:</strong>
                                <p>` + description + `</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('noticeModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to body and show it
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('noticeModal'));
        modal.show();
        
        // Remove modal from DOM when hidden
        document.getElementById('noticeModal').addEventListener('hidden.bs.modal', function () {
            this.remove();
        });
    }
    
    $(document).ready(function() {
        $('#pc-dt-simple').DataTable({
            "language": {
                "emptyTable": "No notices found"
            },
            "lengthMenu": [10, 25, 50, 100],
        });
    });
</script>
@endpush
