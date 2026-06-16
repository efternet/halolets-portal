@extends('layouts.portal')

@section('title', 'Vendor Sales')
@section('breadcrumb', 'Reports - Vendor Sales')

@php
function vsSort(string $col, string $cur, string $dir): string {
    $d = ($cur === $col && $dir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $d, 'page' => 1]);
}
function vsSortIcon(string $col, string $cur, string $dir): string {
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
        <label class="text-muted small mb-0">Sales date</label>
        <input type="date" name="sales_from" class="form-control form-control-sm" style="width:145px;" value="{{ $salesFrom ?? '' }}">
        <label class="text-muted small mb-0">to</label>
        <input type="date" name="sales_to" class="form-control form-control-sm" style="width:145px;" value="{{ $salesTo ?? '' }}">
    </div>
    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">Amount</label>
        <input type="number" step="0.01" name="amount_min" class="form-control form-control-sm" style="width:100px;" placeholder="Min" value="{{ $amountMin ?? '' }}">
        <label class="text-muted small mb-0">-</label>
        <input type="number" step="0.01" name="amount_max" class="form-control form-control-sm" style="width:100px;" placeholder="Max" value="{{ $amountMax ?? '' }}">
    </div>
    <button class="btn btn-sm btn-primary" type="submit">Filter</button>
    @if(($filterCurrency ?? '') || ($salesFrom ?? '') || ($salesTo ?? '') || ($amountMin ?? '') || ($amountMax ?? ''))
    <a href="{{ request()->url() }}{{ request('search') ? '?search='.request('search') : '' }}" class="btn btn-sm btn-outline-secondary">Clear filters</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Vendor Sales</span>
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
                        <th><a href="{{ vsSort('vs.id',$sort,$direction) }}" class="text-decoration-none text-dark">ID {!! vsSortIcon('vs.id',$sort,$direction) !!}</a></th>
                        <th>Customer</th>
                        <th>Reference</th>
                        <th>City</th>
                        <th>Reg. Code</th>
                        <th><a href="{{ vsSort('vs.amount',$sort,$direction) }}" class="text-decoration-none text-dark">Amount {!! vsSortIcon('vs.amount',$sort,$direction) !!}</a></th>
                        <th><a href="{{ vsSort('vs.sales_date',$sort,$direction) }}" class="text-decoration-none text-dark">Sales Date {!! vsSortIcon('vs.sales_date',$sort,$direction) !!}</a></th>
                        <th><a href="{{ vsSort('vs.date_out',$sort,$direction) }}" class="text-decoration-none text-dark">Date Out {!! vsSortIcon('vs.date_out',$sort,$direction) !!}</a></th>
                        <th><a href="{{ vsSort('vs.date_in',$sort,$direction) }}" class="text-decoration-none text-dark">Date In {!! vsSortIcon('vs.date_in',$sort,$direction) !!}</a></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sales as $row)
                    <tr>
                        <td class="text-muted">{{ $row->id }}</td>
                        <td>{{ $row->customer }}</td>
                        <td><code>{{ $row->reference }}</code></td>
                        <td>{{ $row->city ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $row->regulatory_code }}</span></td>
                        <td>{{ $row->currency }} {{ number_format($row->amount, 2) }}</td>
                        <td>{{ $row->sales_date ? \Carbon\Carbon::parse($row->sales_date)->format('d M Y') : '-' }}</td>
                        <td>{{ $row->date_out ? \Carbon\Carbon::parse($row->date_out)->format('d M Y') : '-' }}</td>
                        <td>{{ $row->date_in ? \Carbon\Carbon::parse($row->date_in)->format('d M Y') : '-' }}</td>
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
