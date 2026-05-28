<?php

namespace App\Exports;

use App\Models\AttendanceEmployee;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $request = (object) $this->request;
        
        if (\Auth::user()->type == 'employee') {
            $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
            $attendanceEmployee = AttendanceEmployee::where('employee_id', $emp);
            
            if (isset($request->type) && $request->type == 'monthly' && !empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
                $attendanceEmployee->whereBetween('date', [$start_date, $end_date]);
            } elseif (isset($request->type) && $request->type == 'daily' && !empty($request->date)) {
                $attendanceEmployee->where('date', $request->date);
            } else {
                $month      = date('m');
                $year       = date('Y');
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
                $attendanceEmployee->whereBetween('date', [$start_date, $end_date]);
            }
            return $attendanceEmployee->orderBy('id', 'desc')->get();
        } else {
            $employee = Employee::select('id')->where('created_by', \Auth::user()->creatorId());
            
            if (!empty($request->branch)) {
                $employee->where('branch_id', $request->branch);
            }
            if (!empty($request->department)) {
                $employee->where('department_id', $request->department);
            }
            if (!empty($request->employee)) {
                $employee->where('id', $request->employee);
            }
            
            $employeeIds = $employee->get()->pluck('id');
            $attendanceEmployee = AttendanceEmployee::whereIn('employee_id', $employeeIds);
            
            if (isset($request->type) && $request->type == 'monthly' && !empty($request->month)) {
                $month = date('m', strtotime($request->month));
                $year  = date('Y', strtotime($request->month));
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
                $attendanceEmployee->whereBetween('date', [$start_date, $end_date]);
            } elseif (isset($request->type) && $request->type == 'daily' && !empty($request->date)) {
                $attendanceEmployee->where('date', $request->date);
            } else {
                $month      = date('m');
                $year       = date('Y');
                $start_date = date($year . '-' . $month . '-01');
                $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
                $attendanceEmployee->whereBetween('date', [$start_date, $end_date]);
            }
            return $attendanceEmployee->orderBy('id', 'desc')->get();
        }
    }

    public function map($attendance): array
    {
        return [
            !empty($attendance->employee) ? $attendance->employee->name : '',
            \Auth::user()->dateFormat($attendance->date),
            $attendance->status,
            $attendance->clock_in != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00',
            $attendance->clock_out != '00:00:00' ? \Auth::user()->timeFormat($attendance->clock_out) : '00:00',
            !empty($attendance->clock_in_location) ? $attendance->clock_in_location : '-',
            !empty($attendance->clock_out_location) ? $attendance->clock_out_location : '-',
            $attendance->late,
            $attendance->early_leaving,
            $attendance->overtime
        ];
    }

    public function headings(): array
    {
        return [
            "Employee",
            "Date",
            "Status",
            "Clock In",
            "Clock Out",
            "Clock In Location",
            "Clock Out Location",
            "Late",
            "Early Leaving",
            "Overtime"
        ];
    }
}
