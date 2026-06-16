<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BusinessController extends Controller
{
    use ExportsCsv;

    public function index()
    {
        $search    = request('search');
        $filterId  = request('id');
        $sort      = in_array(request('sort'), ['b.id', 'b.name', 'customer_count']) ? request('sort') : 'b.id';
        $direction = request('direction') === 'desc' ? 'desc' : 'asc';

        $query = Business::query()
            ->from('businesses as b')
            ->leftJoin('business_customer as bc', 'b.id', '=', 'bc.business_id')
            ->select('b.*', DB::raw('COUNT(bc.customer_id) as customer_count'))
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('b.id',   'like', "%{$search}%")
                ->orWhere('b.name','like', "%{$search}%")
            ))
            ->when($filterId, fn ($q) => $q->where('b.id', (int) $filterId))
            ->groupBy('b.id', 'b.name', 'b.created_at', 'b.updated_at')
            ->orderBy($sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('businesses.csv',
                ['ID', 'Business Name', 'Contact Count'],
                $query->get(),
                fn ($r) => [$r->id, $r->name, $r->customer_count]
            );
        }

        $businesses = $query->paginate(15)->appends(request()->only(['search', 'id', 'sort', 'direction']));

        return view('portal.businesses.index', compact('businesses'), [
            'sort'      => $sort, 'direction' => $direction,
        ]);
    }

    public function update(int $id): JsonResponse
    {
        $name = request('name');

        if (empty($name)) {
            return response()->json(['error' => 'Name is required.'], 422);
        }

        Business::query()
            ->where('id', $id)
            ->update(['name' => $name]);

        return response()->json(['success' => true]);
    }
}
