<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Manage Site Visit')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Site Visit')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('action-button'); ?>
    <div class="float-end">
        <?php if(\Auth::user()->type == 'company'): ?>
            <a href="#" data-bs-toggle="modal" data-bs-target="#customEmailModal" class="btn btn-sm btn-info" data-bs-toggle="tooltip" data-bs-original-title="<?php echo e(__('Custom Approval Email')); ?>">
                <i class="ti ti-mail"></i>
            </a>
        <?php endif; ?>
        <?php if(Auth::user()->type == 'employee' || \Auth::user()->can('Create Attendance')): ?>
            <a href="#" data-url="<?php echo e(route('site-visit.create')); ?>" data-ajax-popup="true" data-title="<?php echo e(__('Create Site Visit')); ?>" data-bs-toggle="tooltip" title="<?php echo e(__('Create')); ?>" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <ul class="nav nav-tabs mb-3" id="siteVisitTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab" aria-controls="approved" aria-selected="true">
                                <?php echo e(__('Approved')); ?>

                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="false">
                                <?php echo e(__('Pending')); ?>

                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab" aria-controls="rejected" aria-selected="false">
                                <?php echo e(__('Rejected')); ?>

                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="siteVisitTabsContent">
                        <!-- Approved Tab -->
                        <div class="tab-pane fade show active" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                            <div class="table-responsive">
                                <table class="table pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <?php if(Auth::user()->type != 'employee'): ?>
                                                <th><?php echo e(__('Employee')); ?></th>
                                            <?php endif; ?>
                                            <th><?php echo e(__('Start Date')); ?></th>
                                            <th><?php echo e(__('End Date')); ?></th>
                                            <th><?php echo e(__('Location')); ?></th>
                                            <th><?php echo e(__('Status')); ?></th>
                                            <th width="200px"><?php echo e(__('Action')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $siteVisits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $siteVisit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($siteVisit->status == 'Approved'): ?>
                                            <tr>
                                                <?php if(Auth::user()->type != 'employee'): ?>
                                                    <td><?php echo e(!empty($siteVisit->employee) ? $siteVisit->employee->name : ''); ?></td>
                                                <?php endif; ?>
                                                <td><?php echo e(Auth::user()->dateFormat($siteVisit->start_date)); ?></td>
                                                <td><?php echo e(Auth::user()->dateFormat($siteVisit->end_date)); ?></td>
                                                <td><?php echo e($siteVisit->location); ?></td>
                                                <td>
                                                    <div class="status_badge badge bg-success p-2 px-3 rounded"><?php echo e(__($siteVisit->status)); ?></div>
                                                </td>
                                                <td class="Action">
                                                    <span>
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" data-url="<?php echo e(route('site-visit.show', $siteVisit->id)); ?>" data-ajax-popup="true" data-title="<?php echo e(__('View Site Visit')); ?>" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="<?php echo e(__('View')); ?>">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
                                                        <div class="action-btn bg-danger ms-2">
                                                            <?php echo Form::open(['method' => 'DELETE', 'route' => ['site-visit.destroy', $siteVisit->id], 'id' => 'delete-form-' . $siteVisit->id]); ?>

                                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>" data-confirm="<?php echo e(__('Are You Sure?') . '|' . __('This action cannot be undone. Do you want to continue?')); ?>" data-confirm-yes="document.getElementById('delete-form-<?php echo e($siteVisit->id); ?>').submit();">
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                            <?php echo Form::close(); ?>

                                                        </div>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Pending Tab -->
                        <div class="tab-pane fade" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                            <div class="table-responsive">
                                <table class="table pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <?php if(Auth::user()->type != 'employee'): ?>
                                                <th><?php echo e(__('Employee')); ?></th>
                                            <?php endif; ?>
                                            <th><?php echo e(__('Start Date')); ?></th>
                                            <th><?php echo e(__('End Date')); ?></th>
                                            <th><?php echo e(__('Location')); ?></th>
                                            <th><?php echo e(__('Status')); ?></th>
                                            <th width="200px"><?php echo e(__('Action')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $siteVisits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $siteVisit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($siteVisit->status == 'Pending'): ?>
                                            <tr>
                                                <?php if(Auth::user()->type != 'employee'): ?>
                                                    <td><?php echo e(!empty($siteVisit->employee) ? $siteVisit->employee->name : ''); ?></td>
                                                <?php endif; ?>
                                                <td><?php echo e(Auth::user()->dateFormat($siteVisit->start_date)); ?></td>
                                                <td><?php echo e(Auth::user()->dateFormat($siteVisit->end_date)); ?></td>
                                                <td><?php echo e($siteVisit->location); ?></td>
                                                <td>
                                                    <div class="status_badge badge bg-warning p-2 px-3 rounded"><?php echo e(__($siteVisit->status)); ?></div>
                                                </td>
                                                <td class="Action">
                                                    <span>
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" data-url="<?php echo e(route('site-visit.show', $siteVisit->id)); ?>" data-ajax-popup="true" data-title="<?php echo e(__('View Site Visit')); ?>" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="<?php echo e(__('View')); ?>">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
                                                        <?php if(Auth::user()->type != 'employee'): ?>
                                                            <div class="action-btn bg-success ms-2">
                                                                <?php echo Form::open(['method' => 'POST', 'route' => ['site-visit.approve', $siteVisit->id], 'id' => 'approve-form-' . $siteVisit->id]); ?>

                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Approve')); ?>" onclick="document.getElementById('approve-form-<?php echo e($siteVisit->id); ?>').submit();">
                                                                        <i class="ti ti-check text-white"></i>
                                                                    </a>
                                                                <?php echo Form::close(); ?>

                                                            </div>
                                                            <div class="action-btn bg-danger ms-2">
                                                                <?php echo Form::open(['method' => 'POST', 'route' => ['site-visit.reject', $siteVisit->id], 'id' => 'reject-form-' . $siteVisit->id]); ?>

                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Reject')); ?>" onclick="document.getElementById('reject-form-<?php echo e($siteVisit->id); ?>').submit();">
                                                                        <i class="ti ti-x text-white"></i>
                                                                    </a>
                                                                <?php echo Form::close(); ?>

                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="action-btn bg-danger ms-2">
                                                            <?php echo Form::open(['method' => 'DELETE', 'route' => ['site-visit.destroy', $siteVisit->id], 'id' => 'delete-form-' . $siteVisit->id]); ?>

                                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>" data-confirm="<?php echo e(__('Are You Sure?') . '|' . __('This action cannot be undone. Do you want to continue?')); ?>" data-confirm-yes="document.getElementById('delete-form-<?php echo e($siteVisit->id); ?>').submit();">
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                            <?php echo Form::close(); ?>

                                                        </div>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Rejected Tab -->
                        <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
                            <div class="table-responsive">
                                <table class="table pc-dt-simple">
                                    <thead>
                                        <tr>
                                            <?php if(Auth::user()->type != 'employee'): ?>
                                                <th><?php echo e(__('Employee')); ?></th>
                                            <?php endif; ?>
                                            <th><?php echo e(__('Start Date')); ?></th>
                                            <th><?php echo e(__('End Date')); ?></th>
                                            <th><?php echo e(__('Location')); ?></th>
                                            <th><?php echo e(__('Status')); ?></th>
                                            <th width="200px"><?php echo e(__('Action')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $siteVisits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $siteVisit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($siteVisit->status == 'Rejected'): ?>
                                            <tr>
                                                <?php if(Auth::user()->type != 'employee'): ?>
                                                    <td><?php echo e(!empty($siteVisit->employee) ? $siteVisit->employee->name : ''); ?></td>
                                                <?php endif; ?>
                                                <td><?php echo e(Auth::user()->dateFormat($siteVisit->start_date)); ?></td>
                                                <td><?php echo e(Auth::user()->dateFormat($siteVisit->end_date)); ?></td>
                                                <td><?php echo e($siteVisit->location); ?></td>
                                                <td>
                                                    <div class="status_badge badge bg-danger p-2 px-3 rounded"><?php echo e(__($siteVisit->status)); ?></div>
                                                </td>
                                                <td class="Action">
                                                    <span>
                                                        <div class="action-btn bg-info ms-2">
                                                            <a href="#" data-url="<?php echo e(route('site-visit.show', $siteVisit->id)); ?>" data-ajax-popup="true" data-title="<?php echo e(__('View Site Visit')); ?>" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="<?php echo e(__('View')); ?>">
                                                                <i class="ti ti-eye text-white"></i>
                                                            </a>
                                                        </div>
                                                        <div class="action-btn bg-danger ms-2">
                                                            <?php echo Form::open(['method' => 'DELETE', 'route' => ['site-visit.destroy', $siteVisit->id], 'id' => 'delete-form-' . $siteVisit->id]); ?>

                                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>" data-confirm="<?php echo e(__('Are You Sure?') . '|' . __('This action cannot be undone. Do you want to continue?')); ?>" data-confirm-yes="document.getElementById('delete-form-<?php echo e($siteVisit->id); ?>').submit();">
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                            <?php echo Form::close(); ?>

                                                        </div>
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

    <?php if(\Auth::user()->type == 'company'): ?>
    <!-- Custom Email Modal -->
    <div class="modal fade" id="customEmailModal" tabindex="-1" aria-labelledby="customEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customEmailModalLabel"><?php echo e(__('Custom Site Visit Approval Email')); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <?php echo e(Form::open(['route' => ['site-visit.save_custom_email'], 'method' => 'POST'])); ?>

                <div class="modal-body">
                    <?php
                        $settings = \App\Models\Utility::settings();
                    ?>
                    <div class="alert alert-info">
                        <strong><?php echo e(__('Available Placeholders:')); ?></strong><br>
                        <code>{employee_name}</code> - Employee Name<br>
                        <code>{status}</code> - Status (Approved)<br>
                        <code>{location}</code> - Location<br>
                        <code>{start_date}</code> - Start Date<br>
                        <code>{end_date}</code> - End Date<br>
                        <code>{app_name}</code> - App Name
                    </div>
                    <div class="form-group">
                        <?php echo e(Form::label('subject', __('Email Subject'), ['class' => 'col-form-label'])); ?>

                        <?php echo e(Form::text('subject', $settings['custom_site_visit_approve_subject'] ?? '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter email subject')])); ?>

                    </div>
                    <div class="form-group">
                        <?php echo e(Form::label('body', __('Email Body (HTML supported)'), ['class' => 'col-form-label'])); ?>

                        <?php echo e(Form::textarea('body', $settings['custom_site_visit_approve_body'] ?? '', ['class' => 'form-control', 'rows' => '8', 'required' => 'required', 'placeholder' => __('Enter email body...')])); ?>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo e(__('Save Changes')); ?></button>
                </div>
                <?php echo e(Form::close()); ?>

            </div>
        </div>
    </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/site_visit/index.blade.php ENDPATH**/ ?>