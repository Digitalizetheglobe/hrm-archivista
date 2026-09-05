<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Payslip')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item"><?php echo e(__('payslip')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php if(\Auth::user()->type == 'company' || \Auth::user()->type == 'hr'): ?>
        <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12 mt-4">
            <div class="card">
                <div class="card-body">
                    <?php echo e(Form::open(['route' => ['payslip.store'], 'method' => 'POST', 'id' => 'payslip_form'])); ?>

                    <div class="d-flex align-items-center justify-content-end">
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mx-2">
                            <div class="btn-box">
                                <?php echo e(Form::label('month', __('Select Month'), ['class' => 'form-label'])); ?>

                                <?php echo e(Form::select('month', $month, date('m'), ['class' => 'form-control select', 'id' => 'month'])); ?>

                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mx-2">
                            <div class="btn-box">
                                <?php echo e(Form::label('year', __('Select Year'), ['class' => 'form-label'])); ?>

                                <?php echo e(Form::select('year', $year, date('Y'), ['class' => 'form-control select'])); ?>

                            </div>
                        </div>
                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn  btn-primary"
                                onclick="document.getElementById('payslip_form').submit(); return false;"
                                data-bs-toggle="tooltip" title="<?php echo e(__('payslip')); ?>"
                                data-original-title="<?php echo e(__('payslip')); ?>"><?php echo e(__('Generate Payslip')); ?>

                            </a>
                        </div>
                    </div>
                    <?php echo e(Form::close()); ?>

                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-12">
        <div class="card">
            <div class="card-header payslip-find-header">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h5 class="mb-0"><?php echo e(__('Find Employee Payslip')); ?></h5>
                    <div class="d-flex align-items-center gap-2">
                        <?php if(Auth::user()->type == 'company' || Auth::user()->type == 'hr'): ?>
                            <?php echo e(Form::open(['route' => ['payslip.export'], 'method' => 'POST', 'id' => 'payslip_export_form', 'class' => 'mb-0'])); ?>

                            <input type="hidden" name="filter_month" class="filter_month">
                            <input type="hidden" name="filter_year" class="filter_year">
                            <input type="hidden" name="filter_employee" class="filter_employee">
                            <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" title="<?php echo e(__('Export')); ?>">
                                <i class="ti ti-file-export"></i>
                            </button>
                            <?php echo e(Form::close()); ?>

                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('Create Pay Slip')): ?>
                            <button type="button" class="btn btn-primary" id="bulk_payment" data-bs-toggle="tooltip" title="<?php echo e(__('Bulk Payment')); ?>">
                                <i class="ti ti-cash"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-lg-6 col-md-12">
                        <label class="form-label" for="payslip_emp_search"><?php echo e(__('Select Employee')); ?></label>
                        <div class="payslip-emp-combo">
                            <input type="text" class="form-control" id="payslip_emp_search" placeholder="<?php echo e(__('Search employee...')); ?>" autocomplete="off">
                            <div class="payslip-emp-menu" id="payslip_emp_menu" hidden></div>
                            <select class="employee_id d-none" id="payslip_employee_id" aria-hidden="true">
                                <?php $__currentLoopData = $employeeList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($id); ?>" <?php echo e((isset($_GET['employee_id']) && $_GET['employee_id'] == $id) ? 'selected' : ''); ?>><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6">
                        <label class="form-label"><?php echo e(__('Select Month')); ?></label>
                        <select class="form-control month_date">
                            <?php $__currentLoopData = $month; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $mon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($k); ?>" <?php echo e(date('m') == $k ? 'selected' : ''); ?>><?php echo e($mon); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6">
                        <label class="form-label"><?php echo e(__('Select Year')); ?></label>
                        <?php echo e(Form::select('year', $year, date('Y'), ['class' => 'form-control year_date'])); ?>

                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="pc-dt-render-column-cells">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Employee Id')); ?></th>
                                <?php if(\Auth::user()->type != 'employee'): ?>
                                    <th><?php echo e(__('Name')); ?></th>
                                <?php endif; ?>

                                <th><?php echo e(__('Salary')); ?></th>
                                <th><?php echo e(__('Net Salary')); ?></th>
                                <th><?php echo e(__('Action')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css-page'); ?>
<style>
    .payslip-find-header {
        overflow: visible;
    }
    .payslip-find-header .form-control,
    .payslip-find-header select.form-control {
        height: 42px;
        min-height: 42px;
        line-height: 1.5;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 14px;
    }
    .payslip-emp-combo {
        position: relative;
    }
    .payslip-emp-menu {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 4px);
        z-index: 3000;
        max-height: 280px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 6px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }
    .payslip-emp-menu button {
        display: block;
        width: 100%;
        text-align: left;
        background: none;
        border: 0;
        padding: 9px 12px;
        font-size: 14px;
        line-height: 1.4;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #212529;
        cursor: pointer;
    }
    .payslip-emp-menu button:hover,
    .payslip-emp-menu button.is-active {
        background: #fff4e8;
        color: #d35400;
    }
    .payslip-find-header .btn-primary {
        height: 42px;
        min-width: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 14px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('script-page'); ?>
    <script>
        $(document).ready(function() {
            var $empSelect = $('#payslip_employee_id');
            var $empSearch = $('#payslip_emp_search');
            var $empMenu = $('#payslip_emp_menu');

            function selectedEmployeeLabel() {
                var label = $.trim($empSelect.find('option:selected').text());
                return label || '<?php echo e(__('All')); ?>';
            }

            function renderEmployeeMenu(filter) {
                var q = (filter || '').toLowerCase();
                var html = '';
                $empSelect.find('option').each(function() {
                    var text = $.trim($(this).text());
                    if (q && text.toLowerCase().indexOf(q) === -1) {
                        return;
                    }
                    html += '<button type="button" data-value="' + $(this).attr('value') + '">' + text + '</button>';
                });
                if (!html) {
                    html = '<button type="button" disabled><?php echo e(__('No employees found')); ?></button>';
                }
                $empMenu.html(html).prop('hidden', false);
            }

            $empSearch.val(selectedEmployeeLabel());

            $empSearch.on('focus click', function() {
                if (this.value === selectedEmployeeLabel()) {
                    this.select();
                }
                renderEmployeeMenu($empSearch.val() === selectedEmployeeLabel() ? '' : $empSearch.val());
            });

            $empSearch.on('input', function() {
                renderEmployeeMenu(this.value);
            });

            $empMenu.on('click', 'button[data-value]', function() {
                var value = $(this).attr('data-value');
                var text = $.trim($(this).text());
                $empSelect.val(value).trigger('change');
                $empSearch.val(text);
                $empMenu.prop('hidden', true);
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.payslip-emp-combo').length) {
                    $empMenu.prop('hidden', true);
                    if (!$empSearch.val()) {
                        $empSearch.val(selectedEmployeeLabel());
                    }
                }
            });

            callback();

            function callback() {
                var month = $(".month_date").val();
                var year = $(".year_date").val();
                var employee_id = $(".employee_id").val();

                $('.filter_month').val(month);
                $('.filter_year').val(year);
                $('.filter_employee').val(employee_id);

                if (month == '') {
                    month = '<?php echo e(date('m', strtotime('last month'))); ?>';
                    year = '<?php echo e(date('Y')); ?>';

                    $('.filter_month').val(month);
                    $('.filter_year').val(year);
                }

                var datePicker = year + '-' + month;

                $.ajax({
                    url: '<?php echo e(route('payslip.search_json')); ?>',
                    type: 'POST',
                    data: {
                        "datePicker": datePicker,
                        "employee_id": employee_id,
                        "_token": "<?php echo e(csrf_token()); ?>",
                    },
                    success: function(data) {
                        var datatable_data = {
                            data: data
                        };

                        function renderstatus(data, cell, row) {
                            if (data == 'Paid')
                                return '<div class="badge bg-success p-2 px-3 rounded"><a href="#" class="text-white">' +
                                    data + '</a></div>';
                            else
                                return '<div class="badge bg-danger p-2 px-3 rounded"><a href="#" class="text-white">' +
                                    data + '</a></div>';
                        }

                        function renderButton(data, cell, row) {

                            var $div = $(row);
                            employee_id = $div.find('td:eq(0)').text();
                            status = $div.find('td:eq(6)').text();

                            var month = $(".month_date").val();
                            var year = $(".year_date").val();
                            var id = employee_id;
                            var payslip_id = data;
                            var clickToPaid = '';
                            var payslip = '';
                            var view = '';
                            var edit = '';
                            var deleted = '';
                            var form = '';

                            if (data != 0) {
                                var payslip =
                                    '<a href="#" data-url="<?php echo e(url('payslip/pdf/')); ?>/' + id +
                                    '/' + datePicker +
                                    '" data-size="md-pdf"  data-ajax-popup="true" class="btn btn-primary" data-title="<?php echo e(__('Employee Payslip')); ?>">' +
                                    '<?php echo e(__('Payslip')); ?>' + '</a> ';
                            }

                            if (status == "UnPaid" && data != 0) {
                                clickToPaid = '<a href="<?php echo e(url('payslip/paysalary/')); ?>/' + id +
                                    '/' + datePicker + '"  class="view-btn primary-bg btn-sm">' +
                                    '<?php echo e(__('Click To Paid')); ?>' + '</a>  ';
                            }

                            if (data != 0) {
                                view =
                                    '<a href="#" data-url="<?php echo e(url('payslip/showemployee/')); ?>/' +
                                    payslip_id +
                                    '"  data-ajax-popup="true" class="view-btn gray-bg" data-title="<?php echo e(__('View Employee Detail')); ?>">' +
                                    '<?php echo e(__('View')); ?>' + '</a>';
                            }

                            if (data != 0 && status == "UnPaid") {
                                edit =
                                    '<a href="#" data-url="<?php echo e(url('payslip/editemployee/')); ?>/' +
                                    payslip_id +
                                    '"  data-ajax-popup="true" class="view-btn blue-bg" data-title="<?php echo e(__('Edit Employee salary')); ?>">' +
                                    '<?php echo e(__('Edit')); ?>' + '</a>';
                            }

                            var url = '<?php echo e(route('payslip.delete', ':id')); ?>';
                            url = url.replace(':id', payslip_id);

                            <?php if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee'): ?>
                                if (data != 0) {
                                    deleted = '<a href="#"  data-url="' + url +
                                        '" class="payslip_delete view-btn red-bg" >' +
                                        '<?php echo e(__('Delete')); ?>' + '</a>';
                                }
                            <?php endif; ?>

                            return view + payslip + clickToPaid + edit + deleted + form;
                        }

                        console.clear();
                        var tr = '';
                        if (data.length > 0) {
                            $.each(data, function(indexInArray, valueOfElement) {
                                var status =
                                    '<div class="badge bg-danger p-2 px-3 rounded"><a href="#" class="text-white">' +
                                    valueOfElement[5] + '</a></div>';
                                if (valueOfElement[5] == 'Paid' || valueOfElement[5] ==
                                    'paid') {
                                    var status =
                                        '<div class="badge bg-success p-2 px-3 rounded"><a href="#" class="text-white">' +
                                        valueOfElement[5] + '</a></div>';
                                }
                                var id = valueOfElement[0];
                                var employee_id = valueOfElement[1];
                                var payslip_id = valueOfElement[6];

                                if (valueOfElement[6] != 0) {
                                    var payslip =
                                        '<a href="#" data-url="<?php echo e(url('payslip/pdf/')); ?>/' +
                                        id + '/' + datePicker +
                                        '" data-size="lg"  data-ajax-popup="true" class="btn-sm btn btn-warning" data-bs-toggle="tooltip" title="<?php echo e(__('Payslip')); ?>" data-title="<?php echo e(__('Employee Payslip')); ?>">' +
                                        '<i class="ti ti-download text-white"></i>' + '</a> ';
                                }
                                var clickToPaid = '';
                                var edit = '';

                                var url = '<?php echo e(route('payslip.delete', ':id')); ?>';
                                url = url.replace(':id', payslip_id);

                                <?php if(\Auth::user()->type == 'company' || \Auth::user()->type == 'hr'): ?>
                                    var deleted = '<a href="#"  data-url="' + url +
                                        '" class="payslip_delete view-btn btn btn-danger ms-1 btn-sm" data-bs-toggle="tooltip" title="<?php echo e(__('Delete')); ?>" >' +
                                        '<i class="ti ti-trash text-white"></i>' + '</a>';
                                <?php else: ?>
                                    var deleted = '';
                                <?php endif; ?>

                                var url_employee = valueOfElement['url'];
                                <?php if(\Auth::user()->type == 'company' || \Auth::user()->type == 'hr'): ?>
                                    tr +=
                                        '<tr>' +
                                        '<td> <a class="btn btn-outline-primary" href="' +
                                        url_employee + '">' + valueOfElement[1] + '</a></td> ' +
                                        '<td style="text-transform: uppercase;">' + valueOfElement[2] + '</td> ' +
                                        '<td>' + valueOfElement[3] + '</td>' +
                                        '<td>' + valueOfElement[4] + '</td>' +
                                        '<td>' + payslip + clickToPaid + edit + deleted +
                                        '</td>' +
                                        '</tr>';
                                <?php else: ?>
                                    tr +=
                                        '<tr>' +
                                        '<td> <a class="btn btn-outline-primary" href="' +
                                        url_employee + '">' + valueOfElement[1] + '</a></td> ' +
                                        '<td>' + valueOfElement[3] + '</td>' +
                                        '<td>' + valueOfElement[4] + '</td>' +
                                        '<td>' + payslip + clickToPaid + edit + deleted +
                                        '</td>' +
                                        '</tr>';
                                <?php endif; ?>
                            });
                        } else {
                            var colspan = $('#pc-dt-render-column-cells thead tr th').length;
                            var tr = '<tr><td class="dataTables-empty" colspan="' + colspan +
                                '"><?php echo e(__('No entries found')); ?></td></tr>';
                        }

                        $('#pc-dt-render-column-cells tbody').html(tr);
                        var table = document.querySelector("#pc-dt-render-column-cells");
                        var datatable = new simpleDatatables.DataTable(table);
                    },
                    error: function(data) {

                    }
                });
            }

            $(document).on("change", ".month_date,.year_date,.employee_id", function() {
                callback();
            });

            //bulkpayment Click
            $(document).on("click", "#bulk_payment", function() {
                var month = $(".month_date").val();
                var year = $(".year_date").val();
                var datePicker = year + '_' + month;

            });
            $(document).on('click', '#bulk_payment',
                'a[data-ajax-popup="true"], button[data-ajax-popup="true"], div[data-ajax-popup="true"]',
                function() {
                    var month = $(".month_date").val();
                    var year = $(".year_date").val();
                    var datePicker = year + '-' + month;

                    var title = 'Bulk Payment';
                    var size = 'md';
                    var url = 'payslip/bulk_pay_create/' + datePicker;

                    // return false;

                    $("#commonModal .modal-title").html(title);
                    $("#commonModal .modal-dialog").addClass('modal-' + size);
                    $.ajax({
                        url: url,
                        success: function(data) {
                            // alert(data);
                            // return false;
                            if (data.length) {
                                $('#commonModal .body').html(data);
                                $("#commonModal").modal('show');
                                // common_bind();
                            } else {
                                show_toastr('error', 'Permission denied.');
                                $("#commonModal").modal('hide');
                            }
                        },
                        error: function(data) {
                            data = data.responseJSON;
                            show_toastr('error', data.error);
                        }
                    });
                });

            $(document).on("click", ".payslip_delete", function() {
                var confirmation = confirm("are you sure you want to delete this payslip?");
                var url = $(this).data('url');

                if (confirmation) {
                    $.ajax({
                        type: "GET",
                        url: url,
                        dataType: "JSON",
                        success: function(data) {
                            // show_toastr(data.status, data.msg, 'data.status');
                            show_toastr('error', 'Payslip Deleted Successfully', 'success');

                            setTimeout(function() {
                                location.reload();
                            }, 800)
                        },
                    });
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/payslip/index.blade.php ENDPATH**/ ?>