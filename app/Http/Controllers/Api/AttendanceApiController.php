<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AttendanceApiController extends Controller
{
    /**
     * Punch In with Location
     */
    public function punchIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location' => 'nullable|string',
            'accuracy' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employeeId = $request->employee_id;
            $date = date('Y-m-d');
            $time = date('H:i:s');

            // Check if already punched in today
            $existingAttendance = AttendanceEmployee::where('employee_id', $employeeId)
                ->where('date', $date)
                ->where('clock_out', '00:00:00')
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already punched in today. Please punch out first.'
                ], 400);
            }

            // Calculate attendance status
            $startTime = Utility::getValByName('company_start_time');
            $lateMarkTime = '10:10:00';
            
            $status = 'Present';
            $late = '00:00:00';
            $isLate = false;

            // Calculate late time
            if (strtotime($time) > strtotime($date . ' ' . $lateMarkTime)) {
                $totalLateSeconds = strtotime($time) - strtotime($date . ' ' . $lateMarkTime);
                $hours = floor($totalLateSeconds / 3600);
                $mins = floor($totalLateSeconds / 60 % 60);
                $secs = floor($totalLateSeconds % 60);
                $late = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                $isLate = true;
            }

            // Create attendance record
            $attendance = new AttendanceEmployee();
            $attendance->employee_id = $employeeId;
            $attendance->date = $date;
            $attendance->status = $status;
            $attendance->clock_in = $time;
            $attendance->clock_out = '00:00:00';
            $attendance->late = $late;
            $attendance->early_leaving = '00:00:00';
            $attendance->overtime = '00:00:00';
            $attendance->total_rest = '00:00:00';
            
            // Add location data
            $attendance->clock_in_latitude = $request->latitude;
            $attendance->clock_in_longitude = $request->longitude;
            $attendance->clock_in_location = $request->location;
            
            // Get employee creator ID
            $employee = Employee::find($employeeId);
            $attendance->created_by = $employee->created_by ?? 1;
            
            $attendance->save();

            return response()->json([
                'success' => true,
                'message' => 'Punched in successfully',
                'data' => [
                    'attendance_id' => $attendance->id,
                    'clock_in_time' => $time,
                    'location_captured' => !empty($request->latitude) && !empty($request->longitude),
                    'is_late' => $isLate
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Punch In API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to punch in. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Punch Out with Location
     */
    public function punchOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attendance_id' => 'required|exists:attendance_employees,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location' => 'nullable|string',
            'accuracy' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $attendance = AttendanceEmployee::find($request->attendance_id);
            
            if ($attendance->clock_out != '00:00:00') {
                return response()->json([
                    'success' => false,
                    'message' => 'Already punched out'
                ], 400);
            }

            $time = date('H:i:s');
            $date = $attendance->date;

            // Calculate overtime and early leaving
            $endTime = Utility::getValByName('company_end_time');
            $earlyLeaving = '00:00:00';
            $overtime = '00:00:00';

            if (strtotime($time) < strtotime($date . ' ' . $endTime)) {
                $totalEarlyLeavingSeconds = strtotime($date . ' ' . $endTime) - strtotime($time);
                $hours = floor($totalEarlyLeavingSeconds / 3600);
                $mins = floor($totalEarlyLeavingSeconds / 60 % 60);
                $secs = floor($totalEarlyLeavingSeconds % 60);
                $earlyLeaving = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            } elseif (strtotime($time) > strtotime($date . ' ' . $endTime)) {
                $totalOvertimeSeconds = strtotime($time) - strtotime($date . ' ' . $endTime);
                $hours = floor($totalOvertimeSeconds / 3600);
                $mins = floor($totalOvertimeSeconds / 60 % 60);
                $secs = floor($totalOvertimeSeconds % 60);
                $overtime = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
            }

            // Update attendance record
            $attendance->clock_out = $time;
            $attendance->early_leaving = $earlyLeaving;
            $attendance->overtime = $overtime;
            
            // Add location data
            $attendance->clock_out_latitude = $request->latitude;
            $attendance->clock_out_longitude = $request->longitude;
            $attendance->clock_out_location = $request->location;
            
            $attendance->save();

            return response()->json([
                'success' => true,
                'message' => 'Punched out successfully',
                'data' => [
                    'clock_out_time' => $time,
                    'location_captured' => !empty($request->latitude) && !empty($request->longitude),
                    'early_leaving' => $earlyLeaving,
                    'overtime' => $overtime
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Punch Out API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to punch out. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Today's Attendance Status
     */
    public function getTodayAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employeeId = $request->employee_id;
            $date = date('Y-m-d');

            $attendance = AttendanceEmployee::where('employee_id', $employeeId)
                ->where('date', $date)
                ->first();

            if (!$attendance) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'status' => 'not_punched_in',
                        'message' => 'Not punched in today'
                    ]
                ]);
            }

            $status = 'punched_in';
            if ($attendance->clock_out != '00:00:00') {
                $status = 'punched_out';
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $status,
                    'attendance_id' => $attendance->id,
                    'clock_in' => $attendance->clock_in,
                    'clock_out' => $attendance->clock_out,
                    'clock_in_location' => $attendance->clock_in_location,
                    'clock_out_location' => $attendance->clock_out_location,
                    'is_late' => $attendance->late != '00:00:00'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Get Today Attendance API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get attendance status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
