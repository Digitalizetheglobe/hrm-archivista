<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fields = ['basic', 'medical', 'hra', 'conveyance', 'education', 'executive'];

    public function up(): void
    {
        if (!Schema::hasTable('payroll_data')) {
            return;
        }

        $columns = [];
        foreach ($this->fields as $field) {
            $columns[] = $field . ' DECIMAL(12,6) NOT NULL DEFAULT 0';
        }
        DB::statement('ALTER TABLE payroll_data MODIFY ' . implode(', MODIFY ', $columns));

        $rows = DB::table('payroll_data')
            ->join('employees', 'employees.id', '=', 'payroll_data.employee_id')
            ->select('payroll_data.*', 'employees.set_salary')
            ->get();

        foreach ($rows as $row) {
            $salary = (float) $row->set_salary;
            $percents = [];
            foreach ($this->fields as $field) {
                $percents[] = (float) $row->{$field};
            }
            if ($salary <= 0 || array_sum($percents) <= 0) {
                continue;
            }

            $amounts = $this->recoverAmounts($salary, $percents);
            if ($amounts === null) {
                continue;
            }

            $update = [];
            foreach ($this->fields as $index => $field) {
                $update[$field] = round($amounts[$index] / $salary * 100, 6);
            }
            DB::table('payroll_data')->where('id', $row->id)->update($update);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('payroll_data')) {
            return;
        }

        $columns = [];
        foreach ($this->fields as $field) {
            $columns[] = $field . ' DECIMAL(5,2) NOT NULL DEFAULT 0';
        }
        DB::statement('ALTER TABLE payroll_data MODIFY ' . implode(', MODIFY ', $columns));
    }

    /**
     * Percentages were stored with two decimals, so salary * percent / 100
     * came back about one rupee high on each component. Pick the amounts
     * inside each rounding range that add up to the set salary, preferring
     * round rupee figures.
     */
    private function recoverAmounts(float $salary, array $percents): ?array
    {
        $candidates = [];
        foreach ($percents as $percent) {
            $low = $salary * ($percent - 0.005) / 100;
            $high = $salary * ($percent + 0.005) / 100;
            $list = [];
            for ($amount = ceil($low * 2) / 2; $amount < $high - 0.0001; $amount += 0.5) {
                $amount = round($amount, 2);
                if (abs(round($amount / $salary * 100, 2) - $percent) < 0.001) {
                    $list[] = $amount;
                }
            }
            if ($list === []) {
                return null;
            }
            $candidates[] = $list;
        }

        $best = null;
        $bestKey = null;
        $this->walk($candidates, $percents, $salary, 0, 0, [], $best, $bestKey);

        return $best;
    }

    private function walk(array $candidates, array $percents, float $salary, int $index, float $sum, array $combo, ?array &$best, ?array &$bestKey): void
    {
        if ($index === count($candidates)) {
            if (abs(round($sum, 2) - $salary) > 0.01) {
                return;
            }

            $by50 = 0;
            $by10 = 0;
            $whole = 0;
            $distance = 0;
            foreach ($combo as $i => $amount) {
                $paisa = (int) round($amount * 100);
                if ($paisa % 5000 === 0) {
                    $by50++;
                }
                if ($paisa % 1000 === 0) {
                    $by10++;
                }
                if ($paisa % 100 === 0) {
                    $whole++;
                }
                $distance += abs($amount - ($salary * $percents[$i] / 100));
            }

            $key = [$by50, $by10, $whole, -$distance];
            if ($bestKey === null || $key > $bestKey) {
                $bestKey = $key;
                $best = $combo;
            }
            return;
        }

        foreach ($candidates[$index] as $amount) {
            $this->walk($candidates, $percents, $salary, $index + 1, $sum + $amount, array_merge($combo, [$amount]), $best, $bestKey);
        }
    }
};
