<?php

namespace App\Http\Controllers\Portal\Reports;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Models\FranchiseDispatchReport;
use App\Models\FranchiseReport;
use App\Models\VendorReport;
use App\Models\VendorSale;

class ReportsController extends Controller
{
    use ExportsCsv;

    public function franchiseDispatchReports()
    {
        $search         = request('search');
        $filterCurrency = request('currency');
        $dateFrom       = request('date_from');
        $dateTo         = request('date_to');
        $sort           = in_array(request('sort'), ['id', 'dispatch_report_date', 'created_at'])
                          ? request('sort') : 'dispatch_report_date';
        $direction      = request('direction') === 'asc' ? 'asc' : 'desc';

        $currencies = FranchiseDispatchReport::query()->distinct()->orderBy('currency')->pluck('currency');

        $query = FranchiseDispatchReport::query()
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('id',            'like', "%{$search}%")
                ->orWhere('filename',    'like', "%{$search}%")
                ->orWhere('currency',    'like', "%{$search}%")
                ->orWhere('franchise_id','like', "%{$search}%")
            ))
            ->when($filterCurrency, fn ($q) => $q->where('currency', $filterCurrency))
            ->when($dateFrom, fn ($q) => $q->whereDate('dispatch_report_date', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('dispatch_report_date', '<=', $dateTo))
            ->orderBy($sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('franchise-dispatch-reports.csv',
                ['ID', 'Franchise ID', 'Filename', 'Currency', 'Dispatch Date', 'Created'],
                $query->get(),
                fn ($r) => [$r->id, $r->franchise_id, $r->filename, $r->currency, $r->dispatch_report_date, $r->created_at]
            );
        }

        $reports = $query->paginate(15)->appends(request()->only(['search', 'currency', 'date_from', 'date_to', 'sort', 'direction']));

        return view('portal.reports.franchise-dispatch-reports.index', compact('reports', 'currencies'), [
            'sort'           => $sort, 'direction'      => $direction,
            'filterCurrency' => $filterCurrency, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo,
        ]);
    }

    public function franchiseReports()
    {
        $search    = request('search');
        $dateFrom  = request('date_from');
        $dateTo    = request('date_to');
        $sort      = in_array(request('sort'), ['id', 'created_at', 'updated_at']) ? request('sort') : 'id';
        $direction = request('direction') === 'desc' ? 'desc' : 'asc';

        $query = FranchiseReport::query()
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('id',        'like', "%{$search}%")
                ->orWhere('filename','like', "%{$search}%")
            ))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderBy($sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('franchise-reports.csv',
                ['ID', 'Filename', 'Created', 'Updated'],
                $query->get(),
                fn ($r) => [$r->id, $r->filename, $r->created_at, $r->updated_at]
            );
        }

        $reports = $query->paginate(15)->appends(request()->only(['search', 'date_from', 'date_to', 'sort', 'direction']));

        return view('portal.reports.franchise-reports.index', compact('reports'), [
            'sort'      => $sort, 'direction' => $direction,
            'dateFrom'  => $dateFrom, 'dateTo' => $dateTo,
        ]);
    }

    public function vendorReports()
    {
        $search       = request('search');
        $filterStatus = request('status');
        $dateFrom     = request('date_from');
        $dateTo       = request('date_to');
        $sort         = in_array(request('sort'), ['id', 'reported_on', 'report_last_requested_on', 'report_first_processed', 'report_last_processed', 'report_status'])
                        ? request('sort') : 'reported_on';
        $direction    = request('direction') === 'asc' ? 'asc' : 'desc';

        $statuses = VendorReport::query()->whereNotNull('report_status')->distinct()->orderBy('report_status')->pluck('report_status');

        $query = VendorReport::query()
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('id',             'like', "%{$search}%")
                ->orWhere('filename',     'like', "%{$search}%")
                ->orWhere('report_status','like', "%{$search}%")
            ))
            ->when($filterStatus, fn ($q) => $q->where('report_status', $filterStatus))
            ->when($dateFrom, fn ($q) => $q->whereDate('reported_on', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('reported_on', '<=', $dateTo))
            ->orderBy($sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('vendor-reports.csv',
                ['ID', 'Filename', 'Reported On', 'Last Requested', 'First Processed', 'Last Processed', 'Status'],
                $query->get(),
                fn ($r) => [$r->id, $r->filename, $r->reported_on, $r->report_last_requested_on, $r->report_first_processed, $r->report_last_processed, $r->report_status]
            );
        }

        $reports = $query->paginate(15)->appends(request()->only(['search', 'status', 'date_from', 'date_to', 'sort', 'direction']));

        return view('portal.reports.vendor-reports.index', compact('reports', 'statuses'), [
            'sort'         => $sort, 'direction'    => $direction,
            'filterStatus' => $filterStatus, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo,
        ]);
    }

    public function vendorSales()
    {
        $search         = request('search');
        $filterCurrency = request('currency');
        $salesFrom      = request('sales_from');
        $salesTo        = request('sales_to');
        $amountMin      = request('amount_min');
        $amountMax      = request('amount_max');
        $sort           = in_array(request('sort'), ['vs.id', 'vs.amount', 'vs.sales_date', 'vs.date_out', 'vs.date_in'])
                          ? request('sort') : 'vs.sales_date';
        $direction      = request('direction') === 'asc' ? 'asc' : 'desc';

        $currencies = VendorSale::query()->distinct()->orderBy('currency')->pluck('currency');

        $query = VendorSale::query()
            ->from('vendor_sales as vs')
            ->leftJoin('places as p', 'vs.place_id', '=', 'p.id')
            ->select('vs.id', 'vs.sales_date', 'vs.date_out', 'vs.date_in', 'vs.customer', 'vs.reference', 'vs.regulatory_code', 'vs.amount', 'vs.currency', 'p.city')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('vs.id',               'like', "%{$search}%")
                ->orWhere('vs.customer',       'like', "%{$search}%")
                ->orWhere('vs.reference',      'like', "%{$search}%")
                ->orWhere('vs.regulatory_code','like', "%{$search}%")
                ->orWhere('p.city',            'like', "%{$search}%")
                ->orWhere('vs.currency',       'like', "%{$search}%")
            ))
            ->when($filterCurrency, fn ($q) => $q->where('vs.currency', $filterCurrency))
            ->when($salesFrom,  fn ($q) => $q->whereDate('vs.sales_date', '>=', $salesFrom))
            ->when($salesTo,    fn ($q) => $q->whereDate('vs.sales_date', '<=', $salesTo))
            ->when($amountMin !== null && $amountMin !== '', fn ($q) => $q->where('vs.amount', '>=', $amountMin))
            ->when($amountMax !== null && $amountMax !== '', fn ($q) => $q->where('vs.amount', '<=', $amountMax))
            ->orderBy($sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('vendor-sales.csv',
                ['ID', 'Customer', 'Reference', 'City', 'Reg. Code', 'Amount', 'Currency', 'Sales Date', 'Date Out', 'Date In'],
                $query->get(),
                fn ($r) => [$r->id, $r->customer, $r->reference, $r->city, $r->regulatory_code, $r->amount, $r->currency, $r->sales_date, $r->date_out, $r->date_in]
            );
        }

        $sales = $query->paginate(15)->appends(request()->only(['search', 'currency', 'sales_from', 'sales_to', 'amount_min', 'amount_max', 'sort', 'direction']));

        return view('portal.reports.vendor-sales.index', compact('sales', 'currencies'), [
            'sort'           => $sort, 'direction'      => $direction,
            'filterCurrency' => $filterCurrency, 'salesFrom' => $salesFrom, 'salesTo' => $salesTo,
            'amountMin'      => $amountMin, 'amountMax' => $amountMax,
        ]);
    }
}
