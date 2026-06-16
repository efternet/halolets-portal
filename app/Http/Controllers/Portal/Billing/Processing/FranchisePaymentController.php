<?php

namespace App\Http\Controllers\Portal\Billing\Processing;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFranchisePaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FranchisePaymentController extends Controller
{
    use ExportsCsv;

    public function index()
    {
        $search        = request('search');
        $requestedFrom = request('requested_from');
        $requestedTo   = request('requested_to');
        $paymentFrom   = request('payment_from');
        $paymentTo     = request('payment_to');
        $sort          = in_array(request('sort'), ['id', 'amount', 'requested_on', 'payment_date', 'recorded_on', 'last_processed'])
                         ? request('sort') : 'id';
        $direction     = request('direction') === 'desc' ? 'desc' : 'asc';

        $query = DB::table('franchise_payments')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('id',                  'like', "%{$search}%")
                ->orWhere('sales_id',           'like', "%{$search}%")
                ->orWhere('franchise_report_id','like', "%{$search}%")
            ))
            ->when($requestedFrom, fn ($q) => $q->whereDate('requested_on', '>=', $requestedFrom))
            ->when($requestedTo,   fn ($q) => $q->whereDate('requested_on', '<=', $requestedTo))
            ->when($paymentFrom,   fn ($q) => $q->whereDate('payment_date', '>=', $paymentFrom))
            ->when($paymentTo,     fn ($q) => $q->whereDate('payment_date', '<=', $paymentTo))
            ->orderBy($sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('franchise-payments.csv',
                ['ID', 'Sales ID', 'Amount', 'Requested On', 'Payment Date', 'Report ID', 'Recorded On', 'Last Processed'],
                $query->get(),
                fn ($r) => [$r->id, $r->sales_id, $r->amount, $r->requested_on, $r->payment_date, $r->franchise_report_id, $r->recorded_on, $r->last_processed]
            );
        }

        $payments = $query->paginate(15)->appends(request()->only(['search', 'requested_from', 'requested_to', 'payment_from', 'payment_to', 'sort', 'direction']));

        return view('portal.billing.processing.franchise-payments.index', compact('payments'), [
            'sort'          => $sort, 'direction'     => $direction,
            'requestedFrom' => $requestedFrom, 'requestedTo'   => $requestedTo,
            'paymentFrom'   => $paymentFrom,   'paymentTo'     => $paymentTo,
        ]);
    }

    public function update(UpdateFranchisePaymentRequest $request): JsonResponse
    {
        $id           = $request->validated('id');
        $requestedOn  = $request->validated('requested_on');
        $paymentDate  = $request->validated('payment_date');

        DB::table('franchise_payments')
            ->where('id', $id)
            ->update([
                'requested_on'  => $requestedOn ?: null,
                'payment_date'  => $paymentDate ?: null,
                'last_processed' => now()->toDateString(),
                'updated_at'    => now(),
            ]);

        return response()->json(['success' => true]);
    }
}
