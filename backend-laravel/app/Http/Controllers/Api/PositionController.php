<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Http\Resources\PositionResource;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::withCount('employees');

        if ($request->has('search')) {
            $query->where('title', 'ilike', '%' . $request->search . '%');
        }

        return PositionResource::collection($query->orderBy('title', 'desc')->paginate(10));
    }

    public function store(StorePositionRequest $request)
    {
        $position = Position::create($request->validated());
        return new PositionResource($position);
    }

    public function show($id)
    {
        $position = Position::withCount('employees')->findOrFail($id);
        return new PositionResource($position);
    }

    public function update(UpdatePositionRequest $request, $id)
    {
        $position = Position::findOrFail($id);
        $position->update($request->validated());
        return new PositionResource($position);
    }

    public function destroy($id)
    {
        $position = Position::findOrFail($id);
        
        // Cek apakah ada karyawan aktif di posisi ini?
        if ($position->employees()->where('is_active', true)->exists()) {
             return response()->json(['message' => 'Cannot delete position assigned to active employees.'], 400);
        }

        $position->delete();
        return response()->json(['message' => 'Position deleted successfully']);
    }
}