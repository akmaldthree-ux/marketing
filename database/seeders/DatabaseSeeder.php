<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Pic, Store, FunnelTarget, Target, Cog};
use Carbon\Carbon;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        // ── Users ─────────────────────────────────────────────────────────────
        User::firstOrCreate(['email'=>'admin@dsm.co.id'],['name'=>'Admin DSM','password'=>Hash::make('password'),'role'=>'admin']);
        User::firstOrCreate(['email'=>'viewer@dsm.co.id'],['name'=>'Manajemen DSM','password'=>Hash::make('password'),'role'=>'viewer']);

        // ── PICs ──────────────────────────────────────────────────────────────
        $picData=[
            ['name'=>'Sinta Amelia','email'=>'sinta@dsm.co.id'],
            ['name'=>'Reza Pratama','email'=>'reza@dsm.co.id'],
            ['name'=>'Dewi Sartika','email'=>'dewi@dsm.co.id'],
            ['name'=>'Budi Santoso','email'=>'budi@dsm.co.id'],
        ];
        $pics=[];
        foreach ($picData as $pd) {
            $pic=Pic::firstOrCreate(['email'=>$pd['email']],['name'=>$pd['name'],'is_active'=>true]);
            User::firstOrCreate(['email'=>$pd['email']],['name'=>$pd['name'],'password'=>Hash::make('password'),'role'=>'pic','pic_id'=>$pic->id]);
            $pics[]=$pic;
        }

        // ── Stores ────────────────────────────────────────────────────────────
        $storeData=[
            ['name'=>'DTHREE Official Shopee',  'brand'=>'DTHREE','platform'=>'Shopee',     'channel_type'=>'marketplace',    'pic_id'=>$pics[0]->id],
            ['name'=>'DTHREE Official TikTok',  'brand'=>'DTHREE','platform'=>'TikTok Shop','channel_type'=>'marketplace',    'pic_id'=>$pics[0]->id],
            ['name'=>'DTHREE Meta Ads',         'brand'=>'DTHREE','platform'=>'Meta Ads',   'channel_type'=>'non_marketplace','pic_id'=>$pics[0]->id],
            ['name'=>'HURIM Official Shopee',   'brand'=>'HURIM', 'platform'=>'Shopee',     'channel_type'=>'marketplace',    'pic_id'=>$pics[1]->id],
            ['name'=>'HURIM Official TikTok',   'brand'=>'HURIM', 'platform'=>'TikTok Shop','channel_type'=>'marketplace',    'pic_id'=>$pics[1]->id],
            ['name'=>'Sarimbit Studio Shopee',  'brand'=>'HURIM', 'platform'=>'Shopee',     'channel_type'=>'marketplace',    'pic_id'=>$pics[2]->id],
            ['name'=>'ASFARA Official Shopee',  'brand'=>'ASFARA','platform'=>'Shopee',     'channel_type'=>'marketplace',    'pic_id'=>$pics[2]->id],
            ['name'=>'ASFARA Official TikTok',  'brand'=>'ASFARA','platform'=>'TikTok Shop','channel_type'=>'marketplace',    'pic_id'=>$pics[3]->id],
            ['name'=>'ASFARA Meta Ads',         'brand'=>'ASFARA','platform'=>'Meta Ads',   'channel_type'=>'non_marketplace','pic_id'=>$pics[3]->id],
        ];
        $stores=[];
        foreach ($storeData as $sd) {
            $stores[]=Store::firstOrCreate(['name'=>$sd['name']],$sd);
        }

        // ── Funnel Targets ────────────────────────────────────────────────────
        foreach ($stores as $store) {
            foreach ([
                ['stage'=>'views_to_visitor','target_pct'=>10],
                ['stage'=>'atc_rate',        'target_pct'=>15],
                ['stage'=>'cvr',             'target_pct'=>2],
            ] as $ft) {
                FunnelTarget::firstOrCreate(
                    ['store_id'=>$store->id,'stage'=>$ft['stage']],
                    array_merge($ft,['effective_from'=>'2026-01-01'])
                );
            }
        }

        // ── GMV Targets ───────────────────────────────────────────────────────
        $baseGmv=['DTHREE'=>5000000000,'HURIM'=>3000000000,'ASFARA'=>2000000000];
        foreach ($stores as $store) {
            $base=$baseGmv[$store->brand]??1000000000;
            for ($m=1;$m<=12;$m++) {
                $mult=in_array($m,[1,2,11,12])?1.3:1.0;
                Target::firstOrCreate(
                    ['store_id'=>$store->id,'month'=>$m,'year'=>2026],
                    ['gmv_target'=>(int)($base*$mult/12)]
                );
            }
        }

        // ── COGs (HPP per produk) ─────────────────────────────────────────────
        $cogData=[
            ['product_sku'=>'DTH-001','product_name'=>'Gamis Syar\'i Premium',        'hpp_per_unit'=>185000,'effective_from'=>'2026-01-01'],
            ['product_sku'=>'DTH-002','product_name'=>'Mukena Travel Bordir',          'hpp_per_unit'=>95000, 'effective_from'=>'2026-01-01'],
            ['product_sku'=>'DTH-003','product_name'=>'Hijab Segi Empat Voal',         'hpp_per_unit'=>28000, 'effective_from'=>'2026-01-01'],
            ['product_sku'=>'HRM-001','product_name'=>'Sarimbit Couple Batik Premium', 'hpp_per_unit'=>220000,'effective_from'=>'2026-01-01'],
            ['product_sku'=>'HRM-002','product_name'=>'Baju Koko Hurim Classic',       'hpp_per_unit'=>115000,'effective_from'=>'2026-01-01'],
            ['product_sku'=>'HRM-003','product_name'=>'Sarung Tenun Eksklusif',        'hpp_per_unit'=>85000, 'effective_from'=>'2026-01-01'],
            ['product_sku'=>'ASF-001','product_name'=>'Gamis Anak Asfara Motif',       'hpp_per_unit'=>75000, 'effective_from'=>'2026-01-01'],
            ['product_sku'=>'ASF-002','product_name'=>'Setelan Anak Muslim Premium',   'hpp_per_unit'=>65000, 'effective_from'=>'2026-01-01'],
            ['product_sku'=>'ASF-003','product_name'=>'Mukena Anak Bordir',            'hpp_per_unit'=>55000, 'effective_from'=>'2026-01-01'],
        ];
        foreach ($cogData as $cd) {
            Cog::updateOrCreate(['product_sku'=>$cd['product_sku']],$cd);
        }

        // ── Orders (6 bulan: Jan–Jun 2026) ───────────────────────────────────
        // Mapping: brand → store indices, brand → SKU prefix
        $brandStoreMap = [
            'DTHREE' => array_values(array_filter($stores, fn($s)=>$s->brand==='DTHREE')),
            'HURIM'  => array_values(array_filter($stores, fn($s)=>$s->brand==='HURIM')),
            'ASFARA' => array_values(array_filter($stores, fn($s)=>$s->brand==='ASFARA')),
        ];
        $brandSkus = [
            'DTHREE' => [
                ['sku'=>'DTH-001','name'=>'Gamis Syar\'i Premium',       'price'=>380000,'hpp'=>185000],
                ['sku'=>'DTH-002','name'=>'Mukena Travel Bordir',         'price'=>195000,'hpp'=>95000],
                ['sku'=>'DTH-003','name'=>'Hijab Segi Empat Voal',        'price'=>65000, 'hpp'=>28000],
            ],
            'HURIM'  => [
                ['sku'=>'HRM-001','name'=>'Sarimbit Couple Batik Premium','price'=>450000,'hpp'=>220000],
                ['sku'=>'HRM-002','name'=>'Baju Koko Hurim Classic',      'price'=>235000,'hpp'=>115000],
                ['sku'=>'HRM-003','name'=>'Sarung Tenun Eksklusif',       'price'=>175000,'hpp'=>85000],
            ],
            'ASFARA' => [
                ['sku'=>'ASF-001','name'=>'Gamis Anak Asfara Motif',      'price'=>155000,'hpp'=>75000],
                ['sku'=>'ASF-002','name'=>'Setelan Anak Muslim Premium',  'price'=>135000,'hpp'=>65000],
                ['sku'=>'ASF-003','name'=>'Mukena Anak Bordir',           'price'=>115000,'hpp'=>55000],
            ],
        ];
        $statuses = ['complete','complete','complete','shipped','processing','cancelled'];
        $gmvStatuses = ['complete','shipped','processing'];

        DB::table('orders')->delete();
        $orderRows = [];
        $orderNum  = 1000;

        foreach ($brandStoreMap as $brand => $brandStores) {
            $skus = $brandSkus[$brand];
            // volume multiplier per brand
            $vol = match($brand) { 'DTHREE'=>1.0, 'HURIM'=>0.7, 'ASFARA'=>0.5, default=>0.5 };

            foreach ($brandStores as $store) {
                for ($month = 1; $month <= 6; $month++) {
                    // seasonality: lebaran (Mar/Apr) boost, Jan/Feb normal
                    $seasonal = in_array($month,[3,4]) ? 1.8 : (in_array($month,[5,6]) ? 0.9 : 1.0);
                    $daysInMonth = Carbon::create(2026,$month,1)->daysInMonth;

                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        // 3-8 orders per day per store
                        $ordersPerDay = (int)round(rand(3,8) * $vol * $seasonal);
                        for ($o = 0; $o < $ordersPerDay; $o++) {
                            $sku     = $skus[array_rand($skus)];
                            $qty     = rand(1,3);
                            $status  = $statuses[array_rand($statuses)];
                            $gmv     = in_array($status, $gmvStatuses) ? $sku['price'] * $qty : 0;
                            $orderRows[] = [
                                'order_number'    => 'ORD-'.str_pad($orderNum++,6,'0',STR_PAD_LEFT),
                                'store_id'        => $store->id,
                                'order_date'      => sprintf('2026-%02d-%02d',$month,$day),
                                'product_sku'     => $sku['sku'],
                                'product_name'    => $sku['name'],
                                'qty'             => $qty,
                                'gmv'             => $gmv,
                                'status'          => $status,
                                'is_new_customer' => rand(0,1),
                                'source_platform' => $store->platform,
                                'created_at'      => now(),
                                'updated_at'      => now(),
                            ];
                        }
                    }
                }
            }
        }
        // Insert in chunks
        foreach (array_chunk($orderRows, 500) as $chunk) {
            DB::table('orders')->insert($chunk);
        }

        // ── Ads Performance (Jan–Jun 2026) ────────────────────────────────────
        DB::table('ads_performance')->delete();
        $adsRows = [];
        $platforms = ['Shopee Ads','TikTok Ads','Meta Ads'];

        foreach ($brandStoreMap as $brand => $brandStores) {
            foreach ($brandStores as $store) {
                // hanya toko marketplace yang punya ads
                if ($store->channel_type !== 'marketplace') continue;

                for ($month = 1; $month <= 6; $month++) {
                    $daysInMonth = Carbon::create(2026,$month,1)->daysInMonth;
                    $monthlyBudget = match($brand) {
                        'DTHREE' => rand(8000000,15000000),
                        'HURIM'  => rand(5000000,9000000),
                        'ASFARA' => rand(3000000,6000000),
                        default  => 3000000,
                    };
                    $dailyBudget = $monthlyBudget / $daysInMonth;

                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $spend = (int)($dailyBudget * (0.8 + lcg_value() * 0.4));
                        $roas  = round(2.5 + lcg_value() * 2, 2); // ROAS 2.5x - 4.5x
                        $gmvAds = (int)($spend * $roas);
                        $adsRows[] = [
                            'store_id'      => $store->id,
                            'date'          => sprintf('2026-%02d-%02d',$month,$day),
                            'platform'      => $store->platform,
                            'spend'         => $spend,
                            'impressions'   => rand(5000,50000),
                            'clicks'        => rand(100,2000),
                            'gmv_from_ads'  => $gmvAds,
                            'roas'          => $roas,
                            'reach'         => rand(4000,40000),
                            'conversions'   => rand(10,200),
                            'campaign_name' => $brand.' Campaign '.Carbon::create(2026,$month,1)->format('M'),
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ];
                    }
                }
            }
        }
        foreach (array_chunk($adsRows, 500) as $chunk) {
            DB::table('ads_performance')->insert($chunk);
        }

        // ── Biaya Operasional per store per bulan ─────────────────────────────
        DB::table('financials')->delete();
        $opsPerBrand = ['DTHREE'=>12000000,'HURIM'=>8000000,'ASFARA'=>6000000];
        $financialRows = [];
        foreach ($brandStoreMap as $brand => $brandStores) {
            // distribusikan biaya ops ke store pertama tiap brand sebagai cost center
            $costCenterStore = $brandStores[0];
            $monthlyOps = $opsPerBrand[$brand] ?? 5000000;
            for ($month = 1; $month <= 6; $month++) {
                $financialRows[] = [
                    'store_id'         => $costCenterStore->id,
                    'period'           => sprintf('2026-%02d',$month),
                    'gross_gmv'        => 0,
                    'net_gmv'          => 0,
                    'admin_fee'        => 0,
                    'promo_fee'        => 0,
                    'shipping_fee'     => 0,
                    'settlement'       => 0,
                    'cogs'             => 0,
                    'ads_spend'        => 0,
                    'operational_cost' => (int)($monthlyOps * (0.9 + lcg_value() * 0.2)),
                    'gross_profit'     => 0,
                    'net_profit'       => 0,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
        }
        DB::table('financials')->insert($financialRows);
    }
}
