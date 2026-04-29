@php
    $setting = App\Models\Utility::settings();
@endphp
{{ Form::open(['url' => 'clients', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <!-- Client Group Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('clients_group_id', __('Client Group'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    <select class="form-control" name="clients_group_id" id="clients_group_id" required>
                        <option value="">{{ __('Select Client Group') }}</option>
                        @foreach ($sites as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Client Code Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('client_code', __('Client Code'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('client_code', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Client Code')]) }}
                </div>
            </div>
        </div>

        <!-- Client Name Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('client_name', __('Client Name'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('client_name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Client Name')]) }}
                </div>
            </div>
        </div>

        <!-- Client Address Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('client_address', __('Client Address'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::textarea('client_address', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Client Address')]) }}
                </div>
            </div>
        </div>

        <!-- State Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('state', __('State'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('Enter State')]) }}
                </div>
            </div>
        </div>

        <!-- Country Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('country', null, ['class' => 'form-control', 'placeholder' => __('Enter Country')]) }}
                </div>
            </div>
        </div>

        <!-- GST No Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('gst_no', __('GST No'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('gst_no', null, ['class' => 'form-control', 'placeholder' => __('Enter GST Number')]) }}
                </div>
            </div>
        </div>

        <!-- Company Phone Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('company_phone', __('Company Phone'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('company_phone', null, ['class' => 'form-control', 'placeholder' => __('Enter Company Phone')]) }}
                </div>
            </div>
        </div>

        <!-- Company Email Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('company_email', __('Company Email'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::email('company_email', null, ['class' => 'form-control', 'placeholder' => __('Enter Company Email')]) }}
                </div>
            </div>
        </div>

        <!-- Contact Person Name Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('contact_person_name', __('Contact Person Name'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('contact_person_name', null, ['class' => 'form-control', 'placeholder' => __('Enter Contact Person Name')]) }}
                </div>
            </div>
        </div>

        <!-- Contact Person Phone Field -->
        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="form-group">
                {{ Form::label('contact_person_phone', __('Contact Person Phone'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::text('contact_person_phone', null, ['class' => 'form-control', 'placeholder' => __('Enter Contact Person Phone')]) }}
                </div>
            </div>
        </div>

        <!-- Remark Field -->
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remark'), ['class' => 'form-label']) }}
                <div class="form-icon-user">
                    {{ Form::textarea('remark', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Remark')]) }}
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