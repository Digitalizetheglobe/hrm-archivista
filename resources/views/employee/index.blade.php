    @extends('layouts.admin')

    @section('page-title')
        {{ __('Manage Employee') }}
    @endsection

    @section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
        <li class="breadcrumb-item">{{ __('Employee') }}</li>
    @endsection

    @section('action-button')
        @can('Create Employee')
            <a href="{{ route('employee.create') }}" 
            data-title="{{ __('Create New Employee') }}" 
            class="btn btn-sm btn-primary flex items-center space-x-2">
                <i class="ti ti-plus"></i>
                <span>Create</span>
            </a>
        @endcan

                   <a href="{{ route('employee.index') }}" 
        class="btn btn-sm btn-primary flex items-center space-x-2">
            <i class="ti ti-users"></i>
            <span>Active Employees</span>
        </a>
        <a href="{{ route('employee.index', ['show_left' => true]) }}" 
        class="btn btn-sm btn-primary flex items-center space-x-2">
            <i class="ti ti-user-off"></i>
            <span>Left Employees</span>
        </a>

        <a href="{{ route('employee.export') }}" 
        class="btn btn-sm btn-primary flex items-center space-x-2">
            <i class="ti ti-file-export"></i>
            <span>Export</span> 
        </a>
    @endsection

    @section('content')
        <div class="row">
            
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header card-body table-border-style">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            @if(isset($showLeft) && $showLeft)
                                <h5>{{ __('Employees Who Have Left') }}</h5>
                            @else
                                <h5>{{ __('Active Employees') }}</h5>
                            @endif
                            
                            <div class="d-flex gap-2">
                                <!-- Status Filter -->
                                <!-- <div class="btn-group" role="group">
                                    <a href="{{ route('employee.index') }}" 
                                       class="btn btn-sm {{ !request('show_left') ? 'btn-primary' : 'btn-outline-primary' }}">
                                        {{ __('Active') }}
                                    </a>
                                    <a href="{{ route('employee.index', ['show_left' => true]) }}" 
                                       class="btn btn-sm {{ request('show_left') ? 'btn-primary' : 'btn-outline-primary' }}">
                                        {{ __('Left') }}
                                    </a>
                                </div> -->
                                
                                <!-- Employee Type Filter -->
                                <select id="employee_type_filter" class="form-select form-select-sm" style="width: 150px;">
                                    <option value="">{{ __('All Types') }}</option>
                                    <option value="Contract" {{ request('employee_type') == 'Contract' ? 'selected' : '' }}>{{ __('Contract') }}</option>
                                    <option value="Payroll" {{ request('employee_type') == 'Payroll' ? 'selected' : '' }}>{{ __('Payroll') }}</option>
                                </select>
                                
                                <!-- Confirmation Filter (shown when Contract or Payroll is selected) -->
                                <div id="confirmation_filter_container" style="display: none;">
                                    <select id="confirmation_filter" class="form-select form-select-sm" style="width: 120px;">
                                        <option value="">{{ __('All') }}</option>
                                        <option value="1" {{ request('confirm_employment') == '1' ? 'selected' : '' }}>{{ __('Confirm') }}</option>
                                        <option value="0" {{ request('confirm_employment') == '0' ? 'selected' : '' }}>{{ __('No Confirm') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>                
                        <div class="table-responsive">
                            <table class="table" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>{{ __('Employee ID') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Department') }}</th>
                                        <th>{{ __('Designation') }}</th>
                                        <th>{{ __('Branch') }}</th>
                                        <th>{{ __('Employee Type') }}</th>
                                        <th>{{ __('Date Of Joining') }}</th>
                                        @if(isset($showLeft) && $showLeft)
                                            <th>{{ __('Date Of Leaving') }}</th>
                                        @endif
                                        @if (Gate::check('Edit Employee') || Gate::check('Delete Employee'))
                                            <th width="130px">{{ __('Action') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($employees as $employee)
                                        <tr>
                                            <td>
                                                @can('Show Employee')
                                                    <a class="btn btn-outline-primary"
                                                        href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</a>
                                                @else
                                                    <a href="#"
                                                        class="btn btn-outline-primary">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</a>
                                                @endcan
                                            </td>
                                            <td>{{ $employee->name ?? '-' }}</td>
                                            <td>{{ $employee->email ?? '-' }}</td>  
                                            <td>{{ $employee->department?->name ?? '-' }}</td>
                                            <td>{{ $employee->designation?->name ?? '-' }}</td>
                                            <td>{{ $employee->branch?->name ?? '-' }}</td>
                                            <td>
                                                @if($employee->employee_type)
                                                    <span class="badge bg-{{ $employee->employee_type == 'Contract' ? 'warning' : 'success' }}">
                                                        {{ $employee->employee_type }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ \Auth::user()->dateFormat($employee->company_doj) }}
                                            </td>
                                            @if(isset($showLeft) && $showLeft)
                                                <td>
                                                    {{ \Auth::user()->dateFormat($employee->company_dol) }}
                                                </td>
                                            @endif
                                            @if (Gate::check('Edit Employee') || Gate::check('Delete Employee'))
                                                <td class="Action">
                                                        @if (($employee->user?->is_active ?? 0) == 1 && ($employee->user?->is_disable ?? 0) == 1)                                                    <span>
                                                            <div class="d-flex align-items-center">
                                                                @can('Edit Employee')
                                                                    <div class="action-btn bg-info me-2">
                                                                        <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                                                            class="mx-3 btn btn-sm align-items-center"
                                                                            data-bs-toggle="tooltip" title=""
                                                                            data-bs-original-title="{{ __('Edit') }}">
                                                                            <i class="ti ti-pencil text-white"></i>
                                                                        </a>
                                                                    </div>
                                                                @endcan

                                                                <!-- Confirmation Button for Contract and Payroll Employees -->
                                                                <div class="action-btn-confirm me-2">
                                                                    @if($employee->employee_type == 'Contract' || $employee->employee_type == 'Payroll')
                                                                        @if(!$employee->confirm_of_employment)
                                                                            <div class="action-btn bg-success">
                                                                                <button type="button" 
                                                                                        class="mx-3 btn btn-sm align-items-center text-white"
                                                                                        data-bs-toggle="modal" 
                                                                                        data-bs-target="#confirmEmploymentModal"
                                                                                        data-employee-id="{{ $employee->id }}"
                                                                                        data-employee-name="{{ $employee->name }}"
                                                                                        data-bs-toggle="tooltip" 
                                                                                        title="{{ __('Confirm Employment') }}">
                                                                                    <i class="ti ti-check"></i>
                                                                                </button>
                                                                            </div>
                                                                        @else
                                                                            <div class="action-btn bg-warning">
                                                                                <button type="button" 
                                                                                        class="mx-3 btn btn-sm align-items-center text-white"
                                                                                        data-bs-toggle="modal" 
                                                                                        data-bs-target="#cancelEmploymentModal"
                                                                                        data-employee-id="{{ $employee->id }}"
                                                                                        data-employee-name="{{ $employee->name }}"
                                                                                        data-bs-toggle="tooltip" 
                                                                                        title="{{ __('Cancel Confirmation') }}">
                                                                                    <i class="ti ti-x"></i>
                                                                                </button>
                                                                            </div>
                                                                        @endif
                                                                    @endif
                                                                </div>

                                                                @can('Delete Employee')
                                                                    <div class="action-btn bg-danger">
                                                                        <a href="#"
                                                                            class="mx-3 btn btn-sm align-items-center text-white"
                                                                            onclick="if(confirm('{{ __("Are you sure?") }}')) { document.getElementById('delete-form-{{ $employee->id }}').submit(); } return false;"
                                                                            data-bs-toggle="tooltip" title=""
                                                                            data-bs-original-title="Delete" aria-label="Delete">
                                                                            <i class="ti ti-trash"></i>
                                                                        </a>
                                                                        {!! Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['employee.destroy', $employee->id],
                                                                            'id' => 'delete-form-' . $employee->id,
                                                                            'style' => 'display: none;'
                                                                        ]) !!}
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                @endcan
                                                            </div>
                                                        </span>
                                                    @else
                                                        <i class="ti ti-lock"></i>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endsection

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmEmploymentModal" tabindex="-1" aria-labelledby="confirmEmploymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmEmploymentModalLabel">{{ __('Confirmation of Employment') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to confirm the employment for') }} <strong id="employeeName"></strong>?</p>
                    <p class="text-muted">{{ __('This action will mark the employee as confirmed and cannot be undone.') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-success" id="confirmEmploymentBtn">
                        <i class="ti ti-check me-2"></i>{{ __('Approve') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div class="modal fade" id="cancelEmploymentModal" tabindex="-1" aria-labelledby="cancelEmploymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelEmploymentModalLabel">{{ __('Cancel Confirmation') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to cancel the confirmation for') }} <strong id="cancelEmployeeName"></strong>?</p>
                    <p class="text-muted">{{ __('This action will mark the employee as unconfirmed.') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Back') }}</button>
                    <button type="button" class="btn btn-warning" id="cancelEmploymentBtn">
                        <i class="ti ti-x me-2"></i>{{ __('Cancel Confirmation') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Show/hide confirmation filter based on employee type selection
            function toggleConfirmationFilter() {
                var employeeType = $('#employee_type_filter').val();
                if (employeeType === 'Contract' || employeeType === 'Payroll') {
                    $('#confirmation_filter_container').show();
                } else {
                    $('#confirmation_filter_container').hide();
                    // Clear confirmation filter when not Contract or Payroll
                    $('#confirmation_filter').val('');
                }
            }
            
            // Initial check
            toggleConfirmationFilter();
            
            // Handle employee type filter change
            $('#employee_type_filter').on('change', function() {
                var employeeType = $(this).val();
                var currentUrl = new URL(window.location);
                
                // Update or remove employee_type parameter
                if (employeeType) {
                    currentUrl.searchParams.set('employee_type', employeeType);
                } else {
                    currentUrl.searchParams.delete('employee_type');
                }
                
                // Remove confirmation filter if not Contract or Payroll
                if (employeeType !== 'Contract' && employeeType !== 'Payroll') {
                    currentUrl.searchParams.delete('confirm_employment');
                }
                
                // Navigate to the updated URL
                window.location.href = currentUrl.toString();
            });
            
            // Handle confirmation filter change
            $('#confirmation_filter').on('change', function() {
                var confirmationStatus = $(this).val();
                var currentUrl = new URL(window.location);
                
                // Update or remove confirm_employment parameter
                if (confirmationStatus) {
                    currentUrl.searchParams.set('confirm_employment', confirmationStatus);
                } else {
                    currentUrl.searchParams.delete('confirm_employment');
                }
                
                // Navigate to the updated URL
                window.location.href = currentUrl.toString();
            });
            
            // Handle employment confirmation modal
            $('#confirmEmploymentModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var employeeId = button.data('employee-id');
                var employeeName = button.data('employee-name');
                
                var modal = $(this);
                modal.find('#employeeName').text(employeeName);
                modal.find('#confirmEmploymentBtn').data('employee-id', employeeId);
            });
            
            // Handle confirmation button click
            $('#confirmEmploymentBtn').on('click', function() {
                var employeeId = $(this).data('employee-id');
                var modal = $('#confirmEmploymentModal');
                
                // Disable button to prevent multiple clicks
                $(this).prop('disabled', true).html('<i class="ti ti-loader ti-spin me-2"></i>{{ __('Processing...') }}');
                
                // Send AJAX request to confirm employment
                $.ajax({
                    url: '{{ route("employee.confirm-employment") }}',
                    method: 'POST',
                    data: {
                        employee_id: employeeId,
                        _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Close modal
                            modal.modal('hide');
                            
                            // Show success message
                            var toast = '<div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">' +
                                        '<i class="ti ti-check me-2"></i>{{ __("Employment confirmed successfully!") }}' +
                                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                                        '</div>';
                            $('body').append(toast);
                            
                            // Reload page to show updated status
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            alert(response.message || '{{ __("An error occurred. Please try again.") }}');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON?.message || '{{ __("An error occurred. Please try again.") }}';
                        
                        // Check specifically for CSRF token mismatch
                        if (xhr.status === 419 || xhr.responseJSON?.message?.includes('CSRF') || xhr.responseJSON?.exception?.includes('CSRF')) {
                            errorMessage = '{{ __("CSRF token mismatch. Please refresh the page and try again.") }}';
                            // Optionally reload the page after showing the error
                            setTimeout(function() {
                                window.location.reload();
                            }, 3000);
                        }
                        
                        alert(errorMessage);
                    },
                    complete: function() {
                        // Re-enable button
                        $('#confirmEmploymentBtn').prop('disabled', false).html('<i class="ti ti-check me-2"></i>{{ __("Approve") }}');
                    }
                });
            });
            
            // Handle cancel employment modal
            $('#cancelEmploymentModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var employeeId = button.data('employee-id');
                var employeeName = button.data('employee-name');
                
                var modal = $(this);
                modal.find('#cancelEmployeeName').text(employeeName);
                modal.find('#cancelEmploymentBtn').data('employee-id', employeeId);
            });
            
            // Handle cancel confirmation button click
            $('#cancelEmploymentBtn').on('click', function() {
                var employeeId = $(this).data('employee-id');
                var modal = $('#cancelEmploymentModal');
                
                // Disable button to prevent multiple clicks
                $(this).prop('disabled', true).html('<i class="ti ti-loader ti-spin me-2"></i>{{ __('Processing...') }}');
                
                // Send AJAX request to cancel confirmation
                $.ajax({
                    url: '{{ route("employee.cancel-employment") }}',
                    method: 'POST',
                    data: {
                        employee_id: employeeId,
                        _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Close modal
                            modal.modal('hide');
                            
                            // Show success message
                            var toast = '<div class="alert alert-warning alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">' +
                                        '<i class="ti ti-x me-2"></i>{{ __("Confirmation cancelled successfully!") }}' +
                                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                                        '</div>';
                            $('body').append(toast);
                            
                            // Reload page to show updated status
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            alert(response.message || '{{ __("An error occurred. Please try again.") }}');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON?.message || '{{ __("An error occurred. Please try again.") }}';
                        
                        // Check specifically for CSRF token mismatch
                        if (xhr.status === 419 || xhr.responseJSON?.message?.includes('CSRF') || xhr.responseJSON?.exception?.includes('CSRF')) {
                            errorMessage = '{{ __("CSRF token mismatch. Please refresh the page and try again.") }}';
                            // Optionally reload the page after showing the error
                            setTimeout(function() {
                                window.location.reload();
                            }, 3000);
                        }
                        
                        alert(errorMessage);
                    },
                    complete: function() {
                        // Re-enable button
                        $('#cancelEmploymentBtn').prop('disabled', false).html('<i class="ti ti-x me-2"></i>{{ __("Cancel Confirmation") }}');
                    }
                });
            });
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .Action {
            text-align: center;
            vertical-align: middle;
        }
        
        .Action .d-flex {
            justify-content: center;
        }
        
        .action-btn-confirm {
            width: 44px;
            min-width: 44px;
            height: 32px;
            display: inline-block;
        }
        
        .action-btn {
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .action-btn .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1;
        }
    </style>
    @endpush
