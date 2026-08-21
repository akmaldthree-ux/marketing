<?php
namespace App\Http\Controllers;
use App\Models\{Store, UploadLog, Order, StoreMetric, AdsPerformance, Financial, Customer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Carbon\Carbon;
class UploadController extends Controller {
    public function index(\Illuminate\Http\Request $request) {
        $user  = Auth::user();
        $stores= Store::query()->where('is_active',true)->when($user->isPic()&&$user->pic_id, fn($q)=>$q->where('pic_id',$user->pic_id))->orderBy('brand')->orderBy('name')->get();

        $filterStore = $request->get('filter_store','all');
        $filterType  = $request->get('filter_type','all');

        $logs = UploadLog::with(['store','user'])
            ->when($filterStore!=='all', fn($q)=>$q->where('store_id',$filterStore))
            ->when($filterType!=='all',  fn($q)=>$q->where('report_type',$filterType))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total_files'  => UploadLog::count(),
            'total_rows'   => UploadLog::where('status','success')->sum('rows_imported'),
            'failed'       => UploadLog::where('status','failed')->count(),
            'last_upload'  => UploadLog::max('created_at'),
        ];

        return view('upload.index', compact('stores','logs','stats','filterStore','filterType'));
    }
    public function store(Request $request) {
        $request->validate([
            'store_id'    => 'required|exists:stores,id',
            'report_type' => 'required|in:orders,financials,ads,metrics',
            'file'        => 'required|file|mimes:csv,xlsx,xls|max:20480',
        ]);
        $file = $request->file('file');
        $log  = UploadLog::create([
            'store_id'    => $request->store_id,
            'user_id'     => Auth::id(),
            'report_type' => $request->report_type,
            'filename'    => $file->getClientOriginalName(),
            'status'      => 'processing',
            'uploaded_at' => now(),
        ]);
        try {
            $ext  = strtolower($file->getClientOriginalExtension());
            $rows = $ext === 'csv' ? $this->parseCsv($file->getRealPath()) : $this->parseXlsx($file->getRealPath());
            $count= DB::transaction(fn() => $this->processRows($rows, $request->report_type, $request->store_id, $log->id));
            $log->update(['rows_imported'=>$count,'status'=>'success']);
            return back()->with('success', "Berhasil mengimpor {$count} baris dari \"{$file->getClientOriginalName()}\".");
        } catch (\Throwable $e) {
            $log->update(['status'=>'failed','error_message'=>$e->getMessage()]);
            return back()->with('error', 'Gagal: '.$e->getMessage());
        }
    }
    public function destroy(UploadLog $log) {
        // Hapus data yang terkait dengan upload ini
        $deleted = 0;
        $deleted += DB::table('orders')->where('upload_log_id', $log->id)->delete();
        $deleted += DB::table('ads_performance')->where('upload_log_id', $log->id)->delete();
        $deleted += DB::table('store_metrics')->where('upload_log_id', $log->id)->delete();
        $deleted += DB::table('financials')->where('upload_log_id', $log->id)->delete();
        $log->delete();
        return back()->with('success', "File \"{$log->filename}\" dan {$deleted} baris data berhasil dihapus.");
    }

    public function clearReports() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $counts = [];
        foreach (['upload_logs','orders','ads_performance','store_metrics','financials','customers'] as $t) {
            $counts[$t] = DB::table($t)->count();
            DB::table($t)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $total = array_sum($counts);
        return back()->with('success', "Semua data transaksi berhasil dihapus ({$total} baris dihapus total).");
    }

    private function parseCsv(string $path): array {
        $rows = [];
        if (!($fh = fopen($path,'r'))) throw new \RuntimeException('Tidak bisa membuka file.');
        $headers = null;
        while (($row = fgetcsv($fh,0,',')) !== false) {
            if ($headers===null) { $headers=array_map('trim',$row); continue; }
            if (count($row)===count($headers)) $rows[]=array_combine($headers,array_map('trim',$row));
        }
        fclose($fh);
        return $rows;
    }

    private function parseXlsx(string $path): array {
        $zip = new \ZipArchive();
        if ($zip->open($path)!==true) throw new \RuntimeException('File XLSX tidak valid.');
        // Build shared strings table (standard format)
        $shared = [];
        if ($ss=$zip->getFromName('xl/sharedStrings.xml')) {
            preg_match_all('/<si>(.*?)<\/si>/s',$ss,$siM);
            foreach ($siM[1] as $si) {
                preg_match_all('/<t[^>]*>(.*?)<\/t>/s',$si,$tM);
                $shared[]=html_entity_decode(implode('',$tM[1]),ENT_XML1,'UTF-8');
            }
        }
        // Find first sheet (prefer sheet1, fallback to any sheet)
        $sheetXml=null; $sheetName=null;
        for ($i=0;$i<$zip->numFiles;$i++) {
            $name=$zip->getNameIndex($i);
            if (preg_match('#xl/worksheets/sheet\d+\.xml#',$name)) {
                if ($sheetName===null||strcmp($name,$sheetName)<0) {
                    $sheetXml=$zip->getFromIndex($i); $sheetName=$name;
                }
            }
        }
        $zip->close();
        if (!$sheetXml) throw new \RuntimeException('Sheet tidak ditemukan dalam file XLSX.');
        $rawRows=[];
        preg_match_all('/<row[^>]*>(.*?)<\/row>/s',$sheetXml,$rowM);
        foreach ($rowM[1] as $rowContent) {
            $cells=[];
            preg_match_all('/<c\s([^>]*)>(.*?)<\/c>/s',$rowContent,$cellM,PREG_SET_ORDER);
            foreach ($cellM as $cell) {
                preg_match('/r="([^"]+)"/',$cell[1],$rM);
                preg_match('/t="([^"]+)"/',$cell[1],$tM);
                $col=preg_replace('/[0-9]/','', $rM[1]??'');
                $type=$tM[1]??'';
                // inlineStr: <is><t>value</t></is>
                if ($type==='inlineStr'||$type==='str') {
                    preg_match('/<t[^>]*>(.*?)<\/t>/s',$cell[2],$isM);
                    $val=html_entity_decode($isM[1]??'',ENT_XML1,'UTF-8');
                } elseif ($type==='s') {
                    preg_match('/<v>(.*?)<\/v>/s',$cell[2],$vM);
                    $val=$shared[(int)($vM[1]??0)]??'';
                } else {
                    preg_match('/<v>(.*?)<\/v>/s',$cell[2],$vM);
                    $val=$vM[1]??'';
                }
                if ($col!=='') $cells[$col]=trim($val);
            }
            if (!empty($cells)) $rawRows[]=$cells;
        }
        if (empty($rawRows)) return [];
        $headerRow=array_shift($rawRows);
        $colKeys=array_keys($headerRow); $headers=array_values($headerRow);
        $rows=[];
        foreach ($rawRows as $raw) {
            $mapped=[];
            foreach ($colKeys as $i=>$col) $mapped[$headers[$i]]=$raw[$col]??'';
            $rows[]=$mapped;
        }
        return $rows;
    }

    private function processRows(array $rows, string $type, int $storeId, int $logId = 0): int {
        $store = Store::findOrFail($storeId);
        return match($type) {
            'orders'     => $this->processOrders($rows, $store, $logId),
            'financials' => $this->processFinancials($rows, $store, $logId),
            'ads'        => $this->processAds($rows, $store, $logId),
            'metrics'    => $this->processMetrics($rows, $store, $logId),
            default      => 0,
        };
    }

    private function processOrders(array $rows, Store $store, int $logId = 0): int {
        $count=0;
        foreach ($rows as $row) {
            $row=array_change_key_case($row,CASE_LOWER);
            $orderNum=$this->col($row,['no. pesanan','order id','order_id','nomor pesanan','no pesanan']);
            if (!$orderNum) continue;
            $dateRaw=$this->col($row,['waktu pesanan dibuat','waktu pembayaran dilakukan','order time','tanggal','date','order date','create time']);
            if (!$dateRaw) continue;
            try { $date=Carbon::parse($dateRaw)->toDateString(); } catch(\Exception $e){ continue; }
            // Prioritas: Subtotal Pesanan (sudah termasuk diskon seller) > Harga Setelah Diskon > Total Pembayaran
            $rawStatus = $this->col($row,['status pesanan','status','order status']) ?? '';
            $isCancelled = str_contains(strtolower($rawStatus),'batal') || str_contains(strtolower($rawStatus),'cancel') || str_contains(strtolower($rawStatus),'belum bayar');
            $gmv = $isCancelled ? 0 : $this->num($this->col($row,['subtotal pesanan','harga setelah diskon','total harga produk','gmv','total pesanan','total pembayaran','total price','price']));
            $qty    =(int)($this->num($this->col($row,['jumlah','qty','quantity','jumlah produk di pesan']))?:1);
            $sku    =$this->col($row,['sku induk','nomor referensi sku','sku','product sku'])??'-';
            $name   =$this->col($row,['nama produk','product name','nama barang','item name'])??'-';
            $buyer  =$this->col($row,['username (pembeli)','buyer','username','buyer username','nama pembeli'])?? 'unknown';
            $status =$this->mapStatus($this->col($row,['status pesanan','status','order status'])?? 'complete');
            // Cek apakah order_number sudah ada (1 order bisa punya banyak produk di Shopee)
            $existing = Order::where('order_number',$orderNum)->first();
            if ($existing) {
                // Akumulasi GMV dan qty untuk order yang sama (multi-produk)
                $existing->increment('gmv', $gmv);
                $existing->increment('qty', $qty);
                // Gabungkan nama produk jika berbeda
                if ($sku !== '-' && !str_contains($existing->product_sku??'', $sku)) {
                    $existing->update(['product_name'=>($existing->product_name??'').' | '.$name,'product_sku'=>($existing->product_sku??'').' | '.$sku]);
                }
                $count++;
                continue;
            }
            $isNew  =!Customer::where('username',$buyer)->where('platform',$store->platform)->exists();
            $customer=Customer::firstOrCreate(
                ['username'=>$buyer,'platform'=>$store->platform],
                ['first_order_date'=>$date,'first_store_id'=>$store->id,'total_orders'=>0,'last_order_date'=>$date]
            );
            $customer->increment('total_orders');
            $customer->update(['last_order_date'=>$date]);
            Order::create(['order_number'=>$orderNum,'store_id'=>$store->id,'customer_id'=>$customer->id,'order_date'=>$date,'gmv'=>$gmv,'status'=>$status,'product_sku'=>$sku,'product_name'=>$name,'qty'=>$qty,'is_new_customer'=>$isNew,'source_platform'=>$store->platform,'upload_log_id'=>$logId]);
            $count++;
        }
        return $count;
    }

    private function processFinancials(array $rows, Store $store, int $logId = 0): int {
        $count=0;
        foreach ($rows as $row) {
            $row=array_change_key_case($row,CASE_LOWER);
            $period=$this->col($row,['periode','period','bulan','month'])?? '';
            if (!$period) continue;
            try { $period=Carbon::parse($period)->format('Y-m'); } catch(\Exception $e){ continue; }
            $gross=$this->num($this->col($row,['gross gmv','total gmv','gmv']));
            $net  =$this->num($this->col($row,['net gmv','pendapatan bersih']));
            $admin=$this->num($this->col($row,['biaya admin','admin fee','biaya layanan']));
            $ads  =$this->num($this->col($row,['biaya iklan','ads spend','iklan']));
            $cogs =$this->num($this->col($row,['hpp','cogs','harga pokok']));
            $ops  =$this->num($this->col($row,['biaya operasional','operational']));
            $netGmv=$net?:($gross-$admin);
            Financial::updateOrCreate(['store_id'=>$store->id,'period'=>$period],['upload_log_id'=>$logId,
                'gross_gmv'=>$gross,'net_gmv'=>$netGmv,'admin_fee'=>$admin,
                'promo_fee'=>$this->num($this->col($row,['promo','promo xtra','voucher'])),
                'shipping_fee'=>$this->num($this->col($row,['ongkir','shipping fee','biaya kirim'])),
                'settlement'=>$this->num($this->col($row,['settlement','pencairan']))?:$netGmv,
                'cogs'=>$cogs,'ads_spend'=>$ads,'operational_cost'=>$ops,
                'gross_profit'=>$netGmv-$cogs,'net_profit'=>$netGmv-$cogs-$ads-$ops,
            ]);
            $count++;
        }
        return $count;
    }

    private function processAds(array $rows, Store $store, int $logId = 0): int {
        $count=0;
        foreach ($rows as $row) {
            $row=array_change_key_case($row,CASE_LOWER);
            $dateRaw=$this->col($row,['tanggal','date','waktu','time','report date']);
            if (!$dateRaw) continue;
            try { $date=Carbon::parse($dateRaw)->toDateString(); } catch(\Exception $e){ continue; }
            $spend=$this->num($this->col($row,['pengeluaran iklan','spend','biaya','cost','amount spent']));
            $gmv  =$this->num($this->col($row,['gmv dari iklan','gmv','revenue','purchase value','conversion value']));
            AdsPerformance::updateOrCreate(['store_id'=>$store->id,'date'=>$date,'platform'=>$store->platform],['upload_log_id'=>$logId,
                'spend'=>$spend,'impressions'=>(int)$this->num($this->col($row,['tayangan','impressions','impresi'])),
                'clicks'=>(int)$this->num($this->col($row,['klik','clicks','click'])),
                'gmv_from_ads'=>$gmv,'roas'=>$spend>0?round($gmv/$spend,2):0,
                'reach'=>(int)$this->num($this->col($row,['jangkauan','reach'])),
                'conversions'=>(int)$this->num($this->col($row,['konversi','conversions','purchase'])),
                'campaign_name'=>$this->col($row,['nama kampanye','campaign name','campaign'])??'-',
            ]);
            $count++;
        }
        return $count;
    }

    private function processMetrics(array $rows, Store $store, int $logId = 0): int {
        $count=0;
        foreach ($rows as $row) {
            $row=array_change_key_case($row,CASE_LOWER);
            $dateRaw=$this->col($row,['tanggal','date','waktu']);
            if (!$dateRaw) continue;
            try { $date=Carbon::parse($dateRaw)->toDateString(); } catch(\Exception $e){ continue; }
            $views=$this->num($this->col($row,['tayangan produk','views','page views','kunjungan']));
            $visitors=$this->num($this->col($row,['pengunjung','visitors','unique visitors']));
            $atc=$this->num($this->col($row,['tambah ke keranjang','add to cart','atc']));
            $checkout=$this->num($this->col($row,['checkout','bayar']));
            $buyers=$this->num($this->col($row,['pembeli','buyers','orders']));
            StoreMetric::updateOrCreate(['store_id'=>$store->id,'date'=>$date],['upload_log_id'=>$logId,
                'views'=>(int)$views,'visitors'=>(int)$visitors,'add_to_cart'=>(int)$atc,
                'checkout'=>(int)$checkout,'buyers'=>(int)$buyers,
                'cvr'=>$visitors>0?round($buyers/$visitors*100,4):0,
                'atc_rate'=>$visitors>0?round($atc/$visitors*100,4):0,
            ]);
            $count++;
        }
        return $count;
    }

    private function col(array $row, array $keys): ?string {
        foreach ($keys as $k) { if (isset($row[$k])&&$row[$k]!=='') return $row[$k]; }
        return null;
    }
    private function num(?string $v): float {
        if ($v===null||trim($v)==='') return 0;
        $v=trim($v);
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/',$v)) {
            return (float)str_replace(['.', ','], ['', '.'], $v);
        }
        return (float)preg_replace('/[^0-9.\-]/','',str_replace(',','.',$v));
    }
    private function mapStatus(string $raw): string {
        $r=strtolower(trim($raw));
        // Shopee: "Pesanan diterima, namun Pembeli masih dapat mengajukan pengembalian hingga ..."
        if (str_contains($r,'pesanan diterima')) return 'complete';
        if (str_contains($r,'selesai')||str_contains($r,'complete')||str_contains($r,'delivered')||str_contains($r,'completed')) return 'complete';
        // Shopee: "Telah Dikirim", "Sedang Dikirim"
        if (str_contains($r,'telah dikirim')||str_contains($r,'sedang dikirim')) return 'shipped';
        if (str_contains($r,'batal')||str_contains($r,'cancel')) return 'cancelled';
        if (str_contains($r,'retur')||str_contains($r,'return')) return 'returned';
        if (str_contains($r,'refund')) return 'refunded';
        if (str_contains($r,'kirim')||str_contains($r,'shipped')||str_contains($r,'shipping')||str_contains($r,'pengiriman')) return 'shipped';
        // Shopee: "Perlu Dikirim" = siap dikemas/dikirim
        if (str_contains($r,'perlu dikirim')) return 'processing';
        if (str_contains($r,'proses')||str_contains($r,'process')||str_contains($r,'packing')||str_contains($r,'dikemas')) return 'processing';
        // Shopee: "Belum Bayar" = pending, jangan dihitung GMV
        if (str_contains($r,'belum bayar')||str_contains($r,'unpaid')||str_contains($r,'pending')) return 'cancelled';
        return 'complete';
    }
}
