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
            ->where('status', '!=', 'Single Punch In')
            ->get();
            
        $updatedCount = 0;
        
        foreach ($missedPunchOuts as $attendance) {
            $attendance->status = 'Single Punch In';
            $attendance->save();
            $updatedCount++;
            
            $this->info("Updated attendance ID {$attendance->id} for employee ID {$attendance->employee_id} to Single Punch In");
        }
        
        if ($updatedCount > 0) {
            $this->info("Successfully updated {$updatedCount} attendance records to Single Punch In status.");
        } else {
            $this->info('No missed punch-outs found to update.');
        }
        
        return 0;
    }
}
