<?php echo e(Form::open(['route' => 'attendance-regularisation.store', 'method' => 'post'])); ?>

<div class="modal-body">
    <div class="row">
        <?php if(!request('own') && (\Auth::user()->type == 'company' || Gate::check('attendance.regularisation.create.all'))): ?>
            <div class="form-group col-lg-12 col-md-12">
                <?php echo e(Form::label('employee_id', __('Employee'), ['class' => 'col-form-label'])); ?>

                <select name="employee_id" id="employee_id" class="form-control employee-select" required>
                    <option value=""><?php echo e(__('Select Employee')); ?></option>
                </select>
            </div>
        <?php endif; ?>
        <div class="form-group col-lg-6 col-md-6">
            <?php echo e(Form::label('missed_attendance_date', __('Missed Attendance Date'), ['class' => 'col-form-label'])); ?>

            <?php echo e(Form::date('missed_attendance_date', null, ['class' => 'form-control', 'required' => 'required', 'max' => date('Y-m-d')])); ?>

        </div>
        <div class="form-group col-lg-6 col-md-6">
            <?php echo e(Form::label('punch_in_time', __('Punch In Time'), ['class' => 'col-form-label'])); ?>

            <?php echo e(Form::time('punch_in_time', null, ['class' => 'form-control', 'required' => 'required'])); ?>

        </div>
        <div class="form-group col-lg-6 col-md-6">
            <?php echo e(Form::label('punch_out_time', __('Punch Out Time'), ['class' => 'col-form-label'])); ?>

            <?php echo e(Form::time('punch_out_time', null, ['class' => 'form-control', 'required' => 'required'])); ?>

        </div>
        <div class="form-group col-lg-6 col-md-6">
            <?php echo e(Form::label('reason', __('Reason'), ['class' => 'col-form-label'])); ?>

            <?php echo e(Form::select('reason', ['Missed Punch' => __('Missed Punch'), 'Technical Error' => __('Technical Error'), 'Others' => __('Others')], null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Reason')])); ?>

        </div>
        <div class="form-group col-lg-12 col-md-12">
            <?php echo e(Form::label('remark', __('Remark'), ['class' => 'col-form-label'])); ?>

            <?php echo e(Form::textarea('remark', null, ['class' => 'form-control', 'rows' => 3, 'required' => 'required', 'placeholder' => __('Enter remark')])); ?>

        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
    <?php echo e(Form::submit(__('Create'), ['class' => 'btn btn-primary'])); ?>

</div>
<?php echo e(Form::close()); ?>


<script>
    <?php if(!request('own') && (\Auth::user()->type == 'company' || Gate::check('attendance.regularisation.create.all'))): ?>
    // Initialize Select2 when commonModal is shown (for AJAX-loaded modals)
    $(document).on('shown.bs.modal', '#commonModal', function() {
        // Small delay to ensure DOM is ready
        setTimeout(function() {
            // Check if employee_id field exists in this modal
            var $employeeSelect = $('#employee_id');
            if ($employeeSelect.length > 0) {
                // Check if Select2 is available
                if (typeof $.fn.select2 === 'undefined') {
                    console.error('Select2 library is not loaded');
                    // Fallback: Load employees via regular AJAX and populate dropdown
                    $.ajax({
                        url: "<?php echo e(route('attendance-regularisation.getEmployees')); ?>",
                        type: 'GET',
                        dataType: 'json',
                        data: { search: '' },
                        success: function(data) {
                            if (Array.isArray(data)) {
                                $employeeSelect.empty().append('<option value=""><?php echo e(__('Select Employee')); ?></option>');
                                data.forEach(function(item) {
                                    $employeeSelect.append('<option value="' + item.id + '">' + item.text + '</option>');
                                });
                            }
                        },
                        error: function(xhr) {
                            console.error('Error loading employees:', xhr);
                        }
                    });
                    return;
                }
                
                // Destroy any existing Select2 or Choices initialization
                if ($employeeSelect.hasClass('select2-hidden-accessible')) {
                    $employeeSelect.select2('destroy');
                }
                if ($employeeSelect.data('choices')) {
                    $employeeSelect.data('choices').destroy();
                }
                
                // Initialize Select2 for employee dropdown with AJAX search
                $employeeSelect.select2({
                    dropdownParent: $('#commonModal'),
                    placeholder: "<?php echo e(__('Search Employee...')); ?>",
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: "<?php echo e(route('attendance-regularisation.getEmployees')); ?>",
                        type: 'GET',
                        dataType: 'json',
                        delay: 250,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        data: function (params) {
                            return {
                                search: params.term || '',
                                page: params.page || 1
                            };
                        },
                        processResults: function (data, params) {
                            // Check if data is an array
                            if (Array.isArray(data) && data.length > 0) {
                                return {
                                    results: data.map(function(item) {
                                        return {
                                            id: item.id,
                                            text: item.text
                                        };
                                    })
                                };
                            } else if (data && data.error) {
                                console.error('Server error:', data.error);
                                return {
                                    results: []
                                };
                            } else {
                                return {
                                    results: []
                                };
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error loading employees:', {
                                status: status,
                                error: error,
                                response: xhr.responseText,
                                url: "<?php echo e(route('attendance-regularisation.getEmployees')); ?>"
                            });
                            return {
                                results: []
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0
                });
            }
        }, 200);
    });
    <?php endif; ?>

    // Validate punch out time is after punch in time
    $(document).on('submit', 'form', function(e) {
        var punchIn = $('input[name="punch_in_time"]').val();
        var punchOut = $('input[name="punch_out_time"]').val();
        
        if (punchIn && punchOut) {
            if (punchOut <= punchIn) {
                e.preventDefault();
                alert('<?php echo e(__('Punch Out Time must be after Punch In Time.')); ?>');
                return false;
            }
        }
    });

    // Auto-fill attendance times
    $(document).on('change', '#missed_attendance_date, #employee_id', function() {
        var date = $('#missed_attendance_date').val();
        var employee_id = $('#employee_id').length > 0 ? $('#employee_id').val() : null;
        
        <?php if(!(!request('own') && (\Auth::user()->type == 'company' || Gate::check('attendance.regularisation.create.all')))): ?>
            // If employee select doesn't exist, it means we're an employee creating for ourselves
            employee_id = 'self'; // The controller will use Auth::user()->employee->id
        <?php endif; ?>

        if (date && employee_id) {
            $.ajax({
                url: "<?php echo e(route('attendance-regularisation.getAttendance')); ?>",
                type: 'GET',
                data: {
                    date: date,
                    employee_id: employee_id === 'self' ? '' : employee_id
                },
                success: function(response) {
                    if (response.success && response.data) {
                        if (response.data.clock_in) {
                            $('input[name="punch_in_time"]').val(response.data.clock_in);
                        }
                        if (response.data.clock_out) {
                            $('input[name="punch_out_time"]').val(response.data.clock_out);
                        }
                    } else {
                        // Optional: Clear fields if no attendance found
                        // $('input[name="punch_in_time"]').val('');
                        // $('input[name="punch_out_time"]').val('');
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching attendance data');
                }
            });
        }
    });
</script>


<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/attendance/regularisation/create.blade.php ENDPATH**/ ?>