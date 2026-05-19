

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
        /* === Premium Total Leaves Card === */
        .leave-summary-card {
            background: linear-gradient(135deg, #f6821f 0%, #e67e22 100%);
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 40px rgba(246,130,31,0.3);
            overflow: hidden;
            position: relative;
        }
        .leave-summary-card::before {
            content:''; position:absolute; top:-50px; right:-50px;
            width:200px; height:200px;
            background:rgba(255,255,255,0.07); border-radius:50%;
        }
        .leave-summary-card::after {
            content:''; position:absolute; bottom:-80px; left:-30px;
            width:250px; height:250px;
            background:rgba(255,255,255,0.05); border-radius:50%;
        }
        .leave-total-badge { font-size:3.5rem; font-weight:800; color:#fff; line-height:1; }
        .leave-total-label { font-size:0.85rem; color:rgba(255,255,255,0.75); text-transform:uppercase; letter-spacing:1.5px; font-weight:600; }
        .leave-month-tag { background:rgba(255,255,255,0.18); color:#fff; border-radius:20px; padding:3px 14px; font-size:0.78rem; font-weight:600; display:inline-block; margin-top:8px; }
        .leave-breakdown-item {
            background:#fff; border-radius:12px; padding:16px 18px;
            display:flex; align-items:flex-start; gap:14px;
            box-shadow:0 2px 12px rgba(0,0,0,0.07);
            transition:transform 0.2s ease, box-shadow 0.2s ease;
            height:100%;
        }
        .leave-breakdown-item:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,0.12); }
        .leave-breakdown-icon { width:44px; height:44px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:1.15rem; flex-shrink:0; margin-top:2px; }
        .leave-breakdown-title { font-size:0.75rem; color:#6c757d; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px; }
        .leave-breakdown-days { font-size:1.6rem; font-weight:800; color:#2d3748; line-height:1; }
        .leave-breakdown-days span { font-size:0.72rem; font-weight:500; color:#a0aec0; }
        .leave-progress-mini { height:5px; border-radius:3px; background:#e9ecef; margin-top:8px; overflow:hidden; }
        .leave-progress-mini-bar { height:100%; border-radius:3px; transition:width 0.8s ease; }
        .icon-p0 { background:rgba(78,115,223,0.12); color:#4e73df; }
        .icon-p1 { background:rgba(28,200,138,0.12); color:#1cc88a; }
        .icon-p2 { background:rgba(54,185,204,0.12); color:#36b9cc; }
        .icon-p3 { background:rgba(246,194,62,0.12); color:#f6c23e; }
        .icon-p4 { background:rgba(231,74,59,0.12); color:#e74a3b; }
        .bar-p0 { background:#4e73df; } .bar-p1 { background:#1cc88a; }
        .bar-p2 { background:#36b9cc; } .bar-p3 { background:#f6c23e; } .bar-p4 { background:#e74a3b; }
    </style>
    
    
    <?php if(\Auth::user()->type == 'employee' && !empty($leaveBalances)): ?>
        <?php
            $totalLeavesThisMonth = $leaveBalances['total_leaves_this_month'] ?? 0;
            $leaveBreakdown = [];
            foreach ($leaveBalances as $key => $balance) {
                if ($key === 'total_leaves_this_month' || !is_array($balance)) continue;
                $leaveBreakdown[] = [
                    'name'            => ucwords($key),
                    'used_this_month' => $balance['used_this_month'] ?? 0,
                    'total_used'      => $balance['total_used'] ?? 0,
                    'total_allocated' => $balance['days_per_period'] ?? 0,
                    'is_unlimited'    => $balance['is_unlimited'] ?? false,
                ];
            }
            if (empty($leaveBreakdown) && isset($leaveTypes)) {
                foreach ($leaveTypes as $lt) {
                    $ltKey = strtolower(trim($lt->title));
                    $bal = $leaveBalances[$ltKey] ?? null;
                    $leaveBreakdown[] = [
                        'name'            => $lt->title,
                        'used_this_month' => $bal['used_this_month'] ?? 0,
                        'total_used'      => $bal['total_used'] ?? 0,
                        'total_allocated' => $bal['days_per_period'] ?? 0,
                        'is_unlimited'    => $lt->is_unlimited ?? false,
                    ];
                }
            }
        ?>

        <div class="row mb-4 mt-2">
            <div class="col-12">
                <div class="row align-items-stretch g-3">

                    
                    <div class="col-lg-4 col-md-12">
                        <div class="leave-summary-card h-100 p-4 d-flex flex-column justify-content-between" style="min-height:200px;">
                            <div style="position:relative;z-index:1;">
                                <div class="leave-total-label">Total Leaves Taken</div>
                                <div class="leave-total-badge mt-2"><?php echo e($totalLeavesThisMonth); ?></div>
                                <div style="color:rgba(255,255,255,0.8);font-size:0.9rem;margin-top:4px;">Days this month</div>
                                <div class="leave-month-tag"><i class="fas fa-calendar-alt me-1"></i> <?php echo e(date('F Y')); ?></div>
                            </div>
                            <div style="position:relative;z-index:1;margin-top:20px;">
                                <div style="color:rgba(255,255,255,0.55);font-size:0.72rem;text-transform:uppercase;letter-spacing:1px;">All Leave Types Combined</div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-lg-8 col-md-12">
                        <div class="row g-3 h-100">
                            <?php if(!empty($leaveBreakdown)): ?>
                                <?php $__currentLoopData = $leaveBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $p = $idx % 5; ?>
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <div class="leave-breakdown-item">
                                            <div class="leave-breakdown-icon icon-p<?php echo e($p); ?>">
                                                <i class="fas fa-calendar-check"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="leave-breakdown-title"><?php echo e($item['name']); ?></div>
                                                <div class="leave-breakdown-days"><?php echo e($item['used_this_month']); ?> <span>days this month</span></div>
                                                <?php if(!$item['is_unlimited'] && $item['total_allocated'] > 0): ?>
                                                    <div class="leave-progress-mini">
                                                        <div class="leave-progress-mini-bar bar-p<?php echo e($p); ?>" style="width:<?php echo e(min(100, $item['total_allocated'] > 0 ? ($item['total_used']/$item['total_allocated'])*100 : 0)); ?>%"></div>
                                                    </div>
                                                    <div style="font-size:0.7rem;color:#a0aec0;margin-top:4px;">Total used: <?php echo e($item['total_used']); ?> / <?php echo e($item['total_allocated']); ?></div>
                                                <?php else: ?>
                                                    <div style="font-size:0.7rem;color:#a0aec0;margin-top:4px;">Total used: <?php echo e($item['total_used']); ?> days</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <div class="col-12 d-flex align-items-center justify-content-center">
                                    <div class="text-center py-4">
                                        <i class="fas fa-check-circle text-success" style="font-size:2.5rem;"></i>
                                        <p class="text-muted mt-2 mb-0">No leaves taken this month!</p>
                                        <small class="text-muted">All your leave records will appear here.</small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="row mt-4">

        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    
                    <ul class="nav nav-tabs mb-3" id="leaveTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab" aria-controls="approved" aria-selected="true">
                                <?php echo e(__('Approved Leaves')); ?>

                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="false">
                                <?php echo e(__('Pending Leaves')); ?>

                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab" aria-controls="rejected" aria-selected="false">
                                <?php echo e(__('Rejected Leaves')); ?>

                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="leaveTabsContent">
                        <!-- Approved Leaves Tab -->
                        <div class="tab-pane fade show active" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                            <div class="table-responsive">
                                <table class="table pc-dt-simple">
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
                                            <th><?php echo e(__('status')); ?></th>
                                            <th width="200px"><?php echo e(__('Action')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($leave->status == 'Approved'): ?>
                                                <tr>
                                                    <?php if(\Auth::user()->type != 'employee'): ?>
                                                        <td><?php echo e(!empty($leave->employees) ? $leave->employees->name : ''); ?></td>
                                                    <?php endif; ?>
                                                    <td><?php echo e(!empty($leave->leaveType) ? $leave->leaveType->title : ''); ?></td>
                                                    <td><?php echo e(\Auth::user()->dateFormat($leave->applied_on)); ?></td>
                                                    <td><?php echo e(\Auth::user()->dateFormat($leave->start_date)); ?></td>
                                                    <td><?php echo e(\Auth::user()->dateFormat($leave->end_date)); ?></td>
                                                    <td>
                                                        <?php echo e($leave->total_leave_days); ?>

                                                        <?php if($leave->leave_duration == 'half_day'): ?>
                                                            <br>
                                                            <span class="badge bg-info p-1 px-2 rounded" style="font-size: 0.65rem;">
                                                                <?php echo e($leave->half_day_type == 'first_half' ? __('First Half') : __('Second Half')); ?>

                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="badge bg-success p-2 px-3 rounded status-badge5"><?php echo e($leave->status); ?></div>
                                                    </td>
                                                    <td class="Action">
                                                        <span>
                                                            <?php if(\Auth::user()->type != 'employee'): ?>
                                                                <div class="action-btn bg-success ms-2">
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
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
                                                                        <a href="#" class="mx-3 btn btn-sm align-items-center"
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
                                                                    <div class="action-btn bg-danger ms-2">
                                                                        <?php echo Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['leave.destroy', $leave->id],
                                                                            'id' => 'delete-form-' . $leave->id,
                                                                        ]); ?>

                                                                        <a href="#"
                                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                            data-bs-toggle="tooltip" title=""
                                                                            data-bs-original-title="Delete" aria-label="Delete"><i
                                                                                class="ti ti-trash text-white text-white"></i></a>
                                                                        </form>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <div class="action-btn bg-success ms-2">
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
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
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pending Leaves Tab -->
                        <div class="tab-pane fade" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                            <div class="table-responsive">
                                <table class="table pc-dt-simple">
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
                                            <th><?php echo e(__('status')); ?></th>
                                            <th width="200px"><?php echo e(__('Action')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($leave->status == 'Pending'): ?>
                                                <tr>
                                                    <?php if(\Auth::user()->type != 'employee'): ?>
                                                        <td><?php echo e(!empty($leave->employees) ? $leave->employees->name : ''); ?></td>
                                                    <?php endif; ?>
                                                    <td><?php echo e(!empty($leave->leaveType) ? $leave->leaveType->title : ''); ?></td>
                                                    <td><?php echo e(\Auth::user()->dateFormat($leave->applied_on)); ?></td>
                                                    <td><?php echo e(\Auth::user()->dateFormat($leave->start_date)); ?></td>
                                                    <td><?php echo e(\Auth::user()->dateFormat($leave->end_date)); ?></td>
                                                    <td>
                                                        <?php echo e($leave->total_leave_days); ?>

                                                        <?php if($leave->leave_duration == 'half_day'): ?>
                                                            <br>
                                                            <span class="badge bg-info p-1 px-2 rounded" style="font-size: 0.65rem;">
                                                                <?php echo e($leave->half_day_type == 'first_half' ? __('First Half') : __('Second Half')); ?>

                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="badge bg-warning p-2 px-3 rounded status-badge5"><?php echo e($leave->status); ?></div>
                                                    </td>
                                                    <td class="Action">
                                                        <span>
                                                            <?php if(\Auth::user()->type != 'employee'): ?>
                                                                <div class="action-btn bg-success ms-2">
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
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
                                                                        <a href="#" class="mx-3 btn btn-sm align-items-center"
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
                                                                    <div class="action-btn bg-danger ms-2">
                                                                        <?php echo Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['leave.destroy', $leave->id],
                                                                            'id' => 'delete-form-' . $leave->id,
                                                                        ]); ?>

                                                                        <a href="#"
                                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                            data-bs-toggle="tooltip" title=""
                                                                            data-bs-original-title="Delete" aria-label="Delete"><i
                                                                                class="ti ti-trash text-white text-white"></i></a>
                                                                        </form>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <div class="action-btn bg-success ms-2">
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
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
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Rejected Leaves Tab -->
                        <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
                            <div class="table-responsive">
                                <table class="table pc-dt-simple">
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
                                            <th><?php echo e(__('status')); ?></th>
                                            <th width="200px"><?php echo e(__('Action')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($leave->status == 'Reject'): ?>
                                                <tr>
                                                    <?php if(\Auth::user()->type != 'employee'): ?>
                                                        <td><?php echo e(!empty($leave->employees) ? $leave->employees->name : ''); ?></td>
                                                    <?php endif; ?>
                                                    <td><?php echo e(!empty($leave->leaveType) ? $leave->leaveType->title : ''); ?></td>
                                                    <td><?php echo e(\Auth::user()->dateFormat($leave->applied_on)); ?></td>
                                                    <td><?php echo e(\Auth::user()->dateFormat($leave->start_date)); ?></td>
                                                    <td><?php echo e(\Auth::user()->dateFormat($leave->end_date)); ?></td>
                                                    <td>
                                                        <?php echo e($leave->total_leave_days); ?>

                                                        <?php if($leave->leave_duration == 'half_day'): ?>
                                                            <br>
                                                            <span class="badge bg-info p-1 px-2 rounded" style="font-size: 0.65rem;">
                                                                <?php echo e($leave->half_day_type == 'first_half' ? __('First Half') : __('Second Half')); ?>

                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="badge bg-danger p-2 px-3 rounded status-badge5"><?php echo e($leave->status); ?></div>
                                                    </td>
                                                    <td class="Action">
                                                        <span>
                                                            <?php if(\Auth::user()->type != 'employee'): ?>
                                                                <div class="action-btn bg-success ms-2">
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
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
                                                                        <a href="#" class="mx-3 btn btn-sm align-items-center"
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
                                                                    <div class="action-btn bg-danger ms-2">
                                                                        <?php echo Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['leave.destroy', $leave->id],
                                                                            'id' => 'delete-form-' . $leave->id,
                                                                        ]); ?>

                                                                        <a href="#"
                                                                            class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                                            data-bs-toggle="tooltip" title=""
                                                                            data-bs-original-title="Delete" aria-label="Delete"><i
                                                                                class="ti ti-trash text-white text-white"></i></a>
                                                                        </form>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <div class="action-btn bg-success ms-2">
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center"
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
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <!-- Conflicting legacy script commented out to allow create.blade.php / edit.blade.php to manage the dropdown layout dynamically -->
    <!--
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
    -->
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/leave/index.blade.php ENDPATH**/ ?>