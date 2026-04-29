@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Other Deduction') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Other Deduction') }}</li>
@endsection

@if(auth()->user()->type != 'employee')
    @section('action-button')
        <div class="row align-items-center m-1">
            @if(auth()->user()->type == 'company')
                <a href="#" data-size="lg" data-url="{{ route('saturationdeduction.create') }}" data-ajax-popup="true"
                    data-bs-toggle="tooltip" title="{{ __('Create New Other Deduction') }}" data-title="{{ __('Add New Other Deduction') }}"
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
                                <th>{{ __('Employee Name') }}</th>
                                <th>{{ __('Deduction Type') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th width="130px">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($saturationDeductions as $saturationDeduction)
                                <tr>
                                    <td>{{ $saturationDeduction->employee ? $saturationDeduction->employee->name : '-' }}</td>
                                    <td>{{ $saturationDeduction->deductionOption ? $saturationDeduction->deductionOption->name : '-' }}</td>
                                    <td>{{ $saturationDeduction->title }}</td>
                                    <td>
                                        <span class="badge bg-{{ $saturationDeduction->type == 'fixed' ? 'primary' : 'info' }}">
                                            {{ ucfirst($saturationDeduction->type) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($saturationDeduction->amount, 2) }}</td>
                                    <td class="d-flex gap-2">
                                        @if(auth()->user()->type == 'company')
                                            <!-- Edit Button -->
                                            <a href="#" 
                                                class="btn btn-sm btn-info text-white" 
                                                data-url="{{ route('saturationdeduction.edit', $saturationDeduction->id) }}" 
                                                data-ajax-popup="true" 
                                                data-size="lg" 
                                                data-bs-toggle="tooltip" 
                                                data-title="{{ __('Edit Other Deduction') }}" 
                                                data-bs-original-title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil"></i>
                                            </a>

                                            <!-- Delete Button with Form -->
                                            <form id="delete-form-{{ $saturationDeduction->id }}" method="POST" action="{{ route('saturationdeduction.destroy', $saturationDeduction->id) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                            </form>

                                            <a href="#" class="btn btn-sm btn-danger text-white"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Delete Other Deduction') }}"
                                                onclick="event.preventDefault();  document.getElementById('delete-form-{{ $saturationDeduction->id }}').submit();">
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
