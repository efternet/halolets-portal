<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomersSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('data_for_processing/customers.csv');
        if (! is_readable($path)) {
            $path = base_path('customers.csv');
        }

        $handle = fopen($path, 'r');
        fgetcsv($handle); // skip header

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            [$id, $firstName, $surname, $email, $country, $city,
             $termsAccepted, $contactAccepted, $lastContacted, $createdAt, $updatedAt] = array_pad($row, 11, null);

            if (empty($id)) {
                continue;
            }

            $rows[] = [
                'id'               => (int) $id,
                'first_name'       => $firstName,
                'surname'          => $surname,
                'email'            => $email,
                'country'          => $country ?: null,
                'city'             => $city ?: null,
                'terms_accepted'   => (int) $termsAccepted,
                'contact_accepted' => (int) $contactAccepted,
                'last_contacted'   => $this->parseDate($lastContacted),
                'created_at'       => $this->parseDate($createdAt) ?? now()->format('Y-m-d H:i:s'),
                'updated_at'       => $this->parseDate($updatedAt) ?? now()->format('Y-m-d H:i:s'),
            ];
        }

        fclose($handle);

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('customers')->insertOrIgnore($chunk);
        }

        $this->command->info('Imported ' . count($rows) . ' customers.');
    }

    private function parseDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        foreach (['d/m/Y H:i:s', 'd/m/Y H:i', 'Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim($value))->format('Y-m-d H:i:s');
            } catch (\Exception) {
                continue;
            }
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
