

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Client List')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Client List')); ?></li>
<?php $__env->stopSection(); ?> 

<?php $__env->startSection('action-button'); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Create Employee')): ?>
        <a href="#" data-url="<?php echo e(route('clients.create')); ?>" data-ajax-popup="true"
            data-title="<?php echo e(__('Add New Client')); ?>" data-size="lg" data-bs-toggle="tooltip" title="Create"
            class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        
        
        <a href="#" data-url="<?php echo e(route('clients.import')); ?>" data-ajax-popup="true"
            data-title="<?php echo e(__('Import Clients')); ?>" data-size="md" data-bs-toggle="tooltip" title="Import"
            class="btn btn-sm btn-primary">
            <i class="ti ti-file-import"></i>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?> 

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Client Code')); ?></th>
                                <th><?php echo e(__('Client Name')); ?></th>
                                <th><?php echo e(__('Company Email')); ?></th>
                                <th><?php echo e(__('Company Phone')); ?></th>
                                <?php if(Gate::check('Edit Employee') || Gate::check('Delete Employee')): ?>
                                    <th width="200px"><?php echo e(__('Action')); ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($client->client_code); ?></td>
                                    <td><?php echo e($client->client_name); ?></td>
                                    <td><?php echo e($client->company_email); ?></td>
                                    <td><?php echo e($client->company_phone); ?></td>
                                    <?php if(Gate::check('Edit Employee') || Gate::check('Delete Employee')): ?>
                                        <td class="Action">
                                            <span>
                                                <!-- Edit Button -->
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Edit Employee')): ?>
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" 
                                                        class="mx-3 btn btn-sm align-items-center" 
                                                        data-url="<?php echo e(route('clients.edit', $client->id)); ?>" 
                                                        data-ajax-popup="true" 
                                                        data-size="lg" 
                                                        data-bs-toggle="tooltip" 
                                                        data-title="<?php echo e(__('Edit Client')); ?>" 
                                                        data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Delete Button -->
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Delete Employee')): ?>
                                                    <div class="action-btn bg-danger ms-2">
                                                        <?php echo Form::open([
                                                            'method' => 'DELETE', 
                                                            'route' => ['clients.destroy', $client->id], 
                                                            'style' => 'display:inline'
                                                        ]); ?>

                                                        <a href="#" 
                                                        class="mx-3 btn btn-sm align-items-center bs-pass-para" 
                                                        data-bs-toggle="tooltip" 
                                                        title="<?php echo e(__('Delete Client')); ?>" 
                                                        data-bs-original-title="<?php echo e(__('Delete')); ?>" 
                                                        aria-label="<?php echo e(__('Delete')); ?>" 
                                                        onclick="event.preventDefault(); document.getElementById('delete-form-<?php echo e($client->id); ?>').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        <?php echo Form::close(); ?>

                                                    </div>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center"><?php echo e(__('No clients found')); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/clients/index.blade.php ENDPATH**/ ?>