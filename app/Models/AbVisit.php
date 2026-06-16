<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class AbVisit extends Model
{
    protected $fillable = [
        'ip_address',
        'ab_test_id',
        'group',
        'stage',
    ];

    /**
     * @return BelongsTo<AbTest, $this>
     */
    public function abTest(): BelongsTo
    {
        return $this->belongsTo(AbTest::class);
    }

    /**
     * @return BelongsTo<AbStage, $this>
     */
    public function abStage(): BelongsTo
    {
        return $this->belongsTo(AbStage::class, 'stage');
    }

    /**
     * Funnel breakdown aggregated live from visit rows.
     *
     * @return Builder<AbVisit>
     */
    public static function breakdownQuery(?string $search, ?string $experiment, ?string $group): Builder
    {
        $minStage = static::query()
            ->select('ab_test_id', 'group', DB::raw('MIN(stage) as min_stage'))
            ->groupBy('ab_test_id', 'group');

        $baseline = static::query()
            ->from('ab_visits as base')
            ->joinSub($minStage, 'ms', fn ($j) =>
                $j->on('base.ab_test_id', '=', 'ms.ab_test_id')
                  ->on('base.group',      '=', 'ms.group')
                  ->on('base.stage',      '=', 'ms.min_stage')
            )
            ->select('base.ab_test_id', 'base.group', DB::raw('COUNT(*) as baseline_total'))
            ->groupBy('base.ab_test_id', 'base.group');

        return static::query()
            ->from('ab_visits as av')
            ->leftJoin('ab_tests as at', 'av.ab_test_id', '=', 'at.id')
            ->leftJoin('ab_stages as s', 'av.stage', '=', 's.id')
            ->leftJoinSub($baseline, 'bl', fn ($j) =>
                $j->on('av.ab_test_id', '=', 'bl.ab_test_id')
                  ->on('av.group',      '=', 'bl.group')
            )
            ->select(
                'at.name as experiment', 'av.group', 'av.stage', 's.name as stage_name',
                DB::raw('COUNT(*) as total_visits'),
                DB::raw('ROUND(COUNT(*) * 100.0 / MAX(bl.baseline_total), 2) as percentage_visits')
            )
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('at.name', 'like', "%{$search}%")
                ->orWhere('av.group', 'like', "%{$search}%")
            ))
            ->when($experiment, fn ($q) => $q->where('at.name', $experiment))
            ->when($group, fn ($q) => $q->where('av.group', $group))
            ->groupBy('av.ab_test_id', 'at.name', 'av.group', 'av.stage', 's.name')
            ->orderBy('experiment')
            ->orderBy('av.group')
            ->orderBy('av.stage');
    }

    /**
     * Paginated visit listing with test and stage names joined in.
     *
     * @return Builder<AbVisit>
     */
    public static function listingQuery(
        ?string $search,
        ?string $testId,
        ?string $group,
        ?string $stage,
        string $sort,
        string $direction,
    ): Builder {
        return static::query()
            ->from('ab_visits as av')
            ->leftJoin('ab_tests as at', 'av.ab_test_id', '=', 'at.id')
            ->leftJoin('ab_stages as s', 'av.stage', '=', 's.id')
            ->select(
                'av.id', 'av.ip_address', 'av.ab_test_id', 'at.name as test_name',
                'av.group', 'av.stage', 's.name as stage_name', 'av.created_at',
            )
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('av.ip_address', 'like', "%{$search}%")
                ->orWhere('at.name', 'like', "%{$search}%")
            ))
            ->when($testId, fn ($q) => $q->where('av.ab_test_id', $testId))
            ->when($group, fn ($q) => $q->where('av.group', $group))
            ->when($stage, fn ($q) => $q->where('av.stage', $stage))
            ->orderBy($sort, $direction);
    }
}
