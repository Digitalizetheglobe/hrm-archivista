<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Languages;

class GenerateConfirmationLetter extends Model
{
    protected $table = 'generate_confirmation_letters';
    protected $fillable = [
        'id',
        'lang',
        'content',
        'created_by',
    ];

    public static function replaceVariable($content, $obj)
    {
        $arrVariable = [
            '{applicant_name}',
            '{prefix}',
            '{offer_date}',
            '{app_name}',
            '{job_title}',
            '{department}',
            '{job_type}',
            '{start_date}',
            '{workplace_location}',
            '{days_of_week}',
            '{salary}',
            '{salary_type}',
            '{salary_duration}',
            '{next_pay_period}',
            '{offer_expiration_date}',
            '{download_date}',
            '{designation}',
            '{confirmation_date}',
        ];
        $arrValue = [
            'applicant_name' => '-',
            'prefix' => '-',
            'offer_date' => '-',
            'app_name' => '-',
            'job_title' => '-',
            'department' => '-',
            'job_type' => '-',
            'start_date' => '-',
            'workplace_location' => '-',
            'days_of_week' => '-',
            'salary' => '-',
            'salary_type' => '-',
            'salary_duration' => '-',
            'next_pay_period' => '-',
            'offer_expiration_date' => '-',
            'download_date' => '-',
            'designation' => '-',
            'confirmation_date' => '-',
        ];

        foreach($obj as $key => $val)
        {
            $arrValue[$key] = $val;
        }
       
        $arrValue['app_name']     = env('APP_NAME');
       
       
        return str_replace($arrVariable, array_values($arrValue), $content);
    }

    public static function defaultConfirmationLetterRegister($user_id)
    {
        foreach (Languages::all() as $lang) {
            $content = '';
            if ($lang->code == 'en') {
                $content = '<div style="text-align: right;">Date: {download_date}</div>

<div style="margin-top: 20px;">
    To,<br>
    {prefix}. {applicant_name}<br>
    {designation}<br>
    Archivista Engineering Projects Pvt Ltd
</div>

<div style="font-weight: bold; margin-top: 30px;">
    Subject: Confirmation of Employment
</div>

<div style="margin-top: 20px;">
    Dear {prefix}. {applicant_name},
</div>

<div style="margin-top: 20px; text-align: justify;">
    We are happy and satisfied with your performance, since joining with us as part of our {department} Department.
</div>

<div style="margin-top: 20px; text-align: justify;">
    We are pleased to confirm your services in our growing family of Archivista Engineering Projects Pvt. Ltd., w.e.f <strong>{confirmation_date}</strong>.
</div>

<div style="margin-top: 20px; text-align: justify;">
    As a confirmed employee, you are entitled for leaves and Official holidays as per policy. All the other terms and conditions remain unaltered.
</div>

<div style="margin-top: 20px;">
    We look forward to your continued dedicated performance.
</div>

<div style="margin-top: 20px;">
    Kindly acknowledge a receipt of this letter by signing as a token of your acceptance and return one copy of the same to Human Resources dept.
</div>

<div style="margin-top: 40px;">
    <div style="font-weight: bold;">For Archivista Engineering Projects Pvt. Ltd.</div>
    <div style="margin-top: 50px; font-weight: bold;">Sanjay Rode</div>
    <div style="font-weight: bold;">Director</div>
</div>';
            }
            
            if ($content != '') {
                GenerateConfirmationLetter::create([
                    'lang' => $lang->code,
                    'content' => $content,
                    'created_by' => $user_id,
                ]);
            }
        }
    }
}
