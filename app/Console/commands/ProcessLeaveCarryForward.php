<?php

namespace App\Console\Commands;

use App\Services\LeaveAllocationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessLeaveCarryForward extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:process-carry-forward 
                            {--date= : Process for specific date (Y-m-d format, defaults to today)}
                            {--force : Force processing even if already processed for the period}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automatic leave carry-forward and allocation for eligible employees';

    /**
     * The leave allocation service instance.
     *
     * @var LeaveAllocationService
     */
    protected $leaveAllocationService;

    /**
     * Create a new command instance.
     *
     * @param LeaveAllocationService $leaveAllocationService
     */
    public function __construct(LeaveAllocationService $leaveAllocationService)
    {
        parent::__construct();
        $this->leaveAllocationService = $leaveAllocationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $processDate = $this->option('date') ? date('Y-m-d', strtotime($this->option('date'))) : date('Y-m-d');
        $force = $this->option('force');

        $this->info("Starting leave carry-forward processing for date: {$processDate}");
        
        try {
            $results = $this->leaveAllocationService->processCarryForwardAndAllocation($processDate, $force);
            
            $this->info("✅ Processing completed successfully!");
            $this->info("📊 Results:");
            $this->info("   - Monthly carry-forwards processed: {$results['monthly_processed']}");
            $this->info("   - Yearly carry-forwards processed: {$results['yearly_processed']}");
            $this->info("   - New allocations created: {$results['allocations_created']}");
            $this->info("   - Employees processed: {$results['employees_processed']}");
            $this->info("   - Errors encountered: {$results['errors']}");
            
            if (!empty($results['error_details'])) {
                $this->warn("⚠️  Error details:");
                foreach ($results['error_details'] as $error) {
                    $this->warn("   - {$error}");
                }
            }
            
            Log::info('Leave carry-forward processing completed', [
                'date' => $processDate,
                'results' => $results
            ]);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Processing failed: " . $e->getMessage());
            Log::error('Leave carry-forward processing failed', [
                'date' => $processDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
}
