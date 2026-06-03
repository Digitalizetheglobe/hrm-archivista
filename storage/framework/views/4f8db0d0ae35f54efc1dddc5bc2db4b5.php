                                <?php
                                    // New Regime: Total Income - 75,000 STD deduction - deductions
                                    $totalTaxable = ($employee->set_salary * 12) + $allowances->sum('amount') - 75000 - $deductions->sum('amount');
                                    
                                    // Calculate tax based on NEW REGIME slabs (corrected progressive calculation)
                                    $tax = 0;
                                    
                                    // 0 - 3L: 0%
                                    if ($totalTaxable > 300000) {
                                        // 3L - 6L: 5% on amount between 3L-6L
                                        $amountInSlab = min($totalTaxable - 300000, 300000);
                                        $tax += $amountInSlab * 0.05;
                                        
                                        if ($totalTaxable > 600000) {
                                            // 6L - 9L: 10% on amount between 6L-9L
                                            $amountInSlab = min($totalTaxable - 600000, 300000);
                                            $tax += $amountInSlab * 0.10;
                                            
                                            if ($totalTaxable > 900000) {
                                                // 9L - 12L: 15% on amount between 9L-12L
                                                $amountInSlab = min($totalTaxable - 900000, 300000);
                                                $tax += $amountInSlab * 0.15;
                                                
                                                if ($totalTaxable > 1200000) {
                                                    // 12L - 15L: 20% on amount between 12L-15L
                                                    $amountInSlab = min($totalTaxable - 1200000, 300000);
                                                    $tax += $amountInSlab * 0.20;
                                                    
                                                    if ($totalTaxable > 1500000) {
                                                        // Above 15L: 30% on amount above 15L
                                                        $amountInSlab = $totalTaxable - 1500000;
                                                        $tax += $amountInSlab * 0.30;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Calculate cess and totals - round to whole numbers
                                    $tax = round($tax);
                                    $cess = round($tax * 0.04);
                                    $totalTaxAmount = $tax + $cess;
                                    
                                    // Calculate total paid based on actual paid amounts
                                    $paidMonths = isset($tdsPayments) ? $tdsPayments->where('is_paid', true)->pluck('month_number')->toArray() : [];
                                    $totalPaid = 0;
                                    foreach ($tdsPayments->where('is_paid', true) as $payment) {
                                        $totalPaid += round($payment->amount);
                                    }
                                    
                                    $tdsBalance = $totalTaxAmount - $totalPaid;
                                    
                                    // Calculate monthly TDS amounts for display
                                    $months = [
                                        'April', 'May', 'June', 'July', 'August', 'September',
                                        'October', 'November', 'December', 'January', 'February', 'March'
                                    ];
                                    $remainingMonths = 12 - count($paidMonths); // Count of unticked months
                                    $monthlyTdsAmounts = [];
                                    
                                    foreach ($months as $index => $month) {
                                        $payment = $tdsPayments->where('month_number', $index + 1)->first();
                                        if ($payment) {
                                            // If custom amount exists, use it and round
                                            $monthlyTdsAmounts[$index] = round($payment->amount);
                                        } elseif ($remainingMonths > 0) {
                                            // Divide balance only among remaining months and round
                                            $monthlyTdsAmounts[$index] = round($tdsBalance / $remainingMonths);
                                        } else {
                                            // All months are paid, show 0
                                            $monthlyTdsAmounts[$index] = 0;
                                        }
                                    }

                                    $totalDeduction = $deductions->sum('amount');
                                    $monthlyDeduction = $totalDeduction > 0 ? round($totalDeduction / 12) : 0;
                                    
                                    // Check which months are paid (using the $paidMonths array from above)
                                    $isMonthPaid = function($monthIndex) use ($paidMonths) {
                                        return in_array($monthIndex, $paidMonths);
                                    };
                                ?>




<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('TDS Calculation (New Regime)')); ?> - <?php echo e($employee->name); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('tds.index')); ?>"><?php echo e(__('TDS')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('New Regime')); ?></li>
<?php $__env->stopSection(); ?>

<?php
    // Helper function for Indian number format
    if (!function_exists('formatIndian')) {
        function formatIndian($number) {
            $decimal = round($number, 2);
            $exploded = explode('.', (string)$decimal);
            $whole = $exploded[0];
            $decimal_part = isset($exploded[1]) ? $exploded[1] : '00';
            
            if (strlen($whole) <= 3) {
                return $whole . ($decimal_part != '00' ? '.' . $decimal_part : '');
            }
            
            $last_three = substr($whole, -3);
            $remaining = substr($whole, 0, -3);
            
            if ($remaining) {
                $formatted = formatIndian($remaining) . ',' . $last_three;
            } else {
                $formatted = $last_three;
            }
            
            return $formatted . ($decimal_part != '00' ? '.' . $decimal_part : '');
        }
    }
?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <!-- Left Side: TDS Calculation Details -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5><?php echo e(__('TDS Calculation Details (New Regime)')); ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th><?php echo e(__('Employee Name')); ?></th>
                                    <td><?php echo e($employee->name); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('Basic Salary')); ?></th>
                                    <td><?php echo e($employee->set_salary ? formatIndian($employee->set_salary) : '0'); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('Gross Salary (salary*12)')); ?></th>
                                    <td><?php echo e($employee->set_salary ? formatIndian($employee->set_salary * 12) : '0'); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('Allowances')); ?></th>
                                    <td><?php echo e(formatIndian($allowances->sum('amount'))); ?></td>
                                </tr>
                                <tr class="table-warning">
                                    <th><?php echo e(__('Total')); ?></th>
                                    <td><?php echo e(formatIndian(($employee->set_salary * 12) + $allowances->sum('amount'))); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('STD Deduction')); ?></th>
                                    <td>75,000</td>
                                </tr>
                                <tr class="table-warning">
                                    <th><?php echo e(__('Total')); ?></th>
                                    <td><?php echo e(formatIndian((($employee->set_salary * 12) + $allowances->sum('amount')) - 75000)); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('Deduction')); ?></th>
                                    <td><?php echo e(formatIndian($deductions->sum('amount'))); ?></td>
                                </tr>                               
                                <tr class="table-info">
                                    <th><?php echo e(__('Total Taxable')); ?></th>
                                    <td><?php echo e(formatIndian($totalTaxable)); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('Tax')); ?></th>
                                    <td><strong><?php echo e(formatIndian($tax)); ?></strong></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('Cess@4%')); ?></th>
                                    <td><strong><?php echo e(formatIndian($cess)); ?></strong></td>
                                </tr>
                                <tr class="table-success">
                                    <th><?php echo e(__('Total Tax')); ?></th>
                                    <td><strong><?php echo e(formatIndian($totalTaxAmount)); ?></strong></td>
                                </tr>
                                <tr class="table-danger">
                                    <th><?php echo e(__('Total Paid')); ?></th>
                                    <td><strong><?php echo e(formatIndian($totalPaid)); ?></strong></td>
                                </tr>
                                <tr class="table-success">
                                    <th><?php echo e(__('TDS Balance')); ?></th>
                                    <td><strong><?php echo e(formatIndian($tdsBalance)); ?></strong></td>
                                </tr>
                                <tr>
                                    <th><?php echo e(__('Monthly TDS')); ?></th>
                                    <td><strong><?php echo e(formatIndian($monthlyTdsAmounts[$index])); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Allowance and Deduction Tables -->
        <div class="col-xl-6">
            <!-- Allowance Table -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><?php echo e(__('Allowance')); ?></h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAllowanceModal">
                        <i class="ti ti-plus"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Allowance Type')); ?></th>
                                    <th><?php echo e(__('Amount')); ?></th>
                                    <th><?php echo e(__('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody id="allowanceTableBody">
                                <?php $__currentLoopData = $allowances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $allowance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr data-id="<?php echo e($allowance->id); ?>">
                                    <td><?php echo e($allowance->allowance_type); ?></td>
                                    <td><?php echo e(formatIndian(round($allowance->amount))); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editAllowance(<?php echo e($allowance->id); ?>, '<?php echo e($allowance->allowance_type); ?>', <?php echo e(round($allowance->amount)); ?>)">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteAllowance(<?php echo e($allowance->id); ?>)">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-primary fw-bold">
                                    <td><?php echo e(__('Total')); ?></td>
                                    <td><?php echo e(formatIndian(round($allowances->sum('amount')))); ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Deduction Table - For Reference Only (Not Used in New Regime Calculations) -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><?php echo e(__('Deduction (Not Applicable in New Regime)')); ?></h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDeductionModal">
                        <i class="ti ti-plus"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Deduction Type')); ?></th>
                                    <th><?php echo e(__('Amount')); ?></th>
                                    <th><?php echo e(__('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody id="deductionTableBody">
                                <?php $__currentLoopData = $deductions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deduction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr data-id="<?php echo e($deduction->id); ?>">
                                    <td><?php echo e($deduction->deduction_type); ?></td>
                                    <td><?php echo e(formatIndian($deduction->amount)); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editDeduction(<?php echo e($deduction->id); ?>, '<?php echo e($deduction->deduction_type); ?>', <?php echo e($deduction->amount); ?>)">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteDeduction(<?php echo e($deduction->id); ?>)">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-primary fw-bold">
                                    <td><?php echo e(__('Total')); ?></td>
                                    <td><?php echo e(formatIndian(round($deductions->sum('amount')))); ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Monthly Deduction Table -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><?php echo e(__('Monthly Deduction')); ?></h5>
                    <button type="button" class="btn btn-sm btn-primary" onclick="editMonthlyDeduction()">
                        <i class="ti ti-edit"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Month')); ?></th>
                                    <th><?php echo e(__('Deduction Amount')); ?></th>
                                    <th><?php echo e(__('Action')); ?></th>
                                </tr>
                            <tbody>
                                <?php $__currentLoopData = $months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($month); ?></td>
                                    <td><strong><?php echo e(formatIndian($monthlyTdsAmounts[$index])); ?></strong></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editMonthlyDeductionMonth(<?php echo e($index + 1); ?>, '<?php echo e($month); ?>', <?php echo e($monthlyTdsAmounts[$index]); ?>)">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm <?php echo e($isMonthPaid($index + 1) ? 'btn-success' : 'btn-outline-success'); ?>" onclick="toggleMonthPayment(<?php echo e($index + 1); ?>, '<?php echo e($month); ?>', <?php echo e($monthlyTdsAmounts[$index]); ?>)">
                                            <i class="ti <?php echo e($isMonthPaid($index + 1) ? 'ti-check' : 'ti-square'); ?>"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-primary fw-bold">
                                    <td><?php echo e(__('Total')); ?></td>
                                    <td><strong><?php echo e(formatIndian($tdsBalance)); ?></strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel"><?php echo e(__('Confirm Action')); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage"><?php echo e(__('Are you sure you want to proceed?')); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
                <button type="button" id="confirmModalAction" class="btn btn-primary"><?php echo e(__('Confirm')); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Monthly TDS Modal -->
<div class="modal fade" id="editMonthlyTdsModal" tabindex="-1" aria-labelledby="editMonthlyTdsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMonthlyTdsModalLabel"><?php echo e(__('Edit Monthly TDS')); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editMonthlyTdsForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editMonthName" class="col-form-label"><?php echo e(__('Month')); ?></label>
                                <input type="text" id="editMonthName" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editTdsAmount" class="col-form-label"><?php echo e(__('TDS Amount')); ?></label>
                                <input type="number" id="editTdsAmount" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="editMonthNumber">
                    <input type="hidden" id="editEmployeeId">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
                <button type="button" class="btn btn-primary" onclick="saveMonthlyTds()"><?php echo e(__('Save')); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Add Allowance Modal -->
<div class="modal fade" id="addAllowanceModal" tabindex="-1" aria-labelledby="addAllowanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAllowanceModalLabel"><?php echo e(__('Add Allowance')); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="allowanceForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" id="employeeId" name="employee_id" value="<?php echo e($employee->id); ?>">
                    <input type="hidden" id="allowanceEditId" name="allowance_edit_id">
                    <div class="mb-3">
                        <label for="allowanceType" class="form-label"><?php echo e(__('Allowance Type')); ?></label>
                        <input type="text" class="form-control" id="allowanceType" name="allowance_type" placeholder="<?php echo e(__('Enter allowance type')); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="allowanceAmount" class="form-label"><?php echo e(__('Amount')); ?></label>
                        <input type="number" class="form-control" id="allowanceAmount" name="amount" step="0.01" min="0" placeholder="<?php echo e(__('Enter amount')); ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
                <button type="button" class="btn btn-primary" onclick="addAllowance()"><?php echo e(__('Add Allowance')); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel"><?php echo e(__('Confirm Delete')); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><?php echo e(__('Are you sure you want to delete this allowance?')); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn"><?php echo e(__('Delete')); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Deduction Modals - For Reference Only -->
<!-- Add Deduction Modal -->
<div class="modal fade" id="addDeductionModal" tabindex="-1" aria-labelledby="addDeductionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDeductionModalLabel"><?php echo e(__('Add Deduction')); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="deductionForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" id="deductionEmployeeId" name="employee_id" value="<?php echo e($employee->id); ?>">
                    <input type="hidden" id="deductionEditId" name="deduction_edit_id">
                    <div class="mb-3">
                        <label for="deductionType" class="form-label"><?php echo e(__('Deduction Type')); ?></label>
                        <input type="text" class="form-control" id="deductionType" name="deduction_type" placeholder="<?php echo e(__('Enter deduction type')); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="deductionAmount" class="form-label"><?php echo e(__('Amount')); ?></label>
                        <input type="number" class="form-control" id="deductionAmount" name="amount" step="0.01" min="0" placeholder="<?php echo e(__('Enter amount')); ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
                <button type="button" class="btn btn-primary" onclick="addDeduction()"><?php echo e(__('Add Deduction')); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Deduction Confirmation Modal -->
<div class="modal fade" id="deleteDeductionConfirmModal" tabindex="-1" aria-labelledby="deleteDeductionConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteDeductionConfirmModalLabel"><?php echo e(__('Confirm Delete')); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><?php echo e(__('Are you sure you want to delete this deduction?')); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
                <button type="button" class="btn btn-danger" id="confirmDeleteDeductionBtn"><?php echo e(__('Delete')); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
let isEditing = false;

// Add notification container to the page
document.addEventListener('DOMContentLoaded', function() {
    // Create notification container if it does not exist
    if (!document.getElementById('notification-container')) {
        const notificationHTML = `
            <div id="notification-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            </div>
        `;
        document.body.insertAdjacentHTML('afterbegin', notificationHTML);
    }
});

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    notification.style.cssText = 'min-width: 300px; margin-bottom: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 4px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    const container = document.getElementById('notification-container');
    container.appendChild(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}

function addAllowance() {
    const allowanceType = document.getElementById('allowanceType').value;
    const allowanceAmount = document.getElementById('allowanceAmount').value;
    const employeeId = document.getElementById('employeeId').value;
    const editId = document.getElementById('allowanceEditId').value;
    
    if (!allowanceType || !allowanceAmount) {
        showNotification('Please fill all fields', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('employee_id', employeeId);
    formData.append('allowance_type', allowanceType);
    formData.append('amount', allowanceAmount);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    if (editId) {
        formData.append('_method', 'PUT');
    }
    
    const url = editId ? "<?php echo e(route('tds.allowance.update', ':id')); ?>".replace(':id', editId) : "<?php echo e(route('tds.allowance.store')); ?>";
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.error || 'Error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error occurred', 'error');
    });
}

function editAllowance(id, title, amount) {
    document.getElementById('allowanceType').value = title;
    document.getElementById('allowanceAmount').value = amount;
    document.getElementById('allowanceEditId').value = id;
    document.querySelector('#addAllowanceModal .modal-title').textContent = 'Edit Allowance';
    document.querySelector('#addAllowanceModal .btn-primary').textContent = 'Update Allowance';
    isEditing = true;
    
    const modal = new bootstrap.Modal(document.getElementById('addAllowanceModal'));
    modal.show();
}

function deleteAllowance(id) {
    // Show custom confirmation modal
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    
    // Set up delete button click handler
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    confirmBtn.onclick = function() {
        performDelete(id);
        modal.hide();
        // Clean up the event listener
        confirmBtn.onclick = null;
    };
    
    modal.show();
}

function performDelete(id) {
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch("<?php echo e(route('tds.allowance.delete', ':id')); ?>".replace(':id', id), {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.error || 'Error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showNotification('Error: ' + error.message, 'error');
    });
}

// Reset modal when it's closed
document.getElementById('addAllowanceModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('allowanceForm').reset();
    document.getElementById('allowanceEditId').value = '';
    document.querySelector('#addAllowanceModal .modal-title').textContent = 'Add Allowance';
    document.querySelector('#addAllowanceModal .btn-primary').textContent = 'Add Allowance';
    isEditing = false;
});

// Calculate and update total
function updateTotal() {
    const rows = document.querySelectorAll('#allowanceTableBody tr');
    let total = 0;
    
    rows.forEach(row => {
        const amountCell = row.querySelector('td:nth-child(2)');
        if (amountCell) {
            const amount = parseFloat(amountCell.textContent.replace(/,/g, ''));
            total += amount;
        }
    });
    
    document.querySelector('#allowanceTableBody + tfoot td:nth-child(2)').textContent = total.toFixed(2);
}

// Update total on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTotal();
});

