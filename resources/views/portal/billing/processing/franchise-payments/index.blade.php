@extends('layouts.portal')

@section('title', 'Franchise Payments')
@section('breadcrumb', 'Billing - Processing - Franchise Payments')

@php
function fpSort(string $col, string $cur, string $dir): string {
    $d = ($cur === $col && $dir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $d, 'page' => 1]);
}
function fpSortIcon(string $col, string $cur, string $dir): string {
    if ($cur !== $col) return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:.7rem;"></i>';
    return $dir === 'asc' ? '<i class="bi bi-sort-up ms-1" style="font-size:.75rem;"></i>' : '<i class="bi bi-sort-down ms-1" style="font-size:.75rem;"></i>';
}
@endphp

@section('content')

<form method="GET" class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">Requested</label>
        <input type="date" name="requested_from" class="form-control form-control-sm" style="width:145px;" value="{{ $requestedFrom ?? '' }}">
        <label class="text-muted small mb-0">to</label>
        <input type="date" name="requested_to" class="form-control form-control-sm" style="width:145px;" value="{{ $requestedTo ?? '' }}">
    </div>
    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">Payment</label>
        <input type="date" name="payment_from" class="form-control form-control-sm" style="width:145px;" value="{{ $paymentFrom ?? '' }}">
        <label class="text-muted small mb-0">to</label>
        <input type="date" name="payment_to" class="form-control form-control-sm" style="width:145px;" value="{{ $paymentTo ?? '' }}">
    </div>
    <button class="btn btn-sm btn-primary" type="submit">Filter</button>
    @if(($requestedFrom ?? '') || ($requestedTo ?? '') || ($paymentFrom ?? '') || ($paymentTo ?? ''))
    <a href="{{ request()->url() }}{{ request('search') ? '?search='.request('search') : '' }}" class="btn btn-sm btn-outline-secondary">Clear filters</a>
    @endif
</form>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Franchise Payment Records</span>
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted fw-normal">Edit dates inline and click Save to update.</small>
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
                            <th><a href="{{ fpSort('id',$sort,$direction) }}" class="text-decoration-none text-dark">ID {!! fpSortIcon('id',$sort,$direction) !!}</a></th>
                            <th>Sales ID</th>
                            <th><a href="{{ fpSort('amount',$sort,$direction) }}" class="text-decoration-none text-dark">Amount {!! fpSortIcon('amount',$sort,$direction) !!}</a></th>
                            <th><a href="{{ fpSort('requested_on',$sort,$direction) }}" class="text-decoration-none text-dark">Requested On {!! fpSortIcon('requested_on',$sort,$direction) !!}</a></th>
                            <th><a href="{{ fpSort('payment_date',$sort,$direction) }}" class="text-decoration-none text-dark">Payment Date {!! fpSortIcon('payment_date',$sort,$direction) !!}</a></th>
                            <th>Report ID</th>
                            <th><a href="{{ fpSort('recorded_on',$sort,$direction) }}" class="text-decoration-none text-dark">Recorded On {!! fpSortIcon('recorded_on',$sort,$direction) !!}</a></th>
                            <th><a href="{{ fpSort('last_processed',$sort,$direction) }}" class="text-decoration-none text-dark">Last Processed {!! fpSortIcon('last_processed',$sort,$direction) !!}</a></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $row)
                        <tr data-id="{{ $row->id }}">
                            <td class="text-muted">{{ $row->id }}</td>
                            <td><code>{{ $row->sales_id }}</code></td>
                            <td>{{ number_format($row->amount, 2) }}</td>

                            <td>
                                <input type="text" class="form-control date-picker" data-field="requested_on"
                                    value="{{ $row->requested_on ? \Carbon\Carbon::parse($row->requested_on)->format('Y-m-d') : '' }}"
                                    placeholder="Requires Date"
                                    onfocus="this.type='date'; this.placeholder='';"
                                    onblur="if(!this.value) { this.type='text'; this.placeholder='Requires Date'; }">
                            </td>

                            <td>
                                <input type="text" class="form-control date-picker" data-field="payment_date"
                                    value="{{ $row->payment_date ? \Carbon\Carbon::parse($row->payment_date)->format('Y-m-d') : '' }}"
                                    placeholder="Requires Date"
                                    onfocus="this.type='date'; this.placeholder='';"
                                    onblur="if(!this.value) { this.type='text'; this.placeholder='Requires Date'; }">
                            </td>

                            <td class="text-muted">{{ $row->franchise_report_id ?? '-' }}</td>

                            <td class="text-muted">
                                {{ $row->recorded_on ? \Carbon\Carbon::parse($row->recorded_on)->format('d M Y') : '-' }}
                            </td>

                            <td class="text-muted">
                                {{ $row->last_processed ? \Carbon\Carbon::parse($row->last_processed)->format('d M Y') : '-' }}
                            </td>

                            <td>
                                <button class="btn btn-sm btn-primary save-btn">Save</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if ($payments->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <small class="text-muted">
                    Showing {{ $payments->firstItem() }}-{{ $payments->lastItem() }} of {{ $payments->total() }} records
                </small>
                <div>{{ $payments->links('pagination::bootstrap-5') }}</div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    var updateRoute = "{{ route('portal.billing.processing.franchise-payments.update') }}";

    $(document).on("click", ".save-btn", function () {
        let row = $(this).closest("tr");
        let id           = row.data("id");
        let requestedOn  = row.find("[data-field='requested_on']").val();
        let paymentDate  = row.find("[data-field='payment_date']").val();

        $.ajax({
            url: updateRoute, type: "POST",
            data: {
                _token:       "{{ csrf_token() }}",
                _method:      "PUT",
                id:           id,
                requested_on: requestedOn,
                payment_date: paymentDate,
            },
            success: function (response) {
                alert("Payment updated successfully!");
            },
            error: function (xhr) {
                alert("Failed to update. Please try again.");
                console.error(xhr.responseText);
            }
        });
    });
</script>
@endpush
