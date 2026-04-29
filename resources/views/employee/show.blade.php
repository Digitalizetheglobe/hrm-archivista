@extends('layouts.admin')

@section('page-title')
    {{ __('Employee Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('employee') }}">{{ __('Employees') }}</a></li>
    <li class="breadcrumb-item active">{{ $employee->name }}</li>
@endsection

@section('action-button')
    @can('edit employee')
        <a href="{{ route('employee.edit', Crypt::encrypt($employee->id)) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
            <i class="ti ti-pencil"></i>
        </a>
    @endcan
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Employee Information') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Personal Details -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="ti ti-user me-2"></i>{{ __('Personal Details') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="w-50">{{ __('Employee ID') }}</th>
                                                <td>{{ $employeesId }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Name') }}</th>
                                                <td>{{ $employee->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Email') }}</th>
                                                <td>{{ $employee->email }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Date of Birth') }}</th>
                                                <td>{{ \Auth::user()->dateFormat($employee->dob) }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Gender') }}</th>
                                                <td>{{ ucfirst($employee->gender) }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Phone') }}</th>
                                                <td>{{ $employee->phone }}</td>
                                            </tr>
                                            @if($employee->emergency_number)
                                            <tr>
                                                <th>{{ __('Emergency Number') }}</th>
                                                <td>{{ $employee->emergency_number }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <th>{{ __('Address') }}</th>
                                                <td>{{ $employee->address }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        

                        
                        <!-- Education & Skills Details -->
                        
                    </div>

                    <!-- Company Details -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="ti ti-building me-2"></i>{{ __('Company Details') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <tbody>
                                            @if($employee->branch)
                                            <tr>
                                                <th class="w-50">{{ __('Branch') }}</th>
                                                <td>{{ $employee->branch->name }}</td>
                                            </tr>
                                            @endif
                                            @if($employee->department)
                                            <tr>
                                                <th>{{ __('Department') }}</th>
                                                <td>{{ $employee->department->name }}</td>
                                            </tr>
                                            @endif
                                            @if($employee->designation)
                                            <tr>
                                                <th>{{ __('Designation') }}</th>
                                                <td>{{ $employee->designation->name }}</td>
                                            </tr>
                                            @endif
                                            @if($employee->hourly_charged)
                                            <tr>
                                                <th>{{ __('Hourly Rate') }}</th>
                                                <td>{{ \Auth::user()->priceFormat($employee->hourly_charged) }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <th>{{ __('Date of Joining') }}</th>
                                                <td>{{ \Auth::user()->dateFormat($employee->company_doj) }}</td>
                                            </tr>
                                            @if($employee->employee_type)
                                            <tr>
                                                <th>{{ __('Employee Type') }}</th>
                                                <td>{{ $employee->employee_type }}</td>
                                            </tr>
                                            @endif
                                            @if($employee->company_dol)
                                            <tr>
                                                <th>{{ __('Date of Leaving') }}</th>
                                                <td>{{ \Auth::user()->dateFormat($employee->company_dol) }}</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                       
                    </div>
                </div>

                <div class="row">
                <div class="card md-12">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="ti ti-school me-2"></i>{{ __('Education & Skills') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <tbody>
                                            
                                            
                                            @if($employee->primary_skill)
                                            <tr>
                                                <th>{{ __('Primary Skill') }}</th>
                                                <td>{{ $employee->primary_skill }}</td>
                                            </tr>
                                            @endif
                                            @if($employee->secondary_skill)
                                            <tr>
                                                <th>{{ __('Secondary Skill') }}</th>
                                                <td>{{ $employee->secondary_skill }}</td>
                                            </tr>
                                            @endif
                                            @if($employee->certificate)
                                            <tr>
                                                <th>{{ __('Certificate') }}</th>
                                                <td>{{ $employee->certificate }}</td>
                                            </tr>
                                            @endif
                                            
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                </div>

                

                <!-- Document Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="ti ti-file-download me-2"></i>{{ __('Payroll Details') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <tbody>
                                            @if($employee->esic_no)
                                            <tr>
                                                <th class="w-50">{{ __('ESIC NO') }}</th>
                                                <td>{{ $employee->esic_no }}</td>
                                            </tr>
                                            @endif
                                            @if($employee->bank_ac_no)
                                            <tr>
                                                <th>{{ __('Bank A/c No') }}</th>
                                                <td>{{ $employee->bank_ac_no }}</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css-page')
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .card-body {
            padding: 1.25rem;
        }
        table th {
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
        }
        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .btn-outline-primary:hover {
            color: #fff;
        }
        .bg-primary {
            background-color: #3f51b5 !important;
        }
        .bg-info {
            background-color: #00bcd4 !important;
        }
        .bg-success {
            background-color: #4caf50 !important;
        }
        .bg-secondary {
            background-color: #6c757d !important;
        }
        .table td, .table th {
            padding: 0.75rem;
            vertical-align: middle;
            border-top: 1px solid #e9ecef;
        }
        .table th {
            width: 40%;
        }
    </style>
@endpush