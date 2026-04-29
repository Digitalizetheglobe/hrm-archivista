@extends('layouts.admin') {{-- Or your base layout --}}

@section('page-title')
    {{ __('Contractors List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Contractors') }}</li>
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
                                <th>Name</th>
                                <th>Company Website</th>
                                <th>Project Range</th>
                                <th>Type of Work</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendors as $vendor)
                                <tr>
                                    <td>{{ $vendor->name }}</td>
                                    <td>
                                        @if($vendor->company_website)
                                            <a href="{{ $vendor->company_website }}" target="_blank">{{ $vendor->company_website }}</a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $vendor->project_range }}</td>
                                    <td>{{ $vendor->type_of_work }}</td>
                                    <td>
                                        <a href="{{ route('vendors.contractor_show', $vendor->id) }}" class="btn btn-sm btn-primary">Show</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No contractors found.</td>
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
