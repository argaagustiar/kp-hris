<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Period;
use Illuminate\Http\UploadedFile;
use SplFileObject;
use Exception;

class AttendanceRecordImportService
{
    /**
     * Import attendance records from Excel/CSV file using employee_code
     * 
     * @param UploadedFile $file
     * @param string $periodId
     * @return array
     */
    public function import(UploadedFile $file, string $periodId): array
    {
        $period = Period::findOrFail($periodId);
        
        // Get real path from uploaded file (already stored by controller)
        $fullPath = $file->getRealPath();
        
        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            return [
                'success' => false,
                'message' => "File does not exist or is not readable: {$fullPath}",
                'imported_count' => 0,
                'total_rows' => 0,
                'errors' => ["File at {$fullPath} is not readable"],
            ];
        }
        
        $data = [];
        $errors = [];
        $successCount = 0;

        try {
            // Read file based on extension
            if ($file->getClientOriginalExtension() === 'csv') {
                $data = $this->readCsv($fullPath);
            } else {
                $data = $this->readExcel($fullPath);
            }

            // Process each row
            foreach ($data as $index => $row) {
                try {
                    $this->processRow($row, $period, $index + 1);
                    $successCount++;
                } catch (Exception $e) {
                    $errors[] = [
                        'row' => $index + 1,
                        'message' => $e->getMessage(),
                    ];
                }
            }

            return [
                'success' => true,
                'message' => "Successfully imported {$successCount} attendance records",
                'imported_count' => $successCount,
                'total_rows' => count($data),
                'errors' => $errors,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'imported_count' => 0,
                'total_rows' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Read Excel file using PhpSpreadsheet
     */
    private function readExcel(string $filepath): array
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($filepath);
        $worksheet = $spreadsheet->getActiveSheet();
        
        $data = [];
        $headerRow = null;
        $started = false;

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }

            // Skip empty rows
            if (empty(array_filter($rowData))) {
                continue;
            }

            // First row is header
            if (!$started) {
                $headerRow = array_map('strtolower', array_map('trim', $rowData));
                $started = true;
                continue;
            }

            // Map row data to associative array
            $mappedRow = [];
            foreach ($headerRow as $index => $header) {
                $mappedRow[$header] = $rowData[$index] ?? null;
            }

            $data[] = $mappedRow;
        }

        return $data;
    }

    /**
     * Read CSV file
     */
    private function readCsv(string $filepath): array
    {
        $data = [];
        $headerRow = null;
        $started = false;

        $file = new SplFileObject($filepath, 'r');
        $file->setFlags(SplFileObject::READ_CSV);

        foreach ($file as $rowIndex => $row) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // First row is header
            if (!$started) {
                $headerRow = array_map('strtolower', array_map('trim', $row));
                $started = true;
                continue;
            }

            // Map row data to associative array
            $mappedRow = [];
            foreach ($headerRow as $index => $header) {
                $mappedRow[$header] = $row[$index] ?? null;
            }

            $data[] = $mappedRow;
        }

        return $data;
    }

    /**
     * Process individual row and create/update attendance record
     */
    private function processRow(array $row, Period $period, int $rowNumber): void
    {
        // Get employee_code from row
        $employeeCode = trim($row['employee_code'] ?? $row['kode_karyawan'] ?? '');
        
        if (empty($employeeCode)) {
            throw new Exception("Row {$rowNumber}: employee_code is required");
        }

        // Find employee by code
        $employee = Employee::where('employee_code', $employeeCode)->first();
        
        if (!$employee) {
            throw new Exception("Row {$rowNumber}: Employee with code '{$employeeCode}' not found");
        }

        // Prepare data
        $data = [
            'period_id' => $period->id,
            'employee_id' => $employee->id,
            'sick' => $this->getIntegerValue($row, 'sick', 'sakit') || 0,
            'work_accident' => $this->getIntegerValue($row, 'work_accident', 'kecelakaan_kerja') || 0,
            'permit' => $this->getIntegerValue($row, 'permit', 'izin') || 0,
            'awol' => $this->getIntegerValue($row, 'awol', 'alpa') || 0,
            'late_permit' => $this->getIntegerValue($row, 'late_permit', 'izin_terlambat') || 0,
            'early_leave' => $this->getIntegerValue($row, 'early_leave', 'pulang_cepat') || 0,
            'annual_leave' => $this->getIntegerValue($row, 'annual_leave', 'cuti_tahunan') || 0,
            'late' => $this->getIntegerValue($row, 'late', 'terlambat') || 0,
            'warning_letter_1' => $this->getIntegerValue($row, 'warning_letter_1', 'surat_peringatan_1') || 0,
            'warning_letter_2' => $this->getIntegerValue($row, 'warning_letter_2', 'surat_peringatan_2') || 0,
            'warning_letter_3' => $this->getIntegerValue($row, 'warning_letter_3', 'surat_peringatan_3') || 0,
            'subordinate_late' => $this->getIntegerValue($row, 'subordinate_late', 'bawahan_terlambat') || 0,
            'subordinate_awol' => $this->getIntegerValue($row, 'subordinate_awol', 'bawahan_alpa') || 0,
        ];

        // Check if record exists for this period and employee
        $existing = AttendanceRecord::where('period_id', $period->id)
            ->where('employee_id', $employee->id)
            ->first();

        if ($existing) {
            // Update existing record
            $existing->update($data);
        } else {
            // Create new record
            AttendanceRecord::create($data);
        }
    }

    /**
     * Get integer value from row (support both English and Indonesian keys)
     */
    private function getIntegerValue(array $row, string ...$keys): ?int
    {
        foreach ($keys as $key) {
            $value = $row[strtolower($key)] ?? null;
            if ($value !== null) {
                return intval($value) ?: null;
            }
        }
        return null;
    }

    /**
     * Get boolean value from row (support both English and Indonesian keys)
     */
    private function getBooleanValue(array $row, string ...$keys): ?bool
    {
        foreach ($keys as $key) {
            $value = $row[strtolower($key)] ?? null;
            if ($value !== null) {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }
        }
        return null;
    }
}
