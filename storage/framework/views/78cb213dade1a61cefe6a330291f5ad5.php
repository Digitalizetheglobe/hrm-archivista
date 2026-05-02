<?php
    $setting = App\Models\Utility::settings();
?>
<?php echo e(Form::open(['url' => 'clients/import', 'method' => 'post', 'enctype' => 'multipart/form-data'])); ?>

<div class="modal-body">
    <div class="row">
        <!-- File Upload Field -->
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="form-group">
                <?php echo e(Form::label('file', __('Excel File'), ['class' => 'form-label'])); ?>

                <div class="form-icon-user">
                    <?php echo e(Form::file('file', [
                        'class' => 'form-control',
                        'required' => 'required',
                        'accept' => '.xlsx,.xls,.csv'
                    ])); ?>

                </div>
                <small class="text-muted"><?php echo e(__('File should contain client_name column. Only client names will be imported.')); ?></small>
            </div>
        </div>

        <!-- Sample Download Link -->
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="text-start">
                <a href="<?php echo e(asset('sample/client_import_sample.xlsx')); ?>" class="btn btn-sm btn-primary">
                    <i class="ti ti-download"></i> <?php echo e(__('Download Sample File')); ?>

                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Import')); ?>" class="btn btn-primary">
</div>
<?php echo e(Form::close()); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/clients/import.blade.php ENDPATH**/ ?>