<?php

namespace Database\Seeders;

use App\Models\ProductList;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProductListSeeder extends Seeder
{
    public function run(): void
    {
        $file   = base_path('data_for_processing/products.csv');
        $handle = fopen($file, 'r');
        fgetcsv($handle); // skip header

        $chunk = [];

        while (($row = fgetcsv($handle)) !== false) {
            [$id, $category, $productName, $brand, $model, $serialNumber,
             $assetTag, $batchNo, $purchaseOrder, $rentalSku, $supplier,
             $conditionGrade, $acquisitionDate, $warrantyExpiry, $notes] = array_pad($row, 15, '');

            $chunk[] = [
                'id'               => (int) $id,
                'category'         => $category !== '' ? $category : null,
                'product_name'     => $productName,
                'brand'            => $brand !== '' ? $brand : null,
                'model'            => $model !== '' ? $model : null,
                'serial_number'    => $serialNumber !== '' ? $serialNumber : null,
                'asset_tag'        => $assetTag !== '' ? $assetTag : null,
                'batch_no'         => $batchNo !== '' ? $batchNo : null,
                'purchase_order'   => $purchaseOrder !== '' ? $purchaseOrder : null,
                'rental_sku'       => $rentalSku !== '' ? $rentalSku : null,
                'supplier'         => $supplier !== '' ? $supplier : null,
                'condition_grade'  => $conditionGrade !== '' ? $conditionGrade : null,
                'acquisition_date' => $this->parseDate($acquisitionDate),
                'warranty_expiry'  => $this->parseDate($warrantyExpiry),
                'notes'            => $notes !== '' ? $notes : null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            if (count($chunk) === 500) {
                ProductList::query()->insertOrIgnore($chunk);
                $chunk = [];
            }
        }

        if (!empty($chunk)) {
            ProductList::query()->insertOrIgnore($chunk);
        }

        fclose($handle);
        $this->command->info('product_list seeded: ' . ProductList::query()->count() . ' records.');
    }

    private function parseDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') return null;

        // DD-MM-YY
        try { return Carbon::createFromFormat('d-m-y', $value)->format('Y-m-d'); } catch (\Exception) {}
        // DD-MM-YYYY
        try { return Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d'); } catch (\Exception) {}
        // YYYY-MM-DD (already correct)
        try { return Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d'); } catch (\Exception) {}

        return null;
    }
}
