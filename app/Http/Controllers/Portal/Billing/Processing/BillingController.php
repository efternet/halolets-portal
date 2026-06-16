<?php

namespace App\Http\Controllers\Portal\Billing\Processing;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    use ExportsCsv;

    public function index()
    {
        $search         = request('search');
        $filterStatus   = request('status');
        $filterCurrency = request('currency');
        $paidFrom       = request('paid_from');
        $paidTo         = request('paid_to');
        $sort           = in_array(request('sort'), ['vp.id', 'vp.amount', 'vp.paid_date', 'fp.payment_date', 'vp.status'])
                          ? request('sort') : 'vp.id';
        $direction      = request('direction') === 'desc' ? 'desc' : 'asc';

        $query = DB::table('vendor_payments as vp')
            ->leftJoin('franchise_payments as fp', 'vp.sales_id', '=', 'fp.sales_id')
            ->select('vp.id', 'vp.sales_id', 'vp.vendor_id', 'vp.amount', 'vp.currency', 'vp.status', 'vp.paid_date', 'fp.payment_date')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('vp.id',         'like', "%{$search}%")
                ->orWhere('vp.sales_id', 'like', "%{$search}%")
                ->orWhere('vp.vendor_id','like', "%{$search}%")
                ->orWhere('vp.status',   'like', "%{$search}%")
                ->orWhere('vp.currency', 'like', "%{$search}%")
            ))
            ->when($filterStatus,   fn ($q) => $q->where('vp.status',   $filterStatus))
            ->when($filterCurrency, fn ($q) => $q->where('vp.currency', $filterCurrency))
            ->when($paidFrom, fn ($q) => $q->whereDate('vp.paid_date', '>=', $paidFrom))
            ->when($paidTo,   fn ($q) => $q->whereDate('vp.paid_date', '<=', $paidTo))
            ->orderBy($sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('payments.csv',
                ['ID', 'Sales ID', 'Vendor ID', 'Amount', 'Currency', 'Status', 'Date Paid-in', 'Date Paid-out'],
                $query->get(),
                fn ($r) => [$r->id, $r->sales_id, $r->vendor_id, $r->amount, $r->currency, $r->status, $r->paid_date, $r->payment_date]
            );
        }

        $payments = $query->paginate(15)->appends(request()->only(['search', 'status', 'currency', 'paid_from', 'paid_to', 'sort', 'direction']));

        return view('portal.billing.processing.payments.index', compact('payments'), [
            'sort'           => $sort, 'direction'      => $direction,
            'filterStatus'   => $filterStatus, 'filterCurrency' => $filterCurrency,
            'paidFrom'       => $paidFrom, 'paidTo'         => $paidTo,
        ]);
    }

    public function update(UpdatePaymentRequest $request): JsonResponse
    {
        $vendorPaymentId = $request->validated('id');
        $paidDate        = $request->validated('paid_date');
        $paymentDate     = $request->validated('payment_date');

        $status = match (true) {
            filled($paidDate) && filled($paymentDate) => 'Paid',
            filled($paidDate) || filled($paymentDate) => 'Underpaid',
            default                                   => 'Pending',
        };

        DB::table('vendor_payments')
            ->where('id', $vendorPaymentId)
            ->update([
                'paid_date'  => $paidDate ?: null,
                'status'     => $status,
                'updated_at' => now(),
            ]);

        $salesId = DB::table('vendor_payments')
            ->where('id', $vendorPaymentId)
            ->value('sales_id');

        DB::table('franchise_payments')
            ->where('sales_id', $salesId)
            ->update([
                'payment_date' => $paymentDate ?: null,
                'updated_at'   => now(),
            ]);

        return response()->json([
            'success' => true,
            'status'  => $status,
        ]);
    }
}