// Deduction Functions - For Reference Only (Not Used in New Regime Calculations)
function addDeduction() {
    const deductionType = document.getElementById('deductionType').value;
    const deductionAmount = document.getElementById('deductionAmount').value;
    const employeeId = document.getElementById('deductionEmployeeId').value;
    const editId = document.getElementById('deductionEditId').value;
    
    if (!deductionType || !deductionAmount) {
        showNotification('Please fill all fields', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('employee_id', employeeId);
    formData.append('deduction_type', deductionType);
    formData.append('amount', deductionAmount);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    if (editId) {
        formData.append('_method', 'PUT');
    }
    
    const url = editId ? "<?php echo e(route('tds.deduction.update', ':id')); ?>".replace(':id', editId) : "<?php echo e(route('tds.deduction.store')); ?>";
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.error || 'Error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error occurred', 'error');
    });
}

function editDeduction(id, title, amount) {
    document.getElementById('deductionType').value = title;
    document.getElementById('deductionAmount').value = amount;
    document.getElementById('deductionEditId').value = id;
    document.querySelector('#addDeductionModal .modal-title').textContent = 'Edit Deduction';
    document.querySelector('#addDeductionModal .btn-primary').textContent = 'Update Deduction';
    
    const modal = new bootstrap.Modal(document.getElementById('addDeductionModal'));
    modal.show();
}

