<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateVariable extends Model
{
    protected $fillable = [
        'template_id',
        'variable_name',
        'field_label',
        'field_type',
        'validation_rules',
        'field_options',
        'default_value',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'field_options' => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * Get the template for the variable.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(LetterTemplate::class, 'template_id');
    }

    /**
     * Get field type options.
     */
    public static function getFieldTypes()
    {
        return [
            'text' => 'Text',
            'textarea' => 'Textarea',
            'date' => 'Date',
            'number' => 'Number',
            'email' => 'Email',
            'select' => 'Dropdown',
            'radio' => 'Radio Button',
            'checkbox' => 'Checkbox',
        ];
    }

    /**
     * Get validation rules for field.
     */
    public function getValidationRules()
    {
        $rules = [];
        
        if ($this->is_required) {
            $rules[] = 'required';
        }
        
        if (!empty($this->validation_rules)) {
            $rules = array_merge($rules, $this->validation_rules);
        }
        
        switch ($this->field_type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'date':
                $rules[] = 'date';
                break;
        }
        
        return implode('|', $rules);
    }
}
