<?php
namespace App\Http\Controllers;

use App\Models\{Store, Order, Target};
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class NotificationController
{
    // Cek toko mana yang progress GMV-nya di bawah threshold di pertengahan bulan
    public static function getTargetAlerts(): array
    {
        return Cache::remember('target_alerts_' . now()->format('Y-m-d'), 3600, function () {
            $now    = now();
            $year   = $now->year;
            $month  = $now->month;
            $dayPct = $now->day / $now->daysInMonth; // sudah lewat berapa % bulan ini

            // Hanya relevan jika sudah lewat 30% bulan (hari ke-9+)
            if ($dayPct < 0.3) return [];

            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end   = $now->copy()->endOfDay();

            $alerts  = [];
            $stores  = Store::where('is_active', true)->get();
            $gmvStatuses = Order::gmvStatuses();

            foreach ($stores as $store) {
                $target = Target::where('store_id', $store->id)
                    ->where('month', $month)->where('year', $year)
                    ->value('gmv_target');

                if (!$target || $target <= 0) continue;

                $actual = Order::where('store_id', $store->id)
                    ->whereIn('status', $gmvStatuses)
                    ->whereBetween('order_date', [$start, $end])
                    ->sum('gmv');

                $actualPct = $actual / $target * 100;
                $expectedPct = $dayPct * 100; // seharusnya sudah capai berapa %

                // Alert jika actual < 60% dari yang seharusnya
                if ($actualPct < $expectedPct * 0.6) {
                    $alerts[] = [
                        'store'       => $store->name,
                        'brand'       => $store->brand,
                        'actual_pct'  => round($actualPct, 1),
                        'expected_pct'=> round($expectedPct, 1),
                        'gap'         => round($expectedPct - $actualPct, 1),
                    ];
                }
            }

            usort($alerts, fn($a, $b) => $a['actual_pct'] <=> $b['actual_pct']);
            return $alerts;
        });
    }
}
