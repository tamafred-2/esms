<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Batch;
use App\Models\BatchEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function store(Request $request, BatchEnrollment $batch)
    {
        try {
            DB::beginTransaction();

            $attendanceDate = $request->input('attendance_date');
            $students = $batch->students;

            foreach ($students as $student) {
                $studentId = $student->id;

                // Check if attendance record already exists
                $attendance = Attendance::firstOrNew([
                    'student_id' => $studentId,
                    'batch_id' => $batch->id,
                    'attendance_date' => $attendanceDate,
                ]);

                // Morning attendance
                $attendance->morning_time_in = $request->input("morning_in_{$studentId}");
                $attendance->morning_time_out = $request->input("morning_out_{$studentId}");
                $attendance->morning_minutes_late = $request->input("morning_late_{$studentId}", 0);

                // Afternoon attendance
                $attendance->afternoon_time_in = $request->input("afternoon_in_{$studentId}");
                $attendance->afternoon_time_out = $request->input("afternoon_out_{$studentId}");
                $attendance->afternoon_minutes_late = $request->input("afternoon_late_{$studentId}", 0);

                // Remarks
                $attendance->remarks = $request->input("remarks_{$studentId}");

                $attendance->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(BatchEnrollment $batch, $date = null)
    {
        $date = $date ?? now()->toDateString();
        
        $attendances = Attendance::with('student')
            ->where('batch_id', $batch->id)
            ->where('attendance_date', $date)
            ->get();

        return view('admin.attendance.show', compact('batch', 'attendances', 'date'));
    }

    public function report(BatchEnrollment $batch, Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $attendances = Attendance::with('student')
            ->where('batch_id', $batch->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get()
            ->groupBy('student_id');

        return view('admin.attendance.report', compact('batch', 'attendances', 'startDate', 'endDate'));
    }
}
