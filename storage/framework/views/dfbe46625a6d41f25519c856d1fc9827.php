<?php echo e(Form::open(['url' => 'attendance-regularisation/changeaction', 'method' => 'post', 'id' => 'regularisation-action-form'])); ?>

<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table" id="pc-dt-simple">
                <tr role="row">
                    <th><?php echo e(__('Employee Name')); ?></th>
                    <td><?php echo e(!empty($regularisation->employee) ? $regularisation->employee->full_name : __('N/A')); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Missed Attendance Date')); ?></th>
                    <td><?php echo e(\Auth::user()->dateFormat($regularisation->missed_attendance_date)); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Punch In Time')); ?></th>
                    <td><?php echo e(\Auth::user()->timeFormat($regularisation->punch_in_time)); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Punch Out Time')); ?></th>
                    <td><?php echo e(\Auth::user()->timeFormat($regularisation->punch_out_time)); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Reason')); ?></th>
                    <td><?php echo e($regularisation->reason); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Remark')); ?></th>
                    <td><?php echo e($regularisation->remark); ?></td>
                </tr>
                <tr>
                    <th><?php echo e(__('Status')); ?></th>
                    <td>
                        <?php if($regularisation->status == 'Pending'): ?>
                            <span class="badge bg-warning"><?php echo e(__('Pending')); ?></span>
                        <?php elseif($regularisation->status == 'Approved'): ?>
                            <span class="badge bg-success"><?php echo e(__('Approved')); ?></span>
                        <?php else: ?>
                            <span class="badge bg-danger"><?php echo e(__('Rejected')); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <input type="hidden" value="<?php echo e($regularisation->id); ?>" name="regularisation_id">  
                <input type="hidden" value="<?php echo e($regularisation->status); ?>" name="previous_status">
            </table>
        </div>
    </div>
</div>

<?php if((\Auth::user()->type == 'company' || Gate::check('attendance.regularisation.action.all')) && $regularisation->status == 'Pending'): ?>
    <div class="modal-footer">
        <input type="submit" value="<?php echo e(__('Approved')); ?>" class="btn btn-success rounded" name="status" id="approve-btn">
        <input type="submit" value="<?php echo e(__('Reject')); ?>" class="btn btn-danger rounded" name="status">
    </div>
<?php endif; ?>

<?php echo e(Form::close()); ?>


<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/attendance/regularisation/action.blade.php ENDPATH**/ ?>