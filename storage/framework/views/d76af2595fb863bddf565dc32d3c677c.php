<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Leave')); ?>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Leave ')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <a href="<?php echo e(route('leave.export')); ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="<?php echo e(__('Export')); ?>">
        <i class="ti ti-file-export"></i>
    </a>

    <a href="<?php echo e(route('leave.calender')); ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        data-bs-original-title="<?php echo e(__('Calendar View')); ?>">
        <i class="ti ti-calendar"></i>
    </a>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Create Leave')): ?>
        <a href="#" data-url="<?php echo e(route('leave.create')); ?>" data-ajax-popup="true"
            data-title="<?php echo e(__('Create New Leave')); ?>" data-size="lg" data-bs-toggle="tooltip" title=""
            class="btn btn-sm btn-primary" data-bs-original-title="<?php echo e(__('Create')); ?>">
            <i class="ti ti-plus"></i>
        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <style>
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        .border-left-secondary {
            border-left: 0.25rem solid #858796 !important;
        }
        .shadow {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
        }
        .card-body {
            flex: 1 1 auto;
            min-height: 1px;
            padding: 1.25rem;
        }
        .text-xs {
            font-size: 0.7rem;
        }
        .font-weight-bold {
            font-weight: 700 !important;
        }
        .text-uppercase {
            text-transform: uppercase !important;
        }
        .mb-1 {
            margin-bottom: 0.25rem !important;
        }
        .h5 {
            font-size: 1.25rem;
        }
        .mb-0 {
            margin-bottom: 0 !important;
        }
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        .text-muted {
            color: #858796 !important;
        }
        .mr-2 {
            margin-right: 0.5rem !important;
        }
        .col-auto {
            flex: 0 0 auto;
            width: auto;
            max-width: 100%;
        }
        .fa-2x {
            font-size: 2rem;
        }
        .text-gray-300 {
            color: #dddfeb !important;
        }
        .mt-2 {
            margin-top: 0.5rem !important;
        }
        .progress {
            display: flex;
            height: 1rem;
            overflow: hidden;
            font-size: 0.75rem;
            background-color: #e9ecef;
            border-radius: 0.35rem;
        }
        .progress-bar {
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            background-color: #4e73df;
            transition: width 0.6s ease;
        }
        .bg-primary {
            background-color: #4e73df !important;
        }
        .bg-info {
            background-color: #36b9cc !important;
        }
        .small {
            font-size: 80%;
            font-weight: 400;
        }
    </style>
    
    
    <?php if(\Auth::user()->type == 'employee' && !empty($leaveBalances)): ?>
        <div class="row mb-4 mt-2">
            
            <?php if(\Auth::user()->type == 'employee' && isset($employee) && $employee->employee_type === 'Payroll'): ?>
                
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        <?php echo e(__('Paid Leave')); ?>

                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                            $paidLeaveBalance = null;
                                            $paidKeys = ['paid leave', 'paid', 'paidleave'];
                                            foreach ($paidKeys as $key) {
                                                if (isset($leaveBalances[$key])) {
                                                    $paidLeaveBalance = $leaveBalances[$key];
                                                    break;
                                                }
                                            }
                                        ?>
                                        
                                        <?php if($paidLeaveBalance): ?>
                                            <?php echo e($paidLeaveBalance['available']); ?> 
                                            <span class="text-xs text-muted">
                                                <?php echo e(__('Days')); ?>

                                                <?php if($paidLeaveBalance['carried_forward'] > 0): ?>
                                                    <br>
                                                    <small class="text-success">+<?php echo e($paidLeaveBalance['carried_forward']); ?> <?php echo e(__('Carried Forward')); ?></small>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            0 <?php echo e(__('Days')); ?>

                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                            <?php if($paidLeaveBalance): ?>
                                <div class="mt-2">
                                    <div class="progress" style="height: 4px;">
                                        <?php 
                                        $percentage = $paidLeaveBalance['days_per_period'] > 0 ? 
                                            (($paidLeaveBalance['days_per_period'] - $paidLeaveBalance['available']) / $paidLeaveBalance['days_per_period']) * 100 : 0;
                                        ?>
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo e(min(100, $percentage)); ?>%"></div>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo e(__('Used')); ?>: <?php echo e($paidLeaveBalance['total_used']); ?> / <?php echo e($paidLeaveBalance['days_per_period']); ?>

                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        <?php echo e(__('Casual Leave')); ?>

                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                            $casualLeaveBalance = null;
                                            $casualKeys = ['casual leaves', 'casual leave', 'casual', 'casualleaves'];
                                            foreach ($casualKeys as $key) {
                                                if (isset($leaveBalances[$key])) {
                                                    $casualLeaveBalance = $leaveBalances[$key];
                                                    break;
                                                }
                                            }
                                        ?>
                                        
                                        <?php if($casualLeaveBalance): ?>
                                            <?php echo e($casualLeaveBalance['total_allocated']); ?> 
                                            <span class="text-xs text-muted"><?php echo e(__('Days')); ?></span>
                                        <?php else: ?>
                                            0 <?php echo e(__('Days')); ?>

                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                </div>
                            </div>
                            <?php if($casualLeaveBalance): ?>
                                <div class="mt-2">
                                    <div class="progress" style="height: 4px;">
                                        <?php 
                                        $percentage = $casualLeaveBalance['days_per_period'] > 0 ? 
                                            (($casualLeaveBalance['days_per_period'] - $casualLeaveBalance['available']) / $casualLeaveBalance['days_per_period']) * 100 : 0;
                                        ?>
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo e(min(100, $percentage)); ?>%"></div>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo e(__('Used')); ?>: <?php echo e($casualLeaveBalance['total_used']); ?> / <?php echo e($casualLeaveBalance['days_per_period']); ?>

                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        <?php echo e(__('Remaining Casual Leave')); ?>

                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php if($casualLeaveBalance): ?>
                                            <?php echo e($casualLeaveBalance['available']); ?> 
                                            <span class="text-xs text-muted"><?php echo e(__('Days')); ?></span>
                                        <?php else: ?>
                                            0 <?php echo e(__('Days')); ?>

                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                                </div>
                            </div>
                            <?php if($casualLeaveBalance): ?>
                                <div class="mt-2">
                                    <div class="progress" style="height: 4px;">
                                        <?php 
                                        $percentage = $casualLeaveBalance['days_per_period'] > 0 ? 
                                            (($casualLeaveBalance['days_per_period'] - $casualLeaveBalance['available']) / $casualLeaveBalance['days_per_period']) * 100 : 0;
                                        ?>
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo e(min(100, $percentage)); ?>%"></div>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo e(__('Used')); ?>: <?php echo e($casualLeaveBalance['total_used']); ?> / <?php echo e($casualLeaveBalance['days_per_period']); ?>

                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        <?php echo e(__('Total Leaves (This Month)')); ?>

                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo e($leaveBalances['total_leaves_this_month'] ?? 0); ?> 
                                        <span class="text-xs text-muted"><?php echo e(__('Days')); ?></span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <?php echo e(__('Month')); ?>: <?php echo e(date('F Y')); ?>

                                </small>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                
                <?php if(isset($leaveTypes) && $leaveTypes->count() > 0): ?>
                    <?php $__currentLoopData = $leaveTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $leaveType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            // Find balance for this leave type
                            $leaveBalance = null;
                            $leaveTypeName = strtolower(trim($leaveType->title));
                            
                            // Try to find balance by exact title first
                            if (isset($leaveBalances[$leaveTypeName])) {
                                $leaveBalance = $leaveBalances[$leaveTypeName];
                            } else {
                                // Try variations
                                $possibleKeys = [
                                    $leaveTypeName,
                                    str_replace(' ', '', $leaveTypeName),
                                    str_replace(' ', '_', $leaveTypeName),
                                    ucfirst($leaveTypeName),
                                    ucwords($leaveTypeName)
                                ];
                                
                                foreach ($possibleKeys as $key) {
                                    if (isset($leaveBalances[$key])) {
                                        $leaveBalance = $leaveBalances[$key];
                                        break;
                                    }
                                }
                            }
                            
                            // Determine card color based on index
                            $borderColors = ['primary', 'success', 'info', 'warning', 'danger'];
                            $borderColor = $borderColors[$index % count($borderColors)];
                            $icons = ['fa-calendar-check', 'fa-calendar-day', 'fa-calendar-alt', 'fa-calendar-week', 'fa-calendar'];
                            $icon = $icons[$index % count($icons)];
                        ?>
                        
                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                            <div class="card border-left-<?php echo e($borderColor); ?> shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-<?php echo e($borderColor); ?> text-uppercase mb-1">
                                                <?php echo e($leaveType->title); ?>

                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php if($leaveBalance): ?>
                                                    <?php if($leaveType->is_unlimited): ?>
                                                        
                                                        <?php echo e($leaveBalance['total_used']); ?> 
                                                        <span class="text-xs text-muted"><?php echo e(__('Days Used')); ?></span>
                                                    <?php else: ?>
                                                        
                                                        <?php echo e($leaveBalance['available']); ?> 
                                                        <span class="text-xs text-muted">
                                                            <?php echo e(__('Days')); ?>

                                                            <?php if($leaveBalance['carried_forward'] > 0): ?>
                                                                <br>
                                                                <small class="text-success">+<?php echo e($leaveBalance['carried_forward']); ?> <?php echo e(__('Carried Forward')); ?></small>
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php if($leaveType->is_unlimited): ?>
                                                        0 <?php echo e(__('Days Used')); ?>

                                                    <?php else: ?>
                                                        0 <?php echo e(__('Days')); ?>

                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas <?php echo e($icon); ?> fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                    <?php if($leaveBalance): ?>
                                        <div class="mt-2">
                                            <?php if($leaveType->is_unlimited): ?>
                                                
                                                <small class="text-muted">
                                                    <?php echo e(__('Total Used')); ?>: <?php echo e($leaveBalance['total_used']); ?> <?php echo e(__('Days')); ?>

                                                    <?php if($leaveBalance['used_this_month'] > 0): ?>
                                                        <br><?php echo e(__('This Month')); ?>: <?php echo e($leaveBalance['used_this_month']); ?> <?php echo e(__('Days')); ?>

                                                    <?php endif; ?>
                                                </small>
                                            <?php else: ?>
                                                
                                                <div class="progress" style="height: 4px;">
                                                    <?php 
                                                    $percentage = $leaveBalance['days_per_period'] > 0 ? 
                                                        (($leaveBalance['days_per_period'] - $leaveBalance['available']) / $leaveBalance['days_per_period']) * 100 : 0;
                                                    ?>
                                                    <div class="progress-bar bg-<?php echo e($borderColor); ?>" role="progressbar" style="width: <?php echo e(min(100, $percentage)); ?>%"></div>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo e(__('Used')); ?>: <?php echo e($leaveBalance['total_used']); ?> / <?php echo e($leaveBalance['days_per_period']); ?>

                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    
                    
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            <?php echo e(__('Total Leaves (This Month)')); ?>

                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo e($leaveBalances['total_leaves_this_month'] ?? 0); ?> 
                                            <span class="text-xs text-muted"><?php echo e(__('Days')); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <?php echo e(__('Month')); ?>: <?php echo e(date('F Y')); ?>

                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="row">

        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <?php if(\Auth::user()->type != 'employee'): ?>
                                        <th><?php echo e(__('Employee')); ?></th>
                                    <?php endif; ?>
                                    <th><?php echo e(__('Leave Type')); ?></th>
                                    <th><?php echo e(__('Applied On')); ?></th>
                                    <th><?php echo e(__('Start Date')); ?></th>
                                    <th><?php echo e(__('End Date')); ?></th>
                                    <th><?php echo e(__('Total Days')); ?></th>
                                    <th><?php echo e(__('Leave Reason')); ?></th>
                                    <th><?php echo e(__('status')); ?></th>
                                    <th width="200px"><?php echo e(__('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <?php if(\Auth::user()->type != 'employee'): ?>
                                            <td><?php echo e(!empty($leave->employee_id) ? $leave->employees->name : ''); ?>

                                            </td>
                                        <?php endif; ?>
                                        <td><?php echo e(!empty($leave->leave_type_id) ? $leave->leaveType->title : ''); ?>

                                        </td>
                                        <td><?php echo e(\Auth::user()->dateFormat($leave->applied_on)); ?></td>
                                        <td><?php echo e(\Auth::user()->dateFormat($leave->start_date)); ?></td>
                                        <td><?php echo e(\Auth::user()->dateFormat($leave->end_date)); ?></td>

                                        <td><?php echo e($leave->total_leave_days); ?></td>
                                        <td><?php echo e($leave->leave_reason); ?></td>
                                        <td>
                                            <?php if($leave->status == 'Pending'): ?>
                                                <div class="badge bg-warning p-2 px-3 rounded status-badge5">
                                                    <?php echo e($leave->status); ?></div>
                                            <?php elseif($leave->status == 'Approved'): ?>
                                                <div class="badge bg-success p-2 px-3 rounded status-badge5">
                                                    <?php echo e($leave->status); ?></div>
                                            <?php elseif($leave->status == 'Reject'): ?>
                                                <div class="badge bg-danger p-2 px-3 rounded status-badge5">
                                                    <?php echo e($leave->status); ?></div>
                                            <?php endif; ?>
                                        </td>

                                        <td class="Action">

                                            <span>
                                                <?php if(\Auth::user()->type != 'employee'): ?>
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                            data-size="lg"
                                                            data-url="<?php echo e(URL::to('leave/' . $leave->id . '/action')); ?>"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="<?php echo e(__('Leave Action')); ?>"
                                                            data-bs-original-title="<?php echo e(__('Manage Leave')); ?>">
                                                            <i class="ti ti-caret-right text-white"></i>
                                                        </a>
                                                    </div>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Edit Leave')): ?>
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                                data-size="lg"
                                                                data-url="<?php echo e(URL::to('leave/' . $leave->id . '/edit')); ?>"
                                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                                title="" data-title="<?php echo e(__('Edit Leave')); ?>"
                                                                data-bs-original-title="<?php echo e(__('Edit')); ?>">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Delete Leave')): ?>
                                                        <?php if(\Auth::user()->type != 'employee'): ?>
                                                            <div class="action-btn bg-danger ms-2">
                                                                <?php echo Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['leave.destroy', $leave->id],
                                                                    'id' => 'delete-form-' . $leave->id,
                                                                ]); ?>

                                                                <a href="#"
                                                                    class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-bs-original-title="Delete" aria-label="Delete"><i
                                                                        class="ti ti-trash text-white text-white"></i></a>
                                                                </form>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="action-btn bg-success ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                            data-size="lg"
                                                            data-url="<?php echo e(URL::to('leave/' . $leave->id . '/action')); ?>"
                                                            data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip"
                                                            title="" data-title="<?php echo e(__('Leave Action')); ?>"
                                                            data-bs-original-title="<?php echo e(__('Manage Leave')); ?>">
                                                            <i class="ti ti-caret-right text-white"></i>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>

                                            </span>
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
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script>
        $(document).on('change', '#employee_id', function() {
            var employee_id = $(this).val();

            $.ajax({
                url: '<?php echo e(route('leave.jsoncount')); ?>',
                type: 'POST',
                data: {
                    "employee_id": employee_id,
                    "_token": "<?php echo e(csrf_token()); ?>",
                },
                success: function(data) {
                    var oldval = $('#leave_type_id').val();
                    $('#leave_type_id').empty();
                    $('#leave_type_id').append(
                        '<option value=""><?php echo e(__('Select Leave Type')); ?></option>');

                    $.each(data, function(key, value) {

                        if (value.total_leave == value.days) {
                            $('#leave_type_id').append('<option value="' + value.id +
                                '" disabled>' + value.title + '&nbsp(' + value.total_leave +
                                '/' + value.days + ')</option>');
                        } else {
                            $('#leave_type_id').append('<option value="' + value.id + '">' +
                                value.title + '&nbsp(' + value.total_leave + '/' + value
                                .days + ')</option>');
                        }
                        if (oldval) {
                            if (oldval == value.id) {
                                $("#leave_type_id option[value=" + oldval + "]").attr(
                                    "selected", "selected");
                            }
                        }
                    });
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/leave/index.blade.php ENDPATH**/ ?>