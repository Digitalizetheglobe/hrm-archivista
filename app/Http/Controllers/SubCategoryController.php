<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use App\Models\Category;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function index()
    {
        $subcategories = SubCategory::with('category')->get();
        return view('subcategory.index', compact('subcategories'));
    }

    public function create()
    {
        $categories = \App\Models\Category::pluck('name', 'id'); // key => value
        return view('subcategory.create', compact('categories'));
    }



    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
        ]);

        SubCategory::create($request->all());
        return redirect()->route('subcategories.index')->with('success', 'Sub-category created successfully.');
    }

    public function edit(SubCategory $subcategory)
{
    $categories = Category::all();
    return view('subcategory.edit', compact('subcategory', 'categories'));
}

    public function update(Request $request, SubCategory $subcategory)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
        ]);

        $subcategory->update($request->all());
        return redirect()->route('subcategories.index')->with('success', 'Sub-category updated successfully.');
    }

    public function destroy(SubCategory $subcategory)
    {
        $subcategory->delete();
        return redirect()->route('subcategories.index')->with('success', 'Sub-category deleted successfully.');
    }
}
