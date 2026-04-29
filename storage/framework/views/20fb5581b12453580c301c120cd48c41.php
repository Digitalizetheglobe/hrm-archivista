<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Deduction')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Deduction')); ?></li>
<?php $__env->stopSection(); ?>

<?php if(auth()->user()->type != 'employee'): ?>
    <?php $__env->startSection('action-button'); ?>
        <div class="row align-items-center m-1">
            <?php if(auth()->user()->type == 'company'): ?>
                <a href="#" data-bs-toggle="modal" data-bs-target="#createDeductionModal"
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
                                <th><?php echo e(__('Employee ')); ?></th>
                                <th><?php echo e(__('Deduction Type')); ?></th>
                                <th><?php echo e(__('Month')); ?></th>
                                <th><?php echo e(__('Amount')); ?></th>
                                <th><?php echo e(__('Remark')); ?></th>
                                <th width="130px"><?php echo e(__('Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $deductions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deduction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="<?php echo e(session('new_deduction_id') == $deduction->id ?  : ''); ?>">
                                    <td><?php echo e($deduction->employee ? $deduction->employee->name : '-'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo e($deduction->deduction_type == 'MLWF' ? 'primary' : 'secondary'); ?>">
                                            <?php echo e($deduction->deduction_type); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e(\Carbon\Carbon::createFromFormat('Y-m', $deduction->month)->format('F Y')); ?></td>
                                    <td><?php echo e(number_format($deduction->amount, 2)); ?></td>
                                    <td><?php echo e($deduction->remark ?: '-'); ?></td>
                                    <td class="d-flex gap-2">
                                        <?php if(auth()->user()->type == 'company'): ?>
                                            <!-- Edit Button -->
                                            <a href="#" 
                                                class="btn btn-sm btn-info text-white" 
                                                data-url="<?php echo e(route('deduction.edit', $deduction)); ?>" 
                                                data-ajax-popup="true" 
                                                data-size="lg" 
                                                data-bs-toggle="tooltip" 
                                                data-title="<?php echo e(__('Edit Deduction')); ?>" 
                                                data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                                <i class="ti ti-pencil"></i>
                                            </a>

                                            <!-- Delete Button -->
                                            <div class="action-btn bg-danger ms-2">
                                                <?php echo Form::open([
                                                    'method' => 'DELETE',
                                                    'route' => ['deduction.destroy', $deduction->id],
                                                    'id' => 'delete-form-' . $deduction->id
                                                ]); ?>

                                                <a href="#"
                                                    class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                    data-bs-toggle="tooltip" title="<?php echo e(__('Delete Deduction')); ?>"
                                                    data-bs-original-title="<?php echo e(__('Delete')); ?>" aria-label="<?php echo e(__('Delete')); ?>"
                                                    onclick="event.preventDefault(); document.getElementById('delete-form-<?php echo e($deduction->id); ?>').submit();">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                                <?php echo Form::close(); ?>

                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center"><?php echo e(__('No records found')); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Create Modal -->
<?php echo $__env->make('deduction.create', ['employees' => $employees ?? [], 'deductionTypes' => \App\Models\Deduction::deductionTypes(), 'monthOptions' => \App\Models\Deduction::monthOptions()], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    $(document).ready(function() {
        // Load create form
        $('a[data-bs-target="#deductionModal"]').click(function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            var title = $(this).data('title');
            
            $('.modal-title').text(title);
            $('.modal-body').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
            
            $.get(url, function(data) {
                $('.modal-body').html(data);
            });
        });
        
        // Handle delete
        $('.delete-btn').click(function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            var confirmMsg = $(this).data('confirm');
            
            if (confirm(confirmMsg)) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        location.reload();
                    },
                    error: function(response) {
                        alert('Error: ' + response.responseJSON.message);
                    }
                });
            }
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/deduction/index.blade.php ENDPATH**/ ?>