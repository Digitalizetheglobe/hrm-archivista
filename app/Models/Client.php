<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    protected $fillable = [
        'clients_group_id',
        'client_code',
        'client_name', 
        'client_address',
        'state',
        'country',
        'gst_no',
        'company_phone',
        'company_email',
        'contact_person_name',
        'contact_person_phone',
        'remark',
        'created_by'
    ];

    // Relationship to Site (Client Group)
    public function site()
    {
        return $this->belongsTo(Site::class, 'clients_group_id');
    }

    // Relationship to creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship to projects if needed
    public function projects()
    {
        return $this->hasMany(Project::class, 'client_id');
    }
}