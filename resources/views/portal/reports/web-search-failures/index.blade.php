@extends('layouts.portal')

@section('title', 'Web Search Failures')
@section('breadcrumb', 'Reports - Web Search Failures')

@php
function sfSortUrl(string $col, string $currentSort, string $currentDir): string {
    $dir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $dir, 'page' => 1]);
}
function sfSortIcon(string $col, string $currentSort, string $currentDir): string {
    if ($currentSort !== $col) return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:.7rem;"></i>';
    return $currentDir === 'asc'
        ? '<i class="bi bi-sort-up ms-1" style="font-size:.75rem;"></i>'
        : '<i class="bi bi-sort-down ms-1" style="font-size:.75rem;"></i>';
}
$badgeMap = [
    'timeout'                  => 'danger',
    'validation_error'         => 'warning',
    'fraud_suspected'          => 'danger',
    'payment_declined'         => 'warning',
    'inventory_unavailable'    => 'info',
    'address_validation_failed'=> 'warning',
    'rate_limited'             => 'secondary',
    'null'                     => 'secondary',
];
@endphp

@section('content')

{{-- Filter bar --}}
<form method="GET" class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif

    <select name="reason" class="form-select form-select-sm" style="width:220px;">
        <option value="">All reasons</option>
        @foreach ($failureReasons as $r)
        <option value="{{ $r }}" {{ $filterReason === $r ? 'selected' : '' }}>
            {{ str_replace('_', ' ', ucfirst($r)) }}
        </option>
        @endforeach
    </select>

    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">Failed</label>
        <input type="date" name="failed_from" class="form-control form-control-sm" style="width:145px;"
            value="{{ $failedFrom ?? '' }}">
        <label class="text-muted small mb-0">to</label>
        <input type="date" name="failed_to" class="form-control form-control-sm" style="width:145px;"
            value="{{ $failedTo ?? '' }}">
    </div>

    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">Log</label>
        <input type="date" name="log_from" class="form-control form-control-sm" style="width:145px;"
            value="{{ $logFrom ?? '' }}">
        <label class="text-muted small mb-0">to</label>
        <input type="date" name="log_to" class="form-control form-control-sm" style="width:145px;"
            value="{{ $logTo ?? '' }}">
    </div>

    <button class="btn btn-sm btn-primary" type="submit">Filter</button>

    @if($filterReason || $failedFrom || $failedTo || $logFrom || $logTo)
    <a href="{{ request()->url() }}{{ request('search') ? '?search='.request('search') : '' }}"
       class="btn btn-sm btn-outline-secondary">Clear filters</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Web Search Failures</span>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-download"></i> Export CSV
            </a>
            @include('partials.search-bar')
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ sfSortUrl('log_timestamp', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Log Timestamp {!! sfSortIcon('log_timestamp', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>Original Hash</th>
                        <th>Decoded ID</th>
                        <th>
                            <a href="{{ sfSortUrl('failed_on', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Failed On {!! sfSortIcon('failed_on', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ sfSortUrl('reason', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Reason {!! sfSortIcon('reason', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>Form ID</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($failures as $row)
                    <tr>
                        <td class="text-muted">{{ $row->log_timestamp ? \Carbon\Carbon::parse($row->log_timestamp)->format('d M Y H:i:s') : '-' }}</td>
                        <td><code>{{ $row->original_hash ?? '-' }}</code></td>
                        <td>{{ $row->decoded_id ?? '-' }}</td>
                        <td>
                            @if ($row->failed_on)
                                <span class="text-danger">{{ \Carbon\Carbon::parse($row->failed_on)->format('d M Y H:i:s') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($row->reason)
                                @php $cls = $badgeMap[$row->reason] ?? 'secondary'; @endphp
                                <span class="badge bg-{{ $cls }}">{{ str_replace('_', ' ', ucfirst($row->reason)) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td><code>{{ $row->form_id ?? '-' }}</code></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if ($failures->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">Showing {{ $failures->firstItem() }}-{{ $failures->lastItem() }} of {{ $failures->total() }} records</small>
        <div>{{ $failures->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif
</div>
@endsection
