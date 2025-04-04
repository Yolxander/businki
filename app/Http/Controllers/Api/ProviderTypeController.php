<?php

namespace App\Http\Controllers\Api;

use App\Models\ProviderType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProviderTypeController extends Controller
{
    // GET /api/provider-types
    public function index()
    {
        return ProviderType::all();
    }

    // GET /api/provider-types/{id}
    public function show($id)
    {
        $type = ProviderType::find($id);

        if (!$type) {
            return response()->json(['message' => 'Provider type not found'], 404);
        }

        return response()->json($type);
    }

    // POST /api/provider-types
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $type = ProviderType::create($validated);

        return response()->json($type, 201);
    }

    // PUT /api/provider-types/{id}
    public function update(Request $request, $id)
    {
        $type = ProviderType::find($id);

        if (!$type) {
            return response()->json(['message' => 'Provider type not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $type->update($validated);

        return response()->json($type);
    }

    // DELETE /api/provider-types/{id}
    public function destroy($id)
    {
        $type = ProviderType::find($id);

        if (!$type) {
            return response()->json(['message' => 'Provider type not found'], 404);
        }

        $type->delete();

        return response()->json(['message' => 'Provider type deleted']);
    }
}
