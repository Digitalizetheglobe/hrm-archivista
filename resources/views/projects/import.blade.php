{{ Form::open(['url' => route('projects.process-import'), 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <p>{{ __('Your Excel file should have the following columns:') }}</p>
                <ul>
                    <li><strong>project_name</strong> (required)</li>
                    <li><strong>client_id</strong> (required, must exist in clients table)</li>
                </ul>
                <p class="mt-2"><strong>Note:</strong> Rows with missing or invalid data will be skipped.</p>
            </div>
        </div>

        <div class="col-12">
            <div class="form-group">
                {{ Form::label('file', __('Select Excel File'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::file('file', [
                        'class' => 'form-control',
                        'required' => 'required',
                        'accept' => '.xlsx,.xls,.csv'
                    ]) }}
                </div>
                <small class="text-muted">{{ __('Supported formats: .xlsx, .xls, .csv') }}</small>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Import') }}" class="btn btn-primary">
</div>
{{ Form::close() }}