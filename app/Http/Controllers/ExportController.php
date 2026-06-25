<?php
namespace App\Http\Controllers;

use App\Models\{Store, Order, AdsPerformance, Target, Cog, Financial};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExportController extends Controller
{
    // Export Target GMV ke CSV
    public function targets(Request $request)
    {
        $year   = $request->get('year', now()->year);
        $stores = Store::where('is_active', true)->orderBy('brand')->orderBy('name')->get();
        $targets = \App\Models\Target::where('year', $year)->get()->groupBy('store_id');
        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];

        $rows   = [array_merge(['Toko','Brand'], $months, ['Total'])];
        foreach ($stores as $store) {
            $row   = [$store->name, $store->brand];
            $total = 0;
            for ($m = 1; $m <= 12; $m++) {
                $v = $targets->get($store->id)?->firstWhere('month', $m)?->gmv_target ?? 0;
                $row[] = $v;
                $total += $v;
            }
            $row[]  = $total;
            $rows[] = $row;
        }

        return $this->csvResponse("target_gmv_{$year}.csv", $rows);
    }

    // Export P&L ke CSV
    public function pnl(Request $request)
    {
        $year     = (int)$request->get('year', now()->year);
        $brand    = $request->get('brand', 'all');
        $storeIds = Store::where('is_active', true)
            ->when($brand !== 'all', fn($q) => $q->where('brand', $brand))
            ->pluck('id')->toArray();

        $months = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun',
                   '07'=>'Jul','08'=>'Ags','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];

        $gmvRows  = $this->monthlySum('orders', 'gmv', 'store_id', $storeIds, 'order_date', $year, Order::gmvStatuses());
        $adsRows  = $this->monthlySum('ads_performance', 'spend', 'store_id', $storeIds, 'date', $year);
        $opsRows  = collect();
        if (!empty($storeIds)) {
            $opsRows = DB::table('financials')->whereIn('store_id',$storeIds)->where('period','like',$year.'-%')
                ->selectRaw("SUBSTRING(period,6,2) as mon, SUM(operational_cost) as total")
                ->groupByRaw("SUBSTRING(period,6,2)")->pluck('total','mon');
        }
        $cogsRows = collect();
        if (!empty($storeIds) && DB::getSchemaBuilder()->hasTable('cogs')) {
            $cogsRows = DB::table('orders')->join('cogs','orders.product_sku','=','cogs.product_sku')
                ->whereIn('orders.store_id',$storeIds)->whereIn('orders.status', Order::gmvStatuses())
                ->whereYear('orders.order_date',$year)
                ->selectRaw("DATE_FORMAT(orders.order_date,'%m') as mon, SUM(orders.qty*cogs.hpp_per_unit) as total")
                ->groupByRaw("DATE_FORMAT(orders.order_date,'%m')")->pluck('total','mon');
        }

        $header = array_merge(['Komponen'], array_values($months), ['Total']);
        $rows   = [$header];
        $components = [
            'Gross GMV'   => fn($m) => (float)($gmvRows[$m] ?? 0),
            'HPP/COGS'    => fn($m) => (float)($cogsRows[$m] ?? 0),
            'Ads Spend'   => fn($m) => (float)($adsRows[$m] ?? 0),
            'Biaya Ops'   => fn($m) => (float)($opsRows[$m] ?? 0),
            'Gross Profit'=> fn($m) => (float)($gmvRows[$m]??0) - (float)($cogsRows[$m]??0) - (float)($adsRows[$m]??0),
            'Net Profit'  => fn($m) => (float)($gmvRows[$m]??0) - (float)($cogsRows[$m]??0) - (float)($adsRows[$m]??0) - (float)($opsRows[$m]??0),
        ];

        foreach ($components as $label => $fn) {
            $row   = [$label];
            $total = 0;
            foreach (array_keys($months) as $m) { $v = $fn($m); $row[] = $v; $total += $v; }
            $row[] = $total;
            $rows[] = $row;
        }

        return $this->csvResponse("pnl_{$year}" . ($brand !== 'all' ? "_{$brand}" : '') . ".csv", $rows);
    }

    // Export Daily Sales ke CSV
    public function dailySales(Request $request)
    {
        $month   = $request->get('month', now()->format('Y-m'));
        $brand   = $request->get('brand', 'all');
        [$year, $mon] = explode('-', $month);
        $start = Carbon::create($year, $mon, 1)->startOfMonth();
        $end   = Carbon::create($year, $mon, 1)->endOfMonth();

        $storeIds = Store::where('is_active', true)
            ->when($brand !== 'all', fn($q) => $q->where('brand', $brand))
            ->pluck('id')->toArray();

        $daily = DB::table('orders')
            ->when(!empty($storeIds), fn($q) => $q->whereIn('store_id', $storeIds))
            ->whereIn('status', Order::gmvStatuses())
            ->whereBetween('order_date', [$start, $end])
            ->selectRaw("DATE(order_date) as day, SUM(gmv) as gmv, COUNT(*) as orders")
            ->groupBy('day')->orderBy('day')->get();

        $rows = [['Tanggal','GMV','Jumlah Order']];
        foreach ($daily as $d) {
            $rows[] = [$d->day, $d->gmv, $d->orders];
        }

        return $this->csvResponse("daily_sales_{$month}.csv", $rows);
    }

    private function monthlySum(string $table, string $col, string $storeCol, array $storeIds, string $dateCol, int $year, array $statuses = [])
    {
        if (empty($storeIds)) return collect();
        $q = DB::table($table)->whereIn($storeCol, $storeIds)->whereYear($dateCol, $year)
            ->selectRaw("DATE_FORMAT({$dateCol},'%m') as mon, SUM({$col}) as total")
            ->groupByRaw("DATE_FORMAT({$dateCol},'%m')");
        if ($statuses) $q->whereIn('status', $statuses);
        return $q->pluck('total', 'mon');
    }

    private function csvResponse(string $filename, array $rows)
    {
        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\n";
        }
        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
