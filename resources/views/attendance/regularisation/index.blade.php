@extends('layouts.admin')

@section('page-title')
    {{ __('Attendance Regularisation') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('Attendance Regularisation') }}</li>
@endsection

@section('action-button')
    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee' || (!request('own') && Gate::check('attendance.regularisation.create.all')) || (request('own') && Gate::check('attendance.regularisation.create.own')))
        <a href="#" data-url="{{ route('attendance-regularisation.create', request('own') ? ['own' => 1] : []) }}" data-ajax-popup="true"
            data-title="{{ __('Create Attendance Regularisation') }}" data-size="lg"
            class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-border-style">
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    @if (\Auth::user()->type != 'employee')
                                        <th class="text-start">{{ __('Employee Name') }}</th>
                                    @endif
                                    <th class="text-start">{{ __('Date') }}</th>
                                    <th class="text-start">{{ __('In Time') }}</th>
                                    <th class="text-start">{{ __('Out Time') }}</th>
                                    <th class="text-start">{{ __('Reason') }}</th>
                                    <th class="text-start">{{ __('Remark') }}</th>
                                    <th class="text-start">{{ __('Status') }}</th>
                                    <th class="text-center" width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($regularisations as $regularisation)
                                    <tr id="regularisation-row-{{ $regularisation->id }}">
                                        @if (\Auth::user()->type != 'employee')
                                            <td class="text-start">{{ !empty($regularisation->employee) ? $regularisation->employee->name : __('N/A') }}</td>
                                        @endif
                                        <td class="text-start">{{ \Auth::user()->dateFormat($regularisation->missed_attendance_date) }}</td>
                                        <td class="text-start">{{ \Auth::user()->timeFormat($regularisation->punch_in_time) }}</td>
                                        <td class="text-start">{{ \Auth::user()->timeFormat($regularisation->punch_out_time) }}</td>
                                        <td class="text-start">{{ $regularisation->reason }}</td>
                                        <td class="text-start">{{ \Illuminate\Support\Str::limit($regularisation->remark, 30) }}</td>
                                        <td class="text-start">
                                            @if ($regularisation->status == 'Pending')
                                                <span class="badge bg-warning">{{ __('Pending') }}</span>
                                            @elseif ($regularisation->status == 'Approved')
                                                <span class="badge bg-success">{{ __('Approved') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                            @endif
                                        </td>
                                        <td class="Action" style="vertical-align: middle;">
                                            <span class="d-flex justify-content-end align-items-center gap-1">
                                                
                                                {{-- View Button (Available to all who can access the page) --}}
                                                <div class="action-btn bg-info">
                                                    <a href="#" class="btn btn-sm"
                                                        data-url="{{ route('attendance-regularisation.show', $regularisation->id) }}"
                                                        data-ajax-popup="true" data-size="lg" data-title="{{ __('View Details') }}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>

                                                @if ($regularisation->status == 'Pending')
                                                    {{-- Action Button (Approve/Reject) --}}
                                                    @if (\Auth::user()->type == 'company' || Gate::check('attendance.regularisation.action.all'))
                                                        <div class="action-btn bg-success">
                                                            <a href="#" class="btn btn-sm"
                                                                data-url="{{ route('attendance-regularisation.action', $regularisation->id) }}"
                                                                data-ajax-popup="true" data-size="lg" data-title="{{ __('Attendance Regularisation Action') }}">
                                                                <i class="ti ti-caret-right text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endif

                                                    {{-- Edit Button --}}
                                                    @if (\Auth::user()->type == 'company' || Gate::check('attendance.regularisation.edit.all') || (Gate::check('attendance.regularisation.edit.own') && $regularisation->employee_id == (\Auth::user()->employee ? \Auth::user()->employee->id : 0)))
                                                        <div class="action-btn bg-info">
                                                            <a href="#" class="btn btn-sm"
                                                                data-url="{{ route('attendance-regularisation.edit', $regularisation->id) }}"
                                                                data-ajax-popup="true" data-size="lg" data-title="{{ __('Edit Regularisation') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endif

                                                {{-- Delete Button --}}
                                                @if (\Auth::user()->type == 'company' || Gate::check('attendance.regularisation.delete.all') || (Gate::check('attendance.regularisation.delete.own') && $regularisation->employee_id == (\Auth::user()->employee ? \Auth::user()->employee->id : 0)))
                                                    <div class="action-btn bg-danger">
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['attendance-regularisation.destroy', $regularisation->id],
                                                            'id' => 'delete-form-' . $regularisation->id,
                                                        ]) !!}
                                                        <a href="#"
                                                            class="btn btn-sm bs-pass-para" aria-label="Delete"
                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $regularisation->id }}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endif

                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ \Auth::user()->type != 'employee' ? '8' : '7' }}" class="text-center">
                                            {{ __('No attendance regularisation requests found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        .table th {
            white-space: nowrap !important;
            text-align: left !important;
            vertical-align: middle !important;
            padding: 0.75rem !important;
            position: relative;
        }
        
        .table td {
            vertical-align: middle !important;
        }

        /* Ensure proper column width alignment */
        #pc-dt-simple th {
            min-width: 120px;
        }
        
        @media (max-width: 768px) {
            #pc-dt-simple th {
                min-width: 140px !important;
            }
        }
    </style>
    

<script>
$(document).on("submit", "#regularisation-action-form", function(e) {
    e.preventDefault();
    var form = $(this);
    var submitBtn = $(document.activeElement);
    var status = submitBtn.is("input[type=submit], button[type=submit]") ? submitBtn.val() : form.find("input[name=status]:focus").val();
    var formData = form.serializeArray();
    if (status) {
        formData = formData.filter(function(item) { return item.name !== "status"; });
        formData.push({ name: "status", value: status });
    }
    var regId = form.find("input[name=regularisation_id]").val();
    var submitBtns = form.find("input[type=submit], button[type=submit]");
    submitBtns.prop("disabled", true);
    $.ajax({
        url: form.attr("action"),
        type: "POST",
        data: $.param(formData),
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        success: function(response) {
            $("#commonModal").modal("hide");
            if (response && response.is_success) {
                show_toastr("Success", response.message || "Status updated successfully.", "success");
                var row = $("#regularisation-row-" + regId);
                if (row.length) {
                    var newStatus = response.status || status;
                    var badgeClass = (newStatus === "Approved") ? "bg-success" : ((newStatus === "Pending") ? "bg-warning" : "bg-danger");
                    row.find("td:eq(6)").html('<span class="badge ' + badgeClass + '">' + newStatus + '</span>');
                    row.find(".action-btn.bg-success").remove();
                }
            } else {
                show_toastr("Error", (response && response.error) ? response.error : "Failed to update status", "error");
            }
        },
        error: function(xhr) {
            submitBtns.prop("disabled", false);
            var errorMsg = "Something went wrong. Please try again.";
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            show_toastr("Error", errorMsg, "error");
        }
    });
});
</script>
@endpush


