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

        $this->command->info('Seed base data complete. Run orders seeder separately for large data.');
    }
}
