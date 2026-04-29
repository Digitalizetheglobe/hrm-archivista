<?php

namespace Modules\LandingPage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\LandingPage\Entities\LandingPageSetting;
use App\Models\Category;
use App\Models\TypeOfWork;
use App\Models\SubCategory;
use App\Models\VendorDetail; // Add this with other use statements
use Illuminate\Support\Facades\Storage;
use App\Models\Vendor;



class LandingPageController extends Controller
{
    
    public function home()
    {
        // Directly return the pricing view without authentication check
        return view('landingpage::layouts.home'); // Ensure this view exists
    }


    public function pricing()
    {
        // Directly return the pricing view without authentication check
        return view('landingpage::layouts.pricing'); // Ensure this view exists
    }

    public function contact()
    {
        // Directly return the pricing view without authentication check
        return view('landingpage::layouts.contact'); // Ensure this view exists
    }

    public function vendor()
    {
        $categories = Category::all(); // Fetch all categories
        return view('landingpage::layouts.vendor', compact('categories'));
    }

    public function vendor2()
    {
        $typeofworks = typeofWork::all(); // Fetch all categories
        return view('landingpage::layouts.vendor2', compact('typeofworks'));
    }


    public function getSubcategories($category_id)
    {
        $subcategories = SubCategory::where('category_id', $category_id)->pluck('name', 'id');
        return response()->json($subcategories);
    }

    


    public function blog()
    {
        // Directly return the pricing view without authentication check
        return view('landingpage::layouts.blog'); // Ensure this view exists
    }

    public function privacy()
    {
        // Directly return the pricing view without authentication check
        return view('landingpage::layouts.privacy'); // Ensure this view exists
    }
    
    
    public function blog1()
    {
        // Directly return the pricing view without authentication check
        return view('landingpage::layouts.blog1'); // Ensure this view exists
    }

    public function blog2()
    {
        // Directly return the pricing view without authentication check
        return view('landingpage::layouts.blog2'); // Ensure this view exists
    }

    public function blog3()
    {
        // Directly return the pricing view without authentication check
        return view('landingpage::layouts.blog3'); // Ensure this view exists
    }

    public function blog4()
    {
        // Directly return the pricing view without authentication check
        return view('landingpage::layouts.blog4'); // Ensure this view exists
    }


    public function vendor2store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string',
        'address' => 'required|string',
        'contact_person' => 'required|string',
        'contact_person_phone' => 'required|string',
        'email' => 'required|email',
        'company_website' => 'nullable|url',
        'experience' => 'nullable|string',
        'plan_location' => 'nullable|string',
        'project_range' => 'nullable|string',
        'type_of_work' => 'nullable|string',
        'accurate_accessible_area' => 'nullable|string',
        'turnover' => 'required|array|size:3',
        'turnover.*' => 'required|numeric',
    ]);

    Vendor::create([
        'name' => $request->name,
        'address' => $request->address,
        'contact_person' => $request->contact_person,
        'contact_person_phone' => $request->contact_person_phone,
        'email' => $request->email,
        'company_website' => $request->company_website,
        'experience' => $request->experience,
        'plan_location' => $request->plan_location,
        'project_range' => $request->project_range,
        'type_of_work' => $request->type_of_work,
        'accurate_accessible_area' => $request->accurate_accessible_area,
        'turnover' => $request->turnover,
    ]);

    return redirect()->back()->with('success', 'Vendor created successfully!');
}


    public function vendorStore(Request $request)
{


    $validatedData = $request->validate([
        // Contact Details
        'name' => 'required|string|max:255',
        'address' => 'required|string',
        'contact_person' => 'required|string|max:255',
        'contact_person_phone' => 'required|string|max:20',
        'email' => 'required|email|max:255',
        'company_website' => 'nullable|url',
        'experience' => 'nullable|string',
        'plan_location' => 'nullable|string',
        
        // Product Details
        'category_id' => 'required|exists:categories,id',
        'sub_category_id' => 'required|exists:sub_categories,id',
        'product' => 'required|string|max:255',
        'product_image' => 'nullable',
        'area_of_application' => 'nullable|string',
        'bag_description' => 'nullable|string',
        'rate_in_pure' => 'required|numeric',
        'for_supply_rate' => 'required|numeric',
        'for_apply_rate' => 'required|numeric',
    ]);

    $validatedData['contact_date'] = now()->toDateString();


    // Handle file upload
    if ($request->hasFile('product_image')) {
        $imagePath = $request->file('product_image')->store('product_images', 'public');
        $validatedData['product_image'] = $imagePath;
    }

    try {
            $data = $request->except('_token');
            
            if ($request->hasFile('product_image')) {
                $data['product_image'] = $request->file('product_image')->store('product_images', 'public');
            }
            
            VendorDetail::create($data);
            
            return redirect()->back()->with('success', 'Vendor details submitted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: '.$e->getMessage());
        }

}
    
    

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        // if (\Auth::user()->type == 'super admin') {
        //     return view('landingpage::landingpage.topbar');
        // } else {
        //     return redirect()->back()->with('error', 'Permission Denied');
        // }
        return view('layouts.landingpage');
        
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('landingpage::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

        $data = [
            "topbar_status" => $request->topbar_status ? $request->topbar_status : "off",
            "topbar_notification_msg" =>  $request->topbar_notification_msg,
        ];

        foreach($data as $key => $value){

            LandingPageSetting::updateOrCreate(['name' =>  $key],['value' => $value]);
        }

        return redirect()->back()->with(['success'=> 'Setting updated successfully']);

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('landingpage::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('landingpage::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    
}
