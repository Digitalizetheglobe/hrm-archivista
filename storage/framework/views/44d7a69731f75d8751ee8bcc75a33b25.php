
<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Job Allocation Details')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('joballocation.index')); ?>"><?php echo e(__('Job Allocations')); ?></a></li>
    <li class="breadcrumb-item active"><?php echo e(__('Allocation Details')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?><br>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Job Allocation </h4>
                </div>
                
                <div class="card-body">
                    <!-- Basic Information Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">Basic Information : </h5><br>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                        <tr>
                                            <th width="40%">Status</th>
                                            <td>
                                                <?php if($jobAllocation->status == 'Ongoing'): ?>
                                                    <span class=""><?php echo e($jobAllocation->status); ?></span>
                                                <?php elseif($jobAllocation->status == 'Completed'): ?>
                                                    <span class="><?php echo e($jobAllocation->status); ?></span>
                                                <?php else: ?>
                                                    <span class=""><?php echo e($jobAllocation->status); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Client</th>
                                            <td><?php echo e($jobAllocation->client->client_name ?? 'N/A'); ?></td>
                                            </tr>
                                        <tr>
                                            <th>Project</th>
                                            <td><?php echo e($jobAllocation->project->project_name ?? 'N/A'); ?></td>
                                            </tr>
                                        <tr>
                                            <th>Job Allocate Date</th>
                                            <td><?php echo e($jobAllocation->created_at ? $jobAllocation->created_at->format('Y-m-d') : 'N/A'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">Timing & Budget : </h5><br>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                        <tr>
                                            <th width="40%">Start Date</th>
                                            <td><?php echo e(\Carbon\Carbon::parse($jobAllocation->start_date)->format('d M Y')); ?></td>
                                        </tr>
                                        <tr>
                                            <th>End Date</th>
                                            <td><?php echo e($jobAllocation->end_date ? \Carbon\Carbon::parse($jobAllocation->end_date)->format('d M Y') : 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Billable</th>
                                            <td>
                                                <?php if($jobAllocation->billable): ?>
                                                    <span>Billable</span>
                                                <?php else: ?>
                                                    <span >Non-Billable</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Budgeting</th>
                                            <td>
                                                <?php if($jobAllocation->budgeting): ?>  
                                                    <span >Employees</span>
                                                <?php else: ?>
                                                    <span >Projects</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div><br>
                    
                    <!-- Departments & Employees Section -->
                    <div class="row mb-12   ">
                       
                        
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Assigned Employees : </h5><br>
                            <?php
                                $employeeIds = json_decode($jobAllocation->employees_id, true) ?? [];
                                $employees = \App\Models\Employee::whereIn('id', $employeeIds)->with('department')->get();
                            ?>
                            
                            <?php if($employees->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Employee</th>
                                                <th>Department</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td><?php echo e($employee->name); ?></td>
                                                    <td><?php echo e($employee->department->name ?? 'N/A'); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">No employees assigned</div>
                            <?php endif; ?>
                        </div>
                    </div><br>
                    
                    
                    <!-- Narration Section -->
                    <div class="row">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Narration : </h5>
                            <div class="bg-light p-3 rounded">
                                <?php if($jobAllocation->narration): ?>
                                    <?php echo nl2br(e($jobAllocation->narration)); ?>

                                <?php else: ?>
                                    <div class="text-muted">No narration provided</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer text-end">
                    <a href="<?php echo e(route('joballocation.index')); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <?php if(auth()->user()->can('edit-joballocation')): ?>
                        <a href="<?php echo e(route('joballocation.edit', $jobAllocation->id)); ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .card-header {
        padding: 0.75rem 1.25rem;
    }
    th {
        font-weight: 500;
    }
    .table-sm td, .table-sm th {
        padding: 0.5rem;
    }
    .list-group-item {
        padding: 0.5rem 1rem;
    }
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/joballocation/show.blade.php ENDPATH**/ ?>