<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

$sizes = [6, 1, 3, 3, 7, 6, 10, 2, 4, 1, 3, 5, 1, 2, 6, 5, 3, 5, 8, 1, 8, 6, 3, 2, 12, 3, 2, 2, 9, 5, 8, 7, 5, 13, 4, 5, 8, 6, 9, 5, 6, 1, 3, 3, 1, 3, 2, 3, 6, 4, 4, 3, 3, 11, 6, 6, 2, 7, 2, 4, 15, 6, 5, 6, 9, 7, 3, 1, 3, 3, 3, 12, 9, 3, 6, 4, 10, 4, 3, 3, 5, 3, 5, 10, 4, 3, 15, 5, 2, 1, 2, 6, 8, 4, 1, 3, 6];

$bizIds  = DB::table('businesses')->orderBy('id')->pluck('id')->toArray();
$custIds = DB::table('business_customers')->orderBy('id')->pluck('id')->toArray();

$nBiz = count($sizes);
$keepIds = array_slice($bizIds, 0, $nBiz);

// Assign customers to businesses
$pos = 0;
$updates = [];
foreach ($sizes as $i => $count) {
    $bizId = $keepIds[$i];
    for ($j = 0; $j < $count; $j++) {
        $updates[] = ['cust_id' => $custIds[$pos], 'biz_id' => $bizId];
        $pos++;
    }
}

// Apply updates
foreach ($updates as $u) {
    DB::table('business_customers')
      ->where('id', $u['cust_id'])
      ->update(['business_id' => $u['biz_id']]);
}

// Delete unused businesses
$deleteIds = array_slice($bizIds, $nBiz);
if ($deleteIds) {
    DB::table('businesses')->whereIn('id', $deleteIds)->delete();
}

echo "Done: {$nBiz} businesses, " . count($updates) . " customers reassigned, " . count($deleteIds) . " businesses deleted.\n";
