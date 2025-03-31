<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Batch;
use App\Models\BatchEnrollment;
use App\Models\Course;
use App\Models\CourseBatch;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function store(Request $request, Course $course, CourseBatch $batch)
    {
        try {
            $validated = $request->validate([
                'attendance_date' => 'required|date',
                'students' => 'required|array',
                'students.*.morning_time_in' => 'nullable|string',
                'students.*.morning_time_out' => 'nullable|string',
                'students.*.afternoon_time_in' => 'nullable|string',
                'students.*.afternoon_time_out' => 'nullable|string',
                'students.*.morning_status' => 'required|in:present,late,absent,excused',
                'students.*.afternoon_status' => 'required|in:present,late,absent,excused',
                'students.*.morning_late_minutes' => 'required|integer|min:0',
                'students.*.afternoon_late_minutes' => 'required|integer|min:0',
            ]);
    
            DB::beginTransaction();
    
            foreach ($validated['students'] as $studentId => $attendance) {
                $morningLateMinutes = (int) $attendance['morning_late_minutes'];
                $afternoonLateMinutes = (int) $attendance['afternoon_late_minutes'];
                
                // Set default statuses if not provided
                $morningStatus = $attendance['morning_status'];
                $afternoonStatus = $attendance['afternoon_status'];
    
                // Update status based on late minutes if time is provided
                if ($attendance['morning_time_in']) {
                    $morningStatus = $morningLateMinutes > 15 ? 'late' : 'present';
                }
    
                if ($attendance['afternoon_time_in']) {
                    $afternoonStatus = $afternoonLateMinutes > 15 ? 'late' : 'present';
                }
    
                Attendance::updateOrCreate(
                    [
                        'batch_id' => $batch->id,
                        'student_id' => $studentId,
                        'attendance_date' => $validated['attendance_date']
                    ],
                    [
                        'morning_time_in' => $attendance['morning_time_in'] ?? null,
                        'morning_time_out' => $attendance['morning_time_out'] ?? null,
                        'afternoon_time_in' => $attendance['afternoon_time_in'] ?? null,
                        'afternoon_time_out' => $attendance['afternoon_time_out'] ?? null,
                        'morning_status' => $morningStatus,
                        'afternoon_status' => $afternoonStatus,
                        'morning_late_minutes' => $morningLateMinutes,
                        'afternoon_late_minutes' => $afternoonLateMinutes,
                    ]
                );
            }
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Attendance has been recorded successfully',
                'data' => [
                    'date' => $validated['attendance_date'],
                    'total_students' => count($validated['students'])
                ]
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error recording attendance: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function calculateLateness($actualTime, $scheduledTime): int
    {
        if (!$actualTime || !$scheduledTime) {
            return 0;
        }
    
        $actual = Carbon::createFromFormat('H:i', $actualTime);
        $scheduled = Carbon::createFromFormat('H:i', $scheduledTime);
    
        return max(0, $actual->diffInMinutes($scheduled));
    }
    
    private function determineOverallStatus($morningStatus, $afternoonStatus): string
    {
        if ($morningStatus === 'late' || $afternoonStatus === 'late') {
            return 'late';
        }
        if ($morningStatus === 'present' || $afternoonStatus === 'present') {
            return 'present';
        }
        if ($morningStatus === 'excused' || $afternoonStatus === 'excused') {
            return 'excused';
        }
        return 'absent';
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
