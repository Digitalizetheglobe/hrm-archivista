    

    <?php $__env->startSection('page-title'); ?>
        <?php echo e(__('Manage Employee')); ?>

    <?php $__env->stopSection(); ?>

    <?php $__env->startSection('breadcrumb'); ?>
        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
        <li class="breadcrumb-item"><?php echo e(__('Employee')); ?></li>
    <?php $__env->stopSection(); ?>

    <?php $__env->startSection('action-button'); ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Create Employee')): ?>
            <a href="<?php echo e(route('employee.create')); ?>" 
            data-title="<?php echo e(__('Create New Employee')); ?>" 
            class="btn btn-sm btn-primary flex items-center space-x-2">
                <i class="ti ti-plus"></i>
                <span>Create</span>
            </a>
        <?php endif; ?>

        <a href="<?php echo e(route('employee.export')); ?>" 
        class="btn btn-sm btn-primary flex items-center space-x-2">
            <i class="ti ti-file-export"></i>
            <span>Export</span> 
        </a>
    <?php $__env->stopSection(); ?>

    <?php $__env->startSection('content'); ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            <?php echo e(Form::label('employee_type', __('Employee Type'), ['class' => 'form-label'])); ?>

                                            <select id="employee_type_filter" class="form-control select">
                                                <option value=""><?php echo e(__('All Types')); ?></option>
                                                <option value="Consultant" <?php echo e(request('employee_type') == 'Consultant' ? 'selected' : ''); ?>><?php echo e(__('Consultant')); ?></option>
                                                <option value="Payroll" <?php echo e(request('employee_type') == 'Payroll' ? 'selected' : ''); ?>><?php echo e(__('Payroll')); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12" id="confirmation_filter_container" style="display: none;">
                                        <div class="btn-box">
                                            <?php echo e(Form::label('confirm_employment', __('Confirmation Status'), ['class' => 'form-label'])); ?>

                                            <select id="confirmation_filter" class="form-control select">
                                                <option value=""><?php echo e(__('All Status')); ?></option>
                                                <option value="1" <?php echo e(request('confirm_employment') == '1' ? 'selected' : ''); ?>><?php echo e(__('Confirmed')); ?></option>
                                                <option value="0" <?php echo e(request('confirm_employment') == '0' ? 'selected' : ''); ?>><?php echo e(__('Not Confirmed')); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <a href="<?php echo e(route('employee.index')); ?>" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="<?php echo e(__('Reset')); ?>">
                                    <span class="btn-inner--icon"><i class="ti ti-trash text-white-off"></i></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header p-0 border-0">
                        <ul class="nav nav-tabs" id="pills-tab" role="tablist" style="background: #f8f9fd; border-radius: 10px 10px 0 0;">
                            <li class="nav-item" role="presentation" style="margin-bottom: -1px;">
                                <a href="<?php echo e(route('employee.index')); ?>" 
                                   class="nav-link <?php echo e(!request('show_left') ? 'active' : ''); ?>" 
                                   style="<?php echo e(!request('show_left') ? 'background: white; border: none; border-radius: 10px 10px 0 0; color: #666; font-weight: bold; padding: 15px 25px;' : 'border: none; color: #888; padding: 15px 25px;'); ?>">
                                    <?php echo e(__('Active Employees')); ?>

                                </a>
                            </li>
                            <li class="nav-item" role="presentation" style="margin-bottom: -1px;">
                                <a href="<?php echo e(route('employee.index', ['show_left' => true])); ?>" 
                                   class="nav-link <?php echo e(request('show_left') ? 'active' : ''); ?>" 
                                   style="<?php echo e(request('show_left') ? 'background: white; border: none; border-radius: 10px 10px 0 0; color: #f43f5e; font-weight: bold; padding: 15px 25px;' : 'border: none; color: #f43f5e; padding: 15px 25px;'); ?>">
                                    <?php echo e(__('Inactive Employees')); ?>

                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body table-border-style" style="padding:0px !important;">
                        <table class="table" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('Employee ID')); ?></th>
                                        <th><?php echo e(__('Name')); ?></th>
                                        <th><?php echo e(__('Email')); ?></th>
                                        <th><?php echo e(__('Department')); ?></th>
                                        <th><?php echo e(__('Designation')); ?></th>
                                        <th><?php echo e(__('Branch')); ?></th>
                                        <th><?php echo e(__('Employee Type')); ?></th>
                                        <th><?php echo e(__('Date Of Joining')); ?></th>
                                        <?php if(isset($showLeft) && $showLeft): ?>
                                            <th><?php echo e(__('Date Of Leaving')); ?></th>
                                        <?php endif; ?>
                                        <?php if(Gate::check('Edit Employee') || Gate::check('Delete Employee')): ?>
                                            <th width="130px"><?php echo e(__('Action')); ?></th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Show Employee')): ?>
                                                    <a class="btn btn-outline-primary"
                                                        href="<?php echo e(route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id))); ?>"><?php echo e(\Auth::user()->employeeIdFormat($employee->employee_id)); ?></a>
                                                <?php else: ?>
                                                    <a href="#"
                                                        class="btn btn-outline-primary"><?php echo e(\Auth::user()->employeeIdFormat($employee->employee_id)); ?></a>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($employee->name ?? '-'); ?></td>
                                            <td><?php echo e($employee->email ?? '-'); ?></td>  
                                            <td><?php echo e($employee->department?->name ?? '-'); ?></td>
                                            <td><?php echo e($employee->designation?->name ?? '-'); ?></td>
                                            <td><?php echo e($employee->branch?->name ?? '-'); ?></td>
                                            <td>
                                                <?php if($employee->employee_type): ?>
                                                    <span class="badge bg-<?php echo e($employee->employee_type == 'Consultant' ? 'warning' : 'success'); ?>">
                                                        <?php echo e($employee->employee_type); ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo e(\Auth::user()->dateFormat($employee->company_doj)); ?>

                                            </td>
                                            <?php if(isset($showLeft) && $showLeft): ?>
                                                <td>
                                                    <?php echo e(\Auth::user()->dateFormat($employee->company_dol)); ?>

                                                </td>
                                            <?php endif; ?>
                                            <?php if(Gate::check('Edit Employee') || Gate::check('Delete Employee')): ?>
                                                <td class="Action">
                                                        <?php if(($employee->user?->is_active ?? 0) == 1 && ($employee->user?->is_disable ?? 0) == 1): ?>                                                    <span>
                                                            <div class="d-flex align-items-center">
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Edit Employee')): ?>
                                                                    <div class="action-btn bg-info me-2">
                                                                        <a href="<?php echo e(route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id))); ?>"
                                                                            class="mx-3 btn btn-sm align-items-center"
                                                                            data-bs-toggle="tooltip" title=""
                                                                            data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                                                            <i class="ti ti-pencil text-white"></i>
                                                                        </a>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <!-- Confirmation Button for Contract and Payroll Employees -->
                                                                <div class="action-btn-confirm me-2">
                                                                    <?php if($employee->employee_type == 'Consultant' || $employee->employee_type == 'Payroll'): ?>
                                                                        <?php if(!$employee->confirm_of_employment): ?>
                                                                            <div class="action-btn bg-success">
                                                                                <button type="button" 
                                                                                        class="mx-3 btn btn-sm align-items-center text-white"
                                                                                        data-bs-toggle="modal" 
                                                                                        data-bs-target="#confirmEmploymentModal"
                                                                                        data-employee-id="<?php echo e($employee->id); ?>"
                                                                                        data-employee-name="<?php echo e($employee->name); ?>"
                                                                                        data-bs-toggle="tooltip" 
                                                                                        title="<?php echo e(__('Confirm Employment')); ?>">
                                                                                    <i class="ti ti-check"></i>
                                                                                </button>
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <div class="action-btn bg-warning">
                                                                                <button type="button" 
                                                                                        class="mx-3 btn btn-sm align-items-center text-white"
                                                                                        data-bs-toggle="modal" 
                                                                                        data-bs-target="#cancelEmploymentModal"
                                                                                        data-employee-id="<?php echo e($employee->id); ?>"
                                                                                        data-employee-name="<?php echo e($employee->name); ?>"
                                                                                        data-bs-toggle="tooltip" 
                                                                                        title="<?php echo e(__('Cancel Confirmation')); ?>">
                                                                                    <i class="ti ti-x"></i>
                                                                                </button>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                </div>

                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Delete Employee')): ?>
                                                                    <div class="action-btn bg-danger">
                                                                        <a href="#"
                                                                            class="mx-3 btn btn-sm align-items-center text-white"
                                                                            onclick="if(confirm('<?php echo e(__("Are you sure?")); ?>')) { document.getElementById('delete-form-<?php echo e($employee->id); ?>').submit(); } return false;"
                                                                            data-bs-toggle="tooltip" title=""
                                                                            data-bs-original-title="Delete" aria-label="Delete">
                                                                            <i class="ti ti-trash"></i>
                                                                        </a>
                                                                        <?php echo Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['employee.destroy', $employee->id],
                                                                            'id' => 'delete-form-' . $employee->id,
                                                                            'style' => 'display: none;'
                                                                        ]); ?>

                                                                        <?php echo Form::close(); ?>

                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </span>
                                                    <?php else: ?>
                                                        <i class="ti ti-lock"></i>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>

        </div>
    <?php $__env->stopSection(); ?>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmEmploymentModal" tabindex="-1" aria-labelledby="confirmEmploymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmEmploymentModalLabel"><?php echo e(__('Confirmation of Employment')); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?php echo e(__('Are you sure you want to confirm the employment for')); ?> <strong id="employeeName"></strong>?</p>
                    <p class="text-muted"><?php echo e(__('This action will mark the employee as confirmed and cannot be undone.')); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
                    <button type="button" class="btn btn-success" id="confirmEmploymentBtn">
                        <i class="ti ti-check me-2"></i><?php echo e(__('Approve')); ?>

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
                    <h5 class="modal-title" id="cancelEmploymentModalLabel"><?php echo e(__('Cancel Confirmation')); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?php echo e(__('Are you sure you want to cancel the confirmation for')); ?> <strong id="cancelEmployeeName"></strong>?</p>
                    <p class="text-muted"><?php echo e(__('This action will mark the employee as unconfirmed.')); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Back')); ?></button>
                    <button type="button" class="btn btn-warning" id="cancelEmploymentBtn">
                        <i class="ti ti-x me-2"></i><?php echo e(__('Cancel Confirmation')); ?>

                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function() {
            // Show/hide confirmation filter based on employee type selection
            function toggleConfirmationFilter() {
                var employeeType = $('#employee_type_filter').val();
                if (employeeType === 'Consultant' || employeeType === 'Payroll') {
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
                
                // Remove confirmation filter if not Consultant or Payroll
                if (employeeType !== 'Consultant' && employeeType !== 'Payroll') {
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
                $(this).prop('disabled', true).html('<i class="ti ti-loader ti-spin me-2"></i><?php echo e(__('Processing...')); ?>');
                
                // Send AJAX request to confirm employment
                $.ajax({
                    url: '<?php echo e(route("employee.confirm-employment")); ?>',
                    method: 'POST',
                    data: {
                        employee_id: employeeId,
                        _token: $('meta[name="csrf-token"]').attr('content') || '<?php echo e(csrf_token()); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Close modal
                            modal.modal('hide');
                            
                            // Show success message
                            var toast = '<div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">' +
                                        '<i class="ti ti-check me-2"></i><?php echo e(__("Employment confirmed successfully!")); ?>' +
                                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                                        '</div>';
                            $('body').append(toast);
                            
                            // Reload page to show updated status
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            alert(response.message || '<?php echo e(__("An error occurred. Please try again.")); ?>');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON?.message || '<?php echo e(__("An error occurred. Please try again.")); ?>';
                        
                        // Check specifically for CSRF token mismatch
                        if (xhr.status === 419 || xhr.responseJSON?.message?.includes('CSRF') || xhr.responseJSON?.exception?.includes('CSRF')) {
                            errorMessage = '<?php echo e(__("CSRF token mismatch. Please refresh the page and try again.")); ?>';
                            // Optionally reload the page after showing the error
                            setTimeout(function() {
                                window.location.reload();
                            }, 3000);
                        }
                        
                        alert(errorMessage);
                    },
                    complete: function() {
                        // Re-enable button
                        $('#confirmEmploymentBtn').prop('disabled', false).html('<i class="ti ti-check me-2"></i><?php echo e(__("Approve")); ?>');
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
                $(this).prop('disabled', true).html('<i class="ti ti-loader ti-spin me-2"></i><?php echo e(__('Processing...')); ?>');
                
                // Send AJAX request to cancel confirmation
                $.ajax({
                    url: '<?php echo e(route("employee.cancel-employment")); ?>',
                    method: 'POST',
                    data: {
                        employee_id: employeeId,
                        _token: $('meta[name="csrf-token"]').attr('content') || '<?php echo e(csrf_token()); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Close modal
                            modal.modal('hide');
                            
                            // Show success message
                            var toast = '<div class="alert alert-warning alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">' +
                                        '<i class="ti ti-x me-2"></i><?php echo e(__("Confirmation cancelled successfully!")); ?>' +
                                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                                        '</div>';
                            $('body').append(toast);
                            
                            // Reload page to show updated status
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            alert(response.message || '<?php echo e(__("An error occurred. Please try again.")); ?>');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON?.message || '<?php echo e(__("An error occurred. Please try again.")); ?>';
                        
                        // Check specifically for CSRF token mismatch
                        if (xhr.status === 419 || xhr.responseJSON?.message?.includes('CSRF') || xhr.responseJSON?.exception?.includes('CSRF')) {
                            errorMessage = '<?php echo e(__("CSRF token mismatch. Please refresh the page and try again.")); ?>';
                            // Optionally reload the page after showing the error
                            setTimeout(function() {
                                window.location.reload();
                            }, 3000);
                        }
                        
                        alert(errorMessage);
                    },
                    complete: function() {
                        // Re-enable button
                        $('#cancelEmploymentBtn').prop('disabled', false).html('<i class="ti ti-x me-2"></i><?php echo e(__("Cancel Confirmation")); ?>');
                    }
                });
            });
        });
    </script>
    <?php $__env->stopPush(); ?>

    <?php $__env->startPush('styles'); ?>
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
    <?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/employee/index.blade.php ENDPATH**/ ?>