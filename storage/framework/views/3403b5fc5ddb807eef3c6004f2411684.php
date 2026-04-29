<div class="modal fade" id="createDeductionModal" tabindex="-1" role="dialog" aria-labelledby="createDeductionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDeductionModalLabel"><?php echo e(__('Create Deduction')); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createDeductionForm" method="POST" action="<?php echo e(route('deduction.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="form-label"><?php echo e(__('Employee')); ?> <span class="text-danger">*</span></label>
                            <select class="form-control" id="employee_id" name="employee_id" required>
                                <option value=""><?php echo e(__('Select Employee')); ?></option>
                                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small class="text-muted"><?php echo e(__('Select an employee')); ?></small>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label"><?php echo e(__('Deduction Type')); ?> <span class="text-danger">*</span></label>
                            <select class="form-control" id="deduction_type" name="deduction_type" required>
                                <option value=""><?php echo e(__('Select Type')); ?></option>
                                <?php $__currentLoopData = $deductionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small class="text-muted"><?php echo e(__('Select deduction type')); ?></small>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label"><?php echo e(__('Month')); ?> <span class="text-danger">*</span></label>
                            <input type="month" class="form-control" id="month" name="month" required>
                            <small class="text-muted"><?php echo e(__('Select month for deduction')); ?></small>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label"><?php echo e(__('Amount')); ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo e(\Auth::user()->currencySymbol()); ?></span>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required placeholder="<?php echo e(__('Enter amount')); ?>">
                            </div>
                            <small class="text-muted"><?php echo e(__('Enter deduction amount')); ?></small>
                        </div>
                        <div class="form-group col-md-12">
                            <label class="form-label"><?php echo e(__('Remark')); ?></label>
                            <textarea class="form-control" id="remark" name="remark" rows="3" placeholder="<?php echo e(__('Enter remark (optional)')); ?>"></textarea>
                            <small class="text-muted"><?php echo e(__('Optional remark for this deduction')); ?></small>
                        </div>
                    </div>
                    <div id="formErrors" class="alert alert-danger" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo e(__('Create')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Choices('#employee_id', {
            searchEnabled: true,
            searchPlaceholderText: '<?php echo e(__("Search Employee...")); ?>',
            noResultsText: '<?php echo e(__("No results found")); ?>',
            itemSelectText: '<?php echo e(__("Press to select")); ?>',
        });
        
        // Form submission
        document.getElementById('createDeductionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const errorDiv = document.getElementById('formErrors');
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php echo e(__("Creating...")); ?>';
            errorDiv.style.display = 'none';
            
            fetch('<?php echo e(route("deduction.store")); ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                console.log('Response status:', response.status); // Debug log
                
                // Close modal immediately
                const modal = bootstrap.Modal.getInstance(document.getElementById('createDeductionModal'));
                modal.hide();
                
                // Always reload the page after form submission
                // This ensures the new data is visible immediately
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                errorDiv.innerHTML = '<?php echo e(__("An error occurred. Please try again.")); ?>';
                errorDiv.style.display = 'block';
                
                // Fallback: reload page anyway to show any data that might have been saved
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<?php echo e(__("Create")); ?>';
            });
        });
    });
</script>
<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/deduction/create.blade.php ENDPATH**/ ?>