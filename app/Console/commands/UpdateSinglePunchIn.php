<?php

namespace App\Console\Commands;

use App\Models\AttendanceEmployee;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateSinglePunchIn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:update-single-punch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update missed punch-outs to Single Punch In status after midnight';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for missed punch-outs...');
        
        // Get all attendance records from yesterday that have no punch out
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        
        $missedPunchOuts = AttendanceEmployee::where('date', $yesterday)
            ->where('clock_out', '00:00:00')
            ->get();
            
        $updatedCount = 0;
        
        foreach ($missedPunchOuts as $attendance) {
            // Get settings for the company that created this attendance
            $settings = \App\Models\Utility::fetchSettings($attendance->created_by);
            $companyEndTime = !empty($settings['company_end_time']) ? $settings['company_end_time'] . ':00' : '18:00:00';
            $lateMarkTime = '10:10:00';
            $workingHoursLimit = 8;
            $halfDayHoursLimit = 4;
            
            $clockIn = $attendance->clock_in;
            $clockOut = $companyEndTime;
            $date = $attendance->date;
            
            $status = 'Present';
            $late = '00:00:00';
            $earlyLeaving = '00:00:00';
            $overtime = '00:00:00';
            
            // Re-calculate late time
            if (strtotime($clockIn) > strtotime($date . ' ' . $lateMarkTime)) {
                $totalLateSeconds = strtotime($clockIn) - strtotime($date . ' ' . $lateMarkTime);
                $hours = floor($totalLateSeconds / 3600);
                $mins = floor($totalLateSeconds / 60 % 60);
                $secs = floor($totalLateSeconds % 60);
                $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            }
            
            // Calculate worked hours
            $workedSeconds = strtotime($clockOut) - strtotime($clockIn);
            $workedHours = $workedSeconds / 3600;
            
            if ($workedHours < $halfDayHoursLimit) {
                $status = 'Half Day';
            } elseif ($workedHours < $workingHoursLimit) {
                $status = 'Early Leaving';
            } else {
                $status = 'Present';
            }
            
            // Calculate early leaving (should be 0 since we use company end time, but let's be safe)
            if (strtotime($clockOut) < strtotime($date . ' ' . $companyEndTime)) {
                $totalEarlyLeavingSeconds = strtotime($date . ' ' . $companyEndTime) - strtotime($clockOut);
                $hours = floor($totalEarlyLeavingSeconds / 3600);
                $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } elseif (strtotime($clockOut) > strtotime($date . ' ' . $companyEndTime)) {
                $totalOvertimeSeconds = strtotime($clockOut) - strtotime($date . ' ' . $companyEndTime);
                $hours = floor($totalOvertimeSeconds / 3600);
                $mins = floor($totalOvertimeSeconds / 60 % 60);
                $secs = floor($totalOvertimeSeconds % 60);
                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            }
            
            // Update attendance record
            $attendance->clock_out = $clockOut;
            $attendance->status = $status;
            $attendance->late = $late;
            $attendance->early_leaving = $earlyLeaving;
            $attendance->overtime = $overtime;
            $attendance->save();
            
            $updatedCount++;
            
            $this->info("Updated attendance ID {$attendance->id} for employee ID {$attendance->employee_id}. Clock-out set to {$clockOut}, status: {$status}");
        }
        
        if ($updatedCount > 0) {
            $this->info("Successfully updated {$updatedCount} attendance records.");
        } else {
            $this->info('No missed punch-outs found to update.');
        }
        
        return 0;
    }
}
