<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\StoreEvaluationRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Requests\UpdateEvaluationRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\EvaluationResource;
use App\Models\Evaluation;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function index(Request $request)
    {
        $query = Evaluation::with(['period', 'employee', 'evaluator']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->WhereHas('employee', function($posQuery) use ($search) {
                      $posQuery->where('name', 'ilike', "%{$search}%");
                  });
            });
            
        }

        if ($request->has('period_id')) {
            $query->where('period_id', $request->period_id);
        }

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('evaluator_id')) {
            $query->where('evaluator_id', $request->evaluator_id);
        }

        // Pagination
        $evaluation = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 10));

        return EvaluationResource::collection($evaluation);
    }

    public function store(StoreEvaluationRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $evaluation = Evaluation::create($request->validated());

            $evaluation->load(['period', 'employee', 'evaluator']);

            return new EvaluationResource($evaluation);
        });
    }

    public function show($id)
    {
        $evaluation = Evaluation::with(['period', 'employee', 'evaluator'])->findOrFail($id);
        return new EvaluationResource($evaluation);
    }

    public function update(UpdateEvaluationRequest $request, $id)
    {
        $evaluation = Evaluation::findOrFail($id);

        return DB::transaction(function () use ($request, $evaluation) {
            $evaluation->update($request->validated());

            $evaluation->refresh()->load(['period', 'employee', 'evaluator']);
            return new EvaluationResource($evaluation);
        });
    }

    public function destroy($id)
    {
        $evaluation = Evaluation::findOrFail($id);
        
        // Soft Delete (karena di model sudah pakai SoftDeletes)
        // Relasi pivot otomatis aman karena kita pakai ->wherePivotNull di Model
        $evaluation->delete();

        return response()->json(['message' => 'Evaluation deleted successfully']);
    }
}