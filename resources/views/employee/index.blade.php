    @extends('layouts.admin')

    @section('page-title')
        {{ __('Manage Employee') }}
    @endsection

    @section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
        <li class="breadcrumb-item">{{ __('Employee') }}</li>
    @endsection

    @section('action-button')
        <div class="d-flex align-items-center justify-content-end gap-2">
            <!-- View Toggle Buttons -->
            <div class="btn-group" role="group" aria-label="View Toggle">
                <button type="button" class="btn btn-sm btn-primary active" id="btn-table-view" data-bs-toggle="tooltip" title="{{ __('Table View') }}">
                    <i class="ti ti-list"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-card-view" data-bs-toggle="tooltip" title="{{ __('Card View') }}">
                    <i class="ti ti-layout-grid"></i>
                </button>
            </div>

            @can('Create Employee')
                <a href="{{ route('employee.create') }}" 
                data-title="{{ __('Create New Employee') }}" 
                class="btn btn-sm btn-primary flex items-center space-x-2">
                    <i class="ti ti-plus"></i>
                    <span>Create</span>
                </a>
            @endcan

            <a href="{{ route('employee.export') }}" 
            class="btn btn-sm btn-primary flex items-center space-x-2">
                <i class="ti ti-file-export"></i>
                <span>Export</span> 
            </a>
        </div>
    @endsection

    @section('content')
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('employee_type', __('Employee Type'), ['class' => 'form-label']) }}
                                            <select id="employee_type_filter" class="form-control select">
                                                <option value="">{{ __('All Types') }}</option>
                                                <option value="Consultant" {{ request('employee_type') == 'Consultant' ? 'selected' : '' }}>{{ __('Consultant') }}</option>
                                                <option value="Payroll" {{ request('employee_type') == 'Payroll' ? 'selected' : '' }}>{{ __('Payroll') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12" id="confirmation_filter_container" style="display: none;">
                                        <div class="btn-box">
                                            {{ Form::label('confirm_employment', __('Confirmation Status'), ['class' => 'form-label']) }}
                                            <select id="confirmation_filter" class="form-control select">
                                                <option value="">{{ __('All Status') }}</option>
                                                <option value="1" {{ request('confirm_employment') == '1' ? 'selected' : '' }}>{{ __('Confirmed') }}</option>
                                                <option value="0" {{ request('confirm_employment') == '0' ? 'selected' : '' }}>{{ __('Not Confirmed') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <a href="{{ route('employee.index') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-trash text-white-off"></i></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header p-0 border-0">
                        <ul class="nav nav-tabs" id="pills-tab" role="tablist" style="background: #f8f9fd; border-radius: 10px 10px 0 0;">
                            <li class="nav-item" role="presentation" style="margin-bottom: -1px;">
                                <a href="{{ route('employee.index') }}" 
                                   class="nav-link {{ !request('show_left') ? 'active' : '' }}" 
                                   style="{{ !request('show_left') ? 'background: white; border: none; border-radius: 10px 10px 0 0; color: #666; font-weight: bold; padding: 15px 25px;' : 'border: none; color: #888; padding: 15px 25px;' }}">
                                    {{ __('Active Employees') }}
                                </a>
                            </li>
                            <li class="nav-item" role="presentation" style="margin-bottom: -1px;">
                                <a href="{{ route('employee.index', ['show_left' => true]) }}" 
                                   class="nav-link {{ request('show_left') ? 'active' : '' }}" 
                                   style="{{ request('show_left') ? 'background: white; border: none; border-radius: 10px 10px 0 0; color: #f43f5e; font-weight: bold; padding: 15px 25px;' : 'border: none; color: #f43f5e; padding: 15px 25px;' }}">
                                    {{ __('Inactive Employees') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body table-border-style" style="padding:0px !important;">
                        
                        <!-- Table View Container -->
                        <div id="table-view-container">
                        <table class="table" id="pc-dt-simple">
                                <thead>
                                    <tr>
                                        <th>{{ __('Employee ID') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Department') }}</th>
                                        <th>{{ __('Designation') }}</th>
                                        <th>{{ __('Branch') }}</th>
                                        <th>{{ __('Employee Type') }}</th>
                                        <th>{{ __('Date Of Joining') }}</th>
                                        @if(isset($showLeft) && $showLeft)
                                            <th>{{ __('Date Of Leaving') }}</th>
                                        @endif
                                        @if (Gate::check('Edit Employee') || Gate::check('Delete Employee'))
                                            <th width="130px">{{ __('Action') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($employees as $employee)
                                        <tr>
                                            <td>
                                                @can('Show Employee')
                                                    <a class="btn btn-outline-primary"
                                                        href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</a>
                                                @else
                                                    <a href="#"
                                                        class="btn btn-outline-primary">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</a>
                                                @endcan
                                            </td>
                                            <td>{{ $employee->name ?? '-' }}</td>
                                            <td>{{ $employee->email ?? '-' }}</td>  
                                            <td>{{ $employee->department?->name ?? '-' }}</td>
                                            <td>{{ $employee->designation?->name ?? '-' }}</td>
                                            <td>{{ $employee->branch?->name ?? '-' }}</td>
                                            <td>
                                                @if($employee->employee_type)
                                                    <span class="badge bg-{{ $employee->employee_type == 'Consultant' ? 'warning' : 'success' }}">
                                                        {{ $employee->employee_type }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ \Auth::user()->dateFormat($employee->company_doj) }}
                                            </td>
                                            @if(isset($showLeft) && $showLeft)
                                                <td>
                                                    {{ \Auth::user()->dateFormat($employee->company_dol) }}
                                                </td>
                                            @endif
                                            @if (Gate::check('Edit Employee') || Gate::check('Delete Employee'))
                                                <td class="Action">
                                                        @if (($employee->user?->is_active ?? 0) == 1 && ($employee->user?->is_disable ?? 0) == 1)                                                    <span>
                                                            <div class="d-flex align-items-center">
                                                                @can('Edit Employee')
                                                                    <div class="action-btn bg-info me-2">
                                                                        <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                                                            class="mx-3 btn btn-sm align-items-center"
                                                                            data-bs-toggle="tooltip" title=""
                                                                            data-bs-original-title="{{ __('Edit') }}">
                                                                            <i class="ti ti-pencil text-white"></i>
                                                                        </a>
                                                                    </div>
                                                                @endcan

                                                                <!-- Confirmation Button for Contract and Payroll Employees -->
                                                                <div class="action-btn-confirm me-2">
                                                                    @if($employee->employee_type == 'Consultant' || $employee->employee_type == 'Payroll')
                                                                        @if(!$employee->confirm_of_employment)
                                                                            <div class="action-btn bg-success">
                                                                                <button type="button" 
                                                                                        class="mx-3 btn btn-sm align-items-center text-white"
                                                                                        data-bs-toggle="modal" 
                                                                                        data-bs-target="#confirmEmploymentModal"
                                                                                        data-employee-id="{{ $employee->id }}"
                                                                                        data-employee-name="{{ $employee->name }}"
                                                                                        data-bs-toggle="tooltip" 
                                                                                        title="{{ __('Confirm Employment') }}">
                                                                                    <i class="ti ti-check"></i>
                                                                                </button>
                                                                            </div>
                                                                        @else
                                                                            <div class="action-btn bg-warning">
                                                                                <button type="button" 
                                                                                        class="mx-3 btn btn-sm align-items-center text-white"
                                                                                        data-bs-toggle="modal" 
                                                                                        data-bs-target="#cancelEmploymentModal"
                                                                                        data-employee-id="{{ $employee->id }}"
                                                                                        data-employee-name="{{ $employee->name }}"
                                                                                        data-bs-toggle="tooltip" 
                                                                                        title="{{ __('Cancel Confirmation') }}">
                                                                                    <i class="ti ti-x"></i>
                                                                                </button>
                                                                            </div>
                                                                        @endif
                                                                    @endif
                                                                </div>

                                                                @can('Delete Employee')
                                                                    <div class="action-btn bg-danger">
                                                                        <a href="#"
                                                                            class="mx-3 btn btn-sm align-items-center text-white"
                                                                            onclick="if(confirm('{{ __("Are you sure?") }}')) { document.getElementById('delete-form-{{ $employee->id }}').submit(); } return false;"
                                                                            data-bs-toggle="tooltip" title=""
                                                                            data-bs-original-title="Delete" aria-label="Delete">
                                                                            <i class="ti ti-trash"></i>
                                                                        </a>
                                                                        {!! Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['employee.destroy', $employee->id],
                                                                            'id' => 'delete-form-' . $employee->id,
                                                                            'style' => 'display: none;'
                                                                        ]) !!}
                                                                        {!! Form::close() !!}
                                                                    </div>
                                                                @endcan
                                                            </div>
                                                        </span>
                                                    @else
                                                        <i class="ti ti-lock"></i>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Card View Container -->
                        <div id="card-view-container" style="display: none; padding: 25px; background: #f8f9fd;">
                            <!-- Card View Controls -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-sm-12 col-md-6 d-flex align-items-center">
                                    <select id="card-per-page" class="form-select form-select-sm w-auto me-2" style="padding: 0.25rem 2rem 0.25rem 0.5rem; display: inline-block;">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <span class="text-muted" style="font-size: 14px;">{{ __('entries per page') }}</span>
                                </div>
                                <div class="col-sm-12 col-md-6 d-flex justify-content-md-end mt-3 mt-md-0">
                                    <input type="text" id="card-search" class="form-control form-control-sm" placeholder="{{ __('Search...') }}" style="max-width: 200px;">
                                </div>
                            </div>

                            <div class="row" id="employee-card-list">
                                @foreach ($employees as $employee)
                                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4 employee-card-item" data-search="{{ strtolower(($employee->name ?? '') . ' ' . ($employee->email ?? '') . ' ' . \Auth::user()->employeeIdFormat($employee->employee_id) . ' ' . ($employee->department?->name ?? '') . ' ' . ($employee->designation?->name ?? '')) }}">
                                        <div class="card shadow-sm border-0 h-100 employee-card hover-shadow transition-all">
                                            <div class="card-body p-4 text-center">
                                                <div class="mb-3 position-relative d-inline-block">
                                                    <!-- Avatar Placeholder -->
                                                    <div class="avatar-circle mx-auto d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; border-radius: 50%; font-size: 28px; font-weight: bold; background: linear-gradient(135deg, var(--app-primary, #e8590c), #ff7b33); color: white;">
                                                        {{ strtoupper(substr($employee->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    @if(($employee->user?->is_active ?? 0) == 1 && ($employee->user?->is_disable ?? 0) == 1)
                                                        <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-white rounded-circle" title="Active"></span>
                                                    @else
                                                        <span class="position-absolute bottom-0 end-0 p-2 bg-danger border border-white rounded-circle" title="Inactive"></span>
                                                    @endif
                                                </div>
                                                
                                                <h5 class="mb-1 font-weight-bold text-dark">{{ $employee->name ?? '-' }}</h5>
                                                <p class="text-muted mb-2 font-14">{{ $employee->designation?->name ?? '-' }}</p>
                                                
                                                <div class="d-flex justify-content-center mb-3">
                                                    @if($employee->employee_type)
                                                        <span class="badge bg-{{ $employee->employee_type == 'Consultant' ? 'warning' : 'success' }} px-3 py-2 rounded-pill shadow-sm" style="font-size: 12px; font-weight: 600; letter-spacing: 0.5px;">
                                                            {{ strtoupper($employee->employee_type) }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary px-3 py-2 rounded-pill shadow-sm" style="font-size: 12px; font-weight: 600;">-</span>
                                                    @endif
                                                </div>

                                                <div class="text-start mt-4 pt-3 border-top" style="font-size: 13px;">
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span class="text-muted"><i class="ti ti-id me-2 text-primary"></i>EMP ID</span>
                                                        <span class="font-weight-bold text-dark">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span class="text-muted"><i class="ti ti-mail me-2 text-primary"></i>Email</span>
                                                        <span class="text-truncate ms-2 text-dark" title="{{ $employee->email }}" style="max-width: 150px;">{{ $employee->email ?? '-' }}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span class="text-muted"><i class="ti ti-building me-2 text-primary"></i>Department</span>
                                                        <span class="text-dark">{{ $employee->department?->name ?? '-' }}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span class="text-muted"><i class="ti ti-map-pin me-2 text-primary"></i>Branch</span>
                                                        <span class="text-dark">{{ $employee->branch?->name ?? '-' }}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="text-muted"><i class="ti ti-calendar me-2 text-primary"></i>Joined</span>
                                                        <span class="text-dark">{{ \Auth::user()->dateFormat($employee->company_doj) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Card Actions -->
                                            <div class="card-footer bg-light border-0 p-3 d-flex justify-content-center gap-2" style="border-radius: 0 0 10px 10px;">
                                                @can('Show Employee')
                                                    <a href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}" class="btn btn-sm btn-primary rounded-circle shadow-sm" data-bs-toggle="tooltip" title="{{ __('View Details') }}" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                @endcan
                                                @if (($employee->user?->is_active ?? 0) == 1 && ($employee->user?->is_disable ?? 0) == 1)
                                                    @can('Edit Employee')
                                                        <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}" class="btn btn-sm btn-info rounded-circle shadow-sm" data-bs-toggle="tooltip" title="{{ __('Edit') }}" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    @endcan
                                                    @if($employee->employee_type == 'Consultant' || $employee->employee_type == 'Payroll')
                                                        @if(!$employee->confirm_of_employment)
                                                            <button type="button" class="btn btn-sm btn-success rounded-circle shadow-sm text-white" data-bs-toggle="modal" data-bs-target="#confirmEmploymentModal" data-employee-id="{{ $employee->id }}" data-employee-name="{{ $employee->name }}" data-bs-toggle="tooltip" title="{{ __('Confirm Employment') }}" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ti ti-check"></i>
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-sm btn-warning rounded-circle shadow-sm text-white" data-bs-toggle="modal" data-bs-target="#cancelEmploymentModal" data-employee-id="{{ $employee->id }}" data-employee-name="{{ $employee->name }}" data-bs-toggle="tooltip" title="{{ __('Cancel Confirmation') }}" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="ti ti-x"></i>
                                                            </button>
                                                        @endif
                                                    @endif
                                                    @can('Delete Employee')
                                                        <a href="#" class="btn btn-sm btn-danger rounded-circle shadow-sm text-white" onclick="if(confirm('{{ __("Are you sure?") }}')) { document.getElementById('delete-form-card-{{ $employee->id }}').submit(); } return false;" data-bs-toggle="tooltip" title="{{ __('Delete') }}" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ti ti-trash"></i>
                                                        </a>
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['employee.destroy', $employee->id], 'id' => 'delete-form-card-' . $employee->id, 'style' => 'display: none;']) !!}
                                                        {!! Form::close() !!}
                                                    @endcan
                                                @else
                                                    <span class="btn btn-sm btn-secondary rounded-circle shadow-sm" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;" data-bs-toggle="tooltip" title="{{ __('Locked') }}">
                                                        <i class="ti ti-lock"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- No Results Message -->
                            <div id="card-no-results" class="text-center py-5" style="display: none;">
                                <h5 class="text-muted">{{ __('No matching records found') }}</h5>
                            </div>

                            <!-- Card View Pagination -->
                            <div class="row mt-4 align-items-center">
                                <div class="col-sm-12 col-md-6">
                                    <div class="text-muted" style="font-size: 14px;" id="card-info"></div>
                                </div>
                                <div class="col-sm-12 col-md-6 d-flex justify-content-md-end mt-3 mt-md-0">
                                    <ul class="pagination pagination-sm m-0" id="card-pagination"></ul>
                                </div>
                            </div>
                        </div>

                    </div>
                    </div>
            </div>

        </div>
    @endsection

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmEmploymentModal" tabindex="-1" aria-labelledby="confirmEmploymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmEmploymentModalLabel">{{ __('Confirmation of Employment') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to confirm the employment for') }} <strong id="employeeName"></strong>?</p>
                    <p class="text-muted">{{ __('This action will mark the employee as confirmed and cannot be undone.') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-success" id="confirmEmploymentBtn">
                        <i class="ti ti-check me-2"></i>{{ __('Approve') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div class="modal fade" id="cancelEmploymentModal" tabindex="-1" aria-labelledby="cancelEmploymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelEmploymentModalLabel">{{ __('Cancel Confirmation') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to cancel the confirmation for') }} <strong id="cancelEmployeeName"></strong>?</p>
                    <p class="text-muted">{{ __('This action will mark the employee as unconfirmed.') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Back') }}</button>
                    <button type="button" class="btn btn-warning" id="cancelEmploymentBtn">
                        <i class="ti ti-x me-2"></i>{{ __('Cancel Confirmation') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // View Toggle Logic
            const btnTableView = $('#btn-table-view');
            const btnCardView = $('#btn-card-view');
            const tableViewContainer = $('#table-view-container');
            const cardViewContainer = $('#card-view-container');

            // Restore view preference from localStorage if available
            const savedView = localStorage.getItem('employeeViewPreference');
            if(savedView === 'card') {
                showCardView();
            } else {
                showTableView();
            }

            btnTableView.on('click', showTableView);
            btnCardView.on('click', showCardView);

            function showTableView() {
                tableViewContainer.show();
                cardViewContainer.hide();
                btnTableView.removeClass('btn-outline-primary').addClass('btn-primary active');
                btnCardView.removeClass('btn-primary active').addClass('btn-outline-primary');
                localStorage.setItem('employeeViewPreference', 'table');
            }

            function showCardView() {
                tableViewContainer.hide();
                cardViewContainer.fadeIn();
                btnCardView.removeClass('btn-outline-primary').addClass('btn-primary active');
                btnTableView.removeClass('btn-primary active').addClass('btn-outline-primary');
                localStorage.setItem('employeeViewPreference', 'card');
            }

            // Card View Pagination and Search Logic
            const cardItems = $('.employee-card-item');
            const cardSearch = $('#card-search');
            const cardPerPage = $('#card-per-page');
            const cardPagination = $('#card-pagination');
            const cardInfo = $('#card-info');
            const cardNoResults = $('#card-no-results');
            
            let currentPage = 1;
            let rowsPerPage = parseInt(cardPerPage.val());
            let filteredCards = cardItems.toArray();

            function updateCardView() {
                const totalRows = filteredCards.length;
                const totalPages = Math.ceil(totalRows / rowsPerPage) || 1;
                
                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const start = (currentPage - 1) * rowsPerPage;
                const end = Math.min(start + rowsPerPage, totalRows);

                // Hide all cards first
                cardItems.hide();

                if (totalRows === 0) {
                    cardNoResults.show();
                    cardInfo.text('{{ __("Showing 0 to 0 of 0 entries") }}');
                } else {
                    cardNoResults.hide();
                    // Show cards for current page
                    for (let i = start; i < end; i++) {
                        $(filteredCards[i]).show();
                    }
                    cardInfo.text(`Showing ${start + 1} to ${end} of ${totalRows} entries`);
                }

                renderCardPagination(totalPages);
            }

            function renderCardPagination(totalPages) {
                cardPagination.empty();
                
                if (totalPages <= 1) return;

                // Previous Button
                const prevDisabled = currentPage === 1 ? 'disabled' : '';
                cardPagination.append(`
                    <li class="page-item ${prevDisabled}">
                        <a class="page-link" href="#" data-page="${currentPage - 1}">&lsaquo;</a>
                    </li>
                `);

                // Page Numbers
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, currentPage + 2);
                
                if (startPage > 1) {
                    cardPagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
                    if (startPage > 2) {
                        cardPagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    const active = i === currentPage ? 'active' : '';
                    cardPagination.append(`
                        <li class="page-item ${active}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `);
                }
                
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        cardPagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                    }
                    cardPagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
                }

                // Next Button
                const nextDisabled = currentPage === totalPages ? 'disabled' : '';
                cardPagination.append(`
                    <li class="page-item ${nextDisabled}">
                        <a class="page-link" href="#" data-page="${currentPage + 1}">&rsaquo;</a>
                    </li>
                `);
            }

            cardSearch.on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                filteredCards = cardItems.filter(function() {
                    return $(this).data('search').indexOf(searchTerm) > -1;
                }).toArray();
                currentPage = 1; // Reset to first page on search
                updateCardView();
            });

            cardPerPage.on('change', function() {
                rowsPerPage = parseInt($(this).val());
                currentPage = 1;
                updateCardView();
            });

            cardPagination.on('click', '.page-link', function(e) {
                e.preventDefault();
                const parent = $(this).parent();
                if (parent.hasClass('disabled') || parent.hasClass('active')) return;
                
                const newPage = $(this).data('page');
                if (newPage) {
                    currentPage = parseInt(newPage);
                    updateCardView();
                }
            });

            // Initialize Card View pagination
            updateCardView();


            // Show/hide confirmation filter based on employee type selection
            function toggleConfirmationFilter() {
                var employeeType = $('#employee_type_filter').val();
                if (employeeType === 'Consultant' || employeeType === 'Payroll') {
                    $('#confirmation_filter_container').show();
                } else {
                    $('#confirmation_filter_container').hide();
                    // Clear confirmation filter when not Contract or Payroll
                    $('#confirmation_filter').val('');
                }
            }
            
            // Initial check
            toggleConfirmationFilter();
            
            // Handle employee type filter change
            $('#employee_type_filter').on('change', function() {
                var employeeType = $(this).val();
                var currentUrl = new URL(window.location);
                
                // Update or remove employee_type parameter
                if (employeeType) {
                    currentUrl.searchParams.set('employee_type', employeeType);
                } else {
                    currentUrl.searchParams.delete('employee_type');
                }
                
                // Remove confirmation filter if not Consultant or Payroll
                if (employeeType !== 'Consultant' && employeeType !== 'Payroll') {
                    currentUrl.searchParams.delete('confirm_employment');
                }
                
                // Navigate to the updated URL
                window.location.href = currentUrl.toString();
            });
            
            // Handle confirmation filter change
            $('#confirmation_filter').on('change', function() {
                var confirmationStatus = $(this).val();
                var currentUrl = new URL(window.location);
                
                // Update or remove confirm_employment parameter
                if (confirmationStatus) {
                    currentUrl.searchParams.set('confirm_employment', confirmationStatus);
                } else {
                    currentUrl.searchParams.delete('confirm_employment');
                }
                
                // Navigate to the updated URL
                window.location.href = currentUrl.toString();
            });
            
            // Handle employment confirmation modal
            $('#confirmEmploymentModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var employeeId = button.data('employee-id');
                var employeeName = button.data('employee-name');
                
                var modal = $(this);
                modal.find('#employeeName').text(employeeName);
                modal.find('#confirmEmploymentBtn').data('employee-id', employeeId);
            });
            
            // Handle confirmation button click
            $('#confirmEmploymentBtn').on('click', function() {
                var employeeId = $(this).data('employee-id');
                var modal = $('#confirmEmploymentModal');
                
                // Disable button to prevent multiple clicks
                $(this).prop('disabled', true).html('<i class="ti ti-loader ti-spin me-2"></i>{{ __('Processing...') }}');
                
                // Send AJAX request to confirm employment
                $.ajax({
                    url: '{{ route("employee.confirm-employment") }}',
                    method: 'POST',
                    data: {
                        employee_id: employeeId,
                        _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Close modal
                            modal.modal('hide');
                            
                            // Show success message
                            var toast = '<div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">' +
                                        '<i class="ti ti-check me-2"></i>{{ __("Employment confirmed successfully!") }}' +
                                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                                        '</div>';
                            $('body').append(toast);
                            
                            // Reload page to show updated status
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            alert(response.message || '{{ __("An error occurred. Please try again.") }}');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON?.message || '{{ __("An error occurred. Please try again.") }}';
                        
                        // Check specifically for CSRF token mismatch
                        if (xhr.status === 419 || xhr.responseJSON?.message?.includes('CSRF') || xhr.responseJSON?.exception?.includes('CSRF')) {
                            errorMessage = '{{ __("CSRF token mismatch. Please refresh the page and try again.") }}';
                            // Optionally reload the page after showing the error
                            setTimeout(function() {
                                window.location.reload();
                            }, 3000);
                        }
                        
                        alert(errorMessage);
                    },
                    complete: function() {
                        // Re-enable button
                        $('#confirmEmploymentBtn').prop('disabled', false).html('<i class="ti ti-check me-2"></i>{{ __("Approve") }}');
                    }
                });
            });
            
            // Handle cancel employment modal
            $('#cancelEmploymentModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var employeeId = button.data('employee-id');
                var employeeName = button.data('employee-name');
                
                var modal = $(this);
                modal.find('#cancelEmployeeName').text(employeeName);
                modal.find('#cancelEmploymentBtn').data('employee-id', employeeId);
            });
            
            // Handle cancel confirmation button click
            $('#cancelEmploymentBtn').on('click', function() {
                var employeeId = $(this).data('employee-id');
                var modal = $('#cancelEmploymentModal');
                
                // Disable button to prevent multiple clicks
                $(this).prop('disabled', true).html('<i class="ti ti-loader ti-spin me-2"></i>{{ __('Processing...') }}');
                
                // Send AJAX request to cancel confirmation
                $.ajax({
                    url: '{{ route("employee.cancel-employment") }}',
                    method: 'POST',
                    data: {
                        employee_id: employeeId,
                        _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Close modal
                            modal.modal('hide');
                            
                            // Show success message
                            var toast = '<div class="alert alert-warning alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;">' +
                                        '<i class="ti ti-x me-2"></i>{{ __("Confirmation cancelled successfully!") }}' +
                                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                                        '</div>';
                            $('body').append(toast);
                            
                            // Reload page to show updated status
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            alert(response.message || '{{ __("An error occurred. Please try again.") }}');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON?.message || '{{ __("An error occurred. Please try again.") }}';
                        
                        // Check specifically for CSRF token mismatch
                        if (xhr.status === 419 || xhr.responseJSON?.message?.includes('CSRF') || xhr.responseJSON?.exception?.includes('CSRF')) {
                            errorMessage = '{{ __("CSRF token mismatch. Please refresh the page and try again.") }}';
                            // Optionally reload the page after showing the error
                            setTimeout(function() {
                                window.location.reload();
                            }, 3000);
                        }
                        
                        alert(errorMessage);
                    },
                    complete: function() {
                        // Re-enable button
                        $('#cancelEmploymentBtn').prop('disabled', false).html('<i class="ti ti-x me-2"></i>{{ __("Cancel Confirmation") }}');
                    }
                });
            });
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .Action {
            text-align: center;
            vertical-align: middle;
        }
        
        .Action .d-flex {
            justify-content: center;
        }
        
        .action-btn-confirm {
            width: 44px;
            min-width: 44px;
            height: 32px;
            display: inline-block;
        }
        
        .action-btn {
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .action-btn .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1;
        }

        /* Card View Styles */
        .employee-card {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05) !important;
        }
        .employee-card .card-body {
            background: #fff;
        }
        .employee-card .avatar-circle {
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .hover-shadow:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
            transform: translateY(-5px);
        }
        .transition-all {
            transition: all 0.3s ease;
        }
        
        /* Ensure table wrapper fits correctly when hidden/shown */
        .dataTable-wrapper {
            width: 100%;
        }
    </style>
    @endpush
