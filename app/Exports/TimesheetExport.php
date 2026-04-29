<?php

namespace App\Exports;

use App\Models\TimeSheet;
use App\Models\Employee;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Auth;

class TimeSheetExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $timesheets = TimeSheet::with(['employee', 'client', 'project']);

        // Apply same filters as in controller
        if (Auth::user()->type == 'employee') {
            $employeeId = Auth::user()->id;
            $timesheets->where('employee_id', $employeeId);
            
            if ($this->request && ($this->request->filled('start_date') || $this->request->filled('end_date'))) {
                $startDate = $this->request->start_date ?? now()->format('Y-m-d');
                $endDate = $this->request->end_date ?? now()->format('Y-m-d');
                $timesheets->whereBetween('date', [$startDate, $endDate]);
            }
        } else {
            // Admin filters
            if ($this->request && $this->request->filled('employee')) {
                $timesheets->where('employee_id', $this->request->employee);
            }

            if ($this->request && $this->request->filled('client')) {
                $timesheets->where('client_id', $this->request->client);
            }

            if ($this->request && $this->request->filled('project')) {
                $timesheets->where('project_id', $this->request->project);
            }

            if ($this->request && ($this->request->filled('start_date') || $this->request->filled('end_date'))) {
                $startDate = $this->request->start_date ?? now()->format('Y-m-d');
                $endDate = $this->request->end_date ?? now()->format('Y-m-d');
                $timesheets->whereBetween('date', [$startDate, $endDate]);
            }
        }

        $timesheets = $timesheets->orderBy('date', 'desc')->get();

        $exportData = [];
        
        foreach ($timesheets as $timesheet) {
            $row = [];
            
            if (Auth::user()->type != 'employee') {
                $row[] = !empty($timesheet->employee) ? $timesheet->employee->name : '';
            }
            
            $row[] = Auth::user()->dateFormat($timesheet->date);
            $row[] = $timesheet->total_time . ' hrs';
            
            if (Auth::user()->type != 'employee') {
                $row[] = $timesheet->client->client_name ?? '';
                $row[] = $timesheet->project->project_name ?? '';
                $row[] = $timesheet->expense;
                $row[] = $timesheet->location;
                $row[] = $timesheet->narration;
                $row[] = $timesheet->billable;
            } else {
                $row[] = $timesheet->client->client_name ?? '';
                $row[] = $timesheet->project->project_name ?? '';
                $row[] = $timesheet->billable;
            }
            
            $exportData[] = $row;
        }

        return collect($exportData);
    }

    public function headings(): array
    {
        $headings = [];
        
        if (Auth::user()->type != 'employee') {
            $headings[] = "Employee";
        }
        
        $headings[] = "Date";
        $headings[] = "Total Time";
        
        if (Auth::user()->type != 'employee') {
            $headings[] = "Client";
            $headings[] = "Project";
            $headings[] = "Expense";
            $headings[] = "Location";
            $headings[] = "Narration";
            $headings[] = "Billable";
        } else {
            $headings[] = "Client Name";
            $headings[] = "Project Name";
            $headings[] = "Billable";
        }
        
        return $headings;
    }
}
