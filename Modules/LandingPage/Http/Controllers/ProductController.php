<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // Show the form
    public function create()
    {
        $categories = Category::all();
        $subCategories = collect(); // Initialize empty collection
        
        // If there's old input for category_id, load related sub-categories
        if (old('category_id')) {
            $subCategories = SubCategory::where('category_id', old('category_id'))->get();
        }
        
        return view('products.create', [
            'categories' => $categories,
            'subCategories' => $subCategories
        ]);
    }

    // Handle form submission
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Contact Details validation
            'contact_date' => 'required|date',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'contact_person' => 'required|string|max:255',
            'contact_person_phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'experience' => 'nullable|string|max:255',
            'plan_location' => 'nullable|string|max:255',
            
            // Product Details validation
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'product' => 'required|string|max:255',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'area_of_application' => 'nullable|string|max:500',
            'bag_description' => 'nullable|string|max:500',
            'rate_in_pure' => 'required|numeric|min:0',
            'for_supply_rate' => 'required|numeric|min:0',
            'for_apply_rate' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle file upload
        $imagePath = null;
        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('product_images', 'public');
        }

        // Create new product
        $product = new Product();
        
        // Set contact details
        $product->contact_date = $request->contact_date;
        $product->name = $request->name;
        $product->address = $request->address;
        $product->contact_person = $request->contact_person;
        $product->contact_person_phone = $request->contact_person_phone;
        $product->email = $request->email;
        $product->company_website = $request->company_website;
        $product->experience = $request->experience;
        $product->plan_location = $request->plan_location;
        
        // Set product details
        $product->category_id = $request->category_id;
        $product->sub_category_id = $request->sub_category_id;
        $product->product = $request->product;
        $product->product_image = $imagePath;
        $product->area_of_application = $request->area_of_application;
        $product->bag_description = $request->bag_description;
        $product->rate_in_pure = $request->rate_in_pure;
        $product->for_supply_rate = $request->for_supply_rate;
        $product->for_apply_rate = $request->for_apply_rate;
        
        $product->save();

        return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

    // AJAX endpoint for getting sub-categories
    public function getSubCategories($categoryId)
    {
        $subCategories = SubCategory::where('category_id', $categoryId)->pluck('name', 'id');
        return response()->json($subCategories);
    }
}