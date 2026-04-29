    @extends('layouts.admin')
    @section('page-title')
        {{ __('Allocate New Job') }}
    @endsection

    @section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('joballocation.index') }}">{{ __('Job Allocation') }}</a></li>
        <li class="breadcrumb-item">{{ __('Create') }}</li>
    @endsection

    @section('content')
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Allocate New Job') }}</h5>
                </div>
                <div class="card-body">
                {{ Form::open(['route' => 'joballocation.store', 'method' => 'post']) }}
                        <div class="row">
                            <!-- Client and Project Row -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('client_id', __('Client'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                                    <select class="form-control select" name="client_id" id="client_id" required>
                                        <option value="">{{ __('Select Client') }}</option>
                                        @foreach ($clients as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('project_id', __('Project'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                                    <select class="form-control select" name="project_id" id="project_id" required>
                                        <option value="">{{ __('Select Client First') }}</option>
                                    </select>
                                </div>
                            </div>

                        <!-- Start Date and End Date Row -->
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                                {{ Form::date('start_date', null, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}
                                {{ Form::date('end_date', null, ['class' => 'form-control']) }}
                            </div>
                        </div>

                        <!-- Billable and Budgeting Row -->
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('billable', __('Billable'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                                <select class="form-control select" name="billable" required>
                                    <option value="1">{{ __('Billable') }}</option>
                                    <option value="0">{{ __('Non Billable') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('budgeting', __('Budgeting'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                                <select class="form-control select" name="budgeting" required>
                                    <option value="employees">{{ __('Employees') }}</option>
                                    <option value="projects">{{ __('Projects') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Narration and Status Row -->
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('narration', __('Narration'), ['class' => 'col-form-label']) }}
                                {{ Form::textarea('narration', null, ['class' => 'form-control', 'rows' => 3]) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('status', __('Status'), ['class' => 'col-form-label']) }}<span class="text-danger pl-1">*</span>
                                <select class="form-control select" name="status" required>
                                    <option value="Ongoing">{{ __('Ongoing') }}</option>
                                    <option value="Completed">{{ __('Completed') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Department and Employees Row -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department_ids" class="form-label">{{ __('Departments') }} <span class="text-danger">*</span></label>
                                    <select class="form-select premium-select" name="department_ids[]" id="department_ids" multiple="multiple" required>
                                        @foreach ($departments as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employee_ids" class="form-label">{{ __('Employees') }} <span class="text-danger">*</span></label>
                                    <select class="form-select premium-select" name="employee_ids[]" id="employee_ids" multiple="multiple" disabled>
                                        <option value="">{{ __('Select Departments First') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Approvers Section -->
                        <div class="col-12">
                            <div class="form-group">
                                <label class="col-form-label">{{ __('Approvers') }}</label>
                                <small class="form-text text-muted">{{ __('Select approvers for each department') }}</small>
                                
                                <div id="approvers-container" class="mt-3">
                                    @if(isset($approvers) && count($approvers) > 0)
                                        @foreach($approvers as $approverGroup)
                                            <div class="approver-group mb-4 p-3 border rounded">
                                                <h6 class="mb-3">{{ __('Department') }}: {{ \App\Models\Department::find($approverGroup['department_id'])->name ?? 'N/A' }}</h6>
                                                <div class="row">
                                                    @foreach($employees as $id => $name)
                                                        @if(in_array($id, $approverGroup['approvers']))
                                                            <div class="col-md-4 mb-2">
                                                                <div>
                                                                    
                                                                    <label class="form-check-label" for="approver_{{ $approverGroup['department_id'] }}_{{ $id }}">
                                                                        {{ $name }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="alert alert-info">
                                            {{ __('Please select departments and employees first to assign approvers.') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                   
                    <div class="modal-footer">
                        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" onclick="location.href = '{{ route('joballocation.index') }}';">
                        <input type="submit" value="{{ __('Allocate Job') }}" class="btn btn-primary">
                    </div>


                    {{ Form::close() }}
                </div>
            </div>
        </div>
    @endsection


    @push('scripts')
    <script>
    $(document).ready(function() {
        // Initialize select for multiple select with same style as event form
        $('.select').select({
            width: '100%',
            placeholder: $(this).data('placeholder'),
            allowClear: true,
            closeOnSelect: false
        });

        const baseUrl = '{{ url("/") }}';
        
        // Client-Project Dependency
        $('#client_id').change(function() {
            const clientId = $(this).val();
            const projectSelect = $('#project_id');
            
            if (!clientId) {
                projectSelect.empty().append('<option value="">Select Client First</option>');
                return;
            }

            // Show loading state
            projectSelect.empty().append('<option value="">Loading projects...</option>');

            $.ajax({
                url: `https://hrm.archivistaindia.com/get-projects/${clientId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(data) {
                    projectSelect.empty().append('<option value="">Select Project</option>');
                    
                    if (data && Object.keys(data).length > 0) {
                        $.each(data, function(id, name) {
                            projectSelect.append($('<option>', {
                                value: id,
                                text: name
                            }));
                        });
                    } else {
                        projectSelect.append('<option value="">No projects found</option>');
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    projectSelect.empty().append('<option value="">Error loading projects</option>');
                }
            });
        });

    

        // Load approvers when employees selection changes
        $('#employee_ids').change(function() {
            const departmentIds = $('#department_ids').val();
            const selectedEmployeeIds = $(this).val();
            
            if (!departmentIds || departmentIds.length === 0 || !selectedEmployeeIds || selectedEmployeeIds.length === 0) {
                $('#approvers-container').html('<div class="alert alert-warning">{{ __('Please select departments and employees first to see available approvers.') }}</div>');
                return;
            }
            
            loadApprovers(departmentIds, selectedEmployeeIds);
        });

        function loadApprovers(departmentIds, selectedEmployeeIds) {
            $.ajax({
                url: `${baseUrl}/get-approvers`,
                type: 'GET',
                data: { 
                    department_ids: departmentIds,
                    employee_ids: selectedEmployeeIds
                },
                dataType: 'json',
                success: function(data) {
                    if (data.length === 0) {
                        $('#approvers-container').html('<div class="alert alert-warning">{{ __('No approvers found for selected departments and employees.') }}</div>');
                        return;
                    }

                    let html = '';
                    
                    $.each(data, function(index, department) {
                        html += `
                        <div class="approver-group mb-4 p-3 border rounded">
                            <h6 class="mb-3">${department.department_name}</h6>
                            <input type="hidden" name="approvers[${department.department_id}][]" value=""> <!-- Empty value to ensure array is sent -->
                            <div class="row">`;
                        
                        if (department.employees.length > 0) {
                            $.each(department.employees, function(i, employee) {
                                html += `
                                <div class="col-md-4 mb-2">
                                    <div >
                                    
                                        <label class="form-check-label" for="approver_${department.department_id}_${employee.id}">
                                            ${employee.name}
                                        </label>
                                    </div>
                                </div>`;
                            });
                        } else {
                            html += `<div class="col-12"><span class="text-muted">{{ __('No employees in this department') }}</span></div>`;
                        }
                        
                        html += `</div></div>`;
                    });
                    
                    $('#approvers-container').html(html);
                },
                error: function(xhr) {
                    console.error('Error loading approvers:', xhr.statusText);
                    $('#approvers-container').html('<div class="alert alert-danger">{{ __('Error loading approvers') }}</div>');
                }
            });
        }

    });
    </script>




    <!-- Make sure to include Select2 CSS and JS in your layout -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
    /* Premium Select2 Styling */
    .premium-select.select2-container--open .select2-selection--multiple {
        border-color: #7367f0;
        box-shadow: 0 3px 10px rgba(115, 103, 240, 0.1);
    }

    .premium-select .select2-selection--multiple {
        min-height: 42px;
        padding: 5px 10px;
        border: 1px solid #d8d6de;
        border-radius: 6px;
        transition: all 0.3s;
    }

    .premium-select .select2-selection--multiple:focus {
        border-color: #7367f0;
    }

    .premium-select .select2-selection__choice {
        background-color: #7367f0;
        border-color: #7367f0;
        color: white;
        padding: 3px 8px;
        border-radius: 4px;
        margin-right: 6px;
        margin-top: 4px;
    }

    .premium-select .select2-selection__choice__remove {
        color: white;
        margin-right: 4px;
    }

    .premium-select .select2-search__field {
        padding: 0 5px;
        margin-top: 5px;
    }

    .premium-select .select2-results__option--highlighted {
        background-color: #7367f0;
    }

    .premium-select .select2-dropdown {
        border-color: #d8d6de;
        box-shadow: 0 5px 25px rgba(34, 41, 47, 0.1);
    }

    .premium-select .select2-selection__clear {
        color: #6e6b7b;
        margin-right: 5px;
    }
    </style>

    <script>
    $(document).ready(function() {
        // Initialize premium select2 for departments
        $('#department_ids').select2({
            placeholder: "Select departments...",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#department_ids').parent(),
            templateSelection: formatSelection,
            templateResult: formatResult
        });

        // Initialize disabled select2 for employees
        $('#employee_ids').select2({
            placeholder: "Select departments first",
            allowClear: true,
            width: '100%',
            disabled: true,
            dropdownParent: $('#employee_ids').parent(),
            templateSelection: formatSelection,
            templateResult: formatResult
        });

        // Format selected items
        function formatSelection(item) {
            if (!item.id) return item.text;
            return $('<span class="selected-item">' + item.text + '</span>');
        }

        // Format dropdown items
        function formatResult(item) {
            if (!item.id) return item.text;
            return $('<span class="result-item">' + item.text + '</span>');
        }

        // Department change handler
        $('#department_ids').on('change', function() {
            const departmentIds = $(this).val();
            const employeeSelect = $('#employee_ids');
            
            if (!departmentIds || departmentIds.length === 0) {
                employeeSelect.empty().append('<option value="">{{ __('Select Department First') }}</option>');
                employeeSelect.prop('disabled', true).trigger('change');
                employeeSelect.select2({
                    disabled: true,
                    placeholder: "Select departments first"
                });
                return;
            }

            // Show loading state
            employeeSelect.select2({
                placeholder: "Loading employees...",
                disabled: false
            });
            
            $.ajax({
                url: "{{ route('employees.by_departments') }}",
                type: 'GET',
                data: {
                    'department_ids': departmentIds
                },
                dataType: 'json',
                success: function(data) {
                    employeeSelect.empty();
                    
                    if (!data || data.length === 0) {
                        employeeSelect.append('<option value="">{{ __('No employees found') }}</option>');
                        employeeSelect.select2({
                            placeholder: "No employees found"
                        });
                        return;
                    }

                    // Collect all employee IDs to select them all by default
                    let allEmployeeIds = [];
                    
                    // Add all employees and collect their IDs
                    $.each(data, function(index, employee) {
                        let option = new Option(employee.name, employee.id);
                        employeeSelect.append(option);
                        allEmployeeIds.push(employee.id);
                    });

                    // Select all employees by default
                    employeeSelect.val(allEmployeeIds).trigger('change');

                    // Update select2
                    employeeSelect.select2({
                        placeholder: "All employees selected",
                        disabled: false
                    });
                    
                    // Automatically load approvers after selecting all employees
                    loadApprovers(departmentIds, allEmployeeIds);
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    employeeSelect.empty().append('<option value="">{{ __('Error loading employees') }}</option>');
                    employeeSelect.select2({
                        placeholder: "Error loading employees"
                    });
                }
            });
        });
    });
    </script>
    @endpush


