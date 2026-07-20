<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <h5 class="mb-3"><?php echo e(__('Attendance Regularisation Details')); ?></h5>
        </div>
        
        <?php if(\Auth::user()->type != 'employee'): ?>
            <div class="form-group col-lg-6 col-md-6">
                <label class="col-form-label"><strong><?php echo e(__('Employee Name')); ?>:</strong></label>
                <p><?php echo e(!empty($regularisation->employee) ? $regularisation->employee->full_name : __('N/A')); ?></p>
            </div>
        <?php endif; ?>

        <div class="form-group col-lg-6 col-md-6">
            <label class="col-form-label"><strong><?php echo e(__('Missed Attendance Date')); ?>:</strong></label>
            <p><?php echo e(\Auth::user()->dateFormat($regularisation->missed_attendance_date)); ?></p>
        </div>

        <div class="form-group col-lg-6 col-md-6">
            <label class="col-form-label"><strong><?php echo e(__('Punch In Time')); ?>:</strong></label>
            <p><?php echo e(\Auth::user()->timeFormat($regularisation->punch_in_time)); ?></p>
        </div>

        <div class="form-group col-lg-6 col-md-6">
            <label class="col-form-label"><strong><?php echo e(__('Punch Out Time')); ?>:</strong></label>
            <p><?php echo e(\Auth::user()->timeFormat($regularisation->punch_out_time)); ?></p>
        </div>

        <div class="form-group col-lg-6 col-md-6">
            <label class="col-form-label"><strong><?php echo e(__('Reason')); ?>:</strong></label>
            <p><?php echo e($regularisation->reason); ?></p>
        </div>

        <div class="form-group col-lg-6 col-md-6">
            <label class="col-form-label"><strong><?php echo e(__('Status')); ?>:</strong></label>
            <p>
                <?php if($regularisation->status == 'Pending'): ?>
                    <span class="badge bg-warning"><?php echo e(__('Pending')); ?></span>
                <?php elseif($regularisation->status == 'Approved'): ?>
                    <span class="badge bg-success"><?php echo e(__('Approved')); ?></span>
                <?php else: ?>
                    <span class="badge bg-danger"><?php echo e(__('Rejected')); ?></span>
                <?php endif; ?>
            </p>
        </div>

        <div class="form-group col-lg-12 col-md-12">
            <label class="col-form-label"><strong><?php echo e(__('Remark')); ?>:</strong></label>
            <p><?php echo e($regularisation->remark); ?></p>
        </div>

        <?php if($regularisation->status != 'Pending'): ?>
            <div class="form-group col-lg-6 col-md-6">
                <label class="col-form-label"><strong><?php echo e(__('Processed By')); ?>:</strong></label>
                <p><?php echo e(!empty($regularisation->approvedBy) ? $regularisation->approvedBy->name : __('N/A')); ?></p>
            </div>

            <div class="form-group col-lg-6 col-md-6">
                <label class="col-form-label"><strong><?php echo e(__('Processed At')); ?>:</strong></label>
                <p><?php echo e($regularisation->approved_at ? \Auth::user()->dateFormat($regularisation->approved_at) . ' ' . \Auth::user()->timeFormat($regularisation->approved_at) : __('N/A')); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
</div>

<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/attendance/regularisation/show.blade.php ENDPATH**/ ?>