<?php

use Illuminate\Database\Seeder;
use App\Models\Allowance;

class FixAllowanceMonthsSeeder extends Seeder
{
    public function run()
    {
        // Update existing allowance records that don't have month set
        Allowance::whereNull('month')->update([
            'month' => date('Y-m') // Set to current month
        ]);
        
        $this->command->info('Fixed allowance records with missing month values.');
    }
}
