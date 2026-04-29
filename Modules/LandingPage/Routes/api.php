<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Category; // Assuming you have a Category model
use App\Models\SubCategory; // Assuming you have a SubCategory model

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/landingpage', function (Request $request) {
    return $request->user();
});

// Categories API Endpoint
Route::get('/categories', function () {
    try {
        $categories = Category::select('id', 'name')
                            ->where('status', 1) // Only active categories if you have status field
                            ->orderBy('name', 'asc')
                            ->get();
        
        return response()->json($categories);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to fetch categories',
            'message' => $e->getMessage()
        ], 500);
    }
});

// Subcategories API Endpoint
Route::get('/subcategories', function (Request $request) {
    try {
        $request->validate([
            'category_id' => 'required|integer|exists:categories,id'
        ]);
        
        $subCategories = SubCategory::select('id', 'name', 'category_id')
                                  ->where('category_id', $request->category_id)
                                  ->where('status', 1) // Only active subcategories if you have status field
                                  ->orderBy('name', 'asc')
                                  ->get();
        
        return response()->json($subCategories);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to fetch subcategories',
            'message' => $e->getMessage()
        ], 500);
    }
});