@php
    $setting = App\Models\Utility::settings();
@endphp
{{ Form::open(['url' => 'clients/import', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <!-- File Upload Field -->
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('file', __('Excel File'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::file('file', [
                        'class' => 'form-control',
                        'required' => 'required',
                        'accept' => '.xlsx,.xls,.csv'
                    ]) }}
                </div>
                <small class="text-muted">{{ __('File should contain client_name column. Only client names will be imported.') }}</small>
            </div>
        </div>

        <!-- Sample Download Link -->
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="text-start">
                <a href="{{ asset('sample/client_import_sample.xlsx') }}" class="btn btn-sm btn-primary">
                    <i class="ti ti-download"></i> {{ __('Download Sample File') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Import') }}" class="btn btn-primary">
</div>
{{ Form::close() }}