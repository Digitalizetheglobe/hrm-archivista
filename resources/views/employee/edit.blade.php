@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Employee') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('employee') }}">{{ __('Employee') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Employee') }}</li>
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
                @if(is_object($employee))
                    {{ Form::model($employee, ['route' => ['employee.update', $employee->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
                @else
                    {{ Form::open(['route' => ['employee.store'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                @endif
                
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
                                        {!! Form::text('name', null, [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'placeholder' => 'Enter employee name',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('phone', __('Phone'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('phone', null, [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'placeholder' => 'Enter employee phone',
                                            'oninput' => 'validateNumbers()',
                                        ]) !!}
                                        <span id="phone-error" class="text-danger"></span>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                            {{ Form::date('dob', null, ['class' => 'form-control current_date', 'required' => 'required', 'autocomplete' => 'off', 'placeholder' => 'Select Date of Birth']) }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                            <div class="d-flex radio-check">
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="g_male" value="Male" name="gender"
                                                        class="form-check-input"
                                                        {{ (is_object($employee) && $employee->gender == 'Male') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="g_male">{{ __('Male') }}</label>
                                                </div>
                                                <div class="custom-control custom-radio ms-1 custom-control-inline">
                                                    <input type="radio" id="g_female" value="Female" name="gender"
                                                        class="form-check-input"
                                                        {{ (is_object($employee) && $employee->gender == 'Female') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="g_female">{{ __('Female') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::email('email', is_object($employee) ? $employee->user->email : old('email'), [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'disabled' => 'disabled',
                                            'placeholder' => 'Enter employee email',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('password', __('Password'), ['class' => 'form-label']) !!}
                                        {!! Form::password('password', [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter new password (leave empty to keep current)',
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    {!! Form::label('address', __('Address'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {!! Form::textarea('address', null, [
                                        'class' => 'form-control',
                                        'rows' => 3,
                                        'required' => 'required',
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
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group">
                                        {!! Form::label('employee_id', __('Employee ID'), ['class' => 'form-label']) !!}
                                        {!! Form::text('employee_id', is_object($employee) ? $employee->employee_id : '', ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                                        <select class="form-control select2" name="branch_id" id="branch_id">
                                            <option value="">{{ __('Select Branch') }}</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ (is_object($employee) && $employee->branch_id == $branch->id) ? 'selected' : '' }}>{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                        <select class="form-control select2 department_id" name="department_id" id="department_id" required>
                                            <option value="">{{ __('Select Department') }}</option>
                                            @foreach($departments as $id => $name)
                                                <option value="{{ $id }}" {{ (is_object($employee) && $employee->department_id == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                        <div class="designation_div">
                                            <select class="form-control designation_id" name="designation_id" required>
                                                <option value="">{{ __('Select Designation') }}</option>
                                                @foreach($designations as $id => $name)
                                                    <option value="{{ $id }}" {{ (is_object($employee) && $employee->designation_id == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        {!! Form::label('hourly_charged', __('Hourly Rate'), ['class' => 'form-label']) !!}
                                        {!! Form::number('hourly_charged', is_object($employee) ? $employee->hourly_charged : old('hourly_charged'), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter hourly rate',
                                            'step' => '0.01'
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('company_doj', __('Company Date Of Joining'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {{ Form::date('company_doj', is_object($employee) ? $employee->company_doj : null, ['class' => 'form-control current_date', 'required' => 'required', 'autocomplete' => 'off', 'placeholder' => 'Select company date of joining']) }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('employee_type', __('Employee Type'), ['class' => 'form-label']) !!}
                                        <select class="form-control" name="employee_type" id="employee_type">
                                            <option value="">{{ __('Select Employee Type') }}</option>
                                            <option value="Contract" {{ (is_object($employee) && $employee->employee_type == 'Contract') ? 'selected' : '' }}>{{ __('Contract') }}</option>
                                            <option value="Payroll" {{ (is_object($employee) && $employee->employee_type == 'Payroll') ? 'selected' : '' }}>{{ __('Payroll') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('company_dol', __('Company Date Of Leaving'), ['class' => 'form-label']) !!}
                                        {{ Form::date('company_dol', is_object($employee) ? $employee->company_dol : null, ['class' => 'form-control', 'placeholder' => 'Select company date of leaving']) }}
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
                                <h5>{{ __('Education Details') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        {!! Form::label('primary_skill', __('Primary Skill'), ['class' => 'form-label']) !!}
                                        {!! Form::text('primary_skill', is_object($employee) ? $employee->primary_skill : old('primary_skill'), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter primary skill',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('secondary_skill', __('Secondary Skill'), ['class' => 'form-label']) !!}
                                        {!! Form::text('secondary_skill', is_object($employee) ? $employee->secondary_skill : old('secondary_skill'), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter secondary skill',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('certificate', __('Certificate'), ['class' => 'form-label']) !!}
                                        {!! Form::text('certificate', is_object($employee) ? $employee->certificate : old('certificate'), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter certificates',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('Payroll') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('esic_no', __('ESIC NO'), ['class' => 'form-label']) !!}
                                        {!! Form::text('esic_no', is_object($employee) ? $employee->esic_no : old('esic_no'), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter ESIC number',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('bank_ac_no', __('Bank A/c No'), ['class' => 'form-label']) !!}
                                        {!! Form::text('bank_ac_no', is_object($employee) ? $employee->bank_ac_no : old('bank_ac_no'), [
                                            'class' => 'form-control',
                                            'placeholder' => 'Enter bank account number',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                     <!-- Job Allocation Card -->
                     <div class="card em-card mt-3">
                            <div class="card-header">
                                <h5>{{ __('Job Allocation') }}</h5>
                            </div>
                            <div class="card-body">
                                
                            </div>
                        </div>
                </div>

                

                <div class="float-end">
                    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            var d_id = $('#department_id').val();
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
                    @if(is_object($employee))
                    "employee_id": "{{ $employee->id }}"
                    @endif
                },
                success: function(data) {
                    $('.designation_id').empty();
                    $('.designation_id').append('<option value="">{{ __('Select Designation') }}</option>');
                    $.each(data, function(key, value) {
                        var selected = false;
                        @if(is_object($employee))
                        selected = (key == "{{ $employee->designation_id }}");
                        @endif
                        $('.designation_id').append('<option value="' + key + '" ' + (selected ? 'selected' : '') + '>' + value + '</option>');
                    });
                }
            });
        }

        // Date picker initialization
        $(document).ready(function() {
            var now = new Date();
            var month = (now.getMonth() + 1).toString().padStart(2, '0');
            var day = now.getDate().toString().padStart(2, '0');
            var today = now.getFullYear() + '-' + month + '-' + day;

            $('.current_date').each(function() {
                if (!$(this).val()) {
                    $(this).val(today);
                }
            });
        });
    </script>
@endpush