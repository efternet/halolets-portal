<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AbTestController extends Controller
{
    use ExportsCsv;

    public function index()
    {
        $tab = request('tab', 'tests');

        // ── Tests tab ──────────────────────────────────────────────────────────
        $testSearch = request('test_search');
        $testActive = request('test_active');
        $testSort   = in_array(request('test_sort'), ['id', 'name', 'active', 'active_at', 'user_group'])
                      ? request('test_sort') : 'id';
        $testDir    = request('test_dir') === 'desc' ? 'desc' : 'asc';

        $testsQuery = DB::table('ab_tests')
            ->when($testSearch, fn ($q) => $q->where('name', 'like', "%{$testSearch}%"))
            ->when($testActive !== null && $testActive !== '', fn ($q) => $q->where('active', (int) $testActive))
            ->orderBy($testSort, $testDir);

        if ($this->wantsCsvExport() && $tab === 'tests') {
            return $this->streamCsv('ab-tests.csv',
                ['ID', 'Name', 'Status', 'User Group', 'Active From', 'Deactivated On'],
                $testsQuery->get(),
                fn ($r) => [$r->id, $r->name, $r->active ? 'Active' : 'Inactive', $r->user_group, $r->active_at, $r->deactivated_on ?? '']
            );
        }

        $tests = $testsQuery->paginate(15, ['*'], 'test_page')
            ->appends(request()->only(['tab', 'test_search', 'test_active', 'test_sort', 'test_dir']));

        // ── Breakdown tab (live from ab_visits) ────────────────────────────────
        $bkSearch     = request('bk_search');
        $bkExperiment = request('bk_experiment');
        $bkGroup      = request('bk_group');

        $experiments = DB::table('ab_tests')->orderBy('name')->pluck('name');
        $groups      = DB::table('ab_visits')->distinct()->orderBy('group')->pluck('group');

        $minStage = DB::table('ab_visits')
            ->select('ab_test_id', 'group', DB::raw('MIN(stage) as min_stage'))
            ->groupBy('ab_test_id', 'group');

        $baseline = DB::table('ab_visits as base')
            ->joinSub($minStage, 'ms', fn ($j) =>
                $j->on('base.ab_test_id', '=', 'ms.ab_test_id')
                  ->on('base.group',      '=', 'ms.group')
                  ->on('base.stage',      '=', 'ms.min_stage')
            )
            ->select('base.ab_test_id', 'base.group', DB::raw('COUNT(*) as baseline_total'))
            ->groupBy('base.ab_test_id', 'base.group');

        $breakdownQuery = DB::table('ab_visits as av')
            ->leftJoin('ab_tests as at',  'av.ab_test_id', '=', 'at.id')
            ->leftJoin('ab_stages as s',  'av.stage',      '=', 's.id')
            ->leftJoinSub($baseline, 'bl', fn ($j) =>
                $j->on('av.ab_test_id', '=', 'bl.ab_test_id')
                  ->on('av.group',      '=', 'bl.group')
            )
            ->select(
                'at.name as experiment', 'av.group', 'av.stage', 's.name as stage_name',
                DB::raw('COUNT(*) as total_visits'),
                DB::raw('ROUND(COUNT(*) * 100.0 / MAX(bl.baseline_total), 2) as percentage_visits')
            )
            ->when($bkSearch, fn ($q) => $q->where(fn ($q) => $q
                ->where('at.name',   'like', "%{$bkSearch}%")
                ->orWhere('av.group','like', "%{$bkSearch}%")
            ))
            ->when($bkExperiment, fn ($q) => $q->where('at.name',  $bkExperiment))
            ->when($bkGroup,      fn ($q) => $q->where('av.group', $bkGroup))
            ->groupBy('av.ab_test_id', 'at.name', 'av.group', 'av.stage', 's.name')
            ->orderBy('experiment', 'asc')
            ->orderBy('av.group',   'asc')
            ->orderBy('av.stage',   'asc');

        if ($this->wantsCsvExport() && $tab === 'breakdown') {
            return $this->streamCsv('ab-test-breakdown.csv',
                ['Experiment', 'Group', 'Stage', 'Stage Name', 'Total Visits', 'Funnel %'],
                $breakdownQuery->get(),
                fn ($r) => [$r->experiment, $r->group, $r->stage, $r->stage_name, $r->total_visits, $r->percentage_visits]
            );
        }

        $breakdown = $breakdownQuery->paginate(15, ['*'], 'bk_page')
            ->appends(request()->only(['tab', 'bk_search', 'bk_experiment', 'bk_group']));

        // ── Visits tab ─────────────────────────────────────────────────────────
        $visSearch  = request('vis_search');
        $visTest    = request('vis_test');
        $visGroup   = request('vis_group');
        $visStage   = request('vis_stage');
        $visSort    = in_array(request('vis_sort'), ['av.id', 'av.ab_test_id', 'av.group', 'av.stage', 'av.created_at'])
                      ? request('vis_sort') : 'av.created_at';
        $visDir     = request('vis_dir') === 'asc' ? 'asc' : 'desc';

        $visitGroups = DB::table('ab_visits')->distinct()->orderBy('group')->pluck('group');

        $visitsQuery = DB::table('ab_visits as av')
            ->leftJoin('ab_tests as at', 'av.ab_test_id', '=', 'at.id')
            ->leftJoin('ab_stages as s', 'av.stage',      '=', 's.id')
            ->select('av.id', 'av.ip_address', 'av.ab_test_id', 'at.name as test_name', 'av.group', 'av.stage', 's.name as stage_name', 'av.created_at')
            ->when($visSearch, fn ($q) => $q->where(fn ($q) => $q
                ->where('av.ip_address', 'like', "%{$visSearch}%")
                ->orWhere('at.name',     'like', "%{$visSearch}%")
            ))
            ->when($visTest,  fn ($q) => $q->where('av.ab_test_id', $visTest))
            ->when($visGroup, fn ($q) => $q->where('av.group',      $visGroup))
            ->when($visStage, fn ($q) => $q->where('av.stage',      $visStage))
            ->orderBy($visSort, $visDir);

        if ($this->wantsCsvExport() && $tab === 'visits') {
            return $this->streamCsv('ab-test-visits.csv',
                ['ID', 'IP Address', 'Test ID', 'Test Name', 'Group', 'Stage', 'Stage Name', 'Visited'],
                $visitsQuery->get(),
                fn ($r) => [$r->id, $r->ip_address, $r->ab_test_id, $r->test_name, $r->group, $r->stage, $r->stage_name, $r->created_at]
            );
        }

        $visits = $visitsQuery->paginate(15, ['*'], 'vis_page')
            ->appends(request()->only(['tab', 'vis_search', 'vis_test', 'vis_group', 'vis_stage', 'vis_sort', 'vis_dir']));

        // ── Stages tab ─────────────────────────────────────────────────────────
        $stages = DB::table('ab_stages')->orderBy('id')->get();

        if ($this->wantsCsvExport() && $tab === 'stages') {
            return $this->streamCsv('ab-stages.csv',
                ['ID', 'Name'],
                $stages,
                fn ($r) => [$r->id, $r->name]
            );
        }

        return view('portal.ab-tests.index', compact(
            'tab', 'tests', 'breakdown', 'visits', 'stages',
            'experiments', 'groups', 'visitGroups',
        ), [
            'testSearch'   => $testSearch,   'testActive'   => $testActive, 'testSort' => $testSort, 'testDir' => $testDir,
            'bkSearch'     => $bkSearch,     'bkExperiment' => $bkExperiment, 'bkGroup' => $bkGroup,
            'visSearch'    => $visSearch,    'visTest'      => $visTest, 'visGroup' => $visGroup, 'visStage' => $visStage, 'visSort' => $visSort, 'visDir' => $visDir,
        ]);
    }
}
