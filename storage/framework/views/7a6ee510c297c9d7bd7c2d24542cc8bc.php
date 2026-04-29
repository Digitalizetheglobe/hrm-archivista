

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Notice List')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Notice List')); ?></li>
<?php $__env->stopSection(); ?>


<?php if(auth()->user()->type != 'employee'): ?>
    <?php $__env->startSection('action-button'); ?>
        <div class="row align-items-center m-1">
            <?php if(auth()->user()->type == 'company'): ?>
                <a href="#" data-size="lg" data-url="<?php echo e(route('notices.create')); ?>" data-ajax-popup="true"
                    data-bs-toggle="tooltip" title="<?php echo e(__('Create New Notice')); ?>" data-title="<?php echo e(__('Add New Notice')); ?>"
                    class="btn btn-sm btn-primary">
                    <i class="ti ti-plus"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php $__env->stopSection(); ?>
<?php endif; ?>  


<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Title')); ?></th>
                                <th><?php echo e(__('Description')); ?></th>
                                <th width="130px"><?php echo e(__('Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $notices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($notice->title); ?></td>
                                    <td><?php echo e(Str::limit($notice->description, 50)); ?></td>
                                    <td class="d-flex gap-2">
                                        <!-- View Button -->
                                        <a href="#" 
                                            class="btn btn-sm btn-primary text-white" 
                                            data-bs-toggle="tooltip" 
                                            title="<?php echo e(__('View Notice')); ?>"
                                            onclick="event.preventDefault(); showNoticePopup('<?php echo e($notice->title); ?>', '<?php echo e($notice->description); ?>');">
                                            <i class="ti ti-eye"></i>
                                        </a>

                                        <?php if(auth()->user()->type == 'company'): ?>
                                            <!-- Edit Button -->
                                            <a href="#" 
                                                class="btn btn-sm btn-info text-white" 
                                                data-url="<?php echo e(route('notices.edit', $notice->id)); ?>" 
                                                data-ajax-popup="true" 
                                                data-size="lg" 
                                                data-bs-toggle="tooltip" 
                                                data-title="<?php echo e(__('Edit Notice')); ?>" 
                                                data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                                <i class="ti ti-pencil"></i>
                                            </a>

                                            <!-- Delete Button with Form -->
                                            <form id="delete-form-<?php echo e($notice->id); ?>" method="POST" action="<?php echo e(route('notices.destroy', $notice->id)); ?>" style="display: inline;">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                            </form>

                                            <a href="#" class="btn btn-sm btn-danger text-white"
                                                data-bs-toggle="tooltip"
                                                title="<?php echo e(__('Delete Notice')); ?>"
                                                onclick="event.preventDefault();  document.getElementById('delete-form-<?php echo e($notice->id); ?>').submit();">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                        <?php endif; ?>
  
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
<script type="text/javascript">
    function showNoticePopup(title, description) {
        // Create modal HTML
        const modalHtml = `
            <div class="modal fade" id="noticeModal" tabindex="-1" aria-labelledby="noticeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="noticeModalLabel"><?php echo e(__('Notice Details')); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong><?php echo e(__('Title')); ?>:</strong>
                                <p>` + title + `</p>
                            </div>
                            <div class="mb-3">
                                <strong><?php echo e(__('Description')); ?>:</strong>
                                <p>` + description + `</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('noticeModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to body and show it
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('noticeModal'));
        modal.show();
        
        // Remove modal from DOM when hidden
        document.getElementById('noticeModal').addEventListener('hidden.bs.modal', function () {
            this.remove();
        });
    }
    
    $(document).ready(function() {
        $('#pc-dt-simple').DataTable({
            "language": {
                "emptyTable": "No notices found"
            },
            "lengthMenu": [10, 25, 50, 100],
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/notice/index.blade.php ENDPATH**/ ?>