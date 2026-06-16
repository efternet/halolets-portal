<?php

namespace App\Http\Controllers\Portal\Operations;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkTaskRequest;
use App\Http\Requests\UpdateWorkTaskRequest;
use App\Models\ResolutionType;
use App\Models\WorkTask;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\JsonResponse;

class WorkTaskController extends Controller
{
    use ExportsCsv;

    const CALL_STAGES = ['open', 'in-progress', 'complete', 'pending', 'draft', 'archived'];

    public function index()
    {
        $search           = request('search');
        $filterCallStage  = request('call_stage');
        $filterResolution = request('resolution_type_id');
        $filterCompleted  = request('completed');
        $sort             = in_array(request('sort'), ['id', 'call_stage', 'work_started_at', 'work_completed_at', 'created_at'])
                            ? request('sort') : 'id';
        $direction        = request('direction') === 'asc' ? 'asc' : 'desc';

        $resolutionTypes = ResolutionType::query()->orderBy('name')->get();

        $query = WorkTask::query()
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->from('work_tasks as wt')
            ->join('calls as c', 'wt.call_id', '=', 'c.id')
            ->leftJoin('resolution_types as rt', 'wt.resolution_type_id', '=', 'rt.id')
            ->whereNull('wt.deleted_at')
            ->select(
                'wt.id', 'wt.call_id', 'wt.resolution_type_id',
                'wt.work_started_at', 'wt.work_completed_at', 'wt.created_at',
                'c.stage as call_stage',
                'rt.name as resolution_type_name',
            )
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('wt.id',        'like', "%{$search}%")
                ->orWhere('c.stage',    'like', "%{$search}%")
                ->orWhere('rt.name',    'like', "%{$search}%")
                ->orWhere('wt.call_id', 'like', "%{$search}%")
            ))
            ->when($filterCallStage,  fn ($q) => $q->where('c.stage', $filterCallStage))
            ->when($filterResolution, fn ($q) => $q->where('wt.resolution_type_id', $filterResolution))
            ->when($filterCompleted === 'yes', fn ($q) => $q->whereNotNull('wt.work_completed_at'))
            ->when($filterCompleted === 'no',  fn ($q) => $q->whereNull('wt.work_completed_at'))
            ->when($sort === 'call_stage',
                fn ($q) => $q->orderBy('c.stage', $direction),
                fn ($q) => $q->orderBy('wt.' . $sort, $direction)
            );

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('work-tasks.csv',
                ['ID', 'Call ID', 'Call Stage', 'Resolution Type', 'Work Started', 'Work Completed', 'Created'],
                $query->get(),
                fn ($r) => [$r->id, $r->call_id, $r->call_stage, $r->resolution_type_name, $r->work_started_at, $r->work_completed_at, $r->created_at]
            );
        }

        $tasks = $query->paginate(15)->appends(request()->only(['search', 'call_stage', 'resolution_type_id', 'completed', 'sort', 'direction']));

        return view('portal.operations.work-tasks.index', compact('tasks', 'resolutionTypes'), [
            'callStages'       => self::CALL_STAGES, 'sort'             => $sort, 'direction'        => $direction,
            'filterCallStage'  => $filterCallStage,  'filterResolution' => $filterResolution, 'filterCompleted' => $filterCompleted,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $task = WorkTask::query()->find($id);

        if (! $task) {
            return response()->json(['error' => 'Task not found.'], 404);
        }

        $task->delete();

        return response()->json(['success' => true]);
    }

    public function store(StoreWorkTaskRequest $request): JsonResponse
    {
        $task = WorkTask::create([
            'call_id'            => $request->validated('call_id'),
            'resolution_type_id' => $request->validated('resolution_type_id') ?: null,
            'work_started_at'    => $request->validated('work_started_at') ?: null,
            'work_completed_at'  => $request->validated('work_completed_at') ?: null,
        ]);

        return response()->json(['success' => true, 'id' => $task->id]);
    }

    public function update(UpdateWorkTaskRequest $request): JsonResponse
    {
        $id               = $request->validated('id');
        $resolutionTypeId = $request->validated('resolution_type_id');
        $workStartedAt    = $request->validated('work_started_at');
        $workCompletedAt  = $request->validated('work_completed_at');

        WorkTask::query()
            ->where('id', $id)
            ->update([
                'call_id'            => $request->validated('call_id'),
                'resolution_type_id' => $resolutionTypeId ?: null,
                'work_started_at'    => $workStartedAt ?: null,
                'work_completed_at'  => $workCompletedAt ?: null,
            ]);

        $resolutionName = $resolutionTypeId
            ? ResolutionType::query()->where('id', $resolutionTypeId)->value('name')
            : null;

        return response()->json(['success' => true, 'resolution_type_name' => $resolutionName]);
    }
}
