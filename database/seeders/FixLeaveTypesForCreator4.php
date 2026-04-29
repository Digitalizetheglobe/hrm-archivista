<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class FixLeaveTypesForCreator4 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create leave types for creator ID 4 (existing employees)
        $leaveTypes = [
            [
                'title' => 'Test Monthly Leave for Employees',
                'days' => 1.25,
                'type' => 'monthly',
                'is_unlimited' => false,
                'carry_forward_enabled' => true,
                'max_carry_forward_days' => 5,
                'eligible_employee_types' => ['payroll_confirm'],
            ],
            [
                'title' => 'Test Yearly Leave for Employees',
                'days' => 12,
                'type' => 'yearly',
                'is_unlimited' => false,
                'carry_forward_enabled' => true,
                'max_carry_forward_days' => 10,
                'eligible_employee_types' => ['contract_confirm', 'contract_not_confirm'],
            ],
            [
                'title' => 'Casual Leave for Employees',
                'days' => 2.5,
                'type' => 'monthly',
                'is_unlimited' => false,
                'carry_forward_enabled' => true,
                'max_carry_forward_days' => 3,
                'eligible_employee_types' => ['contract_confirm', 'contract_not_confirm'],
            ],
        ];

        foreach ($leaveTypes as $leaveTypeData) {
            LeaveType::firstOrCreate(
                ['title' => $leaveTypeData['title'], 'created_by' => 4],
                $leaveTypeData
            );
            
            $this->command->info("✅ Created leave type: {$leaveTypeData['title']}");
        }
        
        $this->command->info('✅ Leave types created for creator ID 4');
    }
}
