<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Employee Details')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(url('employee')); ?>"><?php echo e(__('Employees')); ?></a></li>
    <li class="breadcrumb-item active"><?php echo e($employee->name); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit employee')): ?>
        <a href="<?php echo e(route('employee.edit', Crypt::encrypt($employee->id))); ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="<?php echo e(__('Edit')); ?>">
            <i class="ti ti-pencil"></i>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo e(__('Employee Information')); ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Personal Details -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="ti ti-user me-2"></i><?php echo e(__('Personal Details')); ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="w-50"><?php echo e(__('Employee ID')); ?></th>
                                                <td><?php echo e($employeesId); ?></td>
                                            </tr>
                                            <tr>
                                                <th><?php echo e(__('Name')); ?></th>
                                                <td><?php echo e($employee->name); ?></td>
                                            </tr>
                                            <tr>
                                                <th><?php echo e(__('Email')); ?></th>
                                                <td><?php echo e($employee->email); ?></td>
                                            </tr>
                                            <tr>
                                                <th><?php echo e(__('Date of Birth')); ?></th>
                                                <td><?php echo e(\Auth::user()->dateFormat($employee->dob)); ?></td>
                                            </tr>
                                            <tr>
                                                <th><?php echo e(__('Gender')); ?></th>
                                                <td><?php echo e(ucfirst($employee->gender)); ?></td>
                                            </tr>
                                            <tr>
                                                <th><?php echo e(__('Phone')); ?></th>
                                                <td><?php echo e($employee->phone); ?></td>
                                            </tr>
                                            <?php if($employee->emergency_number): ?>
                                            <tr>
                                                <th><?php echo e(__('Emergency Number')); ?></th>
                                                <td><?php echo e($employee->emergency_number); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <th><?php echo e(__('Address')); ?></th>
                                                <td><?php echo e($employee->address); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        

                        
                        <!-- Education & Skills Details -->
                        
                    </div>

                    <!-- Company Details -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="ti ti-building me-2"></i><?php echo e(__('Company Details')); ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <tbody>
                                            <?php if($employee->branch): ?>
                                            <tr>
                                                <th class="w-50"><?php echo e(__('Branch')); ?></th>
                                                <td><?php echo e($employee->branch->name); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if($employee->department): ?>
                                            <tr>
                                                <th><?php echo e(__('Department')); ?></th>
                                                <td><?php echo e($employee->department->name); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if($employee->designation): ?>
                                            <tr>
                                                <th><?php echo e(__('Designation')); ?></th>
                                                <td><?php echo e($employee->designation->name); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if($employee->hourly_charged): ?>
                                            <tr>
                                                <th><?php echo e(__('Hourly Rate')); ?></th>
                                                <td><?php echo e(\Auth::user()->priceFormat($employee->hourly_charged)); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <th><?php echo e(__('Date of Joining')); ?></th>
                                                <td><?php echo e(\Auth::user()->dateFormat($employee->company_doj)); ?></td>
                                            </tr>
                                            <?php if($employee->employee_type): ?>
                                            <tr>
                                                <th><?php echo e(__('Employee Type')); ?></th>
                                                <td><?php echo e($employee->employee_type); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if($employee->company_dol): ?>
                                            <tr>
                                                <th><?php echo e(__('Date of Leaving')); ?></th>
                                                <td><?php echo e(\Auth::user()->dateFormat($employee->company_dol)); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                       
                    </div>
                </div>

                <div class="row">
                <div class="card md-12">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="ti ti-school me-2"></i><?php echo e(__('Education & Skills')); ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <tbody>
                                            
                                            
                                            <?php if($employee->primary_skill): ?>
                                            <tr>
                                                <th><?php echo e(__('Primary Skill')); ?></th>
                                                <td><?php echo e($employee->primary_skill); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if($employee->secondary_skill): ?>
                                            <tr>
                                                <th><?php echo e(__('Secondary Skill')); ?></th>
                                                <td><?php echo e($employee->secondary_skill); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if($employee->certificate): ?>
                                            <tr>
                                                <th><?php echo e(__('Certificate')); ?></th>
                                                <td><?php echo e($employee->certificate); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                </div>

                

                <!-- Document Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="ti ti-file-download me-2"></i><?php echo e(__('Payroll Details')); ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <tbody>
                                            <?php if($employee->esic_no): ?>
                                            <tr>
                                                <th class="w-50"><?php echo e(__('ESIC NO')); ?></th>
                                                <td><?php echo e($employee->esic_no); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if($employee->bank_ac_no): ?>
                                            <tr>
                                                <th><?php echo e(__('Bank A/c No')); ?></th>
                                                <td><?php echo e($employee->bank_ac_no); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css-page'); ?>
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .card-body {
            padding: 1.25rem;
        }
        table th {
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
        }
        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .btn-outline-primary:hover {
            color: #fff;
        }
        .bg-primary {
            background-color: #3f51b5 !important;
        }
        .bg-info {
            background-color: #00bcd4 !important;
        }
        .bg-success {
            background-color: #4caf50 !important;
        }
        .bg-secondary {
            background-color: #6c757d !important;
        }
        .table td, .table th {
            padding: 0.75rem;
            vertical-align: middle;
            border-top: 1px solid #e9ecef;
        }
        .table th {
            width: 40%;
        }
    </style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/employee/show.blade.php ENDPATH**/ ?>