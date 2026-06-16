@extends('layouts.portal')

@section('title', 'Web Sales Search Queries')
@section('breadcrumb', 'Reports - Web Sales Search Queries')

@php
function sqSortUrl(string $col, string $currentSort, string $currentDir): string {
    $dir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $dir, 'page' => 1]);
}
function sqSortIcon(string $col, string $currentSort, string $currentDir): string {
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

    <select name="failed" class="form-select form-select-sm" style="width:160px;">
        <option value="">Failed: any</option>
        <option value="yes" {{ $filterFailed === 'yes' ? 'selected' : '' }}>Failed only</option>
        <option value="no"  {{ $filterFailed === 'no'  ? 'selected' : '' }}>Successful only</option>
    </select>

    <select name="reason" class="form-select form-select-sm" style="width:210px;">
        <option value="">All failure reasons</option>
        @foreach ($failureReasons as $reason)
        <option value="{{ $reason }}" {{ $filterReason === $reason ? 'selected' : '' }}>
            {{ str_replace('_', ' ', ucfirst($reason)) }}
        </option>
        @endforeach
    </select>

    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">Created from</label>
        <input type="date" name="date_from" class="form-control form-control-sm" style="width:145px;"
            value="{{ $dateFrom ?? '' }}">
        <label class="text-muted small mb-0">to</label>
        <input type="date" name="date_to" class="form-control form-control-sm" style="width:145px;"
            value="{{ $dateTo ?? '' }}">
    </div>

    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">Date Out</label>
        <input type="date" name="date_out_from" class="form-control form-control-sm" style="width:145px;"
            value="{{ $dateOutFrom ?? '' }}">
        <label class="text-muted small mb-0">to</label>
        <input type="date" name="date_out_to" class="form-control form-control-sm" style="width:145px;"
            value="{{ $dateOutTo ?? '' }}">
    </div>

    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">Date In</label>
        <input type="date" name="date_in_from" class="form-control form-control-sm" style="width:145px;"
            value="{{ $dateInFrom ?? '' }}">
        <label class="text-muted small mb-0">to</label>
        <input type="date" name="date_in_to" class="form-control form-control-sm" style="width:145px;"
            value="{{ $dateInTo ?? '' }}">
    </div>

    <button class="btn btn-sm btn-primary" type="submit">Filter</button>

    @if($filterFailed || $filterReason || $dateFrom || $dateTo || $dateOutFrom || $dateOutTo || $dateInFrom || $dateInTo)
    <a href="{{ request()->url() }}{{ request('search') ? '?search='.request('search') : '' }}"
       class="btn btn-sm btn-outline-secondary">Clear filters</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Web Sales Search Queries</span>
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
                            <a href="{{ sqSortUrl('id', $sort, $direction) }}" class="text-decoration-none text-dark">
                                ID {!! sqSortIcon('id', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>User ID</th>
                        <th>User IP</th>
                        <th>Product ID</th>
                        <th>
                            <a href="{{ sqSortUrl('date_out', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Date Out {!! sqSortIcon('date_out', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ sqSortUrl('date_in', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Date In {!! sqSortIcon('date_in', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ sqSortUrl('failed_date', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Failed Date {!! sqSortIcon('failed_date', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>Failure Reason</th>
                        <th>
                            <a href="{{ sqSortUrl('record_created_date', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Created {!! sqSortIcon('record_created_date', $sort, $direction) !!}
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($queries as $row)
                    <tr>
                        <td class="text-muted">{{ $row->id }}</td>
                        <td>{{ $row->user_id ?? '-' }}</td>
                        <td><code>{{ $row->user_ip ?? '-' }}</code></td>
                        <td>{{ $row->product_id ?? '-' }}</td>
                        <td>{{ $row->date_out ? \Carbon\Carbon::parse($row->date_out)->format('d M Y') : '-' }}</td>
                        <td>{{ $row->date_in  ? \Carbon\Carbon::parse($row->date_in)->format('d M Y')  : '-' }}</td>
                        <td>
                            @if ($row->failed_date)
                                <span class="text-danger">{{ \Carbon\Carbon::parse($row->failed_date)->format('d M Y H:i') }}</span>
                            @else
                                <span class="text-success">-</span>
                            @endif
                        </td>
                        <td>
                            @if ($row->reason_for_failure)
                                <span class="badge bg-warning text-dark">{{ $row->reason_for_failure }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-muted">{{ $row->record_created_date ? \Carbon\Carbon::parse($row->record_created_date)->format('d M Y H:i') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if ($queries->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">Showing {{ $queries->firstItem() }}-{{ $queries->lastItem() }} of {{ $queries->total() }} records</small>
        <div>{{ $queries->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif
</div>
@endsection
