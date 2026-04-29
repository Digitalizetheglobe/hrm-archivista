@extends('layouts.admin')

@section('page-title')
    {{ __('Suppliers List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Suppliers') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <th>Name</th>
                                <th>Company Website</th>
                                <th>Plan Location</th>
                                <th>Category</th>
                                <th>Sub Category</th>
                                <th>Rate in Pure £</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendors as $vendor)
                                <tr>
                                    <td>{{ $vendor->name }}</td>
                                    <td>
                                        @if($vendor->company_website)
                                            <a href="{{ $vendor->company_website }}" target="_blank">{{ $vendor->company_website }}</a>
                                        @else
                                            N/A
                                        @endif
                                    </td>                                    <td>{{ $vendor->plan_location }}</td>
                                    <td>{{ $vendor->category ? $vendor->category->name : '-' }}</td>
                                    <td>{{ $vendor->subCategory ? $vendor->subCategory->name : '-' }}</td>
                                    <td>{{ $vendor->rate_in_pure }}</td>
                                    <td>
                                        <a href="{{ route('vendor.supplier.show', $vendor->id) }}" class="btn btn-sm btn-primary">Show</a>
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
