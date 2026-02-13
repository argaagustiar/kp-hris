<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRecordRequest;
use App\Http\Requests\UpdateAttendanceRecordRequest;
use App\Http\Requests\ImportAttendanceRecordRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Services\AttendanceRecordImportService;
use Illuminate\Http\Request;

class AttendaceRecordController extends Controller
{
    protected $importService;

    public function __construct(AttendanceRecordImportService $importService)
    {
        $this->importService = $importService;
    }

    public function index(Request $request)
    {
        $query = AttendanceRecord::query()->with(['employee', 'period']);

        if ($request->has('search') && !empty($request->search)) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->search . '%')
                  ->orWhere('employee_code', 'ilike', '%' . $request->search . '%');
            });
        }

        if ($request->has('period_id')) {
            $query->where('period_id', $request->period_id);
        }

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $query->orderBy(
            Employee::select('name')
                ->whereColumn('employees.id', 'attendance_records.employee_id')
                ->limit(1), 
            'asc'
        );

        return AttendanceRecordResource::collection($query->orderBy('created_at', 'desc')->paginate(10));
    }

    public function store(StoreAttendanceRecordRequest $request)
    {
        $attendanceRecord = AttendanceRecord::create($request->validated());
        return new AttendanceRecordResource($attendanceRecord->load(['employee', 'period']));
    }

    public function show($id)
    {
        $attendanceRecord = AttendanceRecord::with(['employee', 'period'])->findOrFail($id);
        return new AttendanceRecordResource($attendanceRecord);
    }

    public function showByPeriodEmployee($period_id, $employee_id)
    {
        $attendanceRecord = AttendanceRecord::where('period_id', $period_id)
            ->where('employee_id', $employee_id)
            ->with(['employee', 'period'])
            ->firstOrFail();

        return new AttendanceRecordResource($attendanceRecord);
    }

    public function update(UpdateAttendanceRecordRequest $request, $id)
    {
        $attendanceRecord = AttendanceRecord::findOrFail($id);
        $attendanceRecord->update($request->validated());
        return new AttendanceRecordResource($attendanceRecord->load(['employee', 'period']));
    }

    public function destroy($id)
    {
        $attendanceRecord = AttendanceRecord::findOrFail($id);
        
        $attendanceRecord->delete();
        return response()->json(['message' => 'Attendance record deleted successfully']);
    }

    /**
     * Import attendance records from Excel/CSV using employee_code
     * 
     * Expected Excel/CSV format:
     * - Column 1: employee_code (or kode_karyawan)
     * - Column 2+: attendance fields (sick, permit, awol, etc.)
     * 
     * Both English and Indonesian column names are supported.
     */
    public function import(ImportAttendanceRecordRequest $request)
    {
        $result = $this->importService->import(
            $request->file('file'),
            $request->input('period_id')
        );

        if ($result['success']) {
            return response()->json($result, 201);
        }

        return response()->json($result, 422);
    }

    /**
     * Download attendance upload template
     */
    public function downloadTemplate()
    {
        $templatePath = public_path('templates/upload attendance template.xlsx');

        if (!file_exists($templatePath)) {
            return response()->json(['message' => 'Template file not found'], 404);
        }

        return response()->download($templatePath, 'upload attendance template.xlsx');
    }
}