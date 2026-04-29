<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaveCarryForwardTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test user if not exists
        $user = User::firstOrCreate([
            'email' => 'test@example.com'
        ], [
            'name' => 'Test User',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'created_by' => 1,
        ]);

        // Create test employees
        $payrollConfirmEmployee = Employee::firstOrCreate([
            'email' => 'payroll_confirm@example.com'
        ], [
            'name' => 'Payroll Confirmed Employee',
            'user_id' => 2,
            'employee_type' => 'Payroll',
            'confirm_of_employment' => true,
            'created_by' => 1,
        ]);

        $contractConfirmEmployee = Employee::firstOrCreate([
            'email' => 'contract_confirm@example.com'
        ], [
            'name' => 'Contract Confirmed Employee',
            'user_id' => 3,
            'employee_type' => 'Contract',
            'confirm_of_employment' => true,
            'created_by' => 1,
        ]);

        $contractNotConfirmEmployee = Employee::firstOrCreate([
            'email' => 'contract_not_confirm@example.com'
        ], [
            'name' => 'Contract Not Confirmed Employee',
            'user_id' => 4,
            'employee_type' => 'Contract',
            'confirm_of_employment' => false,
            'created_by' => 1,
        ]);

        // Create test leave types
        $monthlyLeaveType = LeaveType::firstOrCreate([
            'title' => 'Test Monthly Leave',
            'created_by' => 1,
        ], [
            'days' => 1.25,
            'type' => 'monthly',
            'is_unlimited' => false,
            'carry_forward_enabled' => true,
            'max_carry_forward_days' => 5,
            'eligible_employee_types' => ['payroll_confirm'],
        ]);

        $yearlyLeaveType = LeaveType::firstOrCreate([
            'title' => 'Test Yearly Leave',
            'created_by' => 1,
        ], [
            'days' => 12,
            'type' => 'yearly',
            'is_unlimited' => false,
            'carry_forward_enabled' => true,
            'max_carry_forward_days' => 10,
            'eligible_employee_types' => ['contract_confirm', 'contract_not_confirm'],
        ]);

        $casualLeaveType = LeaveType::firstOrCreate([
            'title' => 'Casual Leave',
            'created_by' => 1,
        ], [
            'days' => 2.5,
            'type' => 'monthly',
            'is_unlimited' => false,
            'carry_forward_enabled' => true,
            'max_carry_forward_days' => 3,
            'eligible_employee_types' => ['contract_confirm', 'contract_not_confirm'],
        ]);

        $this->command->info('✅ Test data created successfully!');
        $this->command->info('📊 Created:');
        $this->command->info("   - Payroll Confirmed Employee: {$payrollConfirmEmployee->name}");
        $this->command->info("   - Contract Confirmed Employee: {$contractConfirmEmployee->name}");
        $this->command->info("   - Contract Not Confirmed Employee: {$contractNotConfirmEmployee->name}");
        $this->command->info("   - Monthly Leave Type: {$monthlyLeaveType->title} (1.25 days/month)");
        $this->command->info("   - Yearly Leave Type: {$yearlyLeaveType->title} (12 days/year)");
        $this->command->info("   - Casual Leave Type: {$casualLeaveType->title} (2.5 days/month for contracts)");
    }
}
