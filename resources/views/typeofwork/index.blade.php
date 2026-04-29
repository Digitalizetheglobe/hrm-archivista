@extends('layouts.admin')

@section('page-title')
    {{ __('Add Type Of Work') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee') }}</li>
@endsection

@section('action-button')
    <a href="#" data-url="{{ route('typeofworks.create') }}" data-ajax-popup="true"
        data-title="{{ __('Create New Type Of Work') }}" data-size="lg" data-bs-toggle="tooltip" title="Create"
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
                                <th>{{ __('Type Of Work Title') }}</th>
                                <th width="130px">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($typeofworks as $typeofwork)
                                <tr>
                                    <td>{{ $typeofwork->name }}</td>
                                    <td>
                                        <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                data-url="{{ route('typeofworks.edit', $typeofwork->id) }}"
                                                data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
                                                data-title="{{ __('Edit Type Of Work') }}" data-bs-original-title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['typeofworks.destroy', $typeofwork->id],
                                                
                                            ]) !!}
                                            <a href="#"
                                                class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                data-bs-toggle="tooltip" title="{{ __('Delete Type Of Work') }}"
                                                data-bs-original-title="{{ __('Delete Type Of Work') }}" aria-label="{{ __('Delete') }}"
                                                onclick="event.preventDefault(); document.getElementById('delete-form-{{ $typeofwork->id }}').submit();">
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

@push('scripts')
<!-- Include DataTables JS -->
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

<!-- <script type="text/javascript">
    $(document).ready(function() {
        $('#pc-dt-simple').DataTable({
            "language": {
                "emptyTable": "No entries found" // This will show when there are no tasks in the table
            },
            "lengthMenu": [10, 25, 50, 100],  // Controls the number of entries per page
        });
    });
</script> -->
@endpush


