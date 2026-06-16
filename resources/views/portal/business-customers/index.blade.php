@extends('layouts.portal')

@section('title', 'Business Customers')
@section('breadcrumb', 'Business Accounts - Business Customers')

@php
function bcSort(string $col, string $cur, string $dir): string {
    $d = ($cur === $col && $dir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $d, 'page' => 1]);
}
function bcSortIcon(string $col, string $cur, string $dir): string {
    if ($cur !== $col) return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:.7rem;"></i>';
    return $dir === 'asc' ? '<i class="bi bi-sort-up ms-1" style="font-size:.75rem;"></i>' : '<i class="bi bi-sort-down ms-1" style="font-size:.75rem;"></i>';
}
@endphp

@section('content')

{{-- Filter bar --}}
<form method="GET" class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif

    <select name="business_id" class="form-select form-select-sm" style="width:230px;">
        <option value="">All businesses</option>
        @foreach ($businesses as $b)
        <option value="{{ $b->id }}" {{ ($filterBusiness ?? '') == $b->id ? 'selected' : '' }}>
            {{ $b->name }}
        </option>
        @endforeach
    </select>

    <select name="contact_accepted" class="form-select form-select-sm" style="width:160px;">
        <option value="">Contact - any</option>
        <option value="1" {{ ($filterContact ?? '') === '1' ? 'selected' : '' }}>Contact OK</option>
        <option value="0" {{ ($filterContact ?? '') === '0' ? 'selected' : '' }}>No contact</option>
    </select>

    <button class="btn btn-sm btn-primary" type="submit">Filter</button>

    @if(($filterBusiness ?? '') || ($filterContact ?? '') !== '')
    <a href="{{ request()->url() }}{{ request('search') ? '?search='.request('search') : '' }}"
       class="btn btn-sm btn-outline-secondary">Clear filters</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Business Customers</span>
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted fw-normal">Edit fields inline and click Save to update.</small>
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
                        <th><a href="{{ bcSort('bc.id',$sort,$direction) }}" class="text-decoration-none text-dark">ID {!! bcSortIcon('bc.id',$sort,$direction) !!}</a></th>
                        <th><a href="{{ bcSort('b.name',$sort,$direction) }}" class="text-decoration-none text-dark">Business {!! bcSortIcon('b.name',$sort,$direction) !!}</a></th>
                        <th><a href="{{ bcSort('bc.first_name',$sort,$direction) }}" class="text-decoration-none text-dark">First Name {!! bcSortIcon('bc.first_name',$sort,$direction) !!}</a></th>
                        <th><a href="{{ bcSort('bc.surname',$sort,$direction) }}" class="text-decoration-none text-dark">Surname {!! bcSortIcon('bc.surname',$sort,$direction) !!}</a></th>
                        <th><a href="{{ bcSort('bc.email',$sort,$direction) }}" class="text-decoration-none text-dark">Email {!! bcSortIcon('bc.email',$sort,$direction) !!}</a></th>
                        <th><a href="{{ bcSort('bc.country',$sort,$direction) }}" class="text-decoration-none text-dark">Country {!! bcSortIcon('bc.country',$sort,$direction) !!}</a></th>
                        <th><a href="{{ bcSort('bc.city',$sort,$direction) }}" class="text-decoration-none text-dark">City {!! bcSortIcon('bc.city',$sort,$direction) !!}</a></th>
                        <th class="text-center">T&amp;Cs</th>
                        <th class="text-center">Contact OK</th>
                        <th><a href="{{ bcSort('bc.last_contacted',$sort,$direction) }}" class="text-decoration-none text-dark">Last Contacted {!! bcSortIcon('bc.last_contacted',$sort,$direction) !!}</a></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $row)
                    <tr data-id="{{ $row->id }}">
                        <td class="text-muted">{{ $row->id }}</td>

                        <td>
                            <a href="{{ route('portal.businesses.index', ['id' => $row->business_id]) }}"
                               class="badge bg-light text-dark border text-decoration-none"
                               title="{{ $row->business_name }}">
                                #{{ $row->business_id }} {{ Str::limit($row->business_name, 20) }}
                            </a>
                        </td>

                        <td style="min-width:120px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="first_name" value="{{ $row->first_name }}">
                        </td>
                        <td style="min-width:120px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="surname" value="{{ $row->surname }}">
                        </td>
                        <td style="min-width:200px;">
                            <input type="email" class="form-control form-control-sm"
                                data-field="email" value="{{ $row->email }}">
                        </td>
                        <td style="min-width:110px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="country" value="{{ $row->country ?? '' }}" placeholder="-">
                        </td>
                        <td style="min-width:110px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="city" value="{{ $row->city ?? '' }}" placeholder="-">
                        </td>

                        <td class="text-center">
                            @if ($row->terms_accepted)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <div class="form-check d-flex justify-content-center mb-0">
                                <input class="form-check-input" type="checkbox"
                                    data-field="contact_accepted"
                                    {{ $row->contact_accepted ? 'checked' : '' }}>
                            </div>
                        </td>

                        <td style="min-width:175px;">
                            <input type="datetime-local" class="form-control form-control-sm"
                                data-field="last_contacted"
                                value="{{ $row->last_contacted ? \Carbon\Carbon::parse($row->last_contacted)->format('Y-m-d\TH:i') : '' }}">
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
    @if ($customers->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">Showing {{ $customers->firstItem() }}-{{ $customers->lastItem() }} of {{ $customers->total() }} customers</small>
        <div>{{ $customers->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
var baseUrl = "{{ url('portal/business-customers') }}"; // updates customers table via customer ID

$(document).on("click", ".save-btn", function () {
    var row = $(this).closest("tr");
    var btn = $(this);
    var id  = row.data("id");
    btn.prop("disabled", true).text("Saving...");

    $.ajax({
        url: baseUrl + "/" + id,
        type: "POST",
        data: {
            _token:           "{{ csrf_token() }}",
            _method:          "PUT",
            first_name:       row.find("[data-field='first_name']").val(),
            surname:          row.find("[data-field='surname']").val(),
            email:            row.find("[data-field='email']").val(),
            country:          row.find("[data-field='country']").val(),
            city:             row.find("[data-field='city']").val(),
            contact_accepted: row.find("[data-field='contact_accepted']").is(":checked") ? 1 : 0,
            last_contacted:   row.find("[data-field='last_contacted']").val(),
        },
        success: function () { btn.prop("disabled", false).text("Save"); },
        error:   function () { alert("Failed to save."); btn.prop("disabled", false).text("Save"); }
    });
});
</script>
@endpush
