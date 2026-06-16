@extends('layouts.portal')

@section('title', 'API Endpoints')

@section('breadcrumb', 'API Endpoints')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>API Endpoint Management</span>
            <div class="d-flex align-items-center gap-3">
                <small class="text-muted fw-normal">Toggle endpoints to enable or disable access.</small>
                @include('partials.search-bar')
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Method</th>
                            <th>Path</th>
                            <th>Description</th>
                            <th>Last Updated</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($endpoints as $endpoint)
                        <tr data-id="{{ $endpoint->id }}" class="{{ $endpoint->is_active ? '' : 'table-danger bg-opacity-50' }}">
                            <td class="text-muted">{{ $endpoint->id }}</td>
                            <td class="fw-semibold">{{ $endpoint->name }}</td>
                            <td>
                                @php
                                    $methodColour = match($endpoint->method) {
                                        'GET'    => 'success',
                                        'POST'   => 'primary',
                                        'PUT'    => 'warning',
                                        'PATCH'  => 'info',
                                        'DELETE' => 'danger',
                                        default  => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $methodColour }}">{{ $endpoint->method }}</span>
                            </td>
                            <td><code>{{ $endpoint->path }}</code></td>
                            <td class="text-muted" style="max-width: 300px;">{{ $endpoint->description ?? '-' }}</td>
                            <td class="text-muted">{{ \Carbon\Carbon::parse($endpoint->updated_at)->format('d M Y H:i') }}</td>
                            <td class="status-cell">
                                <span class="badge bg-{{ $endpoint->is_active ? 'success' : 'danger' }} status-badge">
                                    {{ $endpoint->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm {{ $endpoint->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} toggle-btn">
                                    {{ $endpoint->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    var toggleRoute = "{{ route('portal.endpoints.toggle') }}";

    $(document).on("click", ".toggle-btn", function () {
        var btn  = $(this);
        var row  = btn.closest("tr");
        var id   = row.data("id");

        btn.prop("disabled", true).text("Saving…");

        $.ajax({
            url: toggleRoute, type: "POST",
            data: { _token: "{{ csrf_token() }}", id: id },
            success: function (response) {
                var active = response.is_active;

                row.toggleClass("table-danger", !active);
                row.find(".status-badge")
                    .text(active ? "Active" : "Inactive")
                    .removeClass("bg-success bg-danger")
                    .addClass(active ? "bg-success" : "bg-danger");

                btn.prop("disabled", false)
                    .text(active ? "Deactivate" : "Activate")
                    .removeClass("btn-outline-success btn-outline-danger")
                    .addClass(active ? "btn-outline-danger" : "btn-outline-success");
            },
            error: function (xhr) {
                alert("Failed to update endpoint. Please try again.");
                btn.prop("disabled", false).text("Toggle");
            }
        });
    });
</script>
@endpush
