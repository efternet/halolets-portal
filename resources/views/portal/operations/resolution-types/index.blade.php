@extends('layouts.portal')

@section('title', 'Resolution Types')
@section('breadcrumb', 'Operations - Resolution Types')

@php
function rtSort(string $col, string $cur, string $dir): string {
    $d = ($cur === $col && $dir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $d, 'page' => 1]);
}
function rtSortIcon(string $col, string $cur, string $dir): string {
    if ($cur !== $col) return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:.7rem;"></i>';
    return $dir === 'asc' ? '<i class="bi bi-sort-up ms-1" style="font-size:.75rem;"></i>' : '<i class="bi bi-sort-down ms-1" style="font-size:.75rem;"></i>';
}
@endphp

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span>Resolution Types</span>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newTypeModal">
                <i class="bi bi-plus-lg"></i> New Type
            </button>
        </div>
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
                        <th><a href="{{ rtSort('rt.id',$sort,$direction) }}" class="text-decoration-none text-dark">ID {!! rtSortIcon('rt.id',$sort,$direction) !!}</a></th>
                        <th><a href="{{ rtSort('rt.name',$sort,$direction) }}" class="text-decoration-none text-dark">Name {!! rtSortIcon('rt.name',$sort,$direction) !!}</a></th>
                        <th>Description</th>
                        <th style="width:130px;"><a href="{{ rtSort('task_count',$sort,$direction) }}" class="text-decoration-none text-dark">Tasks Using {!! rtSortIcon('task_count',$sort,$direction) !!}</a></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($types as $row)
                    <tr data-id="{{ $row->id }}">
                        <td class="text-muted">{{ $row->id }}</td>
                        <td style="min-width:220px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="name" value="{{ $row->name }}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm"
                                data-field="description" value="{{ $row->description ?? '' }}"
                                placeholder="No description">
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $row->task_count > 0 ? 'bg-primary' : 'bg-secondary' }}">
                                {{ $row->task_count }}
                            </span>
                        </td>
                        <td class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary save-btn">Save</button>
                            <button class="btn btn-sm btn-outline-danger delete-btn"
                                data-id="{{ $row->id }}"
                                title="Delete this resolution type"
                                {{ $row->task_count > 0 ? 'disabled' : '' }}>
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
{{-- New Resolution Type Modal --}}
<div class="modal fade" id="newTypeModal" tabindex="-1" aria-labelledby="newTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newTypeModalLabel">New Resolution Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" id="new-name" class="form-control" placeholder="e.g. Fix Complete - Parts Replaced">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <input type="text" id="new-description" class="form-control" placeholder="Optional description…">
                </div>
                <div id="new-type-error" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-new-type-btn">Create</button>
            </div>
        </div>
    </div>
</div>

    @if ($types->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">Showing {{ $types->firstItem() }}-{{ $types->lastItem() }} of {{ $types->total() }} records</small>
        <div>{{ $types->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
var updateRoute  = "{{ route('portal.operations.resolution-types.update') }}";
var storeRoute   = "{{ route('portal.operations.resolution-types.store') }}";
var destroyBase  = "{{ url('portal/operations/resolution-types') }}";

$("#save-new-type-btn").on("click", function () {
    var btn = $(this);
    btn.prop("disabled", true).text("Creating…");
    $("#new-type-error").addClass("d-none");

    $.ajax({
        url: storeRoute, type: "POST",
        data: {
            _token:      "{{ csrf_token() }}",
            name:        $("#new-name").val(),
            description: $("#new-description").val(),
        },
        success: function () {
            btn.prop("disabled", false).text("Create");
            bootstrap.Modal.getInstance(document.getElementById("newTypeModal")).hide();
            window.location.reload();
        },
        error: function (xhr) {
            var errors = xhr.responseJSON && xhr.responseJSON.errors
                ? Object.values(xhr.responseJSON.errors).flat().join(" ")
                : "Failed to create resolution type.";
            $("#new-type-error").text(errors).removeClass("d-none");
            btn.prop("disabled", false).text("Create");
        }
    });
});

$("#newTypeModal").on("hidden.bs.modal", function () {
    $("#new-name, #new-description").val("");
    $("#new-type-error").addClass("d-none");
});

$(document).on("click", ".delete-btn", function () {
    var btn = $(this);
    var id  = btn.data("id");
    if (!confirm("Soft delete resolution type #" + id + "? It will be hidden but not permanently removed.")) return;

    btn.prop("disabled", true);
    $.ajax({
        url:  destroyBase + "/" + id,
        type: "POST",
        data: { _token: "{{ csrf_token() }}", _method: "DELETE" },
        success: function () { btn.closest("tr").fadeOut(300, function () { $(this).remove(); }); },
        error:   function () { alert("Failed to delete."); btn.prop("disabled", false); }
    });
});

$(document).on("click", ".save-btn", function () {
    var row  = $(this).closest("tr");
    var btn  = $(this);
    btn.prop("disabled", true).text("Saving…");

    $.ajax({
        url: updateRoute, type: "POST",
        data: {
            _token:      "{{ csrf_token() }}",
            _method:     "PUT",
            id:          row.data("id"),
            name:        row.find("[data-field='name']").val(),
            description: row.find("[data-field='description']").val(),
        },
        success: function () { btn.prop("disabled", false).text("Save"); },
        error:   function () { alert("Failed to save."); btn.prop("disabled", false).text("Save"); }
    });
});
</script>
@endpush
