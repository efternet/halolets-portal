@extends('layouts.portal')

@section('title', 'AB Tests')
@section('breadcrumb', 'AB Testing')

@php
function abSort(string $prefix, string $col, string $cur, string $dir): string {
    $d = ($cur === $col && $dir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(["{$prefix}_sort" => $col, "{$prefix}_dir" => $d, 'page' => 1, 'tab' => request('tab', 'tests')]);
}
function abSortIcon(string $col, string $cur, string $dir): string {
    if ($cur !== $col) return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:.7rem;"></i>';
    return $dir === 'asc' ? '<i class="bi bi-sort-up ms-1" style="font-size:.75rem;"></i>' : '<i class="bi bi-sort-down ms-1" style="font-size:.75rem;"></i>';
}
@endphp

@section('content')

{{-- Summary cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="card shadow-sm text-center py-3">
            <div class="fs-2 fw-bold text-primary">{{ $tests->total() }}</div>
            <div class="text-muted small">Total Tests</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card shadow-sm text-center py-3">
            <div class="fs-2 fw-bold text-success">{{ $activeTestsCount }}</div>
            <div class="text-muted small">Active Tests</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card shadow-sm text-center py-3">
            <div class="fs-2 fw-bold" style="color:var(--hl-accent);">{{ $stages->count() }}</div>
            <div class="text-muted small">Funnel Stages</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card shadow-sm text-center py-3">
            <div class="fs-2 fw-bold text-secondary">{{ number_format($totalVisitsCount) }}</div>
            <div class="text-muted small">Total Visits Logged</div>
        </div>
    </div>
</div>

{{-- Tab nav --}}
<ul class="nav nav-tabs mb-0" id="abTabs">
    @foreach(['tests' => 'Tests', 'breakdown' => 'Breakdown', 'visits' => 'Visits', 'stages' => 'Stages'] as $key => $label)
    <li class="nav-item">
        <a class="nav-link {{ $tab === $key ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['tab' => $key]) }}">
            {{ $label }}
        </a>
    </li>
    @endforeach
</ul>

<div class="card shadow-sm" style="border-top-left-radius:0;">
    <div class="card-body p-0">

    {{-- ====== TESTS TAB ====== --}}
    @if($tab === 'tests')
    <div class="p-3 border-bottom d-flex flex-wrap gap-2 align-items-center">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-center w-100">
            <input type="hidden" name="tab" value="tests">
            <input type="text" name="test_search" class="form-control form-control-sm" style="width:220px;"
                placeholder="Search by name..." value="{{ $testSearch ?? '' }}">
            <select name="test_active" class="form-select form-select-sm" style="width:150px;">
                <option value="">All statuses</option>
                <option value="1" {{ ($testActive ?? '') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ ($testActive ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button class="btn btn-sm btn-primary" type="submit">Filter</button>
            @if(($testSearch ?? '') || ($testActive ?? '') !== '')
            <a href="{{ request()->url() }}?tab=tests" class="btn btn-sm btn-outline-secondary">Clear</a>
            @endif
            <a href="{{ request()->fullUrlWithQuery(['export' => 1, 'tab' => 'tests']) }}" class="btn btn-sm btn-outline-success ms-auto">
                <i class="bi bi-download"></i> Export CSV
            </a>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th><a href="{{ abSort('test', 'id', $testSort, $testDir) }}" class="text-decoration-none text-dark">ID {!! abSortIcon('id', $testSort, $testDir) !!}</a></th>
                    <th><a href="{{ abSort('test', 'name', $testSort, $testDir) }}" class="text-decoration-none text-dark">Name {!! abSortIcon('name', $testSort, $testDir) !!}</a></th>
                    <th><a href="{{ abSort('test', 'active', $testSort, $testDir) }}" class="text-decoration-none text-dark">Status {!! abSortIcon('active', $testSort, $testDir) !!}</a></th>
                    <th><a href="{{ abSort('test', 'user_group', $testSort, $testDir) }}" class="text-decoration-none text-dark">User Group {!! abSortIcon('user_group', $testSort, $testDir) !!}</a></th>
                    <th><a href="{{ abSort('test', 'active_at', $testSort, $testDir) }}" class="text-decoration-none text-dark">Active From {!! abSortIcon('active_at', $testSort, $testDir) !!}</a></th>
                    <th>Deactivated On</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tests as $row)
                <tr>
                    <td class="text-muted">{{ $row->id }}</td>
                    <td>{{ $row->name }}</td>
                    <td>
                        @if($row->active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $row->user_group ?? '-' }}</td>
                    <td>{{ $row->active_at ? \Carbon\Carbon::parse($row->active_at)->format('d M Y H:i') : '-' }}</td>
                    <td>{{ $row->deactivated_on ? \Carbon\Carbon::parse($row->deactivated_on)->format('d M Y H:i') : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No tests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tests->hasPages())
    <div class="d-flex justify-content-between align-items-center p-3 border-top">
        <small class="text-muted">Showing {{ $tests->firstItem() }}-{{ $tests->lastItem() }} of {{ $tests->total() }} tests</small>
        <div>{{ $tests->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif

    {{-- ====== BREAKDOWN TAB ====== --}}
    @elseif($tab === 'breakdown')
    <div class="p-3 border-bottom d-flex flex-wrap gap-2 align-items-center">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-center w-100">
            <input type="hidden" name="tab" value="breakdown">
            <input type="text" name="bk_search" class="form-control form-control-sm" style="width:200px;"
                placeholder="Search experiment..." value="{{ $bkSearch ?? '' }}">
            <select name="bk_experiment" class="form-select form-select-sm" style="width:200px;">
                <option value="">All experiments</option>
                @foreach($experiments as $e)
                <option value="{{ $e }}" {{ ($bkExperiment ?? '') === $e ? 'selected' : '' }}>{{ $e }}</option>
                @endforeach
            </select>
            <select name="bk_group" class="form-select form-select-sm" style="width:150px;">
                <option value="">All groups</option>
                @foreach($groups as $g)
                <option value="{{ $g }}" {{ ($bkGroup ?? '') === $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-primary" type="submit">Filter</button>
            @if(($bkSearch ?? '') || ($bkExperiment ?? '') || ($bkGroup ?? ''))
            <a href="{{ request()->url() }}?tab=breakdown" class="btn btn-sm btn-outline-secondary">Clear</a>
            @endif
            <a href="{{ request()->fullUrlWithQuery(['export' => 1, 'tab' => 'breakdown']) }}" class="btn btn-sm btn-outline-success ms-auto">
                <i class="bi bi-download"></i> Export CSV
            </a>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Experiment</th>
                        <th>Group</th>
                        <th>Stage</th>
                        <th>Total Visits</th>
                        <th title="% of this group's entry-stage visitors who reached this stage">Funnel %</th>
                    </tr>
                </thead>
            <tbody>
                @forelse($breakdown as $row)
                <tr>
                    <td><code>{{ $row->experiment }}</code></td>
                    <td>
                        @php
                            $gc = match(true) {
                                str_contains($row->group, 'variant_A') || str_contains($row->group, 'A') => 'primary',
                                str_contains($row->group, 'variant_B') || str_contains($row->group, 'B') => 'info',
                                str_contains($row->group, 'control')   => 'secondary',
                                default => 'light text-dark border',
                            };
                        @endphp
                        <span class="badge bg-{{ $gc }}">{{ $row->group }}</span>
                    </td>
                    <td>{{ $row->stage_name ?? 'Stage '.$row->stage }}</td>
                    <td>{{ number_format($row->total_visits) }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px; max-width:80px;">
                                <div class="progress-bar bg-primary" style="width:{{ min($row->percentage_visits, 100) }}%"></div>
                            </div>
                            <span class="small">{{ number_format($row->percentage_visits, 1) }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No breakdown data found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($breakdown->hasPages())
    <div class="d-flex justify-content-between align-items-center p-3 border-top">
        <small class="text-muted">Showing {{ $breakdown->firstItem() }}-{{ $breakdown->lastItem() }} of {{ $breakdown->total() }} records</small>
        <div>{{ $breakdown->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif

    {{-- ====== VISITS TAB ====== --}}
    @elseif($tab === 'visits')
    <div class="p-3 border-bottom">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
            <input type="hidden" name="tab" value="visits">
            <input type="text" name="vis_search" class="form-control form-control-sm" style="width:180px;"
                placeholder="IP or test name..." value="{{ $visSearch ?? '' }}">
            <select name="vis_test" class="form-select form-select-sm" style="width:220px;">
                <option value="">All tests</option>
                @foreach($allTests as $t)
                <option value="{{ $t->id }}" {{ ($visTest ?? '') == $t->id ? 'selected' : '' }}>{{ $t->id }} - {{ Str::limit($t->name, 30) }}</option>
                @endforeach
            </select>
            <select name="vis_group" class="form-select form-select-sm" style="width:150px;">
                <option value="">All groups</option>
                @foreach($visitGroups as $g)
                <option value="{{ $g }}" {{ ($visGroup ?? '') === $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>
            <select name="vis_stage" class="form-select form-select-sm" style="width:160px;">
                <option value="">All stages</option>
                @foreach($stages as $s)
                <option value="{{ $s->id }}" {{ ($visStage ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-primary" type="submit">Filter</button>
            @if(($visSearch ?? '') || ($visTest ?? '') || ($visGroup ?? '') || ($visStage ?? ''))
            <a href="{{ request()->url() }}?tab=visits" class="btn btn-sm btn-outline-secondary">Clear</a>
            @endif
            <a href="{{ request()->fullUrlWithQuery(['export' => 1, 'tab' => 'visits']) }}" class="btn btn-sm btn-outline-success ms-auto">
                <i class="bi bi-download"></i> Export CSV
            </a>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th><a href="{{ abSort('vis', 'av.id', $visSort, $visDir) }}" class="text-decoration-none text-dark">ID {!! abSortIcon('av.id', $visSort, $visDir) !!}</a></th>
                    <th>IP Address</th>
                    <th>Test</th>
                    <th><a href="{{ abSort('vis', 'av.group', $visSort, $visDir) }}" class="text-decoration-none text-dark">Group {!! abSortIcon('av.group', $visSort, $visDir) !!}</a></th>
                    <th><a href="{{ abSort('vis', 'av.stage', $visSort, $visDir) }}" class="text-decoration-none text-dark">Stage {!! abSortIcon('av.stage', $visSort, $visDir) !!}</a></th>
                    <th><a href="{{ abSort('vis', 'av.created_at', $visSort, $visDir) }}" class="text-decoration-none text-dark">Visited {!! abSortIcon('av.created_at', $visSort, $visDir) !!}</a></th>
                </tr>
            </thead>
            <tbody>
                @forelse($visits as $row)
                <tr>
                    <td class="text-muted">{{ $row->id }}</td>
                    <td><code>{{ $row->ip_address }}</code></td>
                    <td>
                        @if($row->test_name)
                            <span class="badge bg-light text-dark border" title="{{ $row->test_name }}">
                                #{{ $row->ab_test_id }} {{ Str::limit($row->test_name, 25) }}
                            </span>
                        @else
                            <span class="text-muted">#{{ $row->ab_test_id }}</span>
                        @endif
                    </td>
                    <td><span class="badge bg-secondary">{{ $row->group }}</span></td>
                    <td>{{ $row->stage_name ?? 'Stage '.$row->stage }}</td>
                    <td class="text-muted">{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d M Y H:i') : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No visits found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($visits->hasPages())
    <div class="d-flex justify-content-between align-items-center p-3 border-top">
        <small class="text-muted">Showing {{ $visits->firstItem() }}-{{ $visits->lastItem() }} of {{ $visits->total() }} visits</small>
        <div>{{ $visits->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif

    {{-- ====== STAGES TAB ====== --}}
    @else
    <div class="p-3 border-bottom d-flex justify-content-end">
        <a href="{{ request()->fullUrlWithQuery(['export' => 1, 'tab' => 'stages']) }}" class="btn btn-sm btn-outline-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Stage Name</th>
                    <th>Business Type</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stages as $row)
                <tr>
                    <td class="text-muted">{{ $row->id }}</td>
                    <td><strong>{{ $row->name }}</strong></td>
                    <td><span class="badge bg-light text-dark border">{{ $row->business_type }}</span></td>
                    <td class="text-muted">{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d M Y') : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No stages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    </div>{{-- card-body --}}
</div>
@endsection
