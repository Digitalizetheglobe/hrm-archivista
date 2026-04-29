{{ Form::model($subcategory, ['route' => ['subcategories.update', $subcategory->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">

        <!-- Category Dropdown -->
        <div class="form-group col-md-6">
            {{ Form::label('category_id', __('Select Category'), ['class' => 'form-label']) }}
            <div class="form-icon-user">
                {!! Form::select('category_id', $categories->pluck('name', 'id'), null, [
                    'class' => 'form-control select2',
                    'required' => true,
                    'placeholder' => 'Select Category'
                ]) !!}
            </div>
        </div>

        <!-- Sub-Category Name -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('name', __('Sub-Category Name'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('name', null, ['class' => 'form-control', 'required' => true, 'placeholder' => 'Enter Sub-Category Name']) }}
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
    <a href="{{ route('subcategories.index') }}" class="btn btn-light me-2">{{ __('Cancel') }}</a>
    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
</div>
{{ Form::close() }}
