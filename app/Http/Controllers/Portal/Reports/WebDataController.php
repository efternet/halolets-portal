<?php

namespace App\Http\Controllers\Portal\Reports;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class WebDataController extends Controller
{
    use ExportsCsv;

    public function webSalesSearchQueries()
    {
        $search       = request('search');
        $filterFailed = request('failed');
        $filterReason = request('reason');
        $dateFrom     = request('date_from');
        $dateTo       = request('date_to');
        $dateOutFrom  = request('date_out_from');
        $dateOutTo    = request('date_out_to');
        $dateInFrom   = request('date_in_from');
        $dateInTo     = request('date_in_to');
        $sort         = in_array(request('sort'), ['id', 'failed_date', 'record_created_date', 'date_out', 'date_in'])
                        ? request('sort') : 'id';
        $direction    = request('direction') === 'asc' ? 'asc' : 'desc';

        $failureReasons = DB::table('web_sales_search_queries')
            ->whereNotNull('reason_for_failure')->distinct()->orderBy('reason_for_failure')->pluck('reason_for_failure');

        $query = DB::table('web_sales_search_queries')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('id',                 'like', "%{$search}%")
                ->orWhere('user_ip',          'like', "%{$search}%")
                ->orWhere('user_id',          'like', "%{$search}%")
                ->orWhere('product_id',       'like', "%{$search}%")
                ->orWhere('reason_for_failure','like', "%{$search}%")
            ))
            ->when($filterFailed === 'yes', fn ($q) => $q->whereNotNull('failed_date'))
            ->when($filterFailed === 'no',  fn ($q) => $q->whereNull('failed_date'))
            ->when($filterReason,  fn ($q) => $q->where('reason_for_failure', $filterReason))
            ->when($dateFrom,    fn ($q) => $q->whereDate('record_created_date', '>=', $dateFrom))
            ->when($dateTo,      fn ($q) => $q->whereDate('record_created_date', '<=', $dateTo))
            ->when($dateOutFrom, fn ($q) => $q->whereDate('date_out', '>=', $dateOutFrom))
            ->when($dateOutTo,   fn ($q) => $q->whereDate('date_out', '<=', $dateOutTo))
            ->when($dateInFrom,  fn ($q) => $q->whereDate('date_in', '>=', $dateInFrom))
            ->when($dateInTo,    fn ($q) => $q->whereDate('date_in', '<=', $dateInTo))
            ->orderBy($sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('web-sales-search-queries.csv',
                ['ID', 'User IP', 'User ID', 'Product ID', 'Reason for Failure', 'Failed Date', 'Record Created', 'Date Out', 'Date In'],
                $query->get(),
                fn ($r) => [$r->id, $r->user_ip, $r->user_id, $r->product_id, $r->reason_for_failure, $r->failed_date, $r->record_created_date, $r->date_out, $r->date_in]
            );
        }

        $queries = $query->paginate(15)->appends(request()->only([
            'search', 'failed', 'reason', 'date_from', 'date_to',
            'date_out_from', 'date_out_to', 'date_in_from', 'date_in_to', 'sort', 'direction',
        ]));

        return view('portal.reports.web-sales-search-queries.index', compact('queries', 'failureReasons'), [
            'sort'         => $sort,   'direction'    => $direction,
            'filterFailed' => $filterFailed, 'filterReason' => $filterReason,
            'dateFrom'     => $dateFrom,     'dateTo'       => $dateTo,
            'dateOutFrom'  => $dateOutFrom,  'dateOutTo'    => $dateOutTo,
            'dateInFrom'   => $dateInFrom,   'dateInTo'     => $dateInTo,
        ]);
    }

    public function webSales()
    {
        $search    = request('search');
        $dateFrom  = request('date_from');
        $dateTo    = request('date_to');
        $costMin   = request('cost_min');
        $costMax   = request('cost_max');
        $sort      = in_array(request('sort'), ['id', 'total_cost', 'sold_at']) ? request('sort') : 'sold_at';
        $direction = request('direction') === 'asc' ? 'asc' : 'desc';

        $query = DB::table('web_sales as ws')
            ->leftJoin('web_sales_search_queries as q', 'ws.query_id', '=', 'q.id')
            ->select('ws.id', 'ws.query_id', 'ws.total_cost', 'ws.sold_at',
                     'q.user_id', 'q.user_ip', 'q.product_id')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('ws.id',         'like', "%{$search}%")
                ->orWhere('ws.query_id', 'like', "%{$search}%")
                ->orWhere('q.user_ip',   'like', "%{$search}%")
                ->orWhere('q.user_id',   'like', "%{$search}%")
                ->orWhere('q.product_id','like', "%{$search}%")
            ))
            ->when($dateFrom, fn ($q) => $q->whereDate('ws.sold_at', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('ws.sold_at', '<=', $dateTo))
            ->when($costMin !== null && $costMin !== '', fn ($q) => $q->where('ws.total_cost', '>=', $costMin))
            ->when($costMax !== null && $costMax !== '', fn ($q) => $q->where('ws.total_cost', '<=', $costMax))
            ->orderBy('ws.' . $sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('web-sales.csv',
                ['ID', 'Query ID', 'User IP', 'User ID', 'Product ID', 'Total Cost', 'Sold At'],
                $query->get(),
                fn ($r) => [$r->id, $r->query_id, $r->user_ip, $r->user_id, $r->product_id, $r->total_cost, $r->sold_at]
            );
        }

        $sales = $query->paginate(15)->appends(request()->only(['search', 'date_from', 'date_to', 'cost_min', 'cost_max', 'sort', 'direction']));

        return view('portal.reports.web-sales.index', compact('sales'), [
            'sort'      => $sort, 'direction' => $direction,
            'dateFrom'  => $dateFrom, 'dateTo' => $dateTo,
            'costMin'   => $costMin,  'costMax' => $costMax,
        ]);
    }

    public function webSearchFailures()
    {
        $search       = request('search');
        $filterReason = request('reason');
        $failedFrom   = request('failed_from');
        $failedTo     = request('failed_to');
        $logFrom      = request('log_from');
        $logTo        = request('log_to');
        $sort         = in_array(request('sort'), ['id', 'log_timestamp', 'failed_on', 'reason'])
                        ? request('sort') : 'failed_on';
        $direction    = request('direction') === 'asc' ? 'asc' : 'desc';

        $failureReasons = DB::table('web_search_failures')
            ->whereNotNull('reason')->distinct()->orderBy('reason')->pluck('reason');

        $query = DB::table('web_search_failures')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('id',            'like', "%{$search}%")
                ->orWhere('original_hash','like', "%{$search}%")
                ->orWhere('decoded_id',  'like', "%{$search}%")
                ->orWhere('reason',      'like', "%{$search}%")
                ->orWhere('form_id',     'like', "%{$search}%")
            ))
            ->when($filterReason, fn ($q) => $q->where('reason', $filterReason))
            ->when($failedFrom,   fn ($q) => $q->whereDate('failed_on',    '>=', $failedFrom))
            ->when($failedTo,     fn ($q) => $q->whereDate('failed_on',    '<=', $failedTo))
            ->when($logFrom,      fn ($q) => $q->whereDate('log_timestamp','>=', $logFrom))
            ->when($logTo,        fn ($q) => $q->whereDate('log_timestamp','<=', $logTo))
            ->orderBy($sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('web-search-failures.csv',
                ['ID', 'Log Timestamp', 'Original Hash', 'Decoded ID', 'Failed On', 'Reason', 'Form ID'],
                $query->get(),
                fn ($r) => [$r->id, $r->log_timestamp, $r->original_hash, $r->decoded_id, $r->failed_on, $r->reason, $r->form_id]
            );
        }

        $failures = $query->paginate(15)->appends(request()->only([
            'search', 'reason', 'failed_from', 'failed_to', 'log_from', 'log_to', 'sort', 'direction',
        ]));

        return view('portal.reports.web-search-failures.index', compact('failures', 'failureReasons'), [
            'sort'         => $sort,       'direction'    => $direction,
            'filterReason' => $filterReason, 'failedFrom' => $failedFrom, 'failedTo' => $failedTo,
            'logFrom'      => $logFrom,      'logTo'      => $logTo,
        ]);
    }
}
