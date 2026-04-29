<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Department-Wise Report')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Department-Wise Report')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
<script>
    $(document).ready(function() {
    console.log('Document ready - initializing department report');

    // Branch change event
    $(document).on('change', 'select[name=branch_id]', function() {
        var branch_id = $(this).val();
        console.log('Branch changed:', branch_id);
        getDepartment(branch_id);
        // Reset department and date fields
        $('#department_filter').empty().append('<option value=""><?php echo e(__("All Departments")); ?></option>').prop('disabled', true);
        $('#start_date, #end_date').prop('disabled', true);
        $('#searchBtn').prop('disabled', true);
    });

    function getDepartment(bid) {
        console.log('Fetching departments for branch:', bid);
        $.ajax({
            url: '<?php echo e(route("get.departments.by.branch")); ?>',
            type: 'POST',
            data: {
                "branch_id": bid,
                "_token": "<?php echo e(csrf_token()); ?>",
            },
            success: function(data) {
                console.log('Departments received:', data);
                $('#department_filter').empty();
                $('#department_filter').append('<option value=""><?php echo e(__("All Departments")); ?></option>');
                $.each(data, function(key, value) {
                    $('#department_filter').append('<option value="' + key + '">' + value + '</option>');
                });
                $('#department_filter').prop('disabled', false);
            },
            error: function(xhr) {
                console.error('Error fetching departments:', xhr.responseText);
            }
        });
    }

    // Department change event
    $(document).on('change', 'select[name=department_id]', function() {
        var department_id = $(this).val();
        console.log('Department changed:', department_id);
        if(department_id) {
            $('#start_date, #end_date').prop('disabled', false);
            $('#searchBtn').prop('disabled', false);
        } else {
            $('#start_date, #end_date').prop('disabled', true);
            $('#searchBtn').prop('disabled', true);
        }
    });

    // Form submission debug
    $('#filterForm').on('submit', function(e) {
        console.log('Form submitted with data:', {
            branch_id: $('select[name=branch_id]').val(),
            department_id: $('select[name=department_id]').val(),
            start_date: $('input[name=start_date]').val(),
            end_date: $('input[name=end_date]').val()
        });
    });

    // Initialize DataTable if table exists
    if ($('.table').length) {
        console.log('Initializing DataTable');
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
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form method="GET" action="<?php echo e(route('reports.department-wise')); ?>" id="filterForm">
                    <div class="d-flex flex-wrap justify-content-end gap-3">
                        <!-- Branch Selection -->
                        <div class="form-group" style="min-width: 200px;">
                            <label for="branch_filter" class="form-label"><?php echo e(__('Select Branch')); ?></label>
                            <select class="form-control select w-100" id="branch_filter" name="branch_id">
                                <option value=""><?php echo e(__('All Branches')); ?></option>
                                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($branch->id); ?>" <?php echo e(request('branch_id') == $branch->id ? 'selected' : ''); ?>>
                                        <?php echo e($branch->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Department Selection -->
                        <div class="form-group" style="min-width: 200px;">
                            <label for="department_filter" class="form-label"><?php echo e(__('Select Department')); ?></label>
                            <select class="form-control select w-100" id="department_filter" name="department_id" <?php echo e(!request('branch_id') ? 'disabled' : ''); ?>>
                                <option value=""><?php echo e(__('All Departments')); ?></option>
                                <?php if(request('branch_id') && $departments): ?>
                                    <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($department->id); ?>" <?php echo e(request('department_id') == $department->id ? 'selected' : ''); ?>>
                                            <?php echo e($department->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Start Date -->
                        <div class="form-group" style="min-width: 160px;">
                            <label for="start_date" class="form-label"><?php echo e(__('Start Date')); ?></label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="<?php echo e(request('start_date') ?? date('Y-m-01')); ?>" <?php echo e(!request('department_id') ? 'disabled' : ''); ?>>
                        </div>

                        <!-- End Date -->
                        <div class="form-group" style="min-width: 160px;">
                            <label for="end_date" class="form-label"><?php echo e(__('End Date')); ?></label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="<?php echo e(request('end_date') ?? date('Y-m-t')); ?>" <?php echo e(!request('department_id') ? 'disabled' : ''); ?>>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-sm btn-primary"
                                title="<?php echo e(__('Apply')); ?>" id="searchBtn" <?php echo e(!request('department_id') ? 'disabled' : ''); ?>>
                                <i class="ti ti-search"></i>
                            </button>
                            <a href="<?php echo e(route('reports.department-wise')); ?>" class="btn btn-sm btn-danger" title="<?php echo e(__('Reset')); ?>">
                                <i class="ti ti-trash-off"></i>
                            </a>
                            <?php if(request('department_id')): ?>
                            <a href="<?php echo e(route('department.wise.export', request()->all())); ?>" class="btn btn-sm btn-success"
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



<?php if(request('department_id')): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo e(__('Total Employees')); ?></h5>
                                <p class="card-text display-6"><?php echo e($selectedDepartmentData['total_employees']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo e(__('Total Time Spent')); ?></h5>
                                <p class="card-text display-6"><?php echo e(number_format($selectedDepartmentData['total_hours'], 2)); ?> hrs</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo e(__('Average Hourly Rate')); ?></h5>
                                <p class="card-text display-6"><?php echo e(number_format($selectedDepartmentData['total_hours'] > 0 ? $selectedDepartmentData['total_cost'] / $selectedDepartmentData['total_hours'] : 0, 2)); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body">
                                
                                <h5 class="card-title"><?php echo e(__('Total Cost')); ?></h5>
                                <p class="card-text display-6"><?php echo e(number_format($selectedDepartmentData['total_cost'], 2)); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if(request('department_id')): ?>
    <script>
        // Debug: Output department and reportData to browser console
        console.log('department:', <?php echo json_encode($department, 15, 512) ?>);
        console.log('selectedDepartmentData:', <?php echo json_encode($selectedDepartmentData, 15, 512) ?>);
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

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/report/department-wise.blade.php ENDPATH**/ ?>