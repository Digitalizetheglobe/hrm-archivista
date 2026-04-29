@extends('layouts.admin')
@section('page-title')
    {{ __('Add Sub-Category') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee') }}</li>
@endsection

@section('action-button')
    <a href="#" data-url="{{ route('subcategories.create') }}" data-ajax-popup="true"
        data-title="{{ __('Create New Category') }}" data-size="lg" data-bs-toggle="tooltip" title="Create"
        class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i>
    </a>
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
                                <th>{{ __('Category Title') }}</th>
                                <th>{{ __('Sub-Category Title') }}</th>
                                <th>{{ __('Sub-Category Description') }}</th>
                                <th width="130px">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subcategories as $subcategory)
                                <tr>
                                    <td>{{ $subcategory->category->name ?? '-' }}</td>
                                    <td>{{ $subcategory->name }}</td>
                                    <td>{{ $subcategory->description }}</td>
                                    <td>
                                        <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                data-url="{{ route('subcategories.edit', $subcategory->id) }}"
                                                data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
                                                data-title="{{ __('Edit Category') }}" data-bs-original-title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['subcategories.destroy', $subcategory->id],
                                            ]) !!}
                                            <a href="#"
                                                class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                data-bs-toggle="tooltip" title="{{ __('Delete Task') }}"
                                                data-bs-original-title="{{ __('Delete Category') }}" aria-label="{{ __('Delete') }}"
                                                onclick="event.preventDefault(); document.getElementById('delete-form-{{ $subcategory->id }}').submit();">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                            {!! Form::close() !!}
                                        </div>
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
