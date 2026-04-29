<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Leave Details')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('leave.index')); ?>"><?php echo e(__('Leave')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Leave Details')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="form-group">
                <label for="month_filter" class="form-label"><?php echo e(__('Select Month')); ?></label>
                <input type="month" id="month_filter" name="month" class="form-control" 
                       value="<?php echo e($selectedMonth); ?>" onchange="window.location.href='?month='+this.value">
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        <?php echo e(__('Total Leaves')); ?>

                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo e($monthlySummary['total_leaves']); ?> 
                                        <span class="text-xs text-muted"><?php echo e(__('Days')); ?></span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        <?php echo e(__('Credited Leaves')); ?>

                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo e($monthlySummary['credited_leaves']); ?> 
                                        <span class="text-xs text-muted"><?php echo e(__('Days')); ?></span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-plus-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        <?php echo e(__('Used Leaves')); ?>

                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo e($monthlySummary['used_leaves']); ?> 
                                        <span class="text-xs text-muted"><?php echo e(__('Days')); ?></span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-minus-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        <?php echo e(__('Remaining Leaves')); ?>

                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo e($monthlySummary['remaining_leaves']); ?> 
                                        <span class="text-xs text-muted"><?php echo e(__('Days')); ?></span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($contractConfirmEmployees->count() > 0): ?>
        <div class="employee-category">
            <h4 class="mb-3">
                <i class="fas fa-user-check text-primary mr-2"></i>
                <?php echo e(__('Contract (Confirm) Employees')); ?>

                <span class="badge bg-primary ml-2"><?php echo e($contractConfirmEmployees->count()); ?></span>
            </h4>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Employee Name')); ?></th>
                                    <th><?php echo e(__('Leave Type')); ?></th>
                                    <th><?php echo e(__('Allocated Days')); ?></th>
                                    <th><?php echo e(__('Carried Forward')); ?></th>
                                    <th><?php echo e(__('Used Days')); ?></th>
                                    <th><?php echo e(__('Remaining Days')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $leaveDetails['contract_confirm']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employeeDetail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $employee = $employeeDetail['employee'];
                                        $leaveBalances = $employeeDetail['leave_balances'];
                                        $firstRow = true;
                                    ?>
                                    
                                    <?php $__currentLoopData = $leaveBalances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leaveBalance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <?php if($firstRow): ?>
                                                <td rowspan="<?php echo count($leaveBalances); ?>" class="employee-name">
                                                    <?php echo $employee->name; ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo $employee->email; ?></small>
                                                </td>
                                                <?php $firstRow = false; ?>
                                            <?php endif; ?>
                                            
                                            <td class="leave-type-title">
                                                <?php echo $leaveBalance['leave_type']->title; ?>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="badge bg-info leave-badge"><?php echo e(__('Unlimited')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="text-muted"><?php echo e(__('N/A')); ?></span>
                                                <?php else: ?>
                                                    <?php echo $leaveBalance['allocated_days']; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($leaveBalance['carried_forward_days'] > 0): ?>
                                                    <span class="text-success">+<?php echo $leaveBalance['carried_forward_days']; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $leaveBalance['used_days']; ?></td>
                                            <td>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="remaining-days unlimited"><?php echo e(__('Unlimited')); ?></span>
                                                <?php else: ?>
                                                    <span class="remaining-days <?php echo $leaveBalance['remaining_days'] > 0 ? 'positive' : 'zero'; ?>">
                                                        <?php echo $leaveBalance['remaining_days']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if($contractNotConfirmEmployees->count() > 0): ?>
        <div class="employee-category">
            <h4 class="mb-3">
                <i class="fas fa-user-times text-warning mr-2"></i>
                <?php echo e(__('Contract (Not Confirm) Employees')); ?>

                <span class="badge bg-warning ml-2"><?php echo e($contractNotConfirmEmployees->count()); ?></span>
            </h4>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Employee Name')); ?></th>
                                    <th><?php echo e(__('Leave Type')); ?></th>
                                    <th><?php echo e(__('Allocated Days')); ?></th>
                                    <th><?php echo e(__('Carried Forward')); ?></th>
                                    <th><?php echo e(__('Used Days')); ?></th>
                                    <th><?php echo e(__('Remaining Days')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $leaveDetails['contract_not_confirm']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employeeDetail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $employee = $employeeDetail['employee'];
                                        $leaveBalances = $employeeDetail['leave_balances'];
                                        $firstRow = true;
                                    ?>
                                    
                                    <?php $__currentLoopData = $leaveBalances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leaveBalance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <?php if($firstRow): ?>
                                                <td rowspan="<?php echo count($leaveBalances); ?>" class="employee-name">
                                                    <?php echo $employee->name; ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo $employee->email; ?></small>
                                                </td>
                                                <?php $firstRow = false; ?>
                                            <?php endif; ?>
                                            
                                            <td class="leave-type-title">
                                                <?php echo $leaveBalance['leave_type']->title; ?>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="badge bg-info leave-badge"><?php echo e(__('Unlimited')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="text-muted"><?php echo e(__('N/A')); ?></span>
                                                <?php else: ?>
                                                    <?php echo $leaveBalance['allocated_days']; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($leaveBalance['carried_forward_days'] > 0): ?>
                                                    <span class="text-success">+<?php echo $leaveBalance['carried_forward_days']; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $leaveBalance['used_days']; ?></td>
                                            <td>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="remaining-days unlimited"><?php echo e(__('Unlimited')); ?></span>
                                                <?php else: ?>
                                                    <span class="remaining-days <?php echo $leaveBalance['remaining_days'] > 0 ? 'positive' : 'zero'; ?>">
                                                        <?php echo $leaveBalance['remaining_days']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if($payrollEmployees->count() > 0): ?>
        <div class="employee-category">
            <h4 class="mb-3">
                <i class="fas fa-users text-success mr-2"></i>
                <?php echo e(__('Payroll Employees')); ?>

                <span class="badge bg-success ml-2"><?php echo e($payrollEmployees->count()); ?></span>
            </h4>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Employee Name')); ?></th>
                                    <th><?php echo e(__('Leave Type')); ?></th>
                                    <th><?php echo e(__('Allocated Days')); ?></th>
                                    <th><?php echo e(__('Carried Forward')); ?></th>
                                    <th><?php echo e(__('Used Days')); ?></th>
                                    <th><?php echo e(__('Remaining Days')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $leaveDetails['payroll']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employeeDetail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $employee = $employeeDetail['employee'];
                                        $leaveBalances = $employeeDetail['leave_balances'];
                                        $firstRow = true;
                                    ?>
                                    
                                    <?php $__currentLoopData = $leaveBalances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leaveBalance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <?php if($firstRow): ?>
                                                <td rowspan="<?php echo count($leaveBalances); ?>" class="employee-name">
                                                    <?php echo $employee->name; ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo $employee->email; ?></small>
                                                </td>
                                                <?php $firstRow = false; ?>
                                            <?php endif; ?>
                                            
                                            <td class="leave-type-title">
                                                <?php echo $leaveBalance['leave_type']->title; ?>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="badge bg-info leave-badge"><?php echo e(__('Unlimited')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="text-muted"><?php echo e(__('N/A')); ?></span>
                                                <?php else: ?>
                                                    <?php echo $leaveBalance['allocated_days']; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($leaveBalance['carried_forward_days'] > 0): ?>
                                                    <span class="text-success">+<?php echo $leaveBalance['carried_forward_days']; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $leaveBalance['used_days']; ?></td>
                                            <td>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="remaining-days unlimited"><?php echo e(__('Unlimited')); ?></span>
                                                <?php else: ?>
                                                    <span class="remaining-days <?php echo $leaveBalance['remaining_days'] > 0 ? 'positive' : 'zero'; ?>">
                                                        <?php echo $leaveBalance['remaining_days']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if($contractConfirmEmployees->count() == 0 && $contractNotConfirmEmployees->count() == 0 && $payrollEmployees->count() == 0): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                <h5 class="text-gray-500"><?php echo e(__('No employees found')); ?></h5>
                <p class="text-muted"><?php echo e(__('There are no employees in the system yet.')); ?></p>
            </div>
        </div>
    <?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/leave/leave_details.blade.php ENDPATH**/ ?>