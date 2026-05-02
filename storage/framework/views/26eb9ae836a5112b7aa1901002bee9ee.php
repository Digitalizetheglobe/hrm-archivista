    

    <?php $__env->startSection('page-title'); ?>
        <?php echo e(__('Project List')); ?>

    <?php $__env->stopSection(); ?>

    <?php $__env->startSection('breadcrumb'); ?>
        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
        <li class="breadcrumb-item"><?php echo e(__('Project List')); ?></li>
    <?php $__env->stopSection(); ?> 

    <?php $__env->startSection('action-button'); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Create Employee')): ?>
        <a href="#" data-url="<?php echo e(route('projects.create')); ?>" data-ajax-popup="true"
            data-title="<?php echo e(__('Add New Project')); ?>" data-size="lg" data-bs-toggle="tooltip" title="Create"
            class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        
        

        <a href="#" data-url="<?php echo e(route('projects.import')); ?>" data-ajax-popup="true"
            data-title="<?php echo e(__('Import Projects')); ?>" data-size="md" data-bs-toggle="tooltip" title="Import"
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
                                    <th><?php echo e(__('Project Name')); ?></th>
                                    <th><?php echo e(__('Client Name')); ?></th>
                                    <?php if(Gate::check('Edit Meeting') || Gate::check('Delete Meeting')): ?>
                                            <th width="200px"><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($project->project_name); ?></td>
                                        <td><?php echo e($project->client->client_name ?? 'N/A'); ?></td>                                       
                                        <?php if(Gate::check('Edit Meeting') || Gate::check('Delete Meeting')): ?>

                                            <td class="Action">
                                                <span>
                                                    <!-- Edit Button -->
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Edit Meeting')): ?>
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" 
                                                            class="mx-3 btn btn-sm align-items-center" 
                                                            data-url="<?php echo e(route('projects.edit', $project->id)); ?>" 
                                                            data-ajax-popup="true" 
                                                            data-size="lg" 
                                                            data-bs-toggle="tooltip" 
                                                            data-title="<?php echo e(__('Edit Project')); ?>" 
                                                            data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- Delete Button -->
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Delete Meeting')): ?>
                                                        <div class="action-btn bg-danger ms-2">
                                                            <?php echo Form::open([
                                                                'method' => 'DELETE', 
                                                                'route' => ['projects.destroy', $project->id], 
                                                                'style' => 'display:inline'
                                                            ]); ?>

                                                            <a href="#" 
                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para" 
                                                            data-bs-toggle="tooltip" 
                                                            title="<?php echo e(__('Delete Project')); ?>" 
                                                            data-bs-original-title="<?php echo e(__('Delete')); ?>" 
                                                            aria-label="<?php echo e(__('Delete')); ?>" 
                                                            onclick="event.preventDefault(); document.getElementById('delete-form-<?php echo e($project->id); ?>').submit();">
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
                                        <td colspan="<?php echo e(Auth::user()->type != 'employee' ? '6' : '5'); ?>" class="text-center"><?php echo e(__('No projects found')); ?></td>
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/projects/index.blade.php ENDPATH**/ ?>