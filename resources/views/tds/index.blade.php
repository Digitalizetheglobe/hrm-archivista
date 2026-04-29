@extends('layouts.admin')

@section('page-title')
    {{ __('TDS Management') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item">{{ __('TDS') }}</li>
@endsection

@php
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
@endphp

@section('content')
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <h5>{{ __('TDS Details') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tds-table">
                        <thead>
                            <tr>
                                <th>{{ __('Employee Name') }}</th>
                                <th>{{ __('Monthly Salary') }}</th>
                                <th>{{ __('TDS Type') }}</th>
                                <th>{{ __('TDS') }}</th>
                                <th>{{ __('Monthly TDS') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $employee)
                                <tr>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->set_salary ? formatIndian($employee->set_salary) : '0' }}</td>
                                    <td>
                                        @if(isset($employee->tds_type))
                                            @if($employee->tds_type == 0)
                                                <span class="badge bg-info">Old Regime</span>
                                            @else
                                                <span class="badge bg-success">New Regime</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Not Set</span>
                                        @endif
                                    </td>
                                    <td>{{ isset($employee->total_tax_amount) ? formatIndian($employee->total_tax_amount) : '0' }}</td>
                                    <td>
                                        @if(isset($employee->tds_type) && isset($employee->monthly_tds))
                                            {{ formatIndian($employee->monthly_tds) }}
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary me-1 edit-tds-btn" 
                                                data-employee-id="{{ $employee->id }}"
                                                data-employee-name="{{ $employee->name }}"
                                                data-current-tds-type="{{ $employee->tds_type ?? '' }}">
                                            <i class="ti ti-edit"></i> {{ __('Edit') }}
                                        </button>
                                        @if(isset($employee->tds_type))
                                            <a href="{{ $employee->tds_type == 0 ? route('tds.old.regime', $employee->id) : route('tds.new.regime', $employee->id) }}" 
                                               class="btn btn-sm btn-success tds-regime-btn">
                                                <i class="ti ti-calculator"></i> {{ __('TDS') }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- TDS Type Selection Modal -->
    <div class="modal fade" id="tdsTypeModal" tabindex="-1" aria-labelledby="tdsTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tdsTypeModalLabel">Select TDS Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Which TDS type do you want for <span id="employeeNameDisplay"></span>?</strong></p>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="tds_type" id="old_regime" value="0">
                        <label class="form-check-label" for="old_regime">
                            Old Regime
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tds_type" id="new_regime" value="1">
                        <label class="form-check-label" for="new_regime">
                            New Regime
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveTdsType">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Global variables for employee data
        let currentEmployeeId = null;
        let currentEmployeeName = null;
        
        // Global functions for modal
        function closeModal() {
            console.log('closeModal called');
            const modal = document.getElementById('tdsTypeModal');
            if (modal) {
                modal.remove();
            }
        }
        
        function saveTdsTypeFunction() {
            console.log('saveTdsTypeFunction called');
            
            // Check if employee variables are set
            if (!currentEmployeeId) {
                console.error('currentEmployeeId is not set');
                return;
            }
            
            const selectedType = document.querySelector('input[name="tds_type"]:checked');
            
            if (!selectedType) {
                alert('Please select a TDS type (Old Regime or New Regime).');
                return;
            }
            
            const tdsType = selectedType.value;
            console.log(`Saving TDS type: ${tdsType} for employee: ${currentEmployeeId}`);
            
            // Save via AJAX directly
            $.ajax({
                url: '{{ route("tds.save.type") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    employee_id: currentEmployeeId,
                    tds_type: tdsType
                },
                success: function(response) {
                    console.log('Save success:', response);
                    closeModal();
                    location.reload(); // Reload page to reflect changes
                },
                error: function(xhr, status, error) {
                    console.error('Save error:', xhr.responseText);
                    console.error('Status:', status);
                    console.error('Error:', error);
                    alert('Error saving TDS type: ' + xhr.responseText);
                }
            });
        }
        
        // Test basic functionality first
        console.log('Script starting...');
        
        $(document).ready(function() {
            console.log('Document ready!');
            
            // Initialize DataTable
            try {
                $('#tds-table').DataTable({
                    pageLength: 10,
                    language: {
                        paginate: {
                            next: '<i class="ti ti-chevron-right"></i>',
                            previous: '<i class="ti ti-chevron-left"></i>'
                        }
                    }
                });
                console.log('DataTable initialized successfully');
            } catch (error) {
                console.error('DataTable error:', error);
            }
            
            // Simple click handler
            $(document).on('click', '.edit-tds-btn', function(e) {
                console.log('=== EDIT BUTTON CLICKED ===');
                
                currentEmployeeId = $(this).data('employee-id');
                currentEmployeeName = $(this).data('employee-name');
                const currentTdsType = $(this).data('current-tds-type');
                
                console.log('Employee ID:', currentEmployeeId, 'Name:', currentEmployeeName, 'Current TDS Type:', currentTdsType);
                
                // Create and show modal manually
                const modalHtml = `
                    <div class="modal fade show" id="tdsTypeModal" tabindex="-1" style="display: block;">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="tdsTypeModalLabel">Select TDS Type for ${currentEmployeeName}</h5>
                                    <button type="button" class="btn-close" onclick="closeModal()">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Which TDS type do you want for ${currentEmployeeName}?</strong></p>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="tds_type" id="old_regime" value="0" ${currentTdsType == 0 ? 'checked' : ''}>
                                        <label class="form-check-label" for="old_regime">
                                            Old Regime
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tds_type" id="new_regime" value="1" ${currentTdsType == 1 ? 'checked' : ''}>
                                        <label class="form-check-label" for="new_regime">
                                            New Regime
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                                    <button type="button" class="btn btn-primary" onclick="saveTdsTypeFunction()">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                const existingModal = document.getElementById('tdsTypeModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                // Add modal to body
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                
                // Re-attach event listeners to the new modal buttons
                setTimeout(() => {
                    const saveBtn = document.querySelector('#tdsTypeModal .btn-primary');
                    const cancelBtn = document.querySelector('#tdsTypeModal .btn-close');
                    
                    if (saveBtn) {
                        saveBtn.onclick = function() {
                            console.log('Save button clicked in modal');
                            saveTdsTypeFunction();
                        };
                    }
                    
                    if (cancelBtn) {
                        cancelBtn.onclick = function() {
                            console.log('Cancel button clicked in modal');
                            closeModal();
                        };
                    }
                }, 100);
                
                console.log('Modal added to page');
            });
        });
    </script>
@endpush
