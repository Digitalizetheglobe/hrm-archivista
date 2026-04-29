@extends('layouts.admin')

@section('page-title')
    {{ __('Supplier Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('vendors.supplier') }}">{{ __('Suppliers') }}</a></li>
    <li class="breadcrumb-item">{{ __('Detail') }}</li>
@endsection

@section('content')
<div class="container mt-3">
    <div class="card">
        <div class="card-header"><h4>Supplier Details</h4></div>
        <div class="card-body">

            <h5 class="text-lg font-bold mb-2">Contact Details</h5>
            <p><strong>Contact Date:</strong> {{ $vendor->contact_date }}</p>
            <p><strong>Name:</strong> {{ $vendor->name }}</p>
            <p><strong>Address:</strong> {{ $vendor->address }}</p>
            <p><strong>Contact Person:</strong> {{ $vendor->contact_person }}</p>
            <p><strong>Contact Person Phone:</strong> {{ $vendor->contact_person_phone }}</p>
            <p><strong>Email:</strong> {{ $vendor->email }}</p>
            <p><strong>Company Website:</strong> <a href="{{ $vendor->company_website }}" target="_blank">{{ $vendor->company_website }}</a></p>
            <p><strong>Experience:</strong> {{ $vendor->experience }}</p>
            <p><strong>Plan Location:</strong> {{ $vendor->plan_location }}</p>

            <hr>

            <h5 class="text-lg font-bold mb-2">Product & Category Details</h5>
            <p><strong>Category:</strong> {{ $vendor->category ? $vendor->category->name : '-' }}</p>
            <p><strong>Sub Category:</strong> {{ $vendor->subCategory ? $vendor->subCategory->name : '-' }}</p>
            <p><strong>Product:</strong> {{ $vendor->product }}</p>

            @if($vendor->product_image)
                <p><strong>Product Image:</strong> </p>
                <img src="{{ asset('storage/' . $vendor->product_image) }}" alt="Product Image" style="max-width: 200px;">
            @endif

           
            <br><br><p><strong>Area of Application:</strong> {{ $vendor->area_of_application }}</p>
            <p><strong>Bag Description:</strong> {{ $vendor->bag_description }}</p>

            <hr>

            <h5 class="text-lg font-bold mb-2">Rates</h5>
            <p><strong>Rate in Pure (₹):</strong> {{ $vendor->rate_in_pure }}</p>
            <p><strong>For Supply Rate (₹):</strong> {{ $vendor->for_supply_rate }}</p>
            <p><strong>For Apply Rate (₹):</strong> {{ $vendor->for_apply_rate }}</p>

        </div>
    </div>
</div>
@endsection
