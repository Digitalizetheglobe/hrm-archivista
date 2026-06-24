<?php

namespace App\Exports;

use App\Models\AttendanceEmployee;
use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $request = (object) $this->request;
        
        $month = date('m');
        $year  = date('Y');
        
        if (isset($request->type) && $request->type == 'monthly' && !empty($request->month)) {
            $month = date('m', strtotime($request->month));
            $year  = date('Y', strtotime($request->month));
            $start_date = date($year . '-' . $month . '-01');
            $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
        } elseif (isset($request->type) && $request->type == 'daily' && !empty($request->date)) {
            $start_date = $request->date;
            $end_date = $request->date;
        } else {
            $start_date = date($year . '-' . $month . '-01');
            $end_date = date('Y-m-t', strtotime('01-' . $month . '-' . $year));
        }

        $period = new \DatePeriod(
            new \DateTime($start_date),
            new \DateInterval('P1D'),
            (new \DateTime($end_date))->modify('+1 day')
        );

        $days = [];
        foreach ($period as $dt) {
            $days[] = [
                'date' => $dt->format('Y-m-d'),
                'day' => $dt->format('d'),
                'day_name' => $dt->format('D'),
            ];
        }

        $employeeQuery = Employee::query();
        if (\Auth::user()->type == 'employee') {
            $emp = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
            $employeeQuery->where('id', $emp);
        } else {
            $employeeQuery->where('created_by', \Auth::user()->creatorId());
            if (!empty($request->branch)) {
                $employeeQuery->where('branch_id', $request->branch);
            }
            if (!empty($request->department)) {
                $employeeQuery->where('department_id', $request->department);
            }
            if (!empty($request->employee)) {
                $employeeQuery->where('id', $request->employee);
            }
        }
        
        $employees = $employeeQuery->get();
        $employeeIds = $employees->pluck('id');
        
        $attendances = AttendanceEmployee::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$start_date, $end_date])
            ->get()
            ->groupBy('employee_id');

        $reportData = [];

        foreach ($employees as $employee) {
            $employeeAttendances = $attendances->get($employee->id, collect());
            
            $summary = [
                'monthly_days' => count($days),
                'present' => 0,
                'early_leaving' => 0,
                'half_day' => 0,
                'lwp' => 0,
                'week_off' => 0,
                'leave' => 0,
                'payable_days' => 0,
            ];

            $dailyData = [];
            foreach ($days as $dayInfo) {
                $date = $dayInfo['date'];
                $att = $employeeAttendances->where('date', $date)->first();
                $isSunday = $dayInfo['day_name'] === 'Sun';

                $status = 'A';
                $inTime = '00:00';
                $outTime = '00:00';
                $totalTime = '00:00';
                
                if ($att) {
                    $inTime = substr($att->clock_in, 0, 5);
                    $outTime = substr($att->clock_out, 0, 5);
                    
                    if ($att->clock_in != '00:00:00' && $att->clock_out != '00:00:00') {
                        $diff = strtotime($att->clock_out) - strtotime($att->clock_in);
                        $totalTime = sprintf('%02d:%02d', floor($diff / 3600), floor(($diff % 3600) / 60));
                    } elseif ($att->clock_in != '00:00:00' && $att->clock_out == '00:00:00') {
                        $totalTime = '00:00';
                    }

                    $statusStr = [];
                    if ($att->status == 'Present') {
                        $statusStr[] = 'P';
                        $summary['present']++;
                    } elseif ($att->status == 'Half Day') {
                        $statusStr[] = 'HD';
                        $summary['half_day']++;
                    } elseif ($att->status == 'Leave') {
                        $statusStr[] = 'Leave';
                        $summary['leave']++;
                    } else {
                        $statusStr[] = 'P';
                        $summary['present']++;
                    }

                    $tags = [];
                    if ($att->late != '00:00:00') {
                        $tags[] = 'L';
                    }
                    if ($att->early_leaving != '00:00:00') {
                        $tags[] = 'EL';
                        $summary['early_leaving']++;
                    }
                    if ($att->overtime != '00:00:00') {
                        $tags[] = 'OT';
                    }
                    
                    if (empty($tags) && $att->status == 'Present') {
                        $tags[] = 'NL';
                    }

                    if (!empty($tags)) {
                        $status = implode(' ', $statusStr) . ' (' . implode(', ', $tags) . ')';
                    } else {
                        $status = implode(' ', $statusStr);
                    }
                    
                } else {
                    if ($isSunday) {
                        $status = 'WO';
                        $summary['week_off']++;
                    } else {
                        // For the sake of matching the example, we consider absent as A
                        $status = 'A';
                        $summary['lwp']++;
                    }
                }

                $dailyData[$date] = [
                    'status' => $status,
                    'inTime' => $inTime,
                    'outTime' => $outTime,
                    'totalTime' => $totalTime,
                ];
            }

            $summary['payable_days'] = $summary['present'] + ($summary['half_day'] * 0.5) + $summary['week_off'] + $summary['leave'];

            $reportData[] = [
                'employee' => $employee,
                'summary' => $summary,
                'dailyData' => $dailyData,
            ];
        }

        return view('attendance.export', [
            'start_date' => date('M d Y', strtotime($start_date)),
            'end_date' => date('M d Y', strtotime($end_date)),
            'days' => $days,
            'reportData' => $reportData,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ],
        ]);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
