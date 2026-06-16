@extends('layouts.portal')

@section('title', 'Vendor Deficits')
@section('breadcrumb', 'Billing - Processing - Vendor Deficits')

@php
function vdSort(string $col, string $cur, string $dir): string {
    $d = ($cur === $col && $dir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $d, 'page' => 1]);
}
function vdSortIcon(string $col, string $cur, string $dir): string {
    if ($cur !== $col) return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:.7rem;"></i>';
    return $dir === 'asc' ? '<i class="bi bi-sort-up ms-1" style="font-size:.75rem;"></i>' : '<i class="bi bi-sort-down ms-1" style="font-size:.75rem;"></i>';
}
@endphp

@section('content')

<form method="GET" class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
    <select name="status" class="form-select form-select-sm" style="width:150px;">
        <option value="">All statuses</option>
        @foreach(['PAID','PENDING','RESOLVED','DISPUTED'] as $s)
        <option value="{{ $s }}" {{ ($filterStatus ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
    </select>
    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">Date</label>
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
            <span>Vendor Deficit Records</span>
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted fw-normal">Edit dates and status inline and click Save to update.</small>
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
                            <th><a href="{{ vdSort('id',$sort,$direction) }}" class="text-decoration-none text-dark">ID {!! vdSortIcon('id',$sort,$direction) !!}</a></th>
                            <th>Sales ID</th>
                            <th><a href="{{ vdSort('amount',$sort,$direction) }}" class="text-decoration-none text-dark">Amount {!! vdSortIcon('amount',$sort,$direction) !!}</a></th>
                            <th><a href="{{ vdSort('date',$sort,$direction) }}" class="text-decoration-none text-dark">Date {!! vdSortIcon('date',$sort,$direction) !!}</a></th>
                            <th><a href="{{ vdSort('payment_due_by',$sort,$direction) }}" class="text-decoration-none text-dark">Payment Due By {!! vdSortIcon('payment_due_by',$sort,$direction) !!}</a></th>
                            <th><a href="{{ vdSort('recorded_on',$sort,$direction) }}" class="text-decoration-none text-dark">Recorded On {!! vdSortIcon('recorded_on',$sort,$direction) !!}</a></th>
                            <th>Last Updated</th>
                            <th><a href="{{ vdSort('status',$sort,$direction) }}" class="text-decoration-none text-dark">Status {!! vdSortIcon('status',$sort,$direction) !!}</a></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deficits as $row)
                        <tr data-id="{{ $row->id }}">
                            <td class="text-muted">{{ $row->id }}</td>
                            <td><code>{{ $row->sales_id }}</code></td>
                            <td>{{ number_format($row->amount, 2) }}</td>

                            <td>
                                <input type="text" class="form-control date-picker" data-field="date"
                                    value="{{ $row->date ? \Carbon\Carbon::parse($row->date)->format('Y-m-d') : '' }}"
                                    placeholder="Requires Date"
                                    onfocus="this.type='date'; this.placeholder='';"
                                    onblur="if(!this.value) { this.type='text'; this.placeholder='Requires Date'; }">
                            </td>

                            <td>
                                <input type="text" class="form-control date-picker" data-field="payment_due_by"
                                    value="{{ $row->payment_due_by ? \Carbon\Carbon::parse($row->payment_due_by)->format('Y-m-d') : '' }}"
                                    placeholder="Requires Date"
                                    onfocus="this.type='date'; this.placeholder='';"
                                    onblur="if(!this.value) { this.type='text'; this.placeholder='Requires Date'; }">
                            </td>

                            <td class="text-muted">
                                {{ $row->recorded_on ? \Carbon\Carbon::parse($row->recorded_on)->format('d M Y') : '-' }}
                            </td>

                            <td class="text-muted">
                                {{ $row->last_status_update ? \Carbon\Carbon::parse($row->last_status_update)->format('d M Y') : '-' }}
                            </td>

                            <td class="status-cell" style="min-width: 140px;">
                                @php
                                    $badgeClass = match($row->status) {
                                        'PAID'     => 'success',
                                        'RESOLVED' => 'info',
                                        'PENDING'  => 'warning',
                                        default    => 'secondary',
                                    };
                                @endphp
                                <select class="form-select form-select-sm status-select" data-field="status" data-badge="{{ $badgeClass }}">
                                    @foreach (['PAID', 'PENDING', 'RESOLVED'] as $option)
                                        <option value="{{ $option }}" {{ $row->status === $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
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
        @if ($deficits->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <small class="text-muted">
                    Showing {{ $deficits->firstItem() }}-{{ $deficits->lastItem() }} of {{ $deficits->total() }} records
                </small>
                <div>{{ $deficits->links('pagination::bootstrap-5') }}</div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    var updateRoute = "{{ route('portal.billing.processing.vendor-deficits.update') }}";

    $(document).on("click", ".save-btn", function () {
        let row = $(this).closest("tr");
        let id             = row.data("id");
        let date           = row.find("[data-field='date']").val();
        let paymentDueBy   = row.find("[data-field='payment_due_by']").val();
        let status         = row.find("[data-field='status']").val();

        $.ajax({
            url: updateRoute, type: "POST",
            data: {
                _token:          "{{ csrf_token() }}",
                _method:         "PUT",
                id:              id,
                date:            date,
                payment_due_by:  paymentDueBy,
                status:          status,
            },
            success: function (response) {
                alert("Deficit updated successfully!");
            },
            error: function (xhr) {
                alert("Failed to update. Please try again.");
                console.error(xhr.responseText);
            }
        });
    });
</script>
@endpush
