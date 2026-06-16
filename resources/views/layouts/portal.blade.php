<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal') - Halolets</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --hl-blue:        #3B63E8;
            --hl-blue-dark:   #2947C8;
            --hl-blue-deeper: #1E3499;
            --hl-blue-light:  #EEF2FF;
            --hl-yellow:      #F7C419;
            --hl-yellow-dark: #D4A800;
            --hl-sidebar-w:   245px;
        }

        html, body { height: 100%; margin: 0; }

        body {
            background: #F4F6FB;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            flex-direction: column;
        }

        /* ── Sidebar ───────────────────────────── */
        #sidebar {
            width: var(--hl-sidebar-w);
            min-width: var(--hl-sidebar-w);
            background: var(--hl-blue);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        #sidebar .mt-auto {
            margin-top: auto;
        }

        .sidebar-brand {
            padding: 1.1rem 1.25rem 1rem;
            display: flex;
            align-items: center;
            gap: .6rem;
            border-bottom: 1px solid rgba(255,255,255,.15);
            text-decoration: none;
        }

        .hl-logo {
            height: 48px;
            width: auto;
            flex-shrink: 0;
        }

        .nav-section {
            padding: .85rem 1.25rem .3rem;
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: rgba(255,255,255,.45);
        }

        #sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: .55rem;
            padding: .42rem 1.25rem;
            font-size: .84rem;
            color: rgba(255,255,255,.75);
            border-left: 3px solid transparent;
            transition: background .12s, color .12s, border-color .12s;
            text-decoration: none;
        }

        #sidebar .nav-link:hover {
            background: rgba(255,255,255,.1);
            color: #fff;
        }

        #sidebar .nav-link.active {
            background: rgba(0,0,0,.18);
            color: #fff;
            font-weight: 600;
            border-left-color: var(--hl-yellow);
        }

        #sidebar .nav-link i {
            font-size: .95rem;
            width: 1.1rem;
            text-align: center;
            flex-shrink: 0;
        }

        /* ── Main area ─────────────────────────── */
        #main-wrapper { display: flex; flex: 1; }

        #content-area {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .page-header {
            background: #fff;
            border-bottom: 1px solid #DDE3F0;
            padding: .85rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .page-header-icon {
            width: 6px;
            height: 24px;
            background: var(--hl-yellow);
            border-radius: 3px;
            flex-shrink: 0;
        }

        .page-header h5 {
            margin: 0;
            font-weight: 700;
            color: #1A2A6C;
            font-size: .95rem;
            letter-spacing: .01em;
        }

        .page-body { padding: 1.25rem 1.5rem; flex: 1; }

        /* ── Cards ─────────────────────────────── */
        .card {
            border: 1px solid #DDE3F0;
            border-radius: .6rem;
            box-shadow: 0 1px 4px rgba(59,99,232,.06);
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #DDE3F0;
            font-weight: 700;
            color: #1A2A6C;
            padding: .8rem 1.25rem;
            border-radius: .6rem .6rem 0 0 !important;
            font-size: .9rem;
        }

        .card-footer {
            background: #fff;
            border-top: 1px solid #DDE3F0;
            border-radius: 0 0 .6rem .6rem !important;
        }

        /* ── Tables ────────────────────────────── */
        .table thead th {
            background: var(--hl-blue-light);
            font-size: .73rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--hl-blue);
            border-bottom: 2px solid #D0DAFF;
            white-space: nowrap;
            font-weight: 700;
        }

        .table tbody td { vertical-align: middle; font-size: .875rem; }

        .table-hover tbody tr:hover { background: #F7F9FF; }

        /* ── Badges ────────────────────────────── */
        .status-badge { font-size: .75rem; padding: .3em .65em; }
        .status-cell  { min-width: 150px; }

        /* ── Buttons ───────────────────────────── */
        .btn-primary {
            background: var(--hl-blue);
            border-color: var(--hl-blue);
        }
        .btn-primary:hover {
            background: var(--hl-blue-dark);
            border-color: var(--hl-blue-dark);
        }

        .btn-warning, .btn-hl {
            background: var(--hl-yellow);
            border-color: var(--hl-yellow-dark);
            color: #1A2A6C;
            font-weight: 600;
        }
        .btn-warning:hover, .btn-hl:hover {
            background: var(--hl-yellow-dark);
            border-color: var(--hl-yellow-dark);
            color: #1A2A6C;
        }

        /* ── Forms ─────────────────────────────── */
        input[type="date"],
        input[type="text"].date-picker,
        input[type="search"] {
            font-size: .875rem;
        }

        input[type="search"]:focus,
        .form-control:focus,
        .form-select:focus {
            border-color: var(--hl-blue);
            box-shadow: 0 0 0 .2rem rgba(59,99,232,.18);
        }

        /* ── Pagination ─────────────────────────── */
        .card-footer .pagination { margin-bottom: 0; font-size: .875rem; }

        .page-item.active .page-link {
            background: var(--hl-blue);
            border-color: var(--hl-blue);
        }

        .page-link { color: var(--hl-blue); }
        .page-link:hover { color: var(--hl-blue-dark); }

        /* ── Search bar ─────────────────────────── */
        .search-form input[type="search"] {
            border-color: #D0DAFF;
        }
        .search-form .btn-outline-secondary {
            border-color: #D0DAFF;
            color: var(--hl-blue);
        }
        .search-form .btn-outline-secondary:hover {
            background: var(--hl-blue-light);
            color: var(--hl-blue);
            border-color: var(--hl-blue);
        }
    </style>
    @stack('styles')
</head>
<body>

<div id="main-wrapper">

    {{-- Sidebar --}}
    <nav id="sidebar">
        <a class="sidebar-brand" href="{{ url('/portal/billing/processing/payments') }}">
            <img src="/images/halolets-logo.png" alt="Halolets" class="hl-logo">
        </a>

        <div class="nav-section mt-1">Fault Support Requests</div>

        <a href="{{ route('portal.operations.calls.index') }}"
        class="nav-link {{ request()->routeIs('portal.operations.calls.*') ? 'active' : '' }}">
            <i class="bi bi-telephone"></i> Calls
        </a>
        <a href="{{ route('portal.operations.work-tasks.index') }}"
        class="nav-link {{ request()->routeIs('portal.operations.work-tasks.*') ? 'active' : '' }}">
            <i class="bi bi-list-task"></i> Work Tasks
        </a>
        <a href="{{ route('portal.operations.resolution-types.index') }}"
        class="nav-link {{ request()->routeIs('portal.operations.resolution-types.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> Resolution Types
        </a>


        <div class="nav-section mt-1">Web Data</div>

        <a href="{{ route('portal.reports.web-sales') }}"
           class="nav-link {{ request()->routeIs('portal.reports.web-sales') ? 'active' : '' }}">
            <i class="bi bi-cart"></i> Web Sales
        </a>
        <a href="{{ route('portal.reports.web-sales-search-queries') }}"
           class="nav-link {{ request()->routeIs('portal.reports.web-sales-search-queries') ? 'active' : '' }}">
            <i class="bi bi-search"></i> Search Queries
        </a>
        <a href="{{ route('portal.reports.web-search-failures') }}"
           class="nav-link {{ request()->routeIs('portal.reports.web-search-failures') ? 'active' : '' }}">
            <i class="bi bi-x-circle"></i> Search Failures
        </a>

        <div class="nav-section mt-1">Customer Data</div>

        <a href="{{ route('portal.customers.index') }}"
           class="nav-link {{ request()->routeIs('portal.customers.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Customers
        </a>

        <div class="nav-section mt-1">Business Accounts</div>

        <a href="{{ route('portal.businesses.index') }}"
           class="nav-link {{ request()->routeIs('portal.businesses.*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Businesses
        </a>

        <a href="{{ route('portal.business-customers.index') }}"
           class="nav-link {{ request()->routeIs('portal.business-customers.*') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i> Business Customers
        </a>

        <div class="nav-section mt-1">AB Testing</div>

        <a href="{{ route('portal.ab-tests.index') }}"
           class="nav-link {{ request()->routeIs('portal.ab-tests.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-steps"></i> AB Tests
        </a>

        <div class="nav-section mt-1">Inventory</div>

        <a href="{{ route('portal.product-list.index') }}"
           class="nav-link {{ request()->routeIs('portal.product-list.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Product List
        </a>
        <a href="{{ route('portal.product-categories.index') }}"
           class="nav-link {{ request()->routeIs('portal.product-categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> Product Categories
        </a>

        <div class="nav-section">Billing - Processing</div>

        <a href="{{ route('portal.billing.processing.payments.index') }}"
           class="nav-link {{ request()->routeIs('portal.billing.processing.payments.*') ? 'active' : '' }}">
            <i class="bi bi-credit-card"></i> Payments
        </a>
        <a href="{{ route('portal.billing.processing.vendor-deficits.index') }}"
           class="nav-link {{ request()->routeIs('portal.billing.processing.vendor-deficits.*') ? 'active' : '' }}">
            <i class="bi bi-exclamation-triangle"></i> Vendor Deficits
        </a>
        <a href="{{ route('portal.billing.processing.franchise-payments.index') }}"
           class="nav-link {{ request()->routeIs('portal.billing.processing.franchise-payments.*') ? 'active' : '' }}">
            <i class="bi bi-shop"></i> Franchise Payments
        </a>

        <div class="nav-section mt-1">Reports</div>

        <a href="{{ route('portal.reports.vendor-sales') }}"
           class="nav-link {{ request()->routeIs('portal.reports.vendor-sales') ? 'active' : '' }}">
            <i class="bi bi-bag"></i> Vendor Sales
        </a>
        <a href="{{ route('portal.reports.vendor-reports') }}"
           class="nav-link {{ request()->routeIs('portal.reports.vendor-reports') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph"></i> Vendor Reports
        </a>
        <a href="{{ route('portal.reports.franchise-reports') }}"
           class="nav-link {{ request()->routeIs('portal.reports.franchise-reports') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i> Franchise Reports
        </a>
        <a href="{{ route('portal.reports.franchise-dispatch-reports') }}"
           class="nav-link {{ request()->routeIs('portal.reports.franchise-dispatch-reports') ? 'active' : '' }}">
            <i class="bi bi-send"></i> Dispatch Reports
        </a>

        <div class="nav-section mt-1">API</div>

        <a href="{{ route('portal.endpoints.index') }}"
        class="nav-link {{ request()->routeIs('portal.endpoints.*') ? 'active' : '' }}">
            <i class="bi bi-plug"></i> Endpoints
        </a>

        <div class="mt-auto border-top border-white border-opacity-25">
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start">
                    <i class="bi bi-box-arrow-right"></i> Sign out
                </button>
            </form>
        </div>
    </nav>

    {{-- Content --}}
    <div id="content-area">
        <div class="page-header">
            <div class="page-header-icon"></div>
            <h5>@yield('breadcrumb')</h5>
        </div>
        <div class="page-body">
            @yield('content')
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[title]').forEach(function (el) {
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });
});
</script>
@stack('scripts')
</body>
</html>
