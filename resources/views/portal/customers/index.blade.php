@extends('layouts.portal')

@section('title', 'Customers')
@section('breadcrumb', 'Customers')

@php
function custSortUrl(string $col, string $currentSort, string $currentDir): string {
    $dir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
    return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $dir, 'page' => 1]);
}
function custSortIcon(string $col, string $currentSort, string $currentDir): string {
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

    <select name="country" class="form-select form-select-sm" style="width:180px;">
        <option value="">All countries</option>
        @foreach ($countries as $c)
        <option value="{{ $c }}" {{ ($filterCountry ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
        @endforeach
    </select>

    <select name="terms_accepted" class="form-select form-select-sm" style="width:150px;">
        <option value="">T&amp;Cs - any</option>
        <option value="1" {{ ($filterTerms ?? '') === '1' ? 'selected' : '' }}>T&amp;Cs accepted</option>
        <option value="0" {{ ($filterTerms ?? '') === '0' ? 'selected' : '' }}>T&amp;Cs not accepted</option>
    </select>

    <select name="contact_accepted" class="form-select form-select-sm" style="width:160px;">
        <option value="">Contact - any</option>
        <option value="1" {{ ($filterContact ?? '') === '1' ? 'selected' : '' }}>Contact OK</option>
        <option value="0" {{ ($filterContact ?? '') === '0' ? 'selected' : '' }}>No contact</option>
    </select>

    <div class="d-flex align-items-center gap-1">
        <label class="text-muted small mb-0">Last contacted</label>
        <input type="date" name="last_contacted_from" class="form-control form-control-sm" style="width:145px;"
            value="{{ $lastContactedFrom ?? '' }}">
        <label class="text-muted small mb-0">to</label>
        <input type="date" name="last_contacted_to" class="form-control form-control-sm" style="width:145px;"
            value="{{ $lastContactedTo ?? '' }}">
    </div>

    <button class="btn btn-sm btn-primary" type="submit">Filter</button>

    @if(($filterCountry ?? '') || ($filterTerms ?? '') !== '' && ($filterTerms ?? '') !== null || ($filterContact ?? '') !== '' && ($filterContact ?? '') !== null || ($lastContactedFrom ?? '') || ($lastContactedTo ?? ''))
    <a href="{{ request()->url() }}{{ request('search') ? '?search='.request('search') : '' }}"
       class="btn btn-sm btn-outline-secondary">Clear filters</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Customers</span>
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
                                <a href="{{ custSortUrl('id', $sort, $direction) }}" class="text-decoration-none text-dark">
                                    ID {!! custSortIcon('id', $sort, $direction) !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ custSortUrl('first_name', $sort, $direction) }}" class="text-decoration-none text-dark">
                                    First Name {!! custSortIcon('first_name', $sort, $direction) !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ custSortUrl('surname', $sort, $direction) }}" class="text-decoration-none text-dark">
                                    Surname {!! custSortIcon('surname', $sort, $direction) !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ custSortUrl('email', $sort, $direction) }}" class="text-decoration-none text-dark">
                                    Email {!! custSortIcon('email', $sort, $direction) !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ custSortUrl('country', $sort, $direction) }}" class="text-decoration-none text-dark">
                                    Country {!! custSortIcon('country', $sort, $direction) !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ custSortUrl('city', $sort, $direction) }}" class="text-decoration-none text-dark">
                                    City {!! custSortIcon('city', $sort, $direction) !!}
                                </a>
                            </th>
                            <th class="text-center">T&amp;Cs</th>
                            <th class="text-center">Contact OK</th>
                            <th>
                                <a href="{{ custSortUrl('last_contacted', $sort, $direction) }}" class="text-decoration-none text-dark">
                                    Last Contacted {!! custSortIcon('last_contacted', $sort, $direction) !!}
                                </a>
                            </th>
                            <th></th>
                        </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customer)
                    <tr data-id="{{ $customer->id }}">
                        <td class="text-muted">{{ $customer->id }}</td>

                        <td style="min-width:130px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="first_name" value="{{ $customer->first_name }}">
                        </td>

                        <td style="min-width:130px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="surname" value="{{ $customer->surname }}">
                        </td>

                        <td style="min-width:210px;">
                            <input type="email" class="form-control form-control-sm"
                                data-field="email" value="{{ $customer->email }}">
                        </td>

                        <td style="min-width:120px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="country" value="{{ $customer->country ?? '' }}"
                                placeholder="-">
                        </td>

                        <td style="min-width:120px;">
                            <input type="text" class="form-control form-control-sm"
                                data-field="city" value="{{ $customer->city ?? '' }}"
                                placeholder="-">
                        </td>

                        <td class="text-center">
                            @if ($customer->terms_accepted)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <div class="form-check d-flex justify-content-center mb-0">
                                <input class="form-check-input" type="checkbox"
                                    data-field="contact_accepted"
                                    {{ $customer->contact_accepted ? 'checked' : '' }}>
                            </div>
                        </td>

                        <td style="min-width:175px;">
                            <input type="datetime-local" class="form-control form-control-sm"
                                data-field="last_contacted"
                                value="{{ $customer->last_contacted ? \Carbon\Carbon::parse($customer->last_contacted)->format('Y-m-d\TH:i') : '' }}">
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
        <small class="text-muted">Showing {{ $customers->firstItem() }}-{{ $customers->lastItem() }} of {{ $customers->total() }} records</small>
        <div>{{ $customers->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
var updateRoute = "{{ route('portal.customers.update') }}";

$(document).on("click", ".save-btn", function () {
    var row = $(this).closest("tr");
    var btn = $(this);
    btn.prop("disabled", true).text("Saving…");

    $.ajax({
        url: updateRoute, type: "POST",
        data: {
            _token:           "{{ csrf_token() }}",
            _method:          "PUT",
            id:               row.data("id"),
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
