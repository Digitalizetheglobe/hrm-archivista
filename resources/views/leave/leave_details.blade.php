@extends('layouts.admin')

@section('page-title')
    {{ __('Leave Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('leave.index') }}">{{ __('Leave') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leave Details') }}</li>
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="form-group">
                <label for="month_filter" class="form-label">{{ __('Select Month') }}</label>
                <input type="month" id="month_filter" name="month" class="form-control" 
                       value="{{ $selectedMonth }}" onchange="window.location.href='?month='+this.value">
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        {{ __('Total Leaves') }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $monthlySummary['total_leaves'] }} 
                                        <span class="text-xs text-muted">{{ __('Days') }}</span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        {{ __('Credited Leaves') }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $monthlySummary['credited_leaves'] }} 
                                        <span class="text-xs text-muted">{{ __('Days') }}</span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-plus-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        {{ __('Used Leaves') }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $monthlySummary['used_leaves'] }} 
                                        <span class="text-xs text-muted">{{ __('Days') }}</span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-minus-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        {{ __('Remaining Leaves') }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $monthlySummary['remaining_leaves'] }} 
                                        <span class="text-xs text-muted">{{ __('Days') }}</span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($contractConfirmEmployees->count() > 0)
        <div class="employee-category">
            <h4 class="mb-3">
                <i class="fas fa-user-check text-primary mr-2"></i>
                {{ __('Contract (Confirm) Employees') }}
                <span class="badge bg-primary ml-2">{{ $contractConfirmEmployees->count() }}</span>
            </h4>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee Name') }}</th>
                                    <th>{{ __('Leave Type') }}</th>
                                    <th>{{ __('Allocated Days') }}</th>
                                    <th>{{ __('Carried Forward') }}</th>
                                    <th>{{ __('Used Days') }}</th>
                                    <th>{{ __('Remaining Days') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaveDetails['contract_confirm'] as $employeeDetail)
                                    <?php
                                        $employee = $employeeDetail['employee'];
                                        $leaveBalances = $employeeDetail['leave_balances'];
                                        $firstRow = true;
                                    ?>
                                    
                                    @foreach($leaveBalances as $leaveBalance)
                                        <tr>
                                            @if($firstRow)
                                                <td rowspan="<?php echo count($leaveBalances); ?>" class="employee-name">
                                                    <?php echo $employee->name; ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo $employee->email; ?></small>
                                                </td>
                                                <?php $firstRow = false; ?>
                                            @endif
                                            
                                            <td class="leave-type-title">
                                                <?php echo $leaveBalance['leave_type']->title; ?>
                                                @if($leaveBalance['is_unlimited'])
                                                    <span class="badge bg-info leave-badge">{{ __('Unlimited') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($leaveBalance['is_unlimited'])
                                                    <span class="text-muted">{{ __('N/A') }}</span>
                                                @else
                                                    <?php echo $leaveBalance['allocated_days']; ?>
                                                @endif
                                            </td>
                                            <td>
                                                @if($leaveBalance['carried_forward_days'] > 0)
                                                    <span class="text-success">+<?php echo $leaveBalance['carried_forward_days']; ?></span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td><?php echo $leaveBalance['used_days']; ?></td>
                                            <td>
                                                @if($leaveBalance['is_unlimited'])
                                                    <span class="remaining-days unlimited">{{ __('Unlimited') }}</span>
                                                @else
                                                    <span class="remaining-days <?php echo $leaveBalance['remaining_days'] > 0 ? 'positive' : 'zero'; ?>">
                                                        <?php echo $leaveBalance['remaining_days']; ?>
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($contractNotConfirmEmployees->count() > 0)
        <div class="employee-category">
            <h4 class="mb-3">
                <i class="fas fa-user-times text-warning mr-2"></i>
                {{ __('Contract (Not Confirm) Employees') }}
                <span class="badge bg-warning ml-2">{{ $contractNotConfirmEmployees->count() }}</span>
            </h4>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee Name') }}</th>
                                    <th>{{ __('Leave Type') }}</th>
                                    <th>{{ __('Allocated Days') }}</th>
                                    <th>{{ __('Carried Forward') }}</th>
                                    <th>{{ __('Used Days') }}</th>
                                    <th>{{ __('Remaining Days') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaveDetails['contract_not_confirm'] as $employeeDetail)
                                    <?php
                                        $employee = $employeeDetail['employee'];
                                        $leaveBalances = $employeeDetail['leave_balances'];
                                        $firstRow = true;
                                    ?>
                                    
                                    @foreach($leaveBalances as $leaveBalance)
                                        <tr>
                                            @if($firstRow)
                                                <td rowspan="<?php echo count($leaveBalances); ?>" class="employee-name">
                                                    <?php echo $employee->name; ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo $employee->email; ?></small>
                                                </td>
                                                <?php $firstRow = false; ?>
                                            @endif
                                            
                                            <td class="leave-type-title">
                                                <?php echo $leaveBalance['leave_type']->title; ?>
                                                @if($leaveBalance['is_unlimited'])
                                                    <span class="badge bg-info leave-badge">{{ __('Unlimited') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($leaveBalance['is_unlimited'])
                                                    <span class="text-muted">{{ __('N/A') }}</span>
                                                @else
                                                    <?php echo $leaveBalance['allocated_days']; ?>
                                                @endif
                                            </td>
                                            <td>
                                                @if($leaveBalance['carried_forward_days'] > 0)
                                                    <span class="text-success">+<?php echo $leaveBalance['carried_forward_days']; ?></span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td><?php echo $leaveBalance['used_days']; ?></td>
                                            <td>
                                                @if($leaveBalance['is_unlimited'])
                                                    <span class="remaining-days unlimited">{{ __('Unlimited') }}</span>
                                                @else
                                                    <span class="remaining-days <?php echo $leaveBalance['remaining_days'] > 0 ? 'positive' : 'zero'; ?>">
                                                        <?php echo $leaveBalance['remaining_days']; ?>
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($payrollEmployees->count() > 0)
        <div class="employee-category">
            <h4 class="mb-3">
                <i class="fas fa-users text-success mr-2"></i>
                {{ __('Payroll Employees') }}
                <span class="badge bg-success ml-2">{{ $payrollEmployees->count() }}</span>
            </h4>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee Name') }}</th>
                                    <th>{{ __('Leave Type') }}</th>
                                    <th>{{ __('Allocated Days') }}</th>
                                    <th>{{ __('Carried Forward') }}</th>
                                    <th>{{ __('Used Days') }}</th>
                                    <th>{{ __('Remaining Days') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaveDetails['payroll'] as $employeeDetail)
                                    <?php
                                        $employee = $employeeDetail['employee'];
                                        $leaveBalances = $employeeDetail['leave_balances'];
                                        $firstRow = true;
                                    ?>
                                    
                                    @foreach($leaveBalances as $leaveBalance)
                                        <tr>
                                            @if($firstRow)
                                                <td rowspan="<?php echo count($leaveBalances); ?>" class="employee-name">
                                                    <?php echo $employee->name; ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo $employee->email; ?></small>
                                                </td>
                                                <?php $firstRow = false; ?>
                                            @endif
                                            
                                            <td class="leave-type-title">
                                                <?php echo $leaveBalance['leave_type']->title; ?>
                                                @if($leaveBalance['is_unlimited'])
                                                    <span class="badge bg-info leave-badge">{{ __('Unlimited') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($leaveBalance['is_unlimited'])
                                                    <span class="text-muted">{{ __('N/A') }}</span>
                                                @else
                                                    <?php echo $leaveBalance['allocated_days']; ?>
                                                @endif
                                            </td>
                                            <td>
                                                @if($leaveBalance['carried_forward_days'] > 0)
                                                    <span class="text-success">+<?php echo $leaveBalance['carried_forward_days']; ?></span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td><?php echo $leaveBalance['used_days']; ?></td>
                                            <td>
                                                @if($leaveBalance['is_unlimited'])
                                                    <span class="remaining-days unlimited">{{ __('Unlimited') }}</span>
                                                @else
                                                    <span class="remaining-days <?php echo $leaveBalance['remaining_days'] > 0 ? 'positive' : 'zero'; ?>">
                                                        <?php echo $leaveBalance['remaining_days']; ?>
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($contractConfirmEmployees->count() == 0 && $contractNotConfirmEmployees->count() == 0 && $payrollEmployees->count() == 0)
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                <h5 class="text-gray-500">{{ __('No employees found') }}</h5>
                <p class="text-muted">{{ __('There are no employees in the system yet.') }}</p>
            </div>
        </div>
    @endif

@endsection
