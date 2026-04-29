@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Deduction') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Deduction') }}</li>
@endsection

@if(auth()->user()->type != 'employee')
    @section('action-button')
        <div class="row align-items-center m-1">
            @if(auth()->user()->type == 'company')
                <a href="#" data-bs-toggle="modal" data-bs-target="#createDeductionModal"
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
                                <th>{{ __('Deduction Type') }}</th>
                                <th>{{ __('Month') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Remark') }}</th>
                                <th width="130px">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deductions as $deduction)
                                <tr class="{{ session('new_deduction_id') == $deduction->id ?  : '' }}">
                                    <td>{{ $deduction->employee ? $deduction->employee->name : '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $deduction->deduction_type == 'MLWF' ? 'primary' : 'secondary' }}">
                                            {{ $deduction->deduction_type }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $deduction->month)->format('F Y') }}</td>
                                    <td>{{ number_format($deduction->amount, 2) }}</td>
                                    <td>{{ $deduction->remark ?: '-' }}</td>
                                    <td class="d-flex gap-2">
                                        @if(auth()->user()->type == 'company')
                                            <!-- Edit Button -->
                                            <a href="#" 
                                                class="btn btn-sm btn-info text-white" 
                                                data-url="{{ route('deduction.edit', $deduction) }}" 
                                                data-ajax-popup="true" 
                                                data-size="lg" 
                                                data-bs-toggle="tooltip" 
                                                data-title="{{ __('Edit Deduction') }}" 
                                                data-bs-original-title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil"></i>
                                            </a>

                                            <!-- Delete Button -->
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open([
                                                    'method' => 'DELETE',
                                                    'route' => ['deduction.destroy', $deduction->id],
                                                    'id' => 'delete-form-' . $deduction->id
                                                ]) !!}
                                                <a href="#"
                                                    class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                    data-bs-toggle="tooltip" title="{{ __('Delete Deduction') }}"
                                                    data-bs-original-title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}"
                                                    onclick="event.preventDefault(); document.getElementById('delete-form-{{ $deduction->id }}').submit();">
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
@include('deduction.create', ['employees' => $employees ?? [], 'deductionTypes' => \App\Models\Deduction::deductionTypes(), 'monthOptions' => \App\Models\Deduction::monthOptions()])
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Load create form
        $('a[data-bs-target="#deductionModal"]').click(function(e) {
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
