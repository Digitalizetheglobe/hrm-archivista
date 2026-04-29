<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorDetail extends Model
{
    protected $fillable = [
        'contact_date', 'name', 'address', 'contact_person', 'contact_person_phone',
        'email', 'company_website', 'experience', 'plan_location', 'category_id',
        'sub_category_id', 'product', 'product_image', 'area_of_application',
        'bag_description', 'rate_in_pure', 'for_supply_rate', 'for_apply_rate'
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}