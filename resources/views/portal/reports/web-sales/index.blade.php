@extends('layouts.portal')

@section('title', 'Web Sales')
@section('breadcrumb', 'Reports - Web Sales')

@php
function wsSortUrl(string $col, string $currentSort, string $currentDir): string {
    $dir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $dir, 'page' => 1]);
}
function wsSortIcon(string $col, string $currentSort, string $currentDir): string {
    if ($currentSort !== $col) return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:.7rem;"></i>';
    return $currentDir === 'asc'
        ? '<i class="bi bi-sort-up ms-1" style="font-size:.75rem;"></i>'
        : '<i class="bi bi-sort-down ms-1" style="font-size:.75rem;"></i>';
}
@endphp

@section('content')

{{-- Filter bar --}}
<form method="GET" class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif

    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">From</label>
        <input type="date" name="date_from" class="form-control form-control-sm" style="width:145px;"
            value="{{ $dateFrom ?? '' }}">
    </div>
    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">To</label>
        <input type="date" name="date_to" class="form-control form-control-sm" style="width:145px;"
            value="{{ $dateTo ?? '' }}">
    </div>
    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">Cost</label>
        <input type="number" name="cost_min" class="form-control form-control-sm" style="width:90px;"
            placeholder="Min" step="0.01" value="{{ $costMin ?? '' }}">
        <span class="text-muted small">-</span>
        <input type="number" name="cost_max" class="form-control form-control-sm" style="width:90px;"
            placeholder="Max" step="0.01" value="{{ $costMax ?? '' }}">
    </div>

    <button class="btn btn-sm btn-primary" type="submit">Filter</button>

    @if($dateFrom || $dateTo || $costMin !== null && $costMin !== '' || $costMax !== null && $costMax !== '')
    <a href="{{ request()->url() }}{{ request('search') ? '?search='.request('search') : '' }}"
       class="btn btn-sm btn-outline-secondary">Clear filters</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Web Sales</span>
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
                            <a href="{{ wsSortUrl('id', $sort, $direction) }}" class="text-decoration-none text-dark">
                                ID {!! wsSortIcon('id', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>Query ID</th>
                        <th>User ID</th>
                        <th>User IP</th>
                        <th>Product ID</th>
                        <th>
                            <a href="{{ wsSortUrl('total_cost', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Total Cost {!! wsSortIcon('total_cost', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ wsSortUrl('sold_at', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Sold At {!! wsSortIcon('sold_at', $sort, $direction) !!}
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sales as $row)
                    <tr>
                        <td class="text-muted">{{ $row->id }}</td>
                        <td><code>{{ $row->query_id ?? '-' }}</code></td>
                        <td>{{ $row->user_id ?? '-' }}</td>
                        <td><code>{{ $row->user_ip ?? '-' }}</code></td>
                        <td>{{ $row->product_id ?? '-' }}</td>
                        <td>{{ $row->total_cost !== null ? number_format($row->total_cost, 2) : '-' }}</td>
                        <td>{{ $row->sold_at ? \Carbon\Carbon::parse($row->sold_at)->format('d M Y H:i') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if ($sales->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">Showing {{ $sales->firstItem() }}-{{ $sales->lastItem() }} of {{ $sales->total() }} records</small>
        <div>{{ $sales->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif
</div>
@endsection
