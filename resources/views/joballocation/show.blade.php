@extends('layouts.admin')
@section('page-title')
    {{ __('Job Allocation Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('joballocation.index') }}">{{ __('Job Allocations') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Allocation Details') }}</li>
@endsection

@section('content')<br>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Job Allocation </h4>
                </div>
                
                <div class="card-body">
                    <!-- Basic Information Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">Basic Information : </h5><br>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                        <tr>
                                            <th width="40%">Status</th>
                                            <td>
                                                @if($jobAllocation->status == 'Ongoing')
                                                    <span class="">{{ $jobAllocation->status }}</span>
                                                @elseif($jobAllocation->status == 'Completed')
                                                    <span class=">{{ $jobAllocation->status }}</span>
                                                @else
                                                    <span class="">{{ $jobAllocation->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Client</th>
                                            <td>{{ $jobAllocation->client->client_name ?? 'N/A' }}</td>
                                            </tr>
                                        <tr>
                                            <th>Project</th>
                                            <td>{{ $jobAllocation->project->project_name ?? 'N/A' }}</td>
                                            </tr>
                                        <tr>
                                            <th>Job Allocate Date</th>
                                            <td>{{ $jobAllocation->created_at ? $jobAllocation->created_at->format('Y-m-d') : 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">Timing & Budget : </h5><br>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                        <tr>
                                            <th width="40%">Start Date</th>
                                            <td>{{ \Carbon\Carbon::parse($jobAllocation->start_date)->format('d M Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>End Date</th>
                                            <td>{{ $jobAllocation->end_date ? \Carbon\Carbon::parse($jobAllocation->end_date)->format('d M Y') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Billable</th>
                                            <td>
                                                @if($jobAllocation->billable)
                                                    <span>Billable</span>
                                                @else
                                                    <span >Non-Billable</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Budgeting</th>
                                            <td>
                                                @if($jobAllocation->budgeting)  
                                                    <span >Employees</span>
                                                @else
                                                    <span >Projects</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div><br>
                    
                    <!-- Departments & Employees Section -->
                    <div class="row mb-12   ">
                       
                        
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Assigned Employees : </h5><br>
                            @php
                                $employeeIds = json_decode($jobAllocation->employees_id, true) ?? [];
                                $employees = \App\Models\Employee::whereIn('id', $employeeIds)->with('department')->get();
                            @endphp
                            
                            @if($employees->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Employee</th>
                                                <th>Department</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($employees as $employee)
                                                <tr>
                                                    <td>{{ $employee->name }}</td>
                                                    <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-warning">No employees assigned</div>
                            @endif
                        </div>
                    </div><br>
                    
                    
                    <!-- Narration Section -->
                    <div class="row">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2">Narration : </h5>
                            <div class="bg-light p-3 rounded">
                                @if($jobAllocation->narration)
                                    {!! nl2br(e($jobAllocation->narration)) !!}
                                @else
                                    <div class="text-muted">No narration provided</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer text-end">
                    <a href="{{ route('joballocation.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    @if(auth()->user()->can('edit-joballocation'))
                        <a href="{{ route('joballocation.edit', $jobAllocation->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-header {
        padding: 0.75rem 1.25rem;
    }
    th {
        font-weight: 500;
    }
    .table-sm td, .table-sm th {
        padding: 0.5rem;
    }
    .list-group-item {
        padding: 0.5rem 1rem;
    }
</style>
@endpush