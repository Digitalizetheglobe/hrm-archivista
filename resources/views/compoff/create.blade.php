@extends('layouts.admin')
@section('page-title')
    {{ __('Create Comp-Off') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('compoff.index') }}">{{ __('Comp-Off') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create') }}</li>
@endsection

@section('content')
<div class="col-md-12 mt-4">
    <div class="card shadow-lg border-0" style="border-radius: 15px;">
        <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h5 class="mb-0 text-white font-weight-bold"><i class="ti ti-calendar-event me-2"></i>{{ __('Create Comp-Off Entry') }}</h5>
        </div>
        <div class="card-body p-4">
            {{ Form::open(['route' => 'compoff.store', 'method' => 'post', 'id' => 'compoff-form']) }}
            <div class="row">
                <!-- Branch Selection -->
                <div class="col-md-6 mb-4">
                    <div class="form-group">
                        {{ Form::label('branch_id', __('Branch'), ['class' => 'col-form-label text-dark font-weight-bold']) }}<span class="text-danger pl-1">*</span>
                        <select class="form-control select2-custom" name="branch_id" id="branch_id" required>
                            <option value="">{{ __('Select Branch') }}</option>
                            @foreach ($branches as $id => $name)
                                <option value="{{ $id }}" {{ old('branch_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Department Selection -->
                <div class="col-md-6 mb-4">
                    <div class="form-group">
                        {{ Form::label('department_ids', __('Departments'), ['class' => 'col-form-label text-dark font-weight-bold']) }}<span class="text-danger pl-1">*</span>
                        <select class="form-control select2-custom" name="department_ids[]" id="department_ids" multiple="multiple" disabled required>
                            <!-- Loaded via AJAX -->
                        </select>
                        <small class="form-text text-muted">{{ __('Multiple departments can be selected at the same time') }}</small>
                    </div>
                </div>

                <!-- Multiple Date Selection -->
                <div class="col-md-12 mb-4">
                    <div class="card bg-light border-0 shadow-sm" style="border-radius: 10px;">
                        <div class="card-body p-3">
                            <div class="form-group mb-2">
                                {{ Form::label('dates_picker', __('Select Comp-Off Dates'), ['class' => 'col-form-label text-dark font-weight-bold']) }}<span class="text-danger pl-1">*</span>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i class="ti ti-calendar"></i></span>
                                    <input type="text" name="dates" id="dates_picker" class="form-control" value="{{ old('dates') }}" placeholder="{{ __('Choose one or multiple dates...') }}" required>
                                </div>
                            </div>
                            
                            <!-- Dates Display Tag Container -->
                            <div class="mt-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted font-weight-bold"><i class="ti ti-check-box me-1"></i>{{ __('Selected Dates:') }}</span>
                                    <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm" id="dates-count-badge">0 {{ __('Dates Selected') }}</span>
                                </div>
                                <div id="selected-dates-container" class="d-flex flex-wrap gap-2" style="min-height: 45px; border: 1px dashed #d8d6de; border-radius: 8px; padding: 10px; background-color: #ffffff;">
                                    <span class="text-muted italic py-1 px-2" id="no-dates-selected-placeholder">{{ __('No dates selected yet.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee Selection -->
                <div class="col-md-12 mb-4">
                    <div class="form-group mb-3">
                        {{ Form::label('employee_ids', __('Employees'), ['class' => 'col-form-label text-dark font-weight-bold']) }}<span class="text-danger pl-1">*</span>
                        <select class="form-control select2-custom" name="employee_ids[]" id="employee_ids" multiple="multiple" disabled required>
                            <!-- Loaded via AJAX -->
                        </select>
                    </div>

                    <!-- Selected Employees Visual Grid -->
                    <div class="card bg-light border-0 shadow-sm mt-3" style="border-radius: 10px;">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 text-dark font-weight-bold"><i class="ti ti-users me-2 text-primary"></i>{{ __('Currently Selected Employees') }}</h6>
                                <span class="badge bg-success px-3 py-2 rounded-pill shadow-sm" id="employee-count-badge">0 {{ __('Selected') }}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="selected-employees-grid" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                                <div class="col-12 w-100 text-center py-4 text-muted italic" id="no-employees-selected-placeholder">
                                    {{ __('Select a branch and department first to see employees.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="modal-footer bg-light p-3 mt-4" style="border-radius: 10px;">
                <input type="button" value="{{ __('Cancel') }}" class="btn btn-light rounded-pill px-4" onclick="location.href = '{{ route('compoff.index') }}';">
                <input type="submit" value="{{ __('Create Comp-Off') }}" class="btn btn-primary rounded-pill px-4 shadow">
            </div>

            {{ Form::close() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Select2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Flatpickr CSS & JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
    /* Premium Styling */
    .select2-container--default .select2-selection--multiple {
        min-height: 45px;
        border: 1px solid #d8d6de;
        border-radius: 8px;
        padding: 5px 10px;
        transition: all 0.3s ease;
    }
    .select2-container--default.select2-container--open .select2-selection--multiple {
        border-color: #7367f0;
        box-shadow: 0 3px 10px rgba(115, 103, 240, 0.15);
    }
    .select2-container--default .select2-selection--single {
        height: 45px;
        border: 1px solid #d8d6de;
        border-radius: 8px;
        padding: 8px 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 43px;
    }
    .select2-container--default .select2-selection__choice {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background-color: #7367f0 !important;
        border: none !important;
        color: #ffffff !important;
        border-radius: 20px !important;
        padding: 6px 16px !important; /* Equal generous padding on all sides */
        font-size: 0.85rem !important;
        margin-right: 8px !important;
        margin-top: 6px !important;
        line-height: 1 !important;
        height: 30px !important; /* Modern fixed height */
        box-sizing: border-box !important;
    }
    .select2-container--default .select2-selection__choice__remove {
        border: none !important;
        border-right: none !important;
        background: transparent !important;
        color: #ffffff !important;
        font-weight: bold !important;
        font-size: 1.1rem !important;
        margin-right: 6px !important; /* Perfect space between cross and text */
        padding: 0 !important;
        line-height: 1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        float: none !important;
        order: -1; /* Keep close icon on the left */
        height: 100% !important;
        margin-top: -1px !important; /* Micro sub-pixel alignment nudge for cross sign */
    }
    .select2-container--default .select2-selection__choice__remove:hover {
        background-color: transparent !important;
        color: #ff4d49 !important;
    }
    .date-tag {
        display: inline-flex;
        align-items: center;
        background-color: #eae8ff;
        color: #7367f0;
        border: 1px solid #dcd8ff;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s ease-in-out;
        animation: scaleIn 0.2s ease-out;
    }
    .date-tag:hover {
        background-color: #7367f0;
        color: white;
    }
    .date-tag-remove {
        cursor: pointer;
        margin-left: 8px;
        font-weight: bold;
        color: #ff4d49;
        transition: color 0.2s ease;
    }
    .date-tag:hover .date-tag-remove {
        color: white;
    }
    .employee-card {
        background: #ffffff;
        border: 1px solid #ebebeb;
        border-radius: 12px;
        padding: 12px 16px;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        animation: scaleIn 0.3s ease-out;
    }
    .employee-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.08);
        border-color: #7367f0;
    }
    
    @keyframes scaleIn {
        0% { transform: scale(0.9); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>

<script>
$(document).ready(function() {
    // Cache for raw loaded employees data to update display cards
    let allLoadedEmployees = [];

    // Initialize Select2 dropdowns
    $('.select2-custom').select2({
        width: '100%',
    });

    // Initialize Branch dropdown separately
    $('#branch_id').select2({
        placeholder: "{{ __('Select Branch') }}",
        width: '100%'
    });

    // Initialize Department Multiple Select
    $('#department_ids').select2({
        placeholder: "{{ __('Select Branch First') }}",
        width: '100%',
        allowClear: true
    });

    // Initialize Employee Multiple Select
    $('#employee_ids').select2({
        placeholder: "{{ __('Select Departments First') }}",
        width: '100%',
        allowClear: true
    });

    // Initialize Flatpickr for multiple date selection
    let oldDates = "{{ old('dates') }}";
    let selectedDatesList = oldDates ? oldDates.split(',').map(d => d.trim()) : [];
    const fp = flatpickr("#dates_picker", {
        mode: "multiple",
        dateFormat: "Y-m-d",
        defaultDate: selectedDatesList,
        onChange: function(selectedDates, dateStr, instance) {
            selectedDatesList = selectedDates.map(d => instance.formatDate(d, "Y-m-d"));
            updateDatesDisplay();
        }
    });

    function updateDatesDisplay() {
        const container = $('#selected-dates-container');
        const countBadge = $('#dates-count-badge');
        
        container.empty();
        countBadge.text(`${selectedDatesList.length} {{ __('Dates Selected') }}`);

        if (selectedDatesList.length === 0) {
            container.append(`<span class="text-muted italic py-1 px-2" id="no-dates-selected-placeholder">{{ __('No dates selected yet.') }}</span>`);
            return;
        }

        selectedDatesList.forEach(date => {
            const formatted = new Date(date).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            container.append(`
                <span class="date-tag">
                    <i class="ti ti-calendar me-1"></i>${formatted}
                    <span class="date-tag-remove" data-date="${date}">&times;</span>
                </span>
            `);
        });
    }

    // Load initial display of dates if any exist
    if (selectedDatesList.length > 0) {
        updateDatesDisplay();
    }

    // Handle removing date from tag display
    $(document).on('click', '.date-tag-remove', function() {
        const dateToRemove = $(this).data('date');
        selectedDatesList = selectedDatesList.filter(d => d !== dateToRemove);
        
        // Update Flatpickr instance selection
        fp.setDate(selectedDatesList);
        updateDatesDisplay();
    });

    // Branch selection change handler
    $('#branch_id').on('change', function() {
        const branchId = $(this).val();
        const deptSelect = $('#department_ids');
        const empSelect = $('#employee_ids');
        
        deptSelect.empty().trigger('change');
        empSelect.empty().trigger('change');
        allLoadedEmployees = [];
        updateEmployeesGrid();

        if (!branchId) {
            deptSelect.prop('disabled', true).trigger('change');
            empSelect.prop('disabled', true).trigger('change');
            return;
        }

        deptSelect.select2({
            placeholder: "{{ __('Loading departments...') }}",
            disabled: true
        });

        $.ajax({
            url: "{{ route('compoff.get_departments') }}",
            type: 'GET',
            data: { branch_id: branchId },
            success: function(data) {
                deptSelect.empty();
                
                if (data && Object.keys(data).length > 0) {
                    $.each(data, function(id, name) {
                        deptSelect.append(new Option(name, id));
                    });
                    
                    deptSelect.prop('disabled', false).select2({
                        placeholder: "{{ __('Select Departments') }}",
                        width: '100%'
                    });
                } else {
                    deptSelect.select2({
                        placeholder: "{{ __('No departments found') }}",
                        disabled: true
                    });
                }
            },
            error: function(xhr) {
                console.error(xhr);
                deptSelect.select2({
                    placeholder: "{{ __('Error loading departments') }}",
                    disabled: true
                });
            }
        });
    });

    // Department selection change handler
    $('#department_ids').on('change', function() {
        const departmentIds = $(this).val();
        const empSelect = $('#employee_ids');
        
        empSelect.empty().trigger('change');
        allLoadedEmployees = [];
        updateEmployeesGrid();

        if (!departmentIds || departmentIds.length === 0) {
            empSelect.prop('disabled', true).trigger('change');
            return;
        }

        empSelect.select2({
            placeholder: "{{ __('Loading employees...') }}",
            disabled: true
        });

        $.ajax({
            url: "{{ route('compoff.get_employees') }}",
            type: 'GET',
            data: { department_ids: departmentIds },
            success: function(data) {
                empSelect.empty();
                allLoadedEmployees = data;
                
                if (data && data.length > 0) {
                    let allIds = [];
                    $.each(data, function(index, employee) {
                        empSelect.append(new Option(employee.name, employee.id));
                        allIds.push(employee.id);
                    });

                    // Select all by default
                    empSelect.val(allIds).trigger('change');
                    
                    empSelect.prop('disabled', false).select2({
                        placeholder: "{{ __('Select/Add Employees') }}",
                        width: '100%'
                    });
                } else {
                    empSelect.select2({
                        placeholder: "{{ __('No employees found') }}",
                        disabled: true
                    });
                }
            },
            error: function(xhr) {
                console.error(xhr);
                empSelect.select2({
                    placeholder: "{{ __('Error loading employees') }}",
                    disabled: true
                });
            }
        });
    });

    // Employee dropdown selection change handler
    $('#employee_ids').on('change', function() {
        updateEmployeesGrid();
    });

    function updateEmployeesGrid() {
        const selectedIds = $('#employee_ids').val() || [];
        const grid = $('#selected-employees-grid');
        const countBadge = $('#employee-count-badge');
        
        grid.empty();
        countBadge.text(`${selectedIds.length} {{ __('Selected') }}`);

        if (selectedIds.length === 0) {
            grid.append(`
                <div class="col-12 w-100 text-center py-4 text-muted italic" id="no-employees-selected-placeholder">
                    {{ __('No employees selected.') }}
                </div>
            `);
            return;
        }

        // Loop through all loaded employees and display the selected ones
        allLoadedEmployees.forEach(employee => {
            if (selectedIds.includes(String(employee.id)) || selectedIds.includes(Number(employee.id))) {
                grid.append(`
                    <div class="col">
                        <div class="employee-card d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="avatar-container me-3 bg-soft-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #e3f2fd; color: #0d6efd; font-weight: bold;">
                                    ${employee.name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <h6 class="mb-0 text-dark font-weight-bold">${employee.name}</h6>
                                    <small class="text-muted"><i class="ti ti-hierarchy me-1"></i>${employee.department_name}</small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle remove-employee-btn" data-id="${employee.id}" title="{{ __('Remove Employee') }}" style="width: 32px; height: 32px; padding: 0;">
                                &times;
                            </button>
                        </div>
                    </div>
                `);
            }
        });
    }

    // Restore old dropdown values (Branch, Departments, Employees) on validation failure redirect
    const oldBranchId = "{{ old('branch_id') }}";
    const oldDepartmentIds = {!! json_encode(old('department_ids', [])) !!};
    const oldEmployeeIds = {!! json_encode(old('employee_ids', [])) !!};

    if (oldBranchId) {
        // Show loading state for departments
        const deptSelect = $('#department_ids');
        deptSelect.select2({
            placeholder: "{{ __('Loading departments...') }}",
            disabled: true
        });

        $.ajax({
            url: "{{ route('compoff.get_departments') }}",
            type: 'GET',
            data: { branch_id: oldBranchId },
            success: function(data) {
                deptSelect.empty();
                
                if (data && Object.keys(data).length > 0) {
                    $.each(data, function(id, name) {
                        const isSelected = oldDepartmentIds.includes(String(id)) || oldDepartmentIds.includes(Number(id));
                        deptSelect.append(new Option(name, id, isSelected, isSelected));
                    });
                    
                    deptSelect.prop('disabled', false).select2({
                        placeholder: "{{ __('Select Departments') }}",
                        width: '100%'
                    });

                    // Load employees for old departments if any were selected
                    if (oldDepartmentIds.length > 0) {
                        loadEmployeesAndSelectOld(oldDepartmentIds, oldEmployeeIds);
                    }
                }
            }
        });
    }

    function loadEmployeesAndSelectOld(departmentIds, selectedEmpIds = []) {
        const empSelect = $('#employee_ids');
        empSelect.select2({
            placeholder: "{{ __('Loading employees...') }}",
            disabled: true
        });

        $.ajax({
            url: "{{ route('compoff.get_employees') }}",
            type: 'GET',
            data: { department_ids: departmentIds },
            success: function(data) {
                empSelect.empty();
                allLoadedEmployees = data;
                
                if (data && data.length > 0) {
                    $.each(data, function(index, employee) {
                        const isSelected = selectedEmpIds.length === 0 ? false : (selectedEmpIds.includes(String(employee.id)) || selectedEmpIds.includes(Number(employee.id)));
                        empSelect.append(new Option(employee.name, employee.id, isSelected, isSelected));
                    });

                    empSelect.prop('disabled', false).select2({
                        placeholder: "{{ __('Select/Add Employees') }}",
                        width: '100%'
                    });

                    updateEmployeesGrid();
                }
            }
        });
    }

    // Handle removing employee from visually selected grid card
    $(document).on('click', '.remove-employee-btn', function() {
        const idToRemove = String($(this).data('id'));
        let selectedIds = $('#employee_ids').val() || [];
        
        // Remove from list
        selectedIds = selectedIds.filter(id => String(id) !== idToRemove);
        
        // Update Select2 select field
        $('#employee_ids').val(selectedIds).trigger('change');
    });
});
</script>
@endpush
