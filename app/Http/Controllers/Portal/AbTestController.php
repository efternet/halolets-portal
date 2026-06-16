<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Models\AbStage;
use App\Models\AbTest;
use App\Models\AbVisit;

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

        $testsQuery = AbTest::query()
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

        $experiments = AbTest::query()->orderBy('name')->pluck('name');
        $groups      = AbVisit::query()->distinct()->orderBy('group')->pluck('group');

        $breakdownQuery = AbVisit::breakdownQuery($bkSearch, $bkExperiment, $bkGroup);

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

        $visitGroups = AbVisit::query()->distinct()->orderBy('group')->pluck('group');
        $allTests    = AbTest::query()->orderBy('id')->get(['id', 'name']);

        $visitsQuery = AbVisit::listingQuery($visSearch, $visTest, $visGroup, $visStage, $visSort, $visDir);

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
        $stages = AbStage::query()->orderBy('id')->get();

        if ($this->wantsCsvExport() && $tab === 'stages') {
            return $this->streamCsv('ab-stages.csv',
                ['ID', 'Name'],
                $stages,
                fn ($r) => [$r->id, $r->name]
            );
        }

        $activeTestsCount = AbTest::query()->where('active', true)->count();
        $totalVisitsCount = AbVisit::query()->count();

        return view('portal.ab-tests.index', compact(
            'tab', 'tests', 'breakdown', 'visits', 'stages',
            'experiments', 'groups', 'visitGroups', 'allTests',
            'activeTestsCount', 'totalVisitsCount',
        ), [
            'testSearch'   => $testSearch,   'testActive'   => $testActive, 'testSort' => $testSort, 'testDir' => $testDir,
            'bkSearch'     => $bkSearch,     'bkExperiment' => $bkExperiment, 'bkGroup' => $bkGroup,
            'visSearch'    => $visSearch,    'visTest'      => $visTest, 'visGroup' => $visGroup, 'visStage' => $visStage, 'visSort' => $visSort, 'visDir' => $visDir,
        ]);
    }
}
