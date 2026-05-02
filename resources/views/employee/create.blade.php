@extends('layouts.admin')

@section('page-title')
    {{ __('Create Employee') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('employee') }}">{{ __('Employee') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Employee') }}</li>
@endsection

@push('css')
    <style>
        .cursor-pointer {
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="">
            <div class="">
                <div class="row">

                </div>
                {{ Form::open(['route' => ['employee.store'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                <div class="row">
                    <div class="col-md-6">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5>{{ __('Personal Detail') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('name', __('Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('name', old('name'), [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'placeholder' => 'Enter employee name',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('phone', __('Phone'), ['class' => 'form-label']) !!}
                                        {!! Form::text('phone', old('phone'), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter employee phone',
                                            'oninput' => 'validateNumbers()',
                                        ]) !!}
                                        <span id="phone-error" class="text-danger"></span>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}
                                            {{ Form::date('dob', null, ['class' => 'form-control current_date', 'autocomplete' => 'off', 'placeholder' => 'Select Date of Birth']) }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}
                                            <div class="d-flex radio-check">
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="g_male" value="Male" name="gender"
                                                        class="form-check-input">
                                                    <label class="form-check-label"
                                                        for="g_male">{{ __('Male') }}</label>
                                                </div>
                                                <div class="custom-control custom-radio ms-1 custom-control-inline">
                                                    <input type="radio" id="g_female" value="Female" name="gender"
                                                        class="form-check-input">
                                                    <label class="form-check-label"
                                                        for="g_female">{{ __('Female') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::email('email', old('email'), [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'placeholder' => 'Enter employee email',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('password', __('Password'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::password('password', [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'placeholder' => 'Enter employee password',
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}
                                    {!! Form::textarea('address', old('address'), [
                                        'class' => 'form-control',
                                        'rows' => 3,
                                        'placeholder' => 'Enter employee address',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card em-card">
                            <div class="card-header">
                                <h5>{{ __('Company Detail') }}</h5>
                            </div>
                            <div class="card-body employee-detail-create-body">
                                <div class="row">
                                    @csrf
                                    <div class="form-group">
                                        {!! Form::label('employee_id', __('Employee ID'), ['class' => 'form-label']) !!}
                                        {!! Form::text('employee_id', $employeesId, ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                        <select class="form-control select2" name="branch_id" id="branch_id" required>
                                            <option value="">{{ __('Select Branch') }}</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                        <select class="form-control select2 department_id" name="department_id" id="department_id" required>
                                            <option value="">{{ __('Select Department') }}</option>
                                            @foreach($departments as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                            <div class="designation_div">
                                                <select class="form-control designation_id" name="designation_id" required>
                                                    <option value="">{{ __('Select Designation') }}</option>
                                                    @foreach($designations as $id => $name)
                                                        <option value="{{ $id }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-6">
                                            {!! Form::label('hourly_charged', __('Hourly Rate'), ['class' => 'form-label']) !!}
                                            {!! Form::number('hourly_charged', old('hourly_charged'), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter hourly rate',
                                                'step' => '0.01'
                                            ]) !!}
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            {!! Form::label('company_doj', __('Company Date Of Joining'), ['class' => 'form-label']) !!}
                                            {{ Form::date('company_doj', null, ['class' => 'form-control current_date', 'autocomplete' => 'off', 'placeholder' => 'Select company date of joining']) }}
                                        </div>
                                        <div class="form-group col-md-6">
                                            {!! Form::label('employee_type', __('Employee Type'), ['class' => 'form-label']) !!}
                                            <select class="form-control" name="employee_type" id="employee_type">
                                                <option value="">{{ __('Select Employee Type') }}</option>
                                                <option value="Consultant">{{ __('Consultant') }}</option>
                                                <option value="Payroll">{{ __('Payroll') }}</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6" id="per_day_rate_field" style="display: none;">
                                            {!! Form::label('per_day_rate', __('Per Day Rate'), ['class' => 'form-label']) !!}
                                            {!! Form::number('per_day_rate', null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'placeholder' => 'Enter per day rate']) !!}
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('Education Details') }}</h6>
                            </div>
                            <div class="card-body employee-detail-create-body">
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            {!! Form::label('primary_skill', __('Primary Skill'), ['class' => 'form-label']) !!}
                                            {!! Form::text('primary_skill', old('primary_skill'), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter primary skill',
                                            ]) !!}
                                        </div>
                                        <div class="form-group col-md-4">
                                            {!! Form::label('secondary_skill', __('Secondary Skill'), ['class' => 'form-label']) !!}
                                            {!! Form::text('secondary_skill', old('secondary_skill'), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter secondary skill',
                                            ]) !!}
                                        </div>
                                        <div class="form-group col-md-4">
                                            {!! Form::label('certificate', __('Certificate'), ['class' => 'form-label']) !!}
                                            {!! Form::text('certificate', old('certificate'), [
                                                'class' => 'form-control',
                                                'placeholder' => 'Enter certificates',
                                            ]) !!}
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payroll Card -->
                <div class="card em-card mt-3">
                    <div class="card-header">
                        <h5>{{ __('Payroll') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('esic_no', __('ESIC NO'), ['class' => 'form-label']) !!}
                                {!! Form::text('esic_no', old('esic_no'), [
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter ESIC number',
                                ]) !!}
                            </div>
                            <div class="form-group col-md-6">
                                {!! Form::label('bank_ac_no', __('Bank A/c No'), ['class' => 'form-label']) !!}
                                {!! Form::text('bank_ac_no', old('bank_ac_no'), [
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter bank account number',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>



                <div class="float-end">
                    <button type="submit" class="btn  btn-primary">{{ 'Create' }}</button>
                </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $('input[type="file"]').change(function(e) {
            var file = e.target.files[0].name;
            var file_name = $(this).attr('data-filename');
            $('.' + file_name).append(file);
        });

        $(document).ready(function() {
            var d_id = $('.department_id').val();
            getDesignation(d_id);
        });

        $(document).on('change', 'select[name=department_id]', function() {
            var department_id = $(this).val();
            getDesignation(department_id);
        });

        function getDesignation(did) {
            $.ajax({
                url: '{{ route('employee.json') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('.designation_id').empty();
                    var emp_selct = `<select class="form-control designation_id" name="designation_id"
                                             placeholder="Select Designation" required>
                                        </select>`;
                    $('.designation_div').html(emp_selct);

                    $('.designation_id').append('<option value=""> {{ __('Select Designation') }} </option>');
                    $.each(data, function(key, value) {
                        $('.designation_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    new Choices('#choices-multiple', {
                        removeItemButton: true,
                    });
                }
            });
        }

        $(document).ready(function() {
            var now = new Date();
            var month = (now.getMonth() + 1);
            var day = now.getDate();
            if (month < 10) month = "0" + month;
            if (day < 10) day = "0" + day;
            var today = now.getFullYear() + '-' + month + '-' + day;
            $('.current_date').val(today);
        });

        // Show/hide per day rate field based on employee type selection
        $(document).on('change', '#employee_type', function() {
            var employeeType = $(this).val();
            if (employeeType === 'Consultant' || employeeType === 'Payroll') {
                $('#per_day_rate_field').show();
            } else {
                $('#per_day_rate_field').hide();
                $('#per_day_rate').val(''); // Clear the value when hidden
            }
        });
    </script>
@endpush