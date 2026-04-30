<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceApiController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Attendance API Routes for Mobile App
Route::post('/attendance/punch-in', [AttendanceApiController::class, 'punchIn']);
Route::post('/attendance/punch-out', [AttendanceApiController::class, 'punchOut']);
Route::get('/attendance/today', [AttendanceApiController::class, 'getTodayAttendance']);
