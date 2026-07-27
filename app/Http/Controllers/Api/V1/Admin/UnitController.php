<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ScopesToProperty;
use App\Http\Resources\UnitResource;
use App\Models\Block;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    use ApiResponseTrait;
    use ScopesToProperty;

    public function index(Request $request, Block $block): JsonResponse
    {
        if (! $this->authorizePropertyAccess($block->property)) {
            return $this->unauthorized('You do not have access to this property.');
        }

        $units = $block->units()
            ->with('residents')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 50));

        return $this->success(UnitResource::collection($units)->response()->getData(true));
    }

    public function store(Request $request, Block $block): JsonResponse
    {
        if (! $this->authorizePropertyAccess($block->property)) {
            return $this->unauthorized('You do not have access to this property.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:50'],
        ]);

        $unit = $block->units()->create($validated);

        return $this->success(new UnitResource($unit), 'Unit created successfully.', 201);
    }

    public function update(Request $request, Block $block, Unit $unit): JsonResponse
    {
        if (! $this->authorizePropertyAccess($block->property)) {
            return $this->unauthorized('You do not have access to this property.');
        }

        if ($unit->block_id !== $block->id) {
            return $this->notFound('Unit not found.');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'floor' => ['sometimes', 'nullable', 'string', 'max:50'],
        ]);

        $unit->update($validated);

        return $this->success(new UnitResource($unit), 'Unit updated successfully.');
    }

    public function destroy(Block $block, Unit $unit): JsonResponse
    {
        if (! $this->authorizePropertyAccess($block->property)) {
            return $this->unauthorized('You do not have access to this property.');
        }

        if ($unit->block_id !== $block->id) {
            return $this->notFound('Unit not found.');
        }

        $unit->delete();

        return $this->success(message: 'Unit deleted successfully.');
    }
}
