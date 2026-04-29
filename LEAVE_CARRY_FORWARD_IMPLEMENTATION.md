# Leave Carry-Forward System Implementation

## Overview

This implementation provides a fully automated leave carry-forward system that handles both monthly and yearly leave allocations based on employee eligibility and leave type configurations.

## Features

### ✅ **Automated Processing**
- Monthly carry-forward for monthly leave types
- Yearly carry-forward for yearly leave types  
- Automatic employee eligibility checking
- Scheduled execution via Laravel scheduler

### ✅ **Employee Eligibility**
- `payroll_confirm`: Payroll employees with confirmed employment
- `payroll_not_confirm`: Payroll employees without confirmed employment
- `contract_confirm`: Contract employees with confirmed employment
- `contract_not_confirm`: Contract employees without confirmed employment

### ✅ **Leave Allocation Logic**
- Standard allocation: Uses `days` field from leave type
- Contract casual leave special case: 2.5 days (confirmed) or 1.5 days (not confirmed)
- Carry-forward limits: Respects `max_carry_forward_days` setting

## Configuration

### Leave Type Setup

1. **Enable Carry-Forward**: Set `carry_forward_enabled = 1`
2. **Set Maximum**: Configure `max_carry_forward_days`
3. **Define Period**: Set `type` to `monthly` or `yearly`
4. **Set Days**: Configure `days` per period
5. **Select Eligibility**: Choose `eligible_employee_types`

### Example Configurations

#### Monthly Leave for Payroll Confirmed Employees
```
title: "Sick Leave"
days: 1.25
type: "monthly"
carry_forward_enabled: 1
max_carry_forward_days: 5
eligible_employee_types: ["payroll_confirm"]
```

#### Yearly Leave for Contract Employees
```
title: "Annual Leave"
days: 12
type: "yearly"
carry_forward_enabled: 1
max_carry_forward_days: 10
eligible_employee_types: ["contract_confirm", "contract_not_confirm"]
```

#### Casual Leave for Contract Employees (Special Allocation)
```
title: "Casual Leave"
days: 2.5
type: "monthly"
carry_forward_enabled: 1
max_carry_forward_days: 3
eligible_employee_types: ["contract_confirm", "contract_not_confirm"]
```

## Usage

### Manual Processing

Run the carry-forward process manually:

```bash
# Process for current date
php artisan leave:process-carry-forward

# Process for specific date
php artisan leave:process-carry-forward --date=2026-04-02

# Force processing (override existing records)
php artisan leave:process-carry-forward --force
```

### Automated Processing

The system is scheduled to run automatically on the 1st day of every month at 1:00 AM:

```php
// In app/Console/Kernel.php
$schedule->command('leave:process-carry-forward')->monthlyOn(1, '01:00');
```

## Database Schema

### carry_forward_balances Table

| Column | Type | Description |
|--------|------|-------------|
| employee_id | foreignId | Employee ID |
| leave_type_id | foreignId | Leave type ID |
| month | string | Period (YYYY-MM for monthly, YYYY for yearly) |
| period_type | string | 'monthly' or 'yearly' |
| carried_forward_days | decimal | Days carried forward from previous period |
| allocated_days | decimal | Days allocated for current period |
| used_days | decimal | Days used in current period |
| remaining_days | decimal | Remaining days for current period |

## Implementation Details

### Service Classes

#### LeaveAllocationService
- Handles all carry-forward and allocation logic
- Calculates employee eligibility
- Processes monthly and yearly periods
- Manages carry-forward balance records

#### ProcessLeaveCarryForward Command
- Console command for manual execution
- Provides detailed processing results
- Handles error reporting and logging

### Key Methods

#### Employee Eligibility Checking
```php
private function getEligibleEmployees(LeaveType $leaveType)
```
- Filters employees based on `eligible_employee_types`
- Handles all four employee type combinations
- Supports backward compatibility

#### Carry-Forward Calculation
```php
private function processMonthlyCarryForward($leaveType, $currentMonth, $force, &$results)
private function processYearlyCarryForward($leaveType, $currentYear, $force, &$results)
```
- Calculates unused days from previous period
- Applies carry-forward limits
- Creates new period records

#### Balance Management
```php
public function getCurrentLeaveBalances($employeeId)
```
- Retrieves current leave balances for dashboard
- Includes carried forward days
- Handles both monthly and yearly leave types

## Frontend Integration

The leave dashboard automatically displays:
- Available days including carried forward
- Carried forward days as separate line item
- Total usage for current period
- Progress bars for limited leave types

## Testing

### Test Data Seeder

Run the test seeder to create sample data:

```bash
php artisan db:seed --class=LeaveCarryForwardTestSeeder
```

This creates:
- Test employees for each employee type
- Sample leave types with carry-forward enabled
- Proper eligibility configurations

### Manual Testing

1. Create leave types with carry-forward enabled
2. Create eligible employees
3. Run the carry-forward command
4. Verify balance calculations in dashboard
5. Check carry_forward_balances table

## Error Handling

The system includes comprehensive error handling:
- Database transaction rollbacks on errors
- Detailed error logging
- Graceful handling of missing data
- Validation of leave type configurations

## Performance Considerations

- Indexed database queries for efficient lookups
- Batch processing of employees
- Optimized balance calculations
- Minimal database operations per employee

## Monitoring

Check logs for processing status:
```bash
tail -f storage/logs/laravel.log | grep "leave carry-forward"
```

## Troubleshooting

### Common Issues

1. **No employees processed**: Check eligible_employee_types configuration
2. **Zero carry-forward**: Verify carry_forward_enabled = 1 and unused days exist
3. **Migration errors**: Ensure carry_forward_balances table exists and has period_type column

### Debug Commands

```bash
# Test with specific date
php artisan leave:process-carry-forward --date=2026-03-01 --force

# Check leave type configurations
php artisan tinker
>>> App\Models\LeaveType::where('carry_forward_enabled', 1)->get();
```
