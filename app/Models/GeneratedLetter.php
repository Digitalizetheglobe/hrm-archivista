<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'letter_template_id',
        'created_by',
        'recipient_name',
        'recipient_email',
        'recipient_department',
        'letter_date',
        'variables_data',
        'file_path',
        'file_name',
    ];

    protected $casts = [
        'letter_date' => 'date',
        'variables_data' => 'array',
    ];

    public function letterTemplate()
    {
        return $this->belongsTo(LetterTemplate::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
