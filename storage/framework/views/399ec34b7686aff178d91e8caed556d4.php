

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('To Do List')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Employee')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <a href="#" data-url="<?php echo e(route('todo.create')); ?>" data-ajax-popup="true"
        data-title="<?php echo e(__('Create New ToDo')); ?>" data-size="lg" data-bs-toggle="tooltip" title="Create"
        class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i>
    </a>
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
                                <th><?php echo e(__('Task Title')); ?></th>
                                <th><?php echo e(__('Priority')); ?></th>
                                <th><?php echo e(__('Due Date')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <th width="130px"><?php echo e(__('Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($task->task); ?></td>
                                    <td>
                                        <?php if($task->priority == 'high'): ?>
                                            <span class="badge bg-danger"><?php echo e(__('High')); ?></span>
                                        <?php elseif($task->priority == 'medium'): ?>
                                            <span class="badge bg-warning"><?php echo e(__('Medium')); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?php echo e(__('Low')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($task->expires_at ? \Carbon\Carbon::parse($task->expires_at)->format('Y-m-d') : __('No Deadline')); ?></td>
                                    <td>
                                        <?php if($task->is_completed): ?>
                                            <span class="badge bg-success"><?php echo e(__('Completed')); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning"><?php echo e(__('Pending')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                    <div class="action-btn bg-info ms-2">
                                        <a href="#" class="mx-3 btn btn-sm align-items-center"
                                            data-url="<?php echo e(route('todo.edit', $task->id)); ?>"
                                            data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip"
                                            data-title="<?php echo e(__('Edit ToDo')); ?>" data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                            <i class="ti ti-pencil text-white"></i>
                                        </a>
                                    </div>
                                    <div class="action-btn bg-danger ms-2">
                                        <?php echo Form::open([
                                            'method' => 'DELETE',
                                            'route' => ['todo.destroy', $task->id],
                                            
                                        ]); ?>

                                        <a href="#"
                                            class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                            data-bs-toggle="tooltip" title="<?php echo e(__('Delete Task')); ?>"
                                            data-bs-original-title="<?php echo e(__('Delete')); ?>" aria-label="<?php echo e(__('Delete')); ?>"
                                            onclick="event.preventDefault(); document.getElementById('delete-form-<?php echo e($task->id); ?>').submit();">
                                            <i class="ti ti-trash text-white"></i>
                                        </a>
                                        <?php echo Form::close(); ?>

                                    </div>
  
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<!-- Include DataTables JS -->
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

<!-- <script type="text/javascript">
    $(document).ready(function() {
        $('#pc-dt-simple').DataTable({
            "language": {
                "emptyTable": "No entries found" // This will show when there are no tasks in the table
            },
            "lengthMenu": [10, 25, 50, 100],  // Controls the number of entries per page
        });
    });
</script> -->
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/todo/index.blade.php ENDPATH**/ ?>