<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'date',
        'occasion',
        'created_by',
    ];

    /**
     * Holiday dates in a range, keyed by Y-m-d.
     */
    public static function datesBetween($createdBy, $from, $to): array
    {
        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->startOfDay();

        if ($to->lt($from)) {
            return [];
        }

        $rows = self::query()
            ->where('created_by', $createdBy)
            ->whereDate('start_date', '<=', $to->toDateString())
            ->whereDate('end_date', '>=', $from->toDateString())
            ->get(['occasion', 'start_date', 'end_date']);

        $dates = [];
        foreach ($rows as $row) {
            $start = Carbon::parse($row->start_date)->startOfDay();
            $end = Carbon::parse($row->end_date)->startOfDay();
            if ($start->lt($from)) {
                $start = $from->copy();
            }
            if ($end->gt($to)) {
                $end = $to->copy();
            }
            if ($end->lt($start)) {
                continue;
            }
            for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
                $dates[$day->format('Y-m-d')] = $row->occasion;
            }
        }

        return $dates;
    }
}