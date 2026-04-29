@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Allowance') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Allowance') }}</li>
@endsection

@if(auth()->user()->type != 'employee')
    @section('action-button')
        <div class="row align-items-center m-1">
            @if(auth()->user()->type == 'company')
                <a href="#" data-bs-toggle="modal" data-bs-target="#createAllowanceModal"
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
                                <th>{{ __('Employee ') }}</th>
                                <th>{{ __('Allowance Type') }}</th>
                                <th>{{ __('Month') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Remark') }}</th>
                                <th width="130px">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allowances as $allowance)
                                <tr class="{{ session('new_allowance_id') == $allowance->id ?  : '' }}">
                                    <td>{{ $allowance->employee ? $allowance->employee->name : '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $allowance->allowance_type == 'Leave Encashment' ? 'success' : ($allowance->allowance_type == 'Site Expenses' ? 'info' : 'warning') }}">
                                            {{ $allowance->allowance_type }}
                                        </span>
                                    </td>
                                    <td>{{ $allowance->month ? \Carbon\Carbon::createFromFormat('Y-m', $allowance->month)->format('F Y') : '-' }}</td>
                                    <td>{{ number_format($allowance->amount, 2) }}</td>
                                    <td>{{ $allowance->remark ?: '-' }}</td>
                                    <td class="d-flex gap-2">
                                        @if(auth()->user()->type == 'company')
                                            <!-- Edit Button -->
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" 
                                                    class="mx-3 btn btn-sm btn-info text-white" 
                                                    data-url="{{ route('allowance.edit', $allowance) }}" 
                                                    data-ajax-popup="true" 
                                                    data-size="lg" 
                                                    data-bs-toggle="tooltip" 
                                                    data-title="{{ __('Edit Allowance') }}" 
                                                    data-bs-original-title="{{ __('Edit') }}">
                                                    <i class="ti ti-pencil"></i>
                                                </a>
                                            </div>

                                            <!-- Delete Button -->
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open([
                                                    'method' => 'DELETE',
                                                    'route' => ['allowance.destroy', $allowance->id],
                                                    'id' => 'delete-form-' . $allowance->id
                                                ]) !!}
                                                <a href="#"
                                                    class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                    data-bs-toggle="tooltip" title="{{ __('Delete Allowance') }}"
                                                    data-bs-original-title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"
                                                    onclick="event.preventDefault(); document.getElementById('delete-form-{{ $allowance->id }}').submit();">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('No records found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Create Modal -->
@include('allowance.create', ['employees' => $employees ?? [], 'allowanceTypes' => \App\Models\Allowance::allowanceTypes(), 'monthOptions' => \App\Models\Allowance::monthOptions()])
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Load create form
        $('a[data-bs-target="#allowanceModal"]').click(function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            var title = $(this).data('title');
            
            $('.modal-title').text(title);
            $('.modal-body').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
            
            $.get(url, function(data) {
                $('.modal-body').html(data);
            });
        });
        
        // Handle delete
        $('.delete-btn').click(function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            var confirmMsg = $(this).data('confirm');
            
            if (confirm(confirmMsg)) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        location.reload();
                    },
                    error: function(response) {
                        alert('Error: ' + response.responseJSON.message);
                    }
                });
            }
        });
    });
</script>
@endpush
