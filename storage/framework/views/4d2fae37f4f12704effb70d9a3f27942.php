<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Leave Details')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('leave.index')); ?>"><?php echo e(__('Leave')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Leave Details')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <style>
        /* ---- Stat Cards (Matched from Dashboard) ---- */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            border: 1px solid #f1f3f5;
            position: relative;
            overflow: hidden;
            transition: box-shadow 0.22s ease, transform 0.22s ease;
        }
        .stat-card::before {
            content: ''; position: absolute;
            top: -18px; right: -18px;
            width: 80px; height: 80px;
            background: rgba(232, 89, 12, 0.05);
            border-radius: 50%;
            pointer-events: none;
        }
        .stat-card:hover { box-shadow: 0 6px 15px rgba(0,0,0,0.06); transform: translateY(-2px); }
        .stat-icon {
            width: 52px; height: 52px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            color: white;
            flex-shrink: 0;
        }
        .stat-icon.ic-primary  { background: linear-gradient(135deg, #e8590c, #ff7a3d); box-shadow: 0 4px 12px rgba(232,89,12,0.35); }
        .stat-icon.ic-success  { background: linear-gradient(135deg, #22c55e, #4ade80); box-shadow: 0 4px 12px rgba(34,197,94,0.35); }
        .stat-icon.ic-warning  { background: linear-gradient(135deg, #f59e0b, #fbbf24); box-shadow: 0 4px 12px rgba(245,158,11,0.35); }
        .stat-icon.ic-info     { background: linear-gradient(135deg, #0ea5e9, #38bdf8); box-shadow: 0 4px 12px rgba(14,165,233,0.35); }
        
        .stat-label { font-size: 11px; color: #8492a6; font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px; }
        .stat-value { font-size: 26px; font-weight: 800; color: #3c4b64; line-height: 1; }
        
        /* Employee Group Header in Table */
        .emp-group-header td {
            background-color: rgba(232, 89, 12, 0.06) !important;
            border-top: 2px solid #e8590c !important;
            border-bottom: 1px solid #e8590c !important;
            padding: 14px 16px !important;
            font-weight: 700 !important;
            font-size: 15px !important;
            color: #e8590c !important;
        }
        .employee-category h4 {
            font-size: 16px;
            font-weight: 700;
            color: #3c4b64;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="form-group mb-0">
                <label for="month_filter" class="form-label" style="font-size: 12px; font-weight: 600; color: #8492a6; text-transform: uppercase;"><?php echo e(__('Select Month')); ?></label>
                <input type="month" id="month_filter" name="month" class="form-control" 
                       value="<?php echo e($selectedMonth); ?>" onchange="window.location.href='?month='+this.value"
                       style="border-radius: 8px; border-color: #e2e8f0; padding: 10px 14px;">
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="row g-3">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="stat-card">
                        <div class="stat-icon ic-primary"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <div class="stat-label"><?php echo e(__('Total Leaves')); ?></div>
                            <div class="stat-value"><?php echo e($monthlySummary['total_leaves']); ?> <span style="font-size: 12px; color: #8492a6; font-weight: 600;">Days</span></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="stat-card">
                        <div class="stat-icon ic-success"><i class="fas fa-plus-circle"></i></div>
                        <div>
                            <div class="stat-label"><?php echo e(__('Credited Leaves')); ?></div>
                            <div class="stat-value"><?php echo e($monthlySummary['credited_leaves']); ?> <span style="font-size: 12px; color: #8492a6; font-weight: 600;">Days</span></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="stat-card">
                        <div class="stat-icon ic-warning"><i class="fas fa-minus-circle"></i></div>
                        <div>
                            <div class="stat-label"><?php echo e(__('Used Leaves')); ?></div>
                            <div class="stat-value"><?php echo e($monthlySummary['used_leaves']); ?> <span style="font-size: 12px; color: #8492a6; font-weight: 600;">Days</span></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="stat-card">
                        <div class="stat-icon ic-info"><i class="fas fa-hourglass-half"></i></div>
                        <div>
                            <div class="stat-label"><?php echo e(__('Remaining Leaves')); ?></div>
                            <div class="stat-value"><?php echo e($monthlySummary['remaining_leaves']); ?> <span style="font-size: 12px; color: #8492a6; font-weight: 600;">Days</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($contractConfirmEmployees->count() > 0): ?>
        <div class="employee-category mb-5">
            <h4 class="mb-3">
                <i class="fas fa-user-check" style="color: var(--app-primary);"></i>
                <?php echo e(__('Contract (Confirm) Employees')); ?>

                <span class="badge" style="background-color: var(--app-primary); font-size: 12px;"><?php echo e($contractConfirmEmployees->count()); ?></span>
            </h4>
            
            <div class="card shadow-none" style="border: 1px solid var(--app-table-border); border-radius: 10px;">
                <div class="card-body p-0">
                    <div class="table-responsive" style="border: none; margin-bottom: 0; padding: 25px">
                        <table class="table">
                            <thead>
                                <tr>
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
                                    ?>
                                    
                                    <tr class="emp-group-header">
                                        <td colspan="5">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-user-circle" style="font-size: 18px;"></i>
                                                <?php echo e($employee->name); ?>

                                                <span style="color: #8492a6; font-size: 12px; font-weight: 500; margin-left: 8px;">(<?php echo e($employee->email); ?>)</span>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <?php $__currentLoopData = $leaveBalances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leaveBalance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td style="padding-left: 30px !important;">
                                                <span style="font-weight: 600; color: #475569;"><?php echo e($leaveBalance['leave_type']->title); ?></span>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="badge bg-info ms-2"><?php echo e(__('Unlimited')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="text-muted"><?php echo e(__('N/A')); ?></span>
                                                <?php else: ?>
                                                    <?php echo e($leaveBalance['allocated_days']); ?>

                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($leaveBalance['carried_forward_days'] > 0): ?>
                                                    <span class="text-success fw-bold">+<?php echo e($leaveBalance['carried_forward_days']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="fw-bold"><?php echo e($leaveBalance['used_days']); ?></span></td>
                                            <td>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="fw-bold text-info"><?php echo e(__('Unlimited')); ?></span>
                                                <?php else: ?>
                                                    <span class="fw-bold <?php echo e($leaveBalance['remaining_days'] > 0 ? 'text-success' : 'text-danger'); ?>">
                                                        <?php echo e($leaveBalance['remaining_days']); ?>

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
        <div class="employee-category mb-5">
            <h4 class="mb-3">
                <i class="fas fa-user-times text-warning"></i>
                <?php echo e(__('Contract (Not Confirm) Employees')); ?>

                <span class="badge bg-warning" style="font-size: 12px;"><?php echo e($contractNotConfirmEmployees->count()); ?></span>
            </h4>
            
            <div class="card shadow-none" style="border: 1px solid var(--app-table-border); border-radius: 10px;">
                <div class="card-body p-0">
                    <div class="table-responsive" style="border: none; margin-bottom: 0; padding: 25px">
                        <table class="table">
                            <thead>
                                <tr>
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
                                    ?>
                                    
                                    <tr class="emp-group-header">
                                        <td colspan="5">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-user-circle" style="font-size: 18px;"></i>
                                                <?php echo e($employee->name); ?>

                                                <span style="color: #8492a6; font-size: 12px; font-weight: 500; margin-left: 8px;">(<?php echo e($employee->email); ?>)</span>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <?php $__currentLoopData = $leaveBalances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leaveBalance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td style="padding-left: 30px !important;">
                                                <span style="font-weight: 600; color: #475569;"><?php echo e($leaveBalance['leave_type']->title); ?></span>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="badge bg-info ms-2"><?php echo e(__('Unlimited')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="text-muted"><?php echo e(__('N/A')); ?></span>
                                                <?php else: ?>
                                                    <?php echo e($leaveBalance['allocated_days']); ?>

                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($leaveBalance['carried_forward_days'] > 0): ?>
                                                    <span class="text-success fw-bold">+<?php echo e($leaveBalance['carried_forward_days']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="fw-bold"><?php echo e($leaveBalance['used_days']); ?></span></td>
                                            <td>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="fw-bold text-info"><?php echo e(__('Unlimited')); ?></span>
                                                <?php else: ?>
                                                    <span class="fw-bold <?php echo e($leaveBalance['remaining_days'] > 0 ? 'text-success' : 'text-danger'); ?>">
                                                        <?php echo e($leaveBalance['remaining_days']); ?>

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
        <div class="employee-category mb-5">
            <h4 class="mb-3">
                <i class="fas fa-users text-success"></i>
                <?php echo e(__('Payroll Employees')); ?>

                <span class="badge bg-success" style="font-size: 12px;"><?php echo e($payrollEmployees->count()); ?></span>
            </h4>
            
            <div class="card shadow-none" style="border: 1px solid var(--app-table-border); border-radius: 10px;">
                <div class="card-body p-0">
                    <div class="table-responsive" style="border: none; margin-bottom: 0; padding: 25px">
                        <table class="table">
                            <thead>
                                <tr>
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
                                    ?>
                                    
                                    <tr class="emp-group-header">
                                        <td colspan="5">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-user-circle" style="font-size: 18px;"></i>
                                                <?php echo e($employee->name); ?>

                                                <span style="color: #8492a6; font-size: 12px; font-weight: 500; margin-left: 8px;">(<?php echo e($employee->email); ?>)</span>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <?php $__currentLoopData = $leaveBalances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leaveBalance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td style="padding-left: 30px !important;">
                                                <span style="font-weight: 600; color: #475569;"><?php echo e($leaveBalance['leave_type']->title); ?></span>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="badge bg-info ms-2"><?php echo e(__('Unlimited')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="text-muted"><?php echo e(__('N/A')); ?></span>
                                                <?php else: ?>
                                                    <?php echo e($leaveBalance['allocated_days']); ?>

                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($leaveBalance['carried_forward_days'] > 0): ?>
                                                    <span class="text-success fw-bold">+<?php echo e($leaveBalance['carried_forward_days']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="fw-bold"><?php echo e($leaveBalance['used_days']); ?></span></td>
                                            <td>
                                                <?php if($leaveBalance['is_unlimited']): ?>
                                                    <span class="fw-bold text-info"><?php echo e(__('Unlimited')); ?></span>
                                                <?php else: ?>
                                                    <span class="fw-bold <?php echo e($leaveBalance['remaining_days'] > 0 ? 'text-success' : 'text-danger'); ?>">
                                                        <?php echo e($leaveBalance['remaining_days']); ?>

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
                <i class="fas fa-users fa-3x text-gray-300 mb-3" style="color: #cbd5e1;"></i>
                <h5 class="text-gray-500 mt-2"><?php echo e(__('No employees found')); ?></h5>
                <p class="text-muted"><?php echo e(__('There are no employees in the system yet.')); ?></p>
            </div>
        </div>
    <?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/leave/leave_details.blade.php ENDPATH**/ ?>