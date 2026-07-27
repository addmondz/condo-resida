<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ScopesToProperty;
use App\Http\Resources\BlockResource;
use App\Models\Block;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    use ApiResponseTrait;
    use ScopesToProperty;

    public function index(Request $request, Property $property): JsonResponse
    {
        if (! $this->authorizePropertyAccess($property)) {
            return $this->unauthorized('You do not have access to this property.');
        }

        $blocks = $property->blocks()
            ->withCount('units')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 50));

        return $this->success(BlockResource::collection($blocks)->response()->getData(true));
    }

    public function store(Request $request, Property $property): JsonResponse
    {
        if (! $this->authorizePropertyAccess($property)) {
            return $this->unauthorized('You do not have access to this property.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $block = $property->blocks()->create($validated);
        $block->loadCount('units');

        return $this->success(new BlockResource($block), 'Block created successfully.', 201);
    }

    public function show(Property $property, Block $block): JsonResponse
    {
        if (! $this->authorizePropertyAccess($property)) {
            return $this->unauthorized('You do not have access to this property.');
        }

        if ($block->property_id !== $property->id) {
            return $this->notFound('Block not found.');
        }

        $block->load('units.residents');
        $block->loadCount('units');

        return $this->success(new BlockResource($block));
    }

    public function update(Request $request, Property $property, Block $block): JsonResponse
    {
        if (! $this->authorizePropertyAccess($property)) {
            return $this->unauthorized('You do not have access to this property.');
        }

        if ($block->property_id !== $property->id) {
            return $this->notFound('Block not found.');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
        ]);

        $block->update($validated);
        $block->loadCount('units');

        return $this->success(new BlockResource($block), 'Block updated successfully.');
    }

    public function destroy(Property $property, Block $block): JsonResponse
    {
        if (! $this->authorizePropertyAccess($property)) {
            return $this->unauthorized('You do not have access to this property.');
        }

        if ($block->property_id !== $property->id) {
            return $this->notFound('Block not found.');
        }

        $block->delete();

        return $this->success(message: 'Block deleted successfully.');
    }
}
