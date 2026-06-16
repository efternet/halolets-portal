@extends('layouts.portal')

@section('title', 'Calls')
@section('breadcrumb', 'Operations - Calls')

@php
$stageColour = [
    'open'        => 'primary',
    'in-progress' => 'warning',
    'complete'    => 'success',
    'pending'     => 'info',
    'draft'       => 'secondary',
    'archived'    => 'dark',
];
@endphp

@php
function sortUrl(string $col, string $currentSort, string $currentDir): string {
    $dir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $dir, 'page' => 1]);
}
function sortIcon(string $col, string $currentSort, string $currentDir): string {
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

    <select name="stage" class="form-select form-select-sm" style="width:145px;">
        <option value="">All stages</option>
        @foreach ($stages as $s)
        <option value="{{ $s }}" {{ $filterStage === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>

    <select name="has_task" class="form-select form-select-sm" style="width:155px;">
        <option value="">Work task: any</option>
        <option value="yes" {{ $filterTask === 'yes' ? 'selected' : '' }}>Has work task</option>
        <option value="no"  {{ $filterTask === 'no'  ? 'selected' : '' }}>No work task</option>
    </select>

    <select name="has_resolution" class="form-select form-select-sm" style="width:175px;">
        <option value="">Resolution: any</option>
        <option value="yes" {{ $filterResolved === 'yes' ? 'selected' : '' }}>Resolved</option>
        <option value="no"  {{ $filterResolved === 'no'  ? 'selected' : '' }}>Unresolved</option>
    </select>

    <button class="btn btn-sm btn-primary" type="submit">Filter</button>

    @if($filterStage || $filterTask || $filterResolved)
    <a href="{{ request()->url() }}{{ request('search') ? '?search='.request('search') : '' }}"
       class="btn btn-sm btn-outline-secondary">Clear filters</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span>Calls</span>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newCallModal">
                <i class="bi bi-plus-lg"></i> New Call
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
                        <th>
                            <a href="{{ sortUrl('id', $sort, $direction) }}" class="text-decoration-none text-dark">
                                ID {!! sortIcon('id', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ sortUrl('stage', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Stage {!! sortIcon('stage', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>Notes</th>
                        <th>Work Task</th>
                        <th>Resolution Type</th>
                        <th>
                            <a href="{{ sortUrl('work_completed_at', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Completed {!! sortIcon('work_completed_at', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ sortUrl('created_at', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Created {!! sortIcon('created_at', $sort, $direction) !!}
                            </a>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($calls as $call)
                    <tr data-id="{{ $call->id }}">
                        <td class="text-muted">{{ $call->id }}</td>

                        <td style="min-width:155px;">
                            <select class="form-select form-select-sm" data-field="stage">
                                @foreach ($stages as $stage)
                                <option value="{{ $stage }}" {{ $call->stage === $stage ? 'selected' : '' }}>
                                    {{ ucfirst($stage) }}
                                </option>
                                @endforeach
                            </select>
                        </td>

                        <td style="min-width:200px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="notes" value="{{ $call->notes ?? '' }}"
                                placeholder="No notes">
                        </td>

                        <td class="text-center">
                            @if ($call->task_id)
                                <a href="{{ route('portal.operations.work-tasks.index', ['search' => $call->task_id]) }}"
                                   class="badge bg-primary text-decoration-none">
                                    #{{ $call->task_id }}
                                </a>
                            @else
                                <span class="badge bg-secondary">None</span>
                            @endif
                        </td>

                        <td>
                            @if ($call->resolution_type_name)
                                <span class="badge bg-primary">{{ $call->resolution_type_name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            @if ($call->work_completed_at)
                                <span class="badge bg-success">{{ \Carbon\Carbon::parse($call->work_completed_at)->format('d M Y') }}</span>
                            @else
                                <span class="text-muted">Ongoing</span>
                            @endif
                        </td>

                        <td class="text-muted">{{ \Carbon\Carbon::parse($call->created_at)->format('d M Y') }}</td>

                        <td class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary save-btn">Save</button>
                            @if ($call->stage !== 'archived')
                            <button class="btn btn-sm btn-outline-secondary archive-btn"
                                title="Archive this call">
                                <i class="bi bi-archive"></i>
                            </button>
                            @else
                            <button class="btn btn-sm btn-outline-warning unarchive-btn"
                                title="Unarchive this call">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if ($calls->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">Showing {{ $calls->firstItem() }}–{{ $calls->lastItem() }} of {{ $calls->total() }} records</small>
        <div>{{ $calls->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif
</div>
</div>{{-- /card --}}
{{-- New Call Modal --}}
<div class="modal fade" id="newCallModal" tabindex="-1" aria-labelledby="newCallModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newCallModalLabel">New Call</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Stage</label>
                    <select id="new-stage" class="form-select">
                        @foreach ($stages as $stage)
                        <option value="{{ $stage }}" {{ $stage === 'open' ? 'selected' : '' }}>
                            {{ ucfirst($stage) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea id="new-notes" class="form-control" rows="3" placeholder="Optional notes…"></textarea>
                </div>
                <div id="new-call-error" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-new-call-btn">Create Call</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var updateRoute = "{{ route('portal.operations.calls.update') }}";
var storeRoute  = "{{ route('portal.operations.calls.store') }}";

$("#save-new-call-btn").on("click", function () {
    var btn = $(this);
    btn.prop("disabled", true).text("Creating…");
    $("#new-call-error").addClass("d-none");

    $.ajax({
        url: storeRoute, type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            stage:  $("#new-stage").val(),
            notes:  $("#new-notes").val(),
        },
        success: function (res) {
            btn.prop("disabled", false).text("Create Call");
            $("#new-notes").val("");
            $("#new-stage").val("open");
            bootstrap.Modal.getInstance(document.getElementById("newCallModal")).hide();
            window.location.reload();
        },
        error: function (xhr) {
            var msg = xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : "Failed to create call.";
            $("#new-call-error").text(msg).removeClass("d-none");
            btn.prop("disabled", false).text("Create Call");
        }
    });
});

$(document).on("click", ".archive-btn", function () {
    var btn = $(this);
    var row = btn.closest("tr");
    if (!confirm("Archive call #" + row.data("id") + "?")) return;

    btn.prop("disabled", true);
    $.ajax({
        url: updateRoute, type: "POST",
        data: {
            _token:  "{{ csrf_token() }}",
            _method: "PUT",
            id:      row.data("id"),
            stage:   "archived",
            notes:   row.find("[data-field='notes']").val(),
        },
        success: function () {
            row.find("[data-field='stage']").val("archived");
            var $unarchive = $('<button class="btn btn-sm btn-outline-warning unarchive-btn" title="Unarchive this call"><i class="bi bi-arrow-counterclockwise"></i></button>');
            btn.replaceWith($unarchive);
            new bootstrap.Tooltip($unarchive[0], { trigger: 'hover' });
        },
        error: function () { alert("Failed to archive."); btn.prop("disabled", false); }
    });
});

$(document).on("click", ".unarchive-btn", function () {
    var btn = $(this);
    var row = btn.closest("tr");

    btn.prop("disabled", true);
    $.ajax({
        url: updateRoute, type: "POST",
        data: {
            _token:  "{{ csrf_token() }}",
            _method: "PUT",
            id:      row.data("id"),
            stage:   "open",
            notes:   row.find("[data-field='notes']").val(),
        },
        success: function () {
            row.find("[data-field='stage']").val("open");
            var $archive = $('<button class="btn btn-sm btn-outline-secondary archive-btn" title="Archive this call"><i class="bi bi-archive"></i></button>');
            btn.replaceWith($archive);
            new bootstrap.Tooltip($archive[0], { trigger: 'hover' });
        },
        error: function () { alert("Failed to unarchive."); btn.prop("disabled", false); }
    });
});

$(document).on("click", ".save-btn", function () {
    var row = $(this).closest("tr");
    var btn = $(this);
    btn.prop("disabled", true).text("Saving…");

    $.ajax({
        url: updateRoute, type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            _method: "PUT",
            id:     row.data("id"),
            stage:  row.find("[data-field='stage']").val(),
            notes:  row.find("[data-field='notes']").val(),
        },
        success: function () { btn.prop("disabled", false).text("Save"); },
        error:   function () { alert("Failed to save."); btn.prop("disabled", false).text("Save"); }
    });
});
</script>
@endpush
