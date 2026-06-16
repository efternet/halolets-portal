<?php

namespace App\Http\Controllers\Portal\Billing\Processing;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateVendorDeficitRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class VendorDeficitController extends Controller
{
    use ExportsCsv;

    public function index()
    {
        $search       = request('search');
        $filterStatus = request('status');
        $dateFrom     = request('date_from');
        $dateTo       = request('date_to');
        $sort         = in_array(request('sort'), ['id', 'amount', 'date', 'payment_due_by', 'status', 'recorded_on'])
                        ? request('sort') : 'id';
        $direction    = request('direction') === 'desc' ? 'desc' : 'asc';

        $query = DB::table('vendor_deficits')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('id',        'like', "%{$search}%")
                ->orWhere('sales_id','like', "%{$search}%")
                ->orWhere('status',  'like', "%{$search}%")
            ))
            ->when($filterStatus, fn ($q) => $q->where('status', $filterStatus))
            ->when($dateFrom, fn ($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('date', '<=', $dateTo))
            ->orderBy($sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('vendor-deficits.csv',
                ['ID', 'Sales ID', 'Amount', 'Date', 'Payment Due By', 'Recorded On', 'Last Updated', 'Status'],
                $query->get(),
                fn ($r) => [$r->id, $r->sales_id, $r->amount, $r->date, $r->payment_due_by, $r->recorded_on, $r->last_status_update, $r->status]
            );
        }

        $deficits = $query->paginate(15)->appends(request()->only(['search', 'status', 'date_from', 'date_to', 'sort', 'direction']));

        return view('portal.billing.processing.vendor-deficits.index', compact('deficits'), [
            'sort'         => $sort, 'direction'    => $direction,
            'filterStatus' => $filterStatus, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo,
        ]);
    }

    public function update(UpdateVendorDeficitRequest $request): JsonResponse
    {
        $id            = $request->validated('id');
        $date          = $request->validated('date');
        $paymentDueBy  = $request->validated('payment_due_by');
        $status        = $request->validated('status');

        DB::table('vendor_deficits')
            ->where('id', $id)
            ->update([
                'date'               => $date ?: null,
                'payment_due_by'     => $paymentDueBy ?: null,
                'status'             => $status,
                'last_status_update' => now()->toDateString(),
                'updated_at'         => now(),
            ]);

        return response()->json([
            'success' => true,
            'status'  => $status,
        ]);
    }
}
