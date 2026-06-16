@extends('layouts.portal')

@section('title', 'Businesses')
@section('breadcrumb', 'Business Accounts - Businesses')

@php
function bizSort(string $col, string $cur, string $dir): string {
    $d = ($cur === $col && $dir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $d, 'page' => 1]);
}
function bizSortIcon(string $col, string $cur, string $dir): string {
    if ($cur !== $col) return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:.7rem;"></i>';
    return $dir === 'asc' ? '<i class="bi bi-sort-up ms-1" style="font-size:.75rem;"></i>' : '<i class="bi bi-sort-down ms-1" style="font-size:.75rem;"></i>';
}
@endphp

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Businesses</span>
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
                        <th><a href="{{ bizSort('b.id',$sort,$direction) }}" class="text-decoration-none text-dark">ID {!! bizSortIcon('b.id',$sort,$direction) !!}</a></th>
                        <th><a href="{{ bizSort('b.name',$sort,$direction) }}" class="text-decoration-none text-dark">Business Name {!! bizSortIcon('b.name',$sort,$direction) !!}</a></th>
                        <th><a href="{{ bizSort('customer_count',$sort,$direction) }}" class="text-decoration-none text-dark">Contacts {!! bizSortIcon('customer_count',$sort,$direction) !!}</a></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($businesses as $row)
                    <tr data-id="{{ $row->id }}">
                        <td class="text-muted">{{ $row->id }}</td>
                        <td style="min-width:260px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="name" value="{{ $row->name }}">
                        </td>
                        <td>
                            <a href="{{ route('portal.business-customers.index', ['business_id' => $row->id]) }}"
                               class="badge bg-primary text-decoration-none">
                                {{ $row->customer_count }}
                            </a>
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
    @if ($businesses->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">Showing {{ $businesses->firstItem() }}-{{ $businesses->lastItem() }} of {{ $businesses->total() }} businesses</small>
        <div>{{ $businesses->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
var baseUrl = "{{ url('portal/businesses') }}";

$(document).on("click", ".save-btn", function () {
    var row = $(this).closest("tr");
    var btn = $(this);
    var id  = row.data("id");
    btn.prop("disabled", true).text("Saving...");

    $.ajax({
        url: baseUrl + "/" + id,
        type: "POST",
        data: {
            _token:  "{{ csrf_token() }}",
            _method: "PUT",
            name:    row.find("[data-field='name']").val(),
        },
        success: function () { btn.prop("disabled", false).text("Save"); },
        error:   function () { alert("Failed to save."); btn.prop("disabled", false).text("Save"); }
    });
});
</script>
@endpush
