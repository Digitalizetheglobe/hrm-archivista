<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Schedule the custom command to run daily
        $schedule->command('todos:delete-old')->daily();
        
        // Schedule attendance update to run daily at 12:05 AM (5 minutes after midnight)
        $schedule->command('attendance:update-single-punch')->dailyAt('00:05');

        // Schedule leave carry-forward processing to run monthly on the 1st day at 1:00 AM
        $schedule->command('leave:process-carry-forward')->monthlyOn(1, '01:00');

    }
    

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    
}
