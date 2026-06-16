<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EndpointController extends Controller
{
    public function index()
    {
        $search = request('search');

        $endpoints = DB::table('endpoint_activation_status')
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

        $endpoint = DB::table('endpoint_activation_status')->where('id', $id)->first();

        if (! $endpoint) {
            return response()->json(['error' => 'Endpoint not found.'], 404);
        }

        $newStatus = ! $endpoint->is_active;

        DB::table('endpoint_activation_status')
            ->where('id', $id)
            ->update([
                'is_active'  => $newStatus,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success'   => true,
            'is_active' => $newStatus,
        ]);
    }
}
