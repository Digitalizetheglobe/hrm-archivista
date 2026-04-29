<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'name',
        'address',
        'contact_person',
        'contact_person_phone',
        'email',
        'company_website',
        'experience',
        'plan_location',
        'project_range',
        'type_of_work',
        'accurate_accessible_area',
        'turnover',
    ];

    protected $casts = [
        'turnover' => 'array', // Automatically converts JSON to array and back
    ];
}