function deleteDeduction(id) {
    // Show custom confirmation modal
    const modal = new bootstrap.Modal(document.getElementById('deleteDeductionConfirmModal'));
    
    // Set up delete button click handler
    const confirmBtn = document.getElementById('confirmDeleteDeductionBtn');
    confirmBtn.onclick = function() {
        performDeductionDelete(id);
        modal.hide();
        // Clean up the event listener
        confirmBtn.onclick = null;
    };
    
    modal.show();
}

function performDeductionDelete(id) {
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch("<?php echo e(route('tds.deduction.delete', ':id')); ?>".replace(':id', id), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.error || 'Error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error occurred', 'error');
    });
}

// Reset deduction modal when it's closed
document.getElementById('addDeductionModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('deductionForm').reset();
    document.getElementById('deductionEditId').value = '';
    document.querySelector('#addDeductionModal .modal-title').textContent = 'Add Deduction';
    document.querySelector('#addDeductionModal .btn-primary').textContent = 'Add Deduction';
});

// Monthly Deduction Functions
function editMonthlyDeduction() {
    // Placeholder function for overall monthly deduction edit
    showNotification('Monthly deduction edit functionality will be implemented', 'info');
}

function editMonthlyDeductionMonth(monthNumber, monthName, currentAmount) {
    // Open edit modal with current values
    document.getElementById('editMonthName').value = monthName;
    document.getElementById('editTdsAmount').value = currentAmount;
    document.getElementById('editMonthNumber').value = monthNumber;
    document.getElementById('editEmployeeId').value = document.querySelector('input[name="employee_id"]').value;
    
    const modal = new bootstrap.Modal(document.getElementById('editMonthlyTdsModal'));
    modal.show();
}

