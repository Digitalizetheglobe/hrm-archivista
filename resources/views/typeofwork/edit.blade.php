
    {{ Form::model($typeofwork, ['route' => ['typeofworks.update', $typeofwork->id], 'method' => 'PUT']) }}
    <div class="modal-body">
        <div class="row">

            <!-- Category Name -->
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="form-group">
                    {{ Form::label('name', __('Type Of Work Name'), ['class' => 'form-label']) }}
                    <div class="form-icon-user">
                        {{ Form::text('name', null, ['class' => 'form-control', 'required' => true, 'placeholder' => 'Enter Type Of Work Name']) }}
                    </div>
                </div>
            </div>

            

        </div>
    </div>
    <div class="modal-footer">
        <a href="{{ route('typeofworks.index') }}" class="btn btn-light me-2">{{ __('Cancel') }}</a>
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>

    {{ Form::close() }}
