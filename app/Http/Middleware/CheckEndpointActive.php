<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckEndpointActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $endpoint = DB::table('endpoint_activation_status')
            ->where('method', strtoupper($request->method()))
            ->where('path', $request->path())
            ->first();

        if ($endpoint && ! $endpoint->is_active) {
            return response()->json([
                'error'   => 'This endpoint is currently disabled.',
                'path'    => $request->path(),
                'method'  => $request->method(),
            ], 503);
        }

        return $next($request);
    }
}
