<?php

use App\Models\Business;
use App\Models\BusinessCustomer;
use App\Models\Customer;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

$sizes = [6, 1, 3, 3, 7, 6, 10, 2, 4, 1, 3, 5, 1, 2, 6, 5, 3, 5, 8, 1, 8, 6, 3, 2, 12, 3, 2, 2, 9, 5, 8, 7, 5, 13, 4, 5, 8, 6, 9, 5, 6, 1, 3, 3, 1, 3, 2, 3, 6, 4, 4, 3, 3, 11, 6, 6, 2, 7, 2, 4, 15, 6, 5, 6, 9, 7, 3, 1, 3, 3, 3, 12, 9, 3, 6, 4, 10, 4, 3, 3, 5, 3, 5, 10, 4, 3, 15, 5, 2, 1, 2, 6, 8, 4, 1, 3, 6];

$bizIds  = Business::query()->orderBy('id')->pluck('id')->toArray();
$custIds = Customer::query()->orderBy('id')->pluck('id')->toArray();

$nBiz = count($sizes);
$keepIds = array_slice($bizIds, 0, $nBiz);

$pos = 0;
$updates = [];
foreach ($sizes as $i => $count) {
    $bizId = $keepIds[$i];
    for ($j = 0; $j < $count; $j++) {
        $updates[] = ['customer_id' => $custIds[$pos], 'business_id' => $bizId];
        $pos++;
    }
}

foreach ($updates as $u) {
    BusinessCustomer::query()
        ->where('customer_id', $u['customer_id'])
        ->update(['business_id' => $u['business_id']]);
}

$deleteIds = array_slice($bizIds, $nBiz);
if ($deleteIds) {
    Business::query()->whereIn('id', $deleteIds)->delete();
}

echo "Done: {$nBiz} businesses, " . count($updates) . " customers reassigned, " . count($deleteIds) . " businesses deleted.\n";
