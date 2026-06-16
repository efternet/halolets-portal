@extends('layouts.portal')

@section('title', 'Franchise Dispatch Reports')
@section('breadcrumb', 'Reports - Franchise Dispatch Reports')

@php
function fdrSort(string $col, string $cur, string $dir): string {
    $d = ($cur === $col && $dir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $d, 'page' => 1]);
}
function fdrSortIcon(string $col, string $cur, string $dir): string {
    if ($cur !== $col) return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:.7rem;"></i>';
    return $dir === 'asc' ? '<i class="bi bi-sort-up ms-1" style="font-size:.75rem;"></i>' : '<i class="bi bi-sort-down ms-1" style="font-size:.75rem;"></i>';
}
@endphp

@section('content')

<form method="GET" class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
    <select name="currency" class="form-select form-select-sm" style="width:140px;">
        <option value="">All currencies</option>
        @foreach ($currencies as $c)
        <option value="{{ $c }}" {{ ($filterCurrency ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
        @endforeach
    </select>
    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">Dispatch date</label>
        <input type="date" name="date_from" class="form-control form-control-sm" style="width:145px;" value="{{ $dateFrom ?? '' }}">
        <label class="text-muted small mb-0">to</label>
        <input type="date" name="date_to" class="form-control form-control-sm" style="width:145px;" value="{{ $dateTo ?? '' }}">
    </div>
    <button class="btn btn-sm btn-primary" type="submit">Filter</button>
    @if(($filterCurrency ?? '') || ($dateFrom ?? '') || ($dateTo ?? ''))
    <a href="{{ request()->url() }}{{ request('search') ? '?search='.request('search') : '' }}" class="btn btn-sm btn-outline-secondary">Clear filters</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Franchise Dispatch Reports</span>
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
                        <th><a href="{{ fdrSort('id',$sort,$direction) }}" class="text-decoration-none text-dark">ID {!! fdrSortIcon('id',$sort,$direction) !!}</a></th>
                        <th>Franchise ID</th>
                        <th>Filename</th>
                        <th>Currency</th>
                        <th><a href="{{ fdrSort('dispatch_report_date',$sort,$direction) }}" class="text-decoration-none text-dark">Dispatch Date {!! fdrSortIcon('dispatch_report_date',$sort,$direction) !!}</a></th>
                        <th><a href="{{ fdrSort('created_at',$sort,$direction) }}" class="text-decoration-none text-dark">Created {!! fdrSortIcon('created_at',$sort,$direction) !!}</a></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $row)
                    <tr>
                        <td class="text-muted">{{ $row->id }}</td>
                        <td>{{ $row->franchise_id }}</td>
                        <td><code>{{ $row->filename }}</code></td>
                        <td><span class="badge bg-secondary">{{ $row->currency }}</span></td>
                        <td>{{ $row->dispatch_report_date ? \Carbon\Carbon::parse($row->dispatch_report_date)->format('d M Y') : '-' }}</td>
                        <td class="text-muted">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y') }}</td>
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
