@extends('layouts.admin') {{-- or your layout --}}

@section('page-title')
    {{ __('Vendor Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('vendors.contractor') }}">{{ __('Contractor List') }}</a></li>
    <li class="breadcrumb-item">{{ __('View Details') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card p-4">
            <h4 class="mb-3">{{ $vendor->name }}</h4>

            <div class="row mb-3">
                <div class="col-md-6"><strong>Address:</strong> {{ $vendor->address }}</div>
                <div class="col-md-6"><strong>Contact Person:</strong> {{ $vendor->contact_person }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6"><strong>Phone:</strong> {{ $vendor->contact_person_phone }}</div>
                <div class="col-md-6"><strong>Email:</strong> {{ $vendor->email }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6"><strong>Company Website:</strong> <a href="{{ $vendor->company_website }}" target="_blank">{{ $vendor->company_website }}</a></div>
                <div class="col-md-6"><strong>Experience:</strong> {{ $vendor->experience }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6"><strong>Plan Location:</strong> {{ $vendor->plan_location }}</div>
                <div class="col-md-6"><strong>Project Range:</strong> {{ $vendor->project_range }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6"><strong>Type of Work:</strong> {{ $vendor->type_of_work }}</div>
                <div class="col-md-6"><strong>Accurate Accessible Area:</strong> {{ $vendor->accurate_accessible_area }}</div>
            </div>

            <div class="mb-3">
                <strong>Last 3 Years Turnover:</strong>
                <ul>
@foreach ($vendor->turnover as $year => $amount)
                        <li><strong>{{ $year }}:</strong> ₹{{ number_format($amount, 2) }}</li>
                    @endforeach
                </ul>
            </div>

            <a href="{{ route('vendors.contractor') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
