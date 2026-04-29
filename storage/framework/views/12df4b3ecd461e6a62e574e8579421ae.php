<?php echo e(Form::open(['url' => 'site-visit', 'method' => 'post'])); ?>

<div class="modal-body">
    <div class="row">
        <?php if(Auth::user()->type != 'employee'): ?>
            <div class="col-md-12">
                <div class="form-group">
                    <?php echo e(Form::label('employee_id', __('Employee'), ['class' => 'form-label'])); ?>

                    <?php echo e(Form::select('employee_id', $employees, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Employee')])); ?>

                </div>
            </div>
        <?php endif; ?>
        <div class="col-md-12">
            <div class="form-group">
                <?php echo e(Form::label('date', __('Date'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::date('date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required'])); ?>

            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <?php echo e(Form::label('location', __('Location'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::text('location', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Location')])); ?>

            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <?php echo e(Form::label('reason', __('Reason'), ['class' => 'form-label'])); ?>

                <?php echo e(Form::textarea('reason', null, ['class' => 'form-control', 'placeholder' => __('Enter Reason'), 'rows' => 3])); ?>

            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="<?php echo e(__('Cancel')); ?>" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Create')); ?>" class="btn btn-primary">
</div>
<?php echo e(Form::close()); ?>

<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/site_visit/create.blade.php ENDPATH**/ ?>