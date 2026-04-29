<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Branch-Wise Report')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Branch-Wise Report')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form method="GET" action="<?php echo e(route('reports.branch-wise')); ?>" id="filterForm">
                    <div class="d-flex flex-wrap gap-3">
                        
                        <div class="form-group" style="min-width: 250px;">
                            <label for="branch_filter"><?php echo e(__('Select Branch')); ?></label>
                            <select class="form-control select mt-1" id="branch_filter" name="branch_id">
                                <option value=""><?php echo e(__('All Branches')); ?></option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($branch->id); ?>" <?php echo e(request('branch_id') == $branch->id ? 'selected' : ''); ?>>
                                        <?php echo e($branch->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_date"><?php echo e(__('Start Date')); ?></label>
                            <input type="date" name="start_date" id="start_date" class="form-control mt-1"
                                value="<?php echo e(request('start_date') ?? date('Y-m-01')); ?>">
                        </div>

                        <div class="form-group">
                            <label for="end_date"><?php echo e(__('End Date')); ?></label>
                            <input type="date" name="end_date" id="end_date" class="form-control mt-1"
                                value="<?php echo e(request('end_date') ?? date('Y-m-t')); ?>">
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="document.getElementById('filterForm').submit(); return false;"
                                data-bs-toggle="tooltip" title="<?php echo e(__('Apply')); ?>">
                                <i class="ti ti-search"></i>
                            </a>

                            <a href="<?php echo e(route('reports.branch-wise')); ?>" class="btn btn-sm btn-danger"
                                data-bs-toggle="tooltip" title="<?php echo e(__('Reset')); ?>">
                                <i class="ti ti-trash-off text-white-off"></i>
                            </a>
                            
                            <?php if(request('branch_id')): ?>
                            <a href="<?php echo e(route('branch.wise.export', request()->all())); ?>" class="btn btn-sm btn-success"
                                data-bs-toggle="tooltip" title="<?php echo e(__('Download Excel')); ?>">
                                <i class="ti ti-download"></i>
                            </a>
                            <?php endif; ?>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if(request('branch_id')): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo e(__('Total Employees')); ?></h5>
                                <p class="card-text display-6"><?php echo e($selectedBranchData['total_employees']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo e(__('Total Time Spent')); ?></h5>
                                <p class="card-text display-6"><?php echo e(number_format($selectedBranchData['total_hours'], 2)); ?> hrs</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo e(__('Total Expense')); ?></h5>
                                <p class="card-text display-6"><?php echo e(number_format($selectedBranchData['total_expense'], 2)); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo e(__('Total Cost')); ?></h5>
                                <p class="card-text display-6"><?php echo e(number_format($totalCost, 2)); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

    <?php if(request('branch_id')): ?>
    <script>
        // Debug: Output timesheetDetails to browser console
        console.log('timesheetDetails:', <?php echo json_encode($timesheetDetails, 15, 512) ?>);
    </script>
<?php endif; ?>
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                                <tr>
                                    <th><?php echo e(__('Date')); ?></th>
                                    <th><?php echo e(__('Employee')); ?></th>
                                    <th><?php echo e(__('Project')); ?></th>
                                    <th><?php echo e(__('Time Spent')); ?></th>
                                    <th><?php echo e(__('Hourly Rate')); ?></th>
                                    <th><?php echo e(__('Cost')); ?></th>
                                    <th><?php echo e(__('Expense')); ?></th>
                                    <th><?php echo e(__('Description')); ?></th>
                                </tr>
                        </thead>
                        <tbody>
                                <?php $__currentLoopData = $timesheetDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $timesheet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e(\Auth::user()->dateFormat($timesheet->date)); ?></td>
                                        <td><?php echo e($timesheet->employee->name ?? ''); ?></td>
                                        <td><?php echo e($timesheet->project->project_name ?? ''); ?></td>
                                        <td><?php echo e(number_format($timesheet->total_time, 2)); ?> hrs</td>
                                        <td><?php echo e($timesheet->employee->hourly_charged ?? 0); ?></td>
                                        <td><?php echo e(number_format($timesheet->total_time * ($timesheet->employee->hourly_charged ?? 0), 2)); ?></td>
                                        <td><?php echo e(number_format($timesheet->expense, 2)); ?></td>
                                        <td><?php echo e($timesheet->narration); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
<script>
    $(document).ready(function() {
        // Auto-submit form when branch changes
        $('#branch_filter').change(function() {
            $('#filterForm').submit();
        });

        // Initialize select2
        $('.select').select2({
            width: '100%'
        });
        
        // Make table responsive with DataTables (optional)
        $('.table').DataTable({
            responsive: true,
            dom: '<"row"<"col-md-6"B><"col-md-6"f>>rtlp',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            }
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/report/branch-wise.blade.php ENDPATH**/ ?>