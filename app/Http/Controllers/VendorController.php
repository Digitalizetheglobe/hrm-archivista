<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorDetail;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function contractorIndex()
    {
        $vendors = Vendor::select('id', 'name', 'company_website', 'project_range', 'type_of_work')->get();
        return view('vendors.contractor', compact('vendors'));
    }
    public function contractorshow($id)
    {
        $vendor = Vendor::findOrFail($id);
        return view('vendors.contractor_show', compact('vendor'));
    }

    public function supplierIndex()
    {
        $vendors = VendorDetail::with(['category', 'subCategory'])->get();
        return view('vendors.supplier', compact('vendors'));
    }
    public function suppliershow($id)
    {
        $vendor = VendorDetail::with(['category', 'subCategory'])->findOrFail($id);
        return view('vendors.supplier_show', compact('vendor'));
    }



}
