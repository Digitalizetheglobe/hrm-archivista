{{ Form::open(['url' => 'projects', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <!-- Project Name Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('project_name', __('Project Name'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('project_name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Project Name')]) }}
                </div>
            </div>
        </div>

        <!-- Client Name Dropdown -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('client_id', __('Client'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    <select class="form-control" name="client_id" required>
                        <option value="">{{ __('Select Client') }}</option>
                        @foreach($clients as $id => $client_name)
                            <option value="{{ $id }}">{{ $client_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}