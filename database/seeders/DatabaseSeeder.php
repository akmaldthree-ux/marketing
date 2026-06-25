<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{User, Pic, Store, FunnelTarget, Target};
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        // Admin
        User::firstOrCreate(['email'=>'admin@dsm.co.id'],['name'=>'Admin DSM','password'=>Hash::make('password'),'role'=>'admin']);
        // Viewer
        User::firstOrCreate(['email'=>'viewer@dsm.co.id'],['name'=>'Manajemen DSM','password'=>Hash::make('password'),'role'=>'viewer']);
        // PICs
        $picData=[['name'=>'Sinta Amelia','email'=>'sinta@dsm.co.id'],['name'=>'Reza Pratama','email'=>'reza@dsm.co.id'],['name'=>'Dewi Sartika','email'=>'dewi@dsm.co.id'],['name'=>'Budi Santoso','email'=>'budi@dsm.co.id']];
        $pics=[];
        foreach ($picData as $pd) {
            $pic=Pic::firstOrCreate(['email'=>$pd['email']],['name'=>$pd['name']]);
            User::firstOrCreate(['email'=>$pd['email']],['name'=>$pd['name'],'password'=>Hash::make('password'),'role'=>'pic','pic_id'=>$pic->id]);
            $pics[]=$pic;
        }
        // Stores
        $storeData=[
            ['name'=>'DTHREE Official Shopee','brand'=>'DTHREE','platform'=>'Shopee','channel_type'=>'marketplace','pic_id'=>$pics[0]->id],
            ['name'=>'DTHREE Official TikTok','brand'=>'DTHREE','platform'=>'TikTok Shop','channel_type'=>'marketplace','pic_id'=>$pics[0]->id],
            ['name'=>'DTHREE Meta Ads','brand'=>'DTHREE','platform'=>'Meta Ads','channel_type'=>'non_marketplace','pic_id'=>$pics[0]->id],
            ['name'=>'HURIM Official Shopee','brand'=>'HURIM','platform'=>'Shopee','channel_type'=>'marketplace','pic_id'=>$pics[1]->id],
            ['name'=>'HURIM Official TikTok','brand'=>'HURIM','platform'=>'TikTok Shop','channel_type'=>'marketplace','pic_id'=>$pics[1]->id],
            ['name'=>'Sarimbit Studio Shopee','brand'=>'HURIM','platform'=>'Shopee','channel_type'=>'marketplace','pic_id'=>$pics[2]->id],
            ['name'=>'ASFARA Official Shopee','brand'=>'ASFARA','platform'=>'Shopee','channel_type'=>'marketplace','pic_id'=>$pics[2]->id],
            ['name'=>'ASFARA Official TikTok','brand'=>'ASFARA','platform'=>'TikTok Shop','channel_type'=>'marketplace','pic_id'=>$pics[3]->id],
            ['name'=>'ASFARA Meta Ads','brand'=>'ASFARA','platform'=>'Meta Ads','channel_type'=>'non_marketplace','pic_id'=>$pics[3]->id],
        ];
        $stores=[];
        foreach ($storeData as $sd) {
            $stores[]=Store::firstOrCreate(['name'=>$sd['name']],$sd);
        }
        // Funnel targets
        foreach ($stores as $store) {
            foreach ([['stage'=>'views_to_visitor','target_pct'=>10],['stage'=>'atc_rate','target_pct'=>15],['stage'=>'cvr','target_pct'=>2]] as $ft) {
                FunnelTarget::firstOrCreate(['store_id'=>$store->id,'stage'=>$ft['stage']],array_merge($ft,['effective_from'=>'2026-01-01']));
            }
        }
        // GMV targets
        $baseGmv=['DTHREE'=>5000000000,'HURIM'=>3000000000,'ASFARA'=>2000000000];
        foreach ($stores as $store) {
            $base=$baseGmv[$store->brand]??1000000000;
            for ($m=1;$m<=12;$m++) {
                $mult=in_array($m,[1,2,11,12])?1.3:1.0;
                Target::firstOrCreate(['store_id'=>$store->id,'month'=>$m,'year'=>2026],['gmv_target'=>(int)($base*$mult/12)]);
            }
        }
    }
}
