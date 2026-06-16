@extends('layouts.portal')

@section('title', 'Work Tasks')
@section('breadcrumb', 'Operations - Work Tasks')

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
function wtSortUrl(string $col, string $currentSort, string $currentDir): string {
    $dir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $dir, 'page' => 1]);
}
function wtSortIcon(string $col, string $currentSort, string $currentDir): string {
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

    <select name="call_stage" class="form-select form-select-sm" style="width:155px;">
        <option value="">All call stages</option>
        @foreach ($callStages as $s)
        <option value="{{ $s }}" {{ $filterCallStage === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>

    <select name="resolution_type_id" class="form-select form-select-sm" style="width:210px;">
        <option value="">All resolution types</option>
        @foreach ($resolutionTypes as $rt)
        <option value="{{ $rt->id }}" {{ $filterResolution == $rt->id ? 'selected' : '' }}>{{ $rt->name }}</option>
        @endforeach
    </select>

    <select name="completed" class="form-select form-select-sm" style="width:160px;">
        <option value="">Completion: any</option>
        <option value="yes" {{ $filterCompleted === 'yes' ? 'selected' : '' }}>Completed</option>
        <option value="no"  {{ $filterCompleted === 'no'  ? 'selected' : '' }}>Not completed</option>
    </select>

    <button class="btn btn-sm btn-primary" type="submit">Filter</button>

    @if($filterCallStage || $filterResolution || $filterCompleted)
    <a href="{{ request()->url() }}{{ request('search') ? '?search='.request('search') : '' }}"
       class="btn btn-sm btn-outline-secondary">Clear filters</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span>Work Tasks</span>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newTaskModal">
                <i class="bi bi-plus-lg"></i> New Task
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
                            <a href="{{ wtSortUrl('id', $sort, $direction) }}" class="text-decoration-none text-dark">
                                ID {!! wtSortIcon('id', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>Call</th>
                        <th>
                            <a href="{{ wtSortUrl('call_stage', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Call Stage {!! wtSortIcon('call_stage', $sort, $direction) !!}
                            </a>
                        </th>
                        <th style="min-width:190px;">Resolution Type</th>
                        <th>
                            <a href="{{ wtSortUrl('work_started_at', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Work Started {!! wtSortIcon('work_started_at', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ wtSortUrl('work_completed_at', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Work Completed {!! wtSortIcon('work_completed_at', $sort, $direction) !!}
                            </a>
                        </th>
                        <th>
                            <a href="{{ wtSortUrl('created_at', $sort, $direction) }}" class="text-decoration-none text-dark">
                                Created {!! wtSortIcon('created_at', $sort, $direction) !!}
                            </a>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tasks as $task)
                    <tr data-id="{{ $task->id }}">
                        <td class="text-muted">{{ $task->id }}</td>

                        <td style="min-width:190px;" class="position-relative call-cell">
                            <input type="text" class="form-control form-control-sm call-search-input"
                                value="#{{ $task->call_id }} - {{ $task->call_stage }}"
                                autocomplete="off"
                                placeholder="Search call…">
                            <input type="hidden" class="call-id-hidden" value="{{ $task->call_id }}">
                            <div class="call-suggestions list-group shadow-sm position-absolute w-100 d-none"
                                style="z-index:1060; top:100%; max-height:200px; overflow-y:auto;"></div>
                        </td>

                        <td>
                            @php $colour = $stageColour[$task->call_stage] ?? 'secondary'; @endphp
                            <span class="badge bg-{{ $colour }}">{{ ucfirst($task->call_stage ?? '-') }}</span>
                        </td>

                        <td>
                            <select class="form-select form-select-sm" data-field="resolution_type_id">
                                <option value="">- None -</option>
                                @foreach ($resolutionTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ $task->resolution_type_id == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                        </td>

                        <td style="min-width:175px;">
                            <input type="datetime-local" class="form-control form-control-sm"
                                data-field="work_started_at"
                                value="{{ $task->work_started_at ? \Carbon\Carbon::parse($task->work_started_at)->format('Y-m-d\TH:i') : '' }}">
                        </td>

                        <td style="min-width:175px;">
                            <input type="datetime-local" class="form-control form-control-sm"
                                data-field="work_completed_at"
                                value="{{ $task->work_completed_at ? \Carbon\Carbon::parse($task->work_completed_at)->format('Y-m-d\TH:i') : '' }}">
                        </td>

                        <td class="text-muted">{{ \Carbon\Carbon::parse($task->created_at)->format('d M Y') }}</td>

                        <td class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary save-btn">Save</button>
                            <button class="btn btn-sm btn-outline-danger delete-btn"
                                data-id="{{ $task->id }}"
                                title="Soft delete this task">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if ($tasks->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">Showing {{ $tasks->firstItem() }}–{{ $tasks->lastItem() }} of {{ $tasks->total() }} records</small>
        <div>{{ $tasks->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif
</div>
</div>{{-- /card --}}
{{-- New Work Task Modal --}}
<div class="modal fade" id="newTaskModal" tabindex="-1" aria-labelledby="newTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newTaskModalLabel">New Work Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 position-relative">
                    <label class="form-label fw-semibold">Call <span class="text-danger">*</span></label>
                    <input type="text" id="call-search-input" class="form-control"
                        placeholder="Search by ID, stage or notes…" autocomplete="off">
                    <input type="hidden" id="new-call-id">
                    <div id="call-suggestions"
                         class="list-group shadow-sm position-absolute w-100 d-none"
                         style="z-index:1060; top:100%; max-height:220px; overflow-y:auto;"></div>
                    <div id="call-selected" class="form-text text-success d-none"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Resolution Type</label>
                    <select id="new-resolution-type" class="form-select">
                        <option value="">- None -</option>
                        @foreach ($resolutionTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Work Started</label>
                    <input type="datetime-local" id="new-work-started" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Work Completed</label>
                    <input type="datetime-local" id="new-work-completed" class="form-control">
                </div>
                <div id="new-task-error" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-new-task-btn">Create Task</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var updateRoute  = "{{ route('portal.operations.work-tasks.update') }}";
var storeRoute   = "{{ route('portal.operations.work-tasks.store') }}";
var callSearchRoute = "{{ route('portal.operations.calls.search') }}";

// --- Inline call autocomplete (existing rows) ---
var rowCallTimer;

$(document).on("input", ".call-search-input", function () {
    clearTimeout(rowCallTimer);
    var $input = $(this);
    var $cell  = $input.closest(".call-cell");
    var $hidden = $cell.find(".call-id-hidden");
    var $list   = $cell.find(".call-suggestions");
    var q = $input.val().trim();

    $hidden.val(""); // clear until a suggestion is picked

    if (q.length === 0) { $list.addClass("d-none").empty(); return; }

    rowCallTimer = setTimeout(function () {
        $.getJSON(callSearchRoute, { q: q }, function (results) {
            $list.empty();
            if (results.length === 0) {
                $list.append('<div class="list-group-item text-muted small">No calls found</div>');
            } else {
                results.forEach(function (call) {
                    var notes = call.notes ? call.notes.substring(0, 50) + (call.notes.length > 50 ? "…" : "") : "";
                    var label = "<strong>#" + call.id + "</strong> &middot; "
                        + "<span class='badge bg-secondary me-1'>" + call.stage + "</span>"
                        + (notes ? "<small class='text-muted'>" + notes + "</small>" : "");
                    $list.append(
                        $('<button type="button" class="list-group-item list-group-item-action py-2 small">')
                            .html(label)
                            .data("id", call.id)
                            .data("stage", call.stage)
                    );
                });
            }
            $list.removeClass("d-none");
        });
    }, 250);
});

$(document).on("click", ".call-cell .call-suggestions button", function () {
    var $btn    = $(this);
    var $cell   = $btn.closest(".call-cell");
    $cell.find(".call-id-hidden").val($btn.data("id"));
    $cell.find(".call-search-input").val("#" + $btn.data("id") + " - " + $btn.data("stage"));
    $cell.find(".call-suggestions").addClass("d-none").empty();
});

$(document).on("click", function (e) {
    if (!$(e.target).closest(".call-cell").length) {
        $(".call-suggestions").addClass("d-none");
    }
});

// --- New task modal call autocomplete ---
var callSearchTimer;

$("#call-search-input").on("input", function () {
    clearTimeout(callSearchTimer);
    var q = $(this).val().trim();
    $("#new-call-id").val("");
    $("#call-selected").addClass("d-none");

    if (q.length === 0) {
        $("#call-suggestions").addClass("d-none").empty();
        return;
    }

    callSearchTimer = setTimeout(function () {
        $.getJSON(callSearchRoute, { q: q }, function (results) {
            var $list = $("#call-suggestions").empty();
            if (results.length === 0) {
                $list.append('<div class="list-group-item text-muted small">No calls found</div>');
            } else {
                results.forEach(function (call) {
                    var notes = call.notes ? call.notes.substring(0, 60) + (call.notes.length > 60 ? "…" : "") : "";
                    var label = "<strong>#" + call.id + "</strong> &middot; "
                        + "<span class='badge bg-secondary me-1'>" + call.stage + "</span>"
                        + (notes ? "<small class='text-muted'>" + notes + "</small>" : "");
                    $list.append(
                        $('<button type="button" class="list-group-item list-group-item-action py-2 small">')
                            .html(label)
                            .data("id", call.id)
                            .data("label", "#" + call.id + " - " + call.stage)
                    );
                });
            }
            $list.removeClass("d-none");
        });
    }, 250);
});

$(document).on("click", "#call-suggestions button", function () {
    var id    = $(this).data("id");
    var label = $(this).data("label");
    $("#new-call-id").val(id);
    $("#call-search-input").val("#" + id + " - " + $(this).data("stage"));
            $("#call-selected").text("Call #" + id + " selected").removeClass("d-none");
    $("#call-suggestions").addClass("d-none").empty();
});

// Dismiss dropdown on outside click
$(document).on("click", function (e) {
    if (!$(e.target).closest("#call-search-input, #call-suggestions").length) {
        $("#call-suggestions").addClass("d-none");
    }
});

// Reset modal on close
$("#newTaskModal").on("hidden.bs.modal", function () {
    $("#call-search-input").val("");
    $("#new-call-id").val("");
    $("#call-selected").addClass("d-none");
    $("#call-suggestions").addClass("d-none").empty();
    $("#new-resolution-type").val("");
    $("#new-work-started, #new-work-completed").val("");
    $("#new-task-error").addClass("d-none");
});

$("#save-new-task-btn").on("click", function () {
    var btn = $(this);
    btn.prop("disabled", true).text("Creating…");
    $("#new-task-error").addClass("d-none");

    $.ajax({
        url: storeRoute, type: "POST",
        data: {
            _token:              "{{ csrf_token() }}",
            call_id:             $("#new-call-id").val(),
            resolution_type_id:  $("#new-resolution-type").val(),
            work_started_at:     $("#new-work-started").val(),
            work_completed_at:   $("#new-work-completed").val(),
        },
        success: function () {
            btn.prop("disabled", false).text("Create Task");
            bootstrap.Modal.getInstance(document.getElementById("newTaskModal")).hide();
            window.location.reload();
        },
        error: function (xhr) {
            var errors = xhr.responseJSON && xhr.responseJSON.errors
                ? Object.values(xhr.responseJSON.errors).flat().join(" ")
                : (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "Failed to create task.");
            $("#new-task-error").text(errors).removeClass("d-none");
            btn.prop("disabled", false).text("Create Task");
        }
    });
});

$(document).on("click", ".delete-btn", function () {
    var btn = $(this);
    var id  = btn.data("id");
    if (!confirm("Soft delete work task #" + id + "? It will be hidden but not permanently removed.")) return;

    btn.prop("disabled", true);
    $.ajax({
        url:  "/portal/operations/work-tasks/" + id,
        type: "POST",
        data: { _token: "{{ csrf_token() }}", _method: "DELETE" },
        success: function () { btn.closest("tr").fadeOut(300, function () { $(this).remove(); }); },
        error:   function () { alert("Failed to delete."); btn.prop("disabled", false); }
    });
});

$(document).on("click", ".save-btn", function () {
    var row = $(this).closest("tr");
    var btn = $(this);
    btn.prop("disabled", true).text("Saving…");

    $.ajax({
        url: updateRoute, type: "POST",
        data: {
            _token:              "{{ csrf_token() }}",
            _method:             "PUT",
            id:                  row.data("id"),
            call_id:             row.find(".call-id-hidden").val(),
            resolution_type_id:  row.find("[data-field='resolution_type_id']").val(),
            work_started_at:     row.find("[data-field='work_started_at']").val(),
            work_completed_at:   row.find("[data-field='work_completed_at']").val(),
        },
        success: function () {
            // Sync the search input label to the saved call_id
            var callId = row.find(".call-id-hidden").val();
            row.find(".call-search-input").data("confirmed", true);
            btn.prop("disabled", false).text("Save");
        },
        error:   function () { alert("Failed to save."); btn.prop("disabled", false).text("Save"); }
    });
});
</script>
@endpush
