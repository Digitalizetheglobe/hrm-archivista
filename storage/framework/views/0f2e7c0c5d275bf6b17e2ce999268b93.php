<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label font-bold"><?php echo e(__('Employee')); ?></label>
                <p><?php echo e(!empty($siteVisit->employee) ? $siteVisit->employee->name : ''); ?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label font-bold"><?php echo e(__('Date')); ?></label>
                <p><?php echo e(Auth::user()->dateFormat($siteVisit->date)); ?></p>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label font-bold"><?php echo e(__('Location')); ?></label>
                <p><?php echo e($siteVisit->location); ?></p>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label font-bold"><?php echo e(__('Reason')); ?></label>
                <p><?php echo e($siteVisit->reason ?? __('N/A')); ?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label font-bold"><?php echo e(__('Status')); ?></label>
                <p>
                    <?php if($siteVisit->status == 'Pending'): ?>
                        <span class="badge bg-warning p-2 px-3 rounded"><?php echo e(__($siteVisit->status)); ?></span>
                    <?php elseif($siteVisit->status == 'Approved'): ?>
                        <span class="badge bg-success p-2 px-3 rounded"><?php echo e(__($siteVisit->status)); ?></span>
                    <?php else: ?>
                        <span class="badge bg-danger p-2 px-3 rounded"><?php echo e(__($siteVisit->status)); ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <?php if(Auth::user()->type != 'employee' && $siteVisit->status == 'Pending'): ?>
        <?php echo Form::open(['method' => 'POST', 'route' => ['site-visit.approve', $siteVisit->id]]); ?>

            <input type="submit" value="<?php echo e(__('Approve')); ?>" class="btn btn-success">
        <?php echo Form::close(); ?>

        <?php echo Form::open(['method' => 'POST', 'route' => ['site-visit.reject', $siteVisit->id]]); ?>

            <input type="submit" value="<?php echo e(__('Reject')); ?>" class="btn btn-danger">
        <?php echo Form::close(); ?>

    <?php endif; ?>
    <input type="button" value="<?php echo e(__('Close')); ?>" class="btn btn-light" data-bs-dismiss="modal">
</div>
<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/site_visit/view.blade.php ENDPATH**/ ?>