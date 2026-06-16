@extends('layouts.portal')

@section('title', 'Payments')
@section('breadcrumb', 'Billing - Processing - Payments')

@php
function paySort(string $col, string $cur, string $dir): string {
    $d = ($cur === $col && $dir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $d, 'page' => 1]);
}
function paySortIcon(string $col, string $cur, string $dir): string {
    if ($cur !== $col) return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:.7rem;"></i>';
    return $dir === 'asc' ? '<i class="bi bi-sort-up ms-1" style="font-size:.75rem;"></i>' : '<i class="bi bi-sort-down ms-1" style="font-size:.75rem;"></i>';
}
@endphp

@section('content')

<form method="GET" class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
    <select name="status" class="form-select form-select-sm" style="width:150px;">
        <option value="">All statuses</option>
        @foreach(['Paid','Underpaid','Pending'] as $s)
        <option value="{{ $s }}" {{ ($filterStatus ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
    </select>
    <select name="currency" class="form-select form-select-sm" style="width:120px;">
        <option value="">All currencies</option>
        @foreach(['EUR','GBP','TRY','USD'] as $c)
        <option value="{{ $c }}" {{ ($filterCurrency ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
        @endforeach
    </select>
    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">Paid</label>
        <input type="date" name="paid_from" class="form-control form-control-sm" style="width:145px;" value="{{ $paidFrom ?? '' }}">
        <label class="text-muted small mb-0">to</label>
        <input type="date" name="paid_to" class="form-control form-control-sm" style="width:145px;" value="{{ $paidTo ?? '' }}">
    </div>
    <button class="btn btn-sm btn-primary" type="submit">Filter</button>
    @if(($filterStatus ?? '') || ($filterCurrency ?? '') || ($paidFrom ?? '') || ($paidTo ?? ''))
    <a href="{{ request()->url() }}{{ request('search') ? '?search='.request('search') : '' }}" class="btn btn-sm btn-outline-secondary">Clear filters</a>
    @endif
</form>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Payment Records</span>
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
                            <th><a href="{{ paySort('vp.id',$sort,$direction) }}" class="text-decoration-none text-dark">ID {!! paySortIcon('vp.id',$sort,$direction) !!}</a></th>
                            <th>Sales ID</th>
                            <th>Vendor ID</th>
                            <th><a href="{{ paySort('vp.amount',$sort,$direction) }}" class="text-decoration-none text-dark">Amount {!! paySortIcon('vp.amount',$sort,$direction) !!}</a></th>
                            <th><a href="{{ paySort('vp.paid_date',$sort,$direction) }}" class="text-decoration-none text-dark">Date Paid-in {!! paySortIcon('vp.paid_date',$sort,$direction) !!}</a></th>
                            <th><a href="{{ paySort('fp.payment_date',$sort,$direction) }}" class="text-decoration-none text-dark">Date Paid-out {!! paySortIcon('fp.payment_date',$sort,$direction) !!}</a></th>
                            <th><a href="{{ paySort('vp.status',$sort,$direction) }}" class="text-decoration-none text-dark">Status {!! paySortIcon('vp.status',$sort,$direction) !!}</a></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $command)
                        <tr data-id="{{ $command->id }}">
                        <td class="text-muted">{{ $command->id }}</td>
                            <td><code>{{ $command->sales_id }}</code></td>
                            <td>{{ $command->vendor_id }}</td>
                            <td>{{ $command->currency }} {{ number_format($command->amount, 2) }}</td>

                            <td>
                                <input type="text" class="form-control date-picker" data-field="paid_date" value="{{ $command->paid_date ? \Carbon\Carbon::parse($command->paid_date)->format('Y-m-d') : '' }}" placeholder="Requires Date" onfocus="this.type='date'; this.placeholder='';" onblur="if(!this.value) { this.type='text'; this.placeholder='Requires Date'; }">
                            </td>

                            <td>
                                <input type="text" class="form-control date-picker" data-field="payment_date" value="{{ $command->payment_date ? \Carbon\Carbon::parse($command->payment_date)->format('Y-m-d') : '' }}" placeholder="Requires Date" onfocus="this.type='date'; this.placeholder='';" onblur="if(!this.value) { this.type='text'; this.placeholder='Requires Date'; }">
                            </td>

                            <td class="status-cell">
                                @php
                                    $badgeClass = match($command->status) {
                                        'Fully Completed'     => 'success',
                                        'Partially Completed' => 'warning',
                                        default               => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeClass }} status-badge">{{ $command->status }}</span>
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
                <div class="mb-0">
                    {{ $payments->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    var updateRoute = "{{ route('portal.billing.processing.payments.update') }}";

    $(document).on("click", ".save-btn", function () {
        let row = $(this).closest("tr");
        let id = row.data("id");

        let datePaidByVendor = row.find("[data-field='date_paid_by_vendor']").val();
        let datePaidToPlace = row.find("[data-field='date_paid_to_place']").val();

        $.ajax({
            url: updateRoute, type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                _method: "PUT",
                id: id,
                date_paid_by_vendor: datePaidByVendor,
                date_paid_to_place: datePaidToPlace
            },
            success: function (response) {
                alert("Dates updated successfully!");

                let statusCell = row.find("td.status-cell");
                let status = response.status;

                statusCell.text(status);
            },
            error: function (xhr) {
                alert("Failed to update. Please try again.");
                console.error(xhr.responseText);
            }
        });
    });
</script>
@endpush
