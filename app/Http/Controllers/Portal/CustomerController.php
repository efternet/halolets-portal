<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    use ExportsCsv;

    public function index()
    {
        $search            = request('search');
        $filterCountry     = request('country');
        $filterTerms       = request('terms_accepted');
        $filterContact     = request('contact_accepted');
        $lastContactedFrom = request('last_contacted_from');
        $lastContactedTo   = request('last_contacted_to');
        $sort              = in_array(request('sort'), ['id', 'first_name', 'surname', 'email', 'country', 'city', 'last_contacted'])
                             ? request('sort') : 'id';
        $direction         = request('direction') === 'asc' ? 'asc' : 'desc';

        $countries = Customer::query()->whereNotNull('country')->distinct()->orderBy('country')->pluck('country');

        $query = Customer::query()
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('id',         'like', "%{$search}%")
                ->orWhere('first_name','like', "%{$search}%")
                ->orWhere('surname',  'like', "%{$search}%")
                ->orWhere('email',    'like', "%{$search}%")
                ->orWhere('country',  'like', "%{$search}%")
                ->orWhere('city',     'like', "%{$search}%")
            ))
            ->when($filterCountry !== null && $filterCountry !== '', fn ($q) => $q->where('country', $filterCountry))
            ->when($filterTerms !== null && $filterTerms !== '', fn ($q) => $q->where('terms_accepted', (int) $filterTerms))
            ->when($filterContact !== null && $filterContact !== '', fn ($q) => $q->where('contact_accepted', (int) $filterContact))
            ->when($lastContactedFrom, fn ($q) => $q->whereDate('last_contacted', '>=', $lastContactedFrom))
            ->when($lastContactedTo,   fn ($q) => $q->whereDate('last_contacted', '<=', $lastContactedTo))
            ->orderBy($sort, $direction);

        if ($this->wantsCsvExport()) {
            return $this->streamCsv('customers.csv',
                ['ID', 'First Name', 'Surname', 'Email', 'Country', 'City', 'Terms Accepted', 'Contact OK', 'Last Contacted'],
                $query->get(),
                fn ($r) => [$r->id, $r->first_name, $r->surname, $r->email, $r->country, $r->city, $r->terms_accepted ? 'Yes' : 'No', $r->contact_accepted ? 'Yes' : 'No', $r->last_contacted]
            );
        }

        $customers = $query->paginate(15)->appends(request()->only([
            'search', 'country', 'terms_accepted', 'contact_accepted',
            'last_contacted_from', 'last_contacted_to', 'sort', 'direction',
        ]));

        return view('portal.customers.index', compact('customers', 'countries'), [
            'sort'              => $sort, 'direction'         => $direction,
            'filterCountry'     => $filterCountry, 'filterTerms'       => $filterTerms,
            'filterContact'     => $filterContact, 'lastContactedFrom' => $lastContactedFrom, 'lastContactedTo' => $lastContactedTo,
        ]);
    }

    public function update(UpdateCustomerRequest $request): JsonResponse
    {
        Customer::query()
            ->where('id', $request->validated('id'))
            ->update([
                'first_name'       => $request->validated('first_name'),
                'surname'          => $request->validated('surname'),
                'email'            => $request->validated('email'),
                'country'          => $request->validated('country') ?: null,
                'city'             => $request->validated('city') ?: null,
                'contact_accepted' => $request->validated('contact_accepted') ? 1 : 0,
                'last_contacted'   => $request->validated('last_contacted') ?: null,
            ]);

        return response()->json(['success' => true]);
    }
}