function saveMonthlyTds() {
    const monthNumber = document.getElementById('editMonthNumber').value;
    const monthName = document.getElementById('editMonthName').value;
    const tdsAmount = document.getElementById('editTdsAmount').value;
    const employeeId = document.getElementById('editEmployeeId').value;
    
    if (!tdsAmount || tdsAmount <= 0) {
        showNotification('Please enter a valid TDS amount', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('employee_id', employeeId);
    formData.append('monthly_tds[0][month]', monthNumber);
    formData.append('monthly_tds[0][amount]', tdsAmount);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch('<?php echo e(route("tds.update.monthly.tds")); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('editMonthlyTdsModal'));
            modal.hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.error || 'Error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error occurred', 'error');
    });
}

function toggleMonthPayment(monthNumber, monthName, amount) {
    const employeeId = document.querySelector('input[name="employee_id"]').value;
    const isCurrentlyPaid = event.target.closest('button').classList.contains('btn-success');
    
    const action = isCurrentlyPaid ? 'unmark' : 'mark';
    const title = isCurrentlyPaid ? 'Unmark Payment' : 'Mark Payment';
    const message = isCurrentlyPaid ? 
        `Are you sure you want to unmark ${monthName} as paid?` : 
        `Are you sure you want to mark ${monthName} as paid?`;
    
    // Update modal content
    document.getElementById('confirmModalLabel').textContent = title;
    document.getElementById('confirmModalMessage').textContent = message;
    document.getElementById('confirmModalAction').textContent = action === 'mark' ? 'Mark as Paid' : 'Unmark as Paid';
    document.getElementById('confirmModalAction').className = action === 'mark' ? 'btn btn-success' : 'btn btn-warning';
    
    // Set up confirm button action
    const confirmBtn = document.getElementById('confirmModalAction');
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    newConfirmBtn.addEventListener('click', function() {
        const formData = new FormData();
        formData.append('employee_id', employeeId);
        formData.append('month_number', monthNumber);
        formData.append('amount', amount);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        fetch('<?php echo e(route("tds.toggle.payment")); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
                modal.hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.error || 'Error occurred', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error occurred', 'error');
        });
    });
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
}
</script>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/tds/new_regime.blade.php ENDPATH**/ ?>