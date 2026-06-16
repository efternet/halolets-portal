<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessCustomer;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BusinessAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('data_for_processing/business_accounts.csv');
        $handle = fopen($path, 'r');

        fgetcsv($handle); // skip header

        $businesses = [];
        $customers  = [];

        while (($row = fgetcsv($handle)) !== false) {
            [$id, $firstName, $surname, $businessName, $email, $country, $city,
             $termsAccepted, $contactAccepted, $lastContacted, $createdAt, $updatedAt] = array_pad($row, 12, null);

            if (empty($id) || empty($businessName)) {
                continue;
            }

            $businesses[$businessName] = $businessName;

            $customers[] = [
                'id'              => (int) $id,
                'business_name'   => $businessName,
                'first_name'      => $firstName,
                'surname'         => $surname,
                'email'           => $email,
                'country'         => $country ?: null,
                'city'            => $city ?: null,
                'terms_accepted'  => (int) $termsAccepted,
                'contact_accepted'=> (int) $contactAccepted,
                'last_contacted'  => $lastContacted ? Carbon::parse($lastContacted)->format('Y-m-d H:i:s') : null,
                'created_at'      => $createdAt  ? Carbon::parse($createdAt)->format('Y-m-d H:i:s') : now(),
                'updated_at'      => $updatedAt  ? Carbon::parse($updatedAt)->format('Y-m-d H:i:s') : now(),
            ];
        }

        fclose($handle);

        $now = now();
        $businessMap = [];
        foreach (array_values($businesses) as $name) {
            Business::query()->insertOrIgnore([
                'name'       => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $businessMap[$name] = Business::query()->where('name', $name)->value('id');
        }

        $customerRows = array_map(fn ($c) => [
            'id'               => $c['id'],
            'first_name'       => $c['first_name'],
            'surname'          => $c['surname'],
            'email'            => $c['email'],
            'country'          => $c['country'],
            'city'             => $c['city'],
            'terms_accepted'   => $c['terms_accepted'],
            'contact_accepted' => $c['contact_accepted'],
            'last_contacted'   => $c['last_contacted'],
            'created_at'       => $c['created_at'],
            'updated_at'       => $c['updated_at'],
        ], $customers);

        foreach (array_chunk($customerRows, 100) as $chunk) {
            Customer::query()->insertOrIgnore($chunk);
        }

        $pivotRows = array_map(fn ($c) => [
            'business_id' => $businessMap[$c['business_name']],
            'customer_id' => $c['id'],
        ], $customers);

        foreach (array_chunk($pivotRows, 100) as $chunk) {
            BusinessCustomer::query()->insertOrIgnore($chunk);
        }

        $this->command->info('Imported ' . count($customerRows) . ' business customers and ' . count($businessMap) . ' businesses.');
    }
}
