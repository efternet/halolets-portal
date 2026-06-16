@extends('layouts.portal')

@section('title', 'Product Categories')
@section('breadcrumb', 'Product Categories')

@section('content')
<div class="row g-4">

    {{-- Category list --}}
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">Categories</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th class="text-center">Products</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="category-list">
                        @foreach ($categories as $cat)
                        <tr data-id="{{ $cat->id }}">
                            <td class="text-muted">{{ $cat->id }}</td>
                            <td>{{ $cat->name }}</td>
                            <td class="text-center">
                                <a href="{{ route('portal.product-list.index', ['search' => $cat->name]) }}"
                                   class="badge {{ $cat->product_count > 0 ? 'bg-primary' : 'bg-secondary' }} text-decoration-none">
                                    {{ $cat->product_count }}
                                </a>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger delete-btn"
                                    data-id="{{ $cat->id }}"
                                    data-name="{{ $cat->name }}"
                                    data-count="{{ $cat->product_count }}"
                                    title="Remove category">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add category panel --}}
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header">Add Category</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" id="new-cat-name" class="form-control" placeholder="e.g. Power Tools">
                </div>
                <div id="add-cat-error" class="alert alert-danger d-none"></div>
                <button class="btn btn-primary w-100" id="add-cat-btn">
                    <i class="bi bi-plus-lg"></i> Add Category
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
var storeRoute   = "{{ route('portal.product-categories.store') }}";
var destroyBase  = "{{ url('portal/product-categories') }}";

$("#add-cat-btn").on("click", function () {
    var btn  = $(this);
    var name = $("#new-cat-name").val().trim();
    $("#add-cat-error").addClass("d-none");

    if (!name) {
        $("#add-cat-error").text("Name is required.").removeClass("d-none");
        return;
    }

    btn.prop("disabled", true).text("Adding...");

    $.ajax({
        url: storeRoute, type: "POST",
        data: { _token: "{{ csrf_token() }}", name: name },
        success: function (res) {
            $("#new-cat-name").val("");
            btn.prop("disabled", false).html('<i class="bi bi-plus-lg"></i> Add Category');

            // Append new row to table
            $("#category-list").append(
                '<tr data-id="' + res.id + '">' +
                  '<td class="text-muted">' + res.id + '</td>' +
                  '<td>' + res.name + '</td>' +
                  '<td class="text-center"><span class="badge bg-secondary">0</span></td>' +
                  '<td><button class="btn btn-sm btn-outline-danger delete-btn" ' +
                    'data-id="' + res.id + '" data-name="' + res.name + '" data-count="0" ' +
                    'title="Remove category"><i class="bi bi-trash"></i></button></td>' +
                '</tr>'
            );

            // Init tooltip on new button
            var newBtn = $("#category-list tr[data-id='" + res.id + "'] .delete-btn")[0];
            if (newBtn) new bootstrap.Tooltip(newBtn, { trigger: 'hover' });
        },
        error: function (xhr) {
            var msg = xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.name
                ? xhr.responseJSON.errors.name[0]
                : "Failed to add category.";
            $("#add-cat-error").text(msg).removeClass("d-none");
            btn.prop("disabled", false).html('<i class="bi bi-plus-lg"></i> Add Category');
        }
    });
});

$(document).on("click", ".delete-btn", function () {
    var btn   = $(this);
    var id    = btn.data("id");
    var name  = btn.data("name");
    var count = parseInt(btn.data("count"));

    var msg = count > 0
        ? 'Remove "' + name + '"? This will unlink ' + count + ' product(s) from this category.'
        : 'Remove "' + name + '"?';

    if (!confirm(msg)) return;

    btn.prop("disabled", true);
    $.ajax({
        url:  destroyBase + "/" + id,
        type: "POST",
        data: { _token: "{{ csrf_token() }}", _method: "DELETE" },
        success: function () {
            btn.closest("tr").fadeOut(300, function () { $(this).remove(); });
        },
        error: function () { alert("Failed to remove category."); btn.prop("disabled", false); }
    });
});
</script>
@endpush
