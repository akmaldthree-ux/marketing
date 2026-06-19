<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Pic, Store, Order, Financial, AdsPerformance, StoreMetric, Target, Customer, FunnelTarget, DemandForecast, AppNotification};
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- PICs ---
        $pics = [
            ['name' => 'Sinta Amelia', 'email' => 'sinta@dsm.co.id', 'role' => 'pic'],
            ['name' => 'Reza Pratama', 'email' => 'reza@dsm.co.id', 'role' => 'pic'],
            ['name' => 'Dewi Sartika', 'email' => 'dewi@dsm.co.id', 'role' => 'pic'],
            ['name' => 'Budi Santoso', 'email' => 'budi@dsm.co.id', 'role' => 'pic'],
        ];
        $picModels = [];
        foreach ($pics as $p) {
            $picModels[] = Pic::create($p);
        }

        // --- Users ---
        User::create(['name' => 'Admin DSM', 'email' => 'admin@dsm.co.id', 'password' => Hash::make('password'), 'role' => 'admin']);
        foreach ($picModels as $i => $pic) {
            User::create(['name' => $pic->name, 'email' => $pic->email, 'password' => Hash::make('password'), 'role' => 'pic', 'pic_id' => $pic->id]);
        }
        User::create(['name' => 'Manajemen DSM', 'email' => 'viewer@dsm.co.id', 'password' => Hash::make('password'), 'role' => 'public']);

        // --- Stores ---
        $storeData = [
            ['store_name' => 'DTHREE Official Shopee', 'brand' => 'DTHREE', 'platform' => 'Shopee', 'pic_id' => $picModels[0]->id, 'channel_type' => 'mp'],
            ['store_name' => 'DTHREE Official TikTok', 'brand' => 'DTHREE', 'platform' => 'TikTok Shop', 'pic_id' => $picModels[0]->id, 'channel_type' => 'mp'],
            ['store_name' => 'DTHREE Meta Ads', 'brand' => 'DTHREE', 'platform' => 'Meta Ads', 'pic_id' => $picModels[0]->id, 'channel_type' => 'non_mp'],
            ['store_name' => 'HURIM Official Shopee', 'brand' => 'HURIM', 'platform' => 'Shopee', 'pic_id' => $picModels[1]->id, 'channel_type' => 'mp'],
            ['store_name' => 'HURIM Official TikTok', 'brand' => 'HURIM', 'platform' => 'TikTok Shop', 'pic_id' => $picModels[1]->id, 'channel_type' => 'mp'],
            ['store_name' => 'Sarimbit Studio Shopee', 'brand' => 'HURIM', 'platform' => 'Shopee', 'pic_id' => $picModels[2]->id, 'channel_type' => 'mp'],
            ['store_name' => 'ASFARA Official Shopee', 'brand' => 'ASFARA', 'platform' => 'Shopee', 'pic_id' => $picModels[2]->id, 'channel_type' => 'mp'],
            ['store_name' => 'ASFARA Official TikTok', 'brand' => 'ASFARA', 'platform' => 'TikTok Shop', 'pic_id' => $picModels[3]->id, 'channel_type' => 'mp'],
            ['store_name' => 'ASFARA Meta Ads', 'brand' => 'ASFARA', 'platform' => 'Meta Ads', 'pic_id' => $picModels[3]->id, 'channel_type' => 'non_mp'],
        ];
        $stores = [];
        foreach ($storeData as $sd) {
            $stores[] = Store::create($sd);
        }

        // --- Targets ---
        $months = [['m' => 4, 'y' => 2026], ['m' => 5, 'y' => 2026], ['m' => 6, 'y' => 2026], ['m' => 7, 'y' => 2026]];
        $targets_gmv = [
            1 => [4500000000, 4800000000, 5000000000, 5200000000],
            2 => [2800000000, 3000000000, 3200000000, 3300000000],
            3 => [1200000000, 1300000000, 1400000000, 1500000000],
            4 => [2500000000, 2700000000, 2900000000, 3000000000],
            5 => [2000000000, 2200000000, 2400000000, 2500000000],
            6 => [1500000000, 1600000000, 1700000000, 1800000000],
            7 => [3000000000, 3200000000, 3400000000, 3500000000],
            8 => [2500000000, 2700000000, 2900000000, 3100000000],
            9 => [1000000000, 1100000000, 1200000000, 1300000000],
        ];
        foreach ($stores as $s) {
            foreach ($months as $i => $m) {
                Target::create(['store_id' => $s->id, 'month' => $m['m'], 'year' => $m['y'], 'gmv_target' => $targets_gmv[$s->id][$i] ?? 1000000000]);
            }
        }

        // --- Generate Orders, Metrics, Ads (last 90 days) ---
        $skus = ['DSM-001', 'DSM-002', 'DSM-003', 'DSM-004', 'DSM-005', 'DSM-006'];
        $productNames = ['Gamis Premium', 'Dress Casual', 'Blouse Batik', 'Rok Maxi', 'Kemeja Formal', 'Tunik Modern'];
        $buyers = [];
        for ($b = 1; $b <= 200; $b++) {
            $buyers[] = 'buyer_' . str_pad($b, 4, '0', STR_PAD_LEFT);
        }

        $customerMap = [];
        $orderIdx = 1;

        for ($day = 89; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day);
            foreach ($stores as $store) {
                $ordersPerDay = rand(8, 35);
                $gmvPerDay = 0;
                $views = rand(1000, 8000);
                $visitors = (int)($views * (rand(8, 15) / 100));
                $atc = (int)($visitors * (rand(10, 20) / 100));
                $checkout = (int)($atc * (rand(50, 70) / 100));
                $completedBuyers = (int)($checkout * (rand(70, 90) / 100));
                $cvr = $visitors > 0 ? ($completedBuyers / $visitors) * 100 : 0;
                $atcRate = $visitors > 0 ? ($atc / $visitors) * 100 : 0;

                StoreMetric::create([
                    'store_id' => $store->id,
                    'date' => $date->toDateString(),
                    'views' => $views,
                    'visitors' => $visitors,
                    'add_to_cart' => $atc,
                    'checkout' => $checkout,
                    'buyers' => $completedBuyers,
                    'cvr' => round($cvr, 4),
                    'atc_rate' => round($atcRate, 4),
                ]);

                for ($o = 0; $o < $ordersPerDay; $o++) {
                    $buyer = $buyers[array_rand($buyers)];
                    $skuIdx = array_rand($skus);
                    $qty = rand(1, 3);
                    $price = rand(85000, 450000);
                    $gmv = $price * $qty;
                    $statusRand = rand(1, 100);
                    $status = $statusRand <= 75 ? 'complete' : ($statusRand <= 82 ? 'cancel' : ($statusRand <= 86 ? 'returned' : ($statusRand <= 89 ? 'refunded' : 'pending')));

                    $isNew = !isset($customerMap[$store->id . '_' . $buyer]);
                    $customerId = null;
                    if (!isset($customerMap[$store->id . '_' . $buyer])) {
                        $cust = Customer::create([
                            'buyer_identifier' => $buyer,
                            'platform' => $store->platform,
                            'first_order_date' => $date->toDateString(),
                            'first_store_id' => $store->id,
                            'total_orders' => 1,
                            'last_order_date' => $date->toDateString(),
                        ]);
                        $customerMap[$store->id . '_' . $buyer] = $cust->id;
                        $customerId = $cust->id;
                    } else {
                        $customerId = $customerMap[$store->id . '_' . $buyer];
                        Customer::where('id', $customerId)->increment('total_orders');
                        Customer::where('id', $customerId)->update(['last_order_date' => $date->toDateString()]);
                    }

                    Order::create([
                        'order_id' => 'ORD-' . $store->id . '-' . $date->format('Ymd') . '-' . str_pad($orderIdx, 5, '0', STR_PAD_LEFT),
                        'store_id' => $store->id,
                        'date' => $date->toDateString(),
                        'gmv' => $gmv,
                        'status' => $status,
                        'product_sku' => $skus[$skuIdx],
                        'product_name' => $productNames[$skuIdx],
                        'qty' => $qty,
                        'buyer_identifier' => $buyer,
                        'is_new_customer' => $isNew,
                        'customer_id' => $customerId,
                        'channel_type' => $store->channel_type,
                        'source_platform' => $store->platform,
                    ]);
                    $orderIdx++;
                    if ($status === 'complete') $gmvPerDay += $gmv;
                }

                // Ads Performance
                if ($store->platform !== 'Multi') {
                    $spend = rand(200000, 2500000);
                    $gmvFromAds = $spend * (rand(200, 500) / 100);
                    AdsPerformance::create([
                        'store_id' => $store->id,
                        'date' => $date->toDateString(),
                        'platform' => $store->platform,
                        'spend' => $spend,
                        'impressi' => rand(5000, 50000),
                        'klik' => rand(200, 2000),
                        'gmv_from_ads' => $gmvFromAds,
                        'roas' => round($gmvFromAds / $spend, 2),
                        'reach' => rand(3000, 40000),
                        'konversi' => rand(5, 80),
                        'campaign_name' => 'Campaign ' . $date->format('m-Y'),
                    ]);
                }
            }
        }

        // --- Financials (monthly) ---
        foreach ($stores as $store) {
            foreach ([['m' => 4, 'y' => 2026], ['m' => 5, 'y' => 2026], ['m' => 6, 'y' => 2026]] as $m) {
                $grossGmv = rand(800000000, 4000000000);
                $adminFee = $grossGmv * 0.02;
                $promoXtra = $grossGmv * 0.015;
                $ongkir = $grossGmv * 0.01;
                $netGmv = $grossGmv - $adminFee - $promoXtra - $ongkir;
                $hpp = $grossGmv * rand(35, 55) / 100;
                $ads = rand(5000000, 50000000);
                $ops = rand(2000000, 15000000);
                $grossProfit = $netGmv - $hpp;
                $netProfit = $grossProfit - $ads - $ops;
                Financial::create([
                    'store_id' => $store->id,
                    'period' => $m['y'] . '-' . str_pad($m['m'], 2, '0', STR_PAD_LEFT),
                    'gross_gmv' => $grossGmv,
                    'net_gmv' => $netGmv,
                    'admin_fee' => $adminFee,
                    'promo_xtra' => $promoXtra,
                    'ongkir_fee' => $ongkir,
                    'settlement' => $netGmv,
                    'hpp_total' => $hpp,
                    'ads_spend' => $ads,
                    'operational_cost' => $ops,
                    'gross_profit' => $grossProfit,
                    'net_profit' => $netProfit,
                ]);
            }
        }

        // --- Funnel Targets ---
        foreach ($stores as $store) {
            foreach ([
                ['stage' => 'views_to_visitor', 'target_pct' => 10.00],
                ['stage' => 'atc_rate', 'target_pct' => 15.00],
                ['stage' => 'cvr', 'target_pct' => 2.00],
            ] as $ft) {
                FunnelTarget::create(array_merge($ft, ['store_id' => $store->id, 'effective_from' => '2026-04-01']));
            }
        }

        // --- Demand Forecasts ---
        foreach ($stores as $store) {
            foreach ($skus as $i => $sku) {
                $avgSales = rand(3, 25);
                $forecastQty = $avgSales * 7;
                $safetyStock = (int)($forecastQty * 0.2);
                DemandForecast::create([
                    'store_id' => $store->id,
                    'product_sku' => $sku,
                    'product_name' => $productNames[$i],
                    'forecast_week_start' => Carbon::now()->addWeek()->startOfWeek()->toDateString(),
                    'avg_daily_sales_4w' => $avgSales,
                    'forecast_qty' => $forecastQty,
                    'safety_stock_qty' => $safetyStock,
                    'recommended_order_qty' => $forecastQty + $safetyStock,
                    'trend_direction' => ['up', 'down', 'stable'][rand(0, 2)],
                    'trend_change_pct' => rand(-30, 40),
                    'is_limited_data' => false,
                ]);
            }
        }

        // --- App Notifications ---
        $adminUser = User::where('role', 'admin')->first();
        AppNotification::create(['user_id' => $adminUser->id, 'title' => 'Return Rate Alert', 'message' => 'HURIM Official Shopee mencapai return rate 2.3% — BREACH threshold 2%.', 'type' => 'alert', 'link' => '/pic-performance']);
        AppNotification::create(['user_id' => $adminUser->id, 'title' => 'CVR Drop Alert', 'message' => 'ASFARA Official TikTok CVR turun 25% vs 7 hari terakhir.', 'type' => 'warning', 'link' => '/funnel']);
        AppNotification::create(['user_id' => $adminUser->id, 'title' => 'Upload Reminder', 'message' => 'Sinta belum upload laporan Shopee selama 3 hari.', 'type' => 'info', 'link' => '/upload']);

        $this->command->info('Database seeded successfully!');
    }
}
