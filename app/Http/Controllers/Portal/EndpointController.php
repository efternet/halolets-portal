<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\EndpointActivationStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EndpointController extends Controller
{
    public function index()
    {
        $search = request('search');

        $endpoints = EndpointActivationStatus::query()
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('path', 'like', "%{$search}%")
                ->orWhere('method', 'like', "%{$search}%")
            ))
            ->orderBy('method')
            ->orderBy('path')
            ->get();

        return view('portal.endpoints.index', compact('endpoints'));
    }

    public function toggle(Request $request): JsonResponse
    {
        $id = $request->validate(['id' => ['required', 'integer']])['id'];

        $endpoint = EndpointActivationStatus::query()->find($id);

        if (! $endpoint) {
            return response()->json(['error' => 'Endpoint not found.'], 404);
        }

        $newStatus = ! $endpoint->is_active;

        $endpoint->update(['is_active' => $newStatus]);

        return response()->json([
            'success'   => true,
            'is_active' => $newStatus,
        ]);
    }
}
