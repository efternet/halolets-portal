@extends('layouts.portal')

@section('title', 'Vendor Reports')
@section('breadcrumb', 'Reports - Vendor Reports')

@php
function vrSort(string $col, string $cur, string $dir): string {
    $d = ($cur === $col && $dir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $d, 'page' => 1]);
}
function vrSortIcon(string $col, string $cur, string $dir): string {
    if ($cur !== $col) return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:.7rem;"></i>';
    return $dir === 'asc' ? '<i class="bi bi-sort-up ms-1" style="font-size:.75rem;"></i>' : '<i class="bi bi-sort-down ms-1" style="font-size:.75rem;"></i>';
}
@endphp

@section('content')

<form method="GET" class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
    <select name="status" class="form-select form-select-sm" style="width:160px;">
        <option value="">All statuses</option>
        @foreach ($statuses as $s)
        <option value="{{ $s }}" {{ ($filterStatus ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
    </select>
    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">Reported on</label>
        <input type="date" name="date_from" class="form-control form-control-sm" style="width:145px;" value="{{ $dateFrom ?? '' }}">
        <label class="text-muted small mb-0">to</label>
        <input type="date" name="date_to" class="form-control form-control-sm" style="width:145px;" value="{{ $dateTo ?? '' }}">
    </div>
    <button class="btn btn-sm btn-primary" type="submit">Filter</button>
    @if(($filterStatus ?? '') || ($dateFrom ?? '') || ($dateTo ?? ''))
    <a href="{{ request()->url() }}{{ request('search') ? '?search='.request('search') : '' }}" class="btn btn-sm btn-outline-secondary">Clear filters</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Vendor Reports</span>
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
                        <th><a href="{{ vrSort('id',$sort,$direction) }}" class="text-decoration-none text-dark">ID {!! vrSortIcon('id',$sort,$direction) !!}</a></th>
                        <th>Filename</th>
                        <th><a href="{{ vrSort('reported_on',$sort,$direction) }}" class="text-decoration-none text-dark">Reported On {!! vrSortIcon('reported_on',$sort,$direction) !!}</a></th>
                        <th><a href="{{ vrSort('report_last_requested_on',$sort,$direction) }}" class="text-decoration-none text-dark">Last Requested {!! vrSortIcon('report_last_requested_on',$sort,$direction) !!}</a></th>
                        <th><a href="{{ vrSort('report_first_processed',$sort,$direction) }}" class="text-decoration-none text-dark">First Processed {!! vrSortIcon('report_first_processed',$sort,$direction) !!}</a></th>
                        <th><a href="{{ vrSort('report_last_processed',$sort,$direction) }}" class="text-decoration-none text-dark">Last Processed {!! vrSortIcon('report_last_processed',$sort,$direction) !!}</a></th>
                        <th><a href="{{ vrSort('report_status',$sort,$direction) }}" class="text-decoration-none text-dark">Status {!! vrSortIcon('report_status',$sort,$direction) !!}</a></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $row)
                    <tr>
                        <td class="text-muted">{{ $row->id }}</td>
                        <td><code>{{ $row->filename }}</code></td>
                        <td>{{ $row->reported_on ? \Carbon\Carbon::parse($row->reported_on)->format('d M Y') : '-' }}</td>
                        <td>{{ $row->report_last_requested_on ? \Carbon\Carbon::parse($row->report_last_requested_on)->format('d M Y') : '-' }}</td>
                        <td>{{ $row->report_first_processed ? \Carbon\Carbon::parse($row->report_first_processed)->format('d M Y') : '-' }}</td>
                        <td>{{ $row->report_last_processed ? \Carbon\Carbon::parse($row->report_last_processed)->format('d M Y') : '-' }}</td>
                        <td>
                            @php
                                $badgeClass = match($row->report_status) {
                                    'REISSUED'  => 'info',
                                    'COMPLETED' => 'success',
                                    'PENDING'   => 'warning',
                                    default     => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $badgeClass }}">{{ $row->report_status ?? '-' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if ($reports->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">Showing {{ $reports->firstItem() }}-{{ $reports->lastItem() }} of {{ $reports->total() }} records</small>
        <div>{{ $reports->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif
</div>
@endsection
