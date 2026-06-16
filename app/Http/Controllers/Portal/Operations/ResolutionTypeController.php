<?php

namespace App\Http\Controllers\Portal\Operations;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResolutionTypeRequest;
use App\Http\Requests\UpdateResolutionTypeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ResolutionTypeController extends Controller
{
    use ExportsCsv;

    public function index()
    {
        $search    = request('search');
        $sort      = in_array(request('sort'), ['rt.id', 'rt.name', 'task_count']) ? request('sort') : 'rt.name';
        $direction = request('direction') === 'desc' ? 'desc' : 'asc';

        $query = DB::table('resolution_types as rt')
            ->whereNull('rt.deleted_at')
            ->leftJoin('work_tasks as wt', function ($join) {
                $join->on('rt.id', '=', 'wt.resolution_type_id')->whereNull('wt.deleted_at');
            })
            ->select('rt.*', DB::raw('COUNT(wt.id) as task_count'))
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('rt.id',           'like', "%{$search}%")
                ->orWhere('rt.name',       'like', "%{$search}%")
                ->orWhere('rt.description','like', "%{$search}%")
            ))
            ->groupBy('rt.id')
            ->orderBy($sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('resolution-types.csv',
                ['ID', 'Name', 'Description', 'Task Count'],
                $query->get(),
                fn ($r) => [$r->id, $r->name, $r->description, $r->task_count]
            );
        }

        $types = $query->paginate(25)->appends(request()->only(['search', 'sort', 'direction']));

        return view('portal.operations.resolution-types.index', compact('types'), [
            'sort'      => $sort, 'direction' => $direction,
        ]);
    }

    public function store(StoreResolutionTypeRequest $request): JsonResponse
    {
        $id = DB::table('resolution_types')->insertGetId([
            'name'        => $request->validated('name'),
            'description' => $request->validated('description') ?: null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function destroy(int $id): JsonResponse
    {
        $affected = DB::table('resolution_types')
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->update(['deleted_at' => now()]);

        if (! $affected) {
            return response()->json(['error' => 'Resolution type not found.'], 404);
        }

        return response()->json(['success' => true]);
    }

    public function update(UpdateResolutionTypeRequest $request): JsonResponse
    {
        $id          = $request->validated('id');
        $name        = $request->validated('name');
        $description = $request->validated('description');

        DB::table('resolution_types')
            ->where('id', $id)
            ->update([
                'name'        => $name,
                'description' => $description ?: null,
                'updated_at'  => now(),
            ]);

        return response()->json(['success' => true]);
    }
}
