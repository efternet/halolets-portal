@extends('layouts.portal')

@section('title', 'Product List')
@section('breadcrumb', 'Product List')

@php
function plSort(string $col, string $cur, string $dir): string {
    $d = ($cur === $col && $dir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $d, 'page' => 1]);
}
function plSortIcon(string $col, string $cur, string $dir): string {
    if ($cur !== $col) return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:.7rem;"></i>';
    return $dir === 'asc' ? '<i class="bi bi-sort-up ms-1" style="font-size:.75rem;"></i>' : '<i class="bi bi-sort-down ms-1" style="font-size:.75rem;"></i>';
}
@endphp

@section('content')

<form method="GET" class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
    <select name="category_id" class="form-select form-select-sm" style="width:200px;">
        <option value="">All categories</option>
        @foreach ($categories as $cat)
        <option value="{{ $cat->id }}" {{ ($filterCategory ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>
    <button class="btn btn-sm btn-primary" type="submit">Filter</button>
    @if($filterCategory ?? '')
    <a href="{{ request()->url() }}{{ request('search') ? '?search='.request('search') : '' }}" class="btn btn-sm btn-outline-secondary">Clear filters</a>
    @endif
</form>
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span>Product List</span>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newProductModal">
                <i class="bi bi-plus-lg"></i> Add Product
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
            <table class="table table-hover mb-0" style="font-size:0.85rem;">
                <thead>
                    <tr>
                        <th><a href="{{ plSort('p.id',$sort,$direction) }}" class="text-decoration-none text-dark">ID {!! plSortIcon('p.id',$sort,$direction) !!}</a></th>
                        <th><a href="{{ plSort('pc.name',$sort,$direction) }}" class="text-decoration-none text-dark">Category {!! plSortIcon('pc.name',$sort,$direction) !!}</a></th>
                        <th><a href="{{ plSort('p.product_name',$sort,$direction) }}" class="text-decoration-none text-dark">Product Name {!! plSortIcon('p.product_name',$sort,$direction) !!}</a></th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Serial No.</th>
                        <th>Asset Tag</th>
                        <th>Batch No.</th>
                        <th>Purchase Order</th>
                        <th>Rental SKU</th>
                        <th>Supplier</th>
                        <th>Grade</th>
                        <th><a href="{{ plSort('p.acquisition_date',$sort,$direction) }}" class="text-decoration-none text-dark">Acquired {!! plSortIcon('p.acquisition_date',$sort,$direction) !!}</a></th>
                        <th><a href="{{ plSort('p.warranty_expiry',$sort,$direction) }}" class="text-decoration-none text-dark">Warranty Exp. {!! plSortIcon('p.warranty_expiry',$sort,$direction) !!}</a></th>
                        <th>Notes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                    <tr data-id="{{ $product->id }}">
                        <td class="text-muted">{{ $product->id }}</td>

                        <td style="min-width:150px;">
                            <select class="form-select form-select-sm" data-field="category_id">
                                <option value="">- None -</option>
                                @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                        </td>
                        <td style="min-width:160px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="product_name" value="{{ $product->product_name }}">
                        </td>
                        <td style="min-width:110px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="brand" value="{{ $product->brand ?? '' }}" placeholder="-">
                        </td>
                        <td style="min-width:110px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="model" value="{{ $product->model ?? '' }}" placeholder="-">
                        </td>
                        <td style="min-width:130px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="serial_number" value="{{ $product->serial_number ?? '' }}" placeholder="-">
                        </td>
                        <td style="min-width:110px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="asset_tag" value="{{ $product->asset_tag ?? '' }}" placeholder="-">
                        </td>
                        <td style="min-width:110px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="batch_no" value="{{ $product->batch_no ?? '' }}" placeholder="-">
                        </td>
                        <td style="min-width:130px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="purchase_order" value="{{ $product->purchase_order ?? '' }}" placeholder="-">
                        </td>
                        <td style="min-width:120px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="rental_sku" value="{{ $product->rental_sku ?? '' }}" placeholder="-">
                        </td>
                        <td style="min-width:120px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="supplier" value="{{ $product->supplier ?? '' }}" placeholder="-">
                        </td>
                        <td style="min-width:80px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="condition_grade" value="{{ $product->condition_grade ?? '' }}" placeholder="-">
                        </td>
                        <td style="min-width:140px;">
                            <input type="date" class="form-control form-control-sm"
                                data-field="acquisition_date"
                                value="{{ $product->acquisition_date ? \Carbon\Carbon::parse($product->acquisition_date)->format('Y-m-d') : '' }}">
                        </td>
                        <td style="min-width:140px;">
                            <input type="date" class="form-control form-control-sm"
                                data-field="warranty_expiry"
                                value="{{ $product->warranty_expiry ? \Carbon\Carbon::parse($product->warranty_expiry)->format('Y-m-d') : '' }}">
                        </td>
                        <td style="min-width:160px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="notes" value="{{ $product->notes ?? '' }}" placeholder="-">
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
    @if ($products->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">Showing {{ $products->firstItem() }}-{{ $products->lastItem() }} of {{ $products->total() }} records</small>
        <div>{{ $products->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif
</div>

{{-- Add Product Modal --}}
<div class="modal fade" id="newProductModal" tabindex="-1" aria-labelledby="newProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newProductModalLabel">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" id="np-product_name" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category</label>
                        <select id="np-category_id" class="form-select">
                            <option value="">- None -</option>
                            @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Brand</label>
                        <input type="text" id="np-brand" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Model</label>
                        <input type="text" id="np-model" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Condition Grade</label>
                        <input type="text" id="np-condition_grade" class="form-control" placeholder="e.g. A, B, C">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Serial Number</label>
                        <input type="text" id="np-serial_number" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Asset Tag</label>
                        <input type="text" id="np-asset_tag" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Batch No.</label>
                        <input type="text" id="np-batch_no" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Purchase Order</label>
                        <input type="text" id="np-purchase_order" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Rental SKU</label>
                        <input type="text" id="np-rental_sku" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Supplier</label>
                        <input type="text" id="np-supplier" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Acquisition Date</label>
                        <input type="date" id="np-acquisition_date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Warranty Expiry</label>
                        <input type="date" id="np-warranty_expiry" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea id="np-notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div id="new-product-error" class="alert alert-danger mt-3 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-new-product-btn">Add Product</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var updateRoute = "{{ route('portal.product-list.update') }}";
var storeRoute  = "{{ route('portal.product-list.store') }}";

$(document).on("click", ".save-btn", function () {
    var row = $(this).closest("tr");
    var btn = $(this);
    btn.prop("disabled", true).text("Saving...");

    $.ajax({
        url: updateRoute, type: "POST",
        data: {
            _token:                 "{{ csrf_token() }}",
            _method:                "PUT",
            id:                     row.data("id"),
            category_id:            row.find("[data-field='category_id']").val(),
            product_name:           row.find("[data-field='product_name']").val(),
            brand:                  row.find("[data-field='brand']").val(),
            model:                  row.find("[data-field='model']").val(),
            serial_number:          row.find("[data-field='serial_number']").val(),
            asset_tag:              row.find("[data-field='asset_tag']").val(),
            batch_no:               row.find("[data-field='batch_no']").val(),
            purchase_order:         row.find("[data-field='purchase_order']").val(),
            rental_sku:             row.find("[data-field='rental_sku']").val(),
            supplier:               row.find("[data-field='supplier']").val(),
            condition_grade:        row.find("[data-field='condition_grade']").val(),
            acquisition_date:       row.find("[data-field='acquisition_date']").val(),
            warranty_expiry:        row.find("[data-field='warranty_expiry']").val(),
            notes:                  row.find("[data-field='notes']").val(),
        },
        success: function () { btn.prop("disabled", false).text("Save"); },
        error:   function () { alert("Failed to save."); btn.prop("disabled", false).text("Save"); }
    });
});

$("#save-new-product-btn").on("click", function () {
    var btn = $(this);
    btn.prop("disabled", true).text("Adding...");
    $("#new-product-error").addClass("d-none");

    $.ajax({
        url: storeRoute, type: "POST",
        data: {
            _token:                 "{{ csrf_token() }}",
            product_name:           $("#np-product_name").val(),
            category_id:            $("#np-category_id").val(),
            brand:                  $("#np-brand").val(),
            model:                  $("#np-model").val(),
            serial_number:          $("#np-serial_number").val(),
            asset_tag:              $("#np-asset_tag").val(),
            batch_no:               $("#np-batch_no").val(),
            purchase_order:         $("#np-purchase_order").val(),
            rental_sku:             $("#np-rental_sku").val(),
            supplier:               $("#np-supplier").val(),
            condition_grade:        $("#np-condition_grade").val(),
            acquisition_date:       $("#np-acquisition_date").val(),
            warranty_expiry:        $("#np-warranty_expiry").val(),
            notes:                  $("#np-notes").val(),
        },
        success: function () {
            btn.prop("disabled", false).text("Add Product");
            bootstrap.Modal.getInstance(document.getElementById("newProductModal")).hide();
            window.location.reload();
        },
        error: function (xhr) {
            var errors = xhr.responseJSON && xhr.responseJSON.errors
                ? Object.values(xhr.responseJSON.errors).flat().join(" ")
                : "Failed to add product.";
            $("#new-product-error").text(errors).removeClass("d-none");
            btn.prop("disabled", false).text("Add Product");
        }
    });
});

$("#newProductModal").on("hidden.bs.modal", function () {
    $(this).find("input, textarea").val("");
    $("#new-product-error").addClass("d-none");
});
</script>
@endpush
