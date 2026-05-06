<?php $__env->startSection('page-title'); ?>
   <?php echo e(__('Manage Employee Salary')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Home')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('Employee Salary')); ?></li>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<div class="row">

    <div class="col-md-12 col-lg-12 col-sm-12 col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Employee Id')); ?></th>
                                <th><?php echo e(__('Name')); ?></th>
                                <th><?php echo e(__('Payroll Type')); ?></th>
                                <th><?php echo e(__('Salary')); ?></th>
                                <th><?php echo e(__('Net Salary')); ?></th>
                                <th width="200px"><?php echo e(__('Action')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo e(route('setsalary.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id))); ?>"
                                            class="btn btn-outline-primary">
                                            <?php echo e(\Auth::user()->employeeIdFormat($employee->employee_id)); ?>

                                        </a>
                                    </td>
                                    <td><?php echo e($employee->name); ?></td>
                                    <td><?php echo e(!empty($employee->getSalaryTypeName()) ? $employee->getSalaryTypeName() : '-'); ?></td>
                                    <td><?php echo e(\Auth::user()->priceFormat($employee->set_salary ?? 0)); ?></td>
                                    <td><?php echo e(!empty($employee->get_net_salary()) ? \Auth::user()->priceFormat($employee->get_net_salary()) : '-'); ?>

                                    </td>
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn bg-warning ms-2">
                                                <button type="button" 
                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                    title="" data-bs-original-title="<?php echo e(__('View')); ?>"
                                                    onclick="openSalaryModal('<?php echo e($employee->name); ?>', '<?php echo e($employee->id); ?>', '<?php echo e($employee->set_salary ?? 0); ?>')">
                                                    <i class="ti ti-eye text-white"></i>
                                                </button>
                                            </div>
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

<!-- Salary Management Modal -->
<div class="modal fade" id="salaryModal" tabindex="-1" role="dialog" aria-labelledby="salaryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="salaryModalLabel">Manage Salary: <span id="modalEmployeeName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="salaryForm">
                    <div class="mb-3">
                        <label for="employeeName" class="form-label">Employee Name</label>
                        <input type="text" class="form-control" id="employeeName" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="salary" class="form-label">Salary</label>
                        <input type="number" class="form-control" id="salary" name="salary" required>
                        <input type="hidden" id="employeeId" name="employee_id">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="saveSalary(this)">Save Change</button>
            </div>
        </div>
    </div>
</div>

<script>
function openSalaryModal(employeeName, employeeId, currentSalary) {
    document.getElementById('modalEmployeeName').textContent = employeeName;
    document.getElementById('employeeName').value = employeeName;
    document.getElementById('employeeId').value = employeeId;
    document.getElementById('salary').value = currentSalary;
    
    var salaryModal = new bootstrap.Modal(document.getElementById('salaryModal'));
    salaryModal.show();
}

function saveSalary(btn) {
    var employeeId = document.getElementById('employeeId').value;
    var salary = document.getElementById('salary').value;
    
    console.log('Saving salary:', { employeeId, salary });
    
    if (!salary || salary <= 0) {
        alert('Please enter a valid salary amount');
        return;
    }
    
    // Show loading state
    var saveButton = btn;
    var originalText = saveButton.textContent;
    saveButton.textContent = 'Saving...';
    saveButton.disabled = true;
    
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Make AJAX call to save the salary
    fetch(`<?php echo e(url('/employee/update/sallary')); ?>/${employeeId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            set_salary: salary
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        console.log('Success:', data);
        // Reload page to show updated salary
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.error || 'Error updating salary. Please try again.');
        saveButton.textContent = originalText;
        saveButton.disabled = false;
    });
}

function showSuccessModal(message) {
    document.getElementById('successModalBody').textContent = message;
    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
}
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/setsalary/index.blade.php ENDPATH**/ ?>