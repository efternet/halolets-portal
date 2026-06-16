<?php

namespace App\Http\Controllers\Portal\Operations;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCallRequest;
use App\Http\Requests\UpdateCallRequest;
use App\Models\Call;
use Illuminate\Http\JsonResponse;

class CallController extends Controller
{
    use ExportsCsv;

    const STAGES = ['open', 'in-progress', 'complete', 'pending', 'draft', 'archived'];

    public function index()
    {
        $search         = request('search');
        $filterStage    = request('stage');
        $filterTask     = request('has_task');
        $filterResolved = request('has_resolution');
        $sort           = in_array(request('sort'), ['id', 'stage', 'created_at', 'work_completed_at']) ? request('sort') : 'id';
        $direction      = request('direction') === 'asc' ? 'asc' : 'desc';

        $query = Call::query()
            ->from('calls as c')
            ->leftJoin('work_tasks as wt', function ($join) {
                $join->on('c.id', '=', 'wt.call_id')->whereNull('wt.deleted_at');
            })
            ->leftJoin('resolution_types as rt', 'wt.resolution_type_id', '=', 'rt.id')
            ->select(
                'c.id', 'c.stage', 'c.notes', 'c.created_at',
                'wt.id as task_id',
                'wt.work_started_at', 'wt.work_completed_at',
                'rt.name as resolution_type_name',
            )
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('c.id',      'like', "%{$search}%")
                ->orWhere('c.stage', 'like', "%{$search}%")
                ->orWhere('c.notes', 'like', "%{$search}%")
                ->orWhere('rt.name', 'like', "%{$search}%")
            ))
            ->when($filterStage, fn ($q) => $q->where('c.stage', $filterStage))
            ->when($filterTask === 'yes', fn ($q) => $q->whereNotNull('wt.id'))
            ->when($filterTask === 'no',  fn ($q) => $q->whereNull('wt.id'))
            ->when($filterResolved === 'yes', fn ($q) => $q->whereNotNull('wt.resolution_type_id'))
            ->when($filterResolved === 'no',  fn ($q) => $q->whereNull('wt.resolution_type_id'))
            ->when($sort === 'work_completed_at',
                fn ($q) => $q->orderBy('wt.work_completed_at', $direction),
                fn ($q) => $q->orderBy('c.' . $sort, $direction)
            );

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('calls.csv',
                ['ID', 'Stage', 'Notes', 'Resolution Type', 'Work Started', 'Work Completed', 'Created'],
                $query->get(),
                fn ($r) => [$r->id, $r->stage, $r->notes, $r->resolution_type_name, $r->work_started_at, $r->work_completed_at, $r->created_at]
            );
        }

        $calls = $query->paginate(15)->appends(request()->only(['search', 'stage', 'has_task', 'has_resolution', 'sort', 'direction']));

        return view('portal.operations.calls.index', compact('calls'), [
            'stages'         => self::STAGES, 'sort'         => $sort, 'direction'    => $direction,
            'filterStage'    => $filterStage, 'filterTask'   => $filterTask, 'filterResolved' => $filterResolved,
        ]);
    }

    public function search(): JsonResponse
    {
        $q = trim(request('q', ''));

        $results = Call::query()
            ->when($q !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('id', 'like', "%{$q}%")
                ->orWhere('notes', 'like', "%{$q}%")
                ->orWhere('stage', 'like', "%{$q}%")
            ))
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get(['id', 'stage', 'notes']);

        return response()->json($results);
    }

    public function store(StoreCallRequest $request): JsonResponse
    {
        $call = Call::create([
            'stage' => $request->validated('stage'),
            'notes' => $request->validated('notes') ?: null,
        ]);

        return response()->json(['success' => true, 'id' => $call->id]);
    }

    public function update(UpdateCallRequest $request): JsonResponse
    {
        $id    = $request->validated('id');
        $stage = $request->validated('stage');
        $notes = $request->validated('notes');

        Call::query()
            ->where('id', $id)
            ->update([
                'stage' => $stage,
                'notes' => $notes ?: null,
            ]);

        return response()->json(['success' => true, 'stage' => $stage]);
    }
}
