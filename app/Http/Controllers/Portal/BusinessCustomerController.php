<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class BusinessCustomerController extends Controller
{
    use ExportsCsv;

    public function index()
    {
        $search         = request('search');
        $filterBusiness = request('business_id');
        $filterContact  = request('contact_accepted');
        $sort           = in_array(request('sort'), ['c.id', 'c.first_name', 'c.surname', 'c.email', 'c.country', 'c.city', 'c.last_contacted', 'b.name'])
                          ? request('sort') : 'c.id';
        $direction      = request('direction') === 'desc' ? 'desc' : 'asc';

        $businesses = Business::query()->orderBy('name')->get();

        $query = Customer::query()
            ->from('customers as c')
            ->join('business_customer as bc', 'c.id', '=', 'bc.customer_id')
            ->join('businesses as b', 'bc.business_id', '=', 'b.id')
            ->select('c.*', 'b.id as business_id', 'b.name as business_name')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('c.id',         'like', "%{$search}%")
                ->orWhere('c.first_name','like', "%{$search}%")
                ->orWhere('c.surname',  'like', "%{$search}%")
                ->orWhere('c.email',    'like', "%{$search}%")
                ->orWhere('b.name',     'like', "%{$search}%")
                ->orWhere('c.country',  'like', "%{$search}%")
                ->orWhere('c.city',     'like', "%{$search}%")
            ))
            ->when($filterBusiness, fn ($q) => $q->where('bc.business_id', $filterBusiness))
            ->when($filterContact !== null && $filterContact !== '', fn ($q) => $q->where('c.contact_accepted', (int) $filterContact))
            ->orderBy($sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('business-customers.csv',
                ['ID', 'Business ID', 'Business Name', 'First Name', 'Surname', 'Email', 'Country', 'City', 'Terms Accepted', 'Contact OK', 'Last Contacted'],
                $query->get(),
                fn ($r) => [$r->id, $r->business_id, $r->business_name, $r->first_name, $r->surname, $r->email, $r->country, $r->city, $r->terms_accepted ? 'Yes' : 'No', $r->contact_accepted ? 'Yes' : 'No', $r->last_contacted]
            );
        }

        $customers = $query->paginate(15)->appends(request()->only(['search', 'business_id', 'contact_accepted', 'sort', 'direction']));

        return view('portal.business-customers.index', compact('customers', 'businesses'), [
            'sort'           => $sort, 'direction'      => $direction,
            'filterBusiness' => $filterBusiness, 'filterContact'  => $filterContact,
        ]);
    }

    public function update(int $id): JsonResponse
    {
        $allowed = ['first_name', 'surname', 'email', 'country', 'city', 'contact_accepted', 'last_contacted'];
        $data    = array_intersect_key(request()->all(), array_flip($allowed));

        if (isset($data['contact_accepted'])) {
            $data['contact_accepted'] = (int) $data['contact_accepted'];
        }
        if (array_key_exists('last_contacted', $data)) {
            $data['last_contacted'] = $data['last_contacted'] ?: null;
        }

        Customer::query()->where('id', $id)->update($data);

        return response()->json(['success' => true]);
    }
}
