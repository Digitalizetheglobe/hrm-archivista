
    {{ Form::model($category, ['route' => ['categories.update', $category->id], 'method' => 'PUT']) }}
    <div class="modal-body">
        <div class="row">

            <!-- Category Name -->
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="form-group">
                    {{ Form::label('name', __('Category Name'), ['class' => 'form-label']) }}
                    <div class="form-icon-user">
                        {{ Form::text('name', null, ['class' => 'form-control', 'required' => true, 'placeholder' => 'Enter Category Name']) }}
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="form-group">
                    {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                    <div class="form-icon-user">
                        {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Optional Description']) }}
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <a href="{{ route('categories.index') }}" class="btn btn-light me-2">{{ __('Cancel') }}</a>
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>

    {{ Form::close() }}
