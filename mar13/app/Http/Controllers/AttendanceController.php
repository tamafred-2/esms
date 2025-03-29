<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Batch;
use App\Models\BatchEnrollment;
use App\Models\Course;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function store(Request $request, $batchId)
    {
        try {
            $updated = false;
            foreach ($request->students as $studentId => $attendance) {
                // Get the course schedule for this batch
                $batch = BatchEnrollment::with('course')->find($batchId);
                $courseSchedule = $batch->course;
    
                // Calculate minutes late
                $morningMinutesLate = 0;
                $afternoonMinutesLate = 0;
    
                // Calculate morning minutes late
                if (!empty($attendance['morning_time_in']) && isset($courseSchedule->morning_schedule['in'])) {
                    $timeIn = Carbon::parse($attendance['morning_time_in']);
                    $scheduleIn = Carbon::parse($request->attendance_date . ' ' . $courseSchedule->morning_schedule['in']);
                    
                    // Only calculate minutes late if time is actually late
                    if ($timeIn->gt($scheduleIn)) {
                        $morningMinutesLate = max(0, $timeIn->diffInMinutes($scheduleIn));
                    }
                }
    
                // Calculate afternoon minutes late
                if (!empty($attendance['afternoon_time_in']) && isset($courseSchedule->afternoon_schedule['in'])) {
                    $timeIn = Carbon::parse($attendance['afternoon_time_in']);
                    $scheduleIn = Carbon::parse($request->attendance_date . ' ' . $courseSchedule->afternoon_schedule['in']);
                    
                    if ($timeIn->gt($scheduleIn)) {
                        $afternoonMinutesLate = max(0, $timeIn->diffInMinutes($scheduleIn));
                    }
                }
    
                $exists = Attendance::where([
                    'batch_id' => $batchId,
                    'student_id' => $studentId,
                    'attendance_date' => $request->attendance_date,
                ])->exists();
    
                if ($exists) {
                    $updated = true;
                }
    
                Attendance::updateOrCreate(
                    [
                        'batch_id' => $batchId,
                        'student_id' => $studentId,
                        'attendance_date' => $request->attendance_date,
                    ],
                    [
                        'morning_time_in' => $attendance['morning_time_in'],
                        'morning_time_out' => $attendance['morning_time_out'],
                        'afternoon_time_in' => $attendance['afternoon_time_in'],
                        'afternoon_time_out' => $attendance['afternoon_time_out'],
                        'morning_minutes_late' => $morningMinutesLate,
                        'afternoon_minutes_late' => $afternoonMinutesLate,
                        'status' => $attendance['status']
                    ]
                );
            }
    
            return response()->json([
                'success' => true,
                'message' => $updated ? 'Attendance updated successfully' : 'Attendance saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save attendance: ' . $e->getMessage()
            ]);
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


    public function edit(BatchEnrollment $batch, $date)
    {
        $attendances = Attendance::with('student')
            ->where('batch_id', $batch->id)
            ->where('attendance_date', $date)
            ->get();

        return view('admin.attendance.edit', compact('batch', 'attendances', 'date'));
    }

    public function update(Request $request, BatchEnrollment $batch, $date)
    {
        try {
            DB::beginTransaction();

            $courseSchedule = $batch->course;

            $validated = $request->validate([
                'students' => 'required|array',
                'students.*.student_id' => 'required|exists:users,id',
                'students.*.morning_time_in' => [
                    'nullable',
                    'date_format:H:i',
                ],
                'students.*.morning_time_out' => [
                    'nullable',
                    'date_format:H:i',
                    'after:students.*.morning_time_in',
                ],
                'students.*.afternoon_time_in' => [
                    'nullable',
                    'date_format:H:i',
                ],
                'students.*.afternoon_time_out' => [
                    'nullable',
                    'date_format:H:i',
                    'after:students.*.afternoon_time_in',
                ],
                'students.*.remarks' => 'nullable|string'
            ]);

            foreach ($validated['students'] as $studentData) {
                $attendance = Attendance::where('batch_id', $batch->id)
                    ->where('student_id', $studentData['student_id'])
                    ->where('attendance_date', $date)
                    ->first();

                if (!$attendance) {
                    continue;
                }

                // Calculate minutes late
                $morningMinutesLate = 0;
                $afternoonMinutesLate = 0;

                if (!empty($studentData['morning_time_in']) && isset($courseSchedule->morning_schedule['in'])) {
                    $timeIn = Carbon::createFromFormat('H:i', $studentData['morning_time_in']);
                    $scheduleIn = Carbon::createFromFormat('H:i', $courseSchedule->morning_schedule['in']);
                    if ($timeIn->gt($scheduleIn)) {
                        $morningMinutesLate = $timeIn->diffInMinutes($scheduleIn);
                    }
                }

                if (!empty($studentData['afternoon_time_in']) && isset($courseSchedule->afternoon_schedule['in'])) {
                    $timeIn = Carbon::createFromFormat('H:i', $studentData['afternoon_time_in']);
                    $scheduleIn = Carbon::createFromFormat('H:i', $courseSchedule->afternoon_schedule['in']);
                    if ($timeIn->gt($scheduleIn)) {
                        $afternoonMinutesLate = $timeIn->diffInMinutes($scheduleIn);
                    }
                }

                $attendance->update([
                    'morning_time_in' => $studentData['morning_time_in'] ?? null,
                    'morning_time_out' => $studentData['morning_time_out'] ?? null,
                    'morning_minutes_late' => $morningMinutesLate,
                    'afternoon_time_in' => $studentData['afternoon_time_in'] ?? null,
                    'afternoon_time_out' => $studentData['afternoon_time_out'] ?? null,
                    'afternoon_minutes_late' => $afternoonMinutesLate,
                    'remarks' => $studentData['remarks'] ?? null
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(BatchEnrollment $batch, $date)
    {
        try {
            DB::beginTransaction();

            Attendance::where('batch_id', $batch->id)
                ->where('attendance_date', $date)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance records deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting attendance records: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStudents(BatchEnrollment $batch)
    {
        $students = $batch->students()
            ->where('usertype', 'student')
            ->select('id', 'name')
            ->get();

        return response()->json($students);
    }
}
