<?php
namespace App\Http\Controllers;

use App\Models\{Store, UploadLog, Order, StoreMetric, AdsPerformance, Financial, Customer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UploadController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $storeQuery = Store::query()->where('is_active', true);
        if ($user->isPic() && $user->pic_id) {
            $storeQuery->where('pic_id', $user->pic_id);
        }
        $stores = $storeQuery->get();
        $logs = UploadLog::with('store')->orderByDesc('uploaded_at')->limit(20)->get();
        return view('upload.index', compact('stores', 'logs'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'store_id'        => 'required|exists:stores,id',
            'report_type'     => 'required|in:orders,financials,ads,metrics',
            'source_platform' => 'required|in:Shopee,TikTok Shop,Meta Ads',
            'file'            => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ]);

        $file     = $request->file('file');
        $filename = $file->getClientOriginalName();
        $ext      = strtolower($file->getClientOriginalExtension());

        $log = UploadLog::create([
            'pic_id'          => Auth::user()->pic_id,
            'store_id'        => $request->store_id,
            'report_type'     => $request->report_type,
            'source_platform' => $request->source_platform,
            'filename'        => $filename,
            'rows_parsed'     => 0,
            'status'          => 'processing',
            'uploaded_at'     => now(),
        ]);

        try {
            $rows = $ext === 'csv' ? $this->parseCsv($file->getRealPath())
                                   : $this->parseXlsx($file->getRealPath());

            $count = $this->processRows(
                $rows,
                $request->report_type,
                $request->source_platform,
                $request->store_id
            );

            $log->update(['rows_parsed' => $count, 'status' => 'success']);
            return back()->with('success', "File \"$filename\" berhasil diproses! ($count baris data diimpor)");

        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    // ── Parsers ─────────────────────────────────────────────────────────────

    private function parseCsv(string $path): array
    {
        $rows = [];
        if (($fh = fopen($path, 'r')) === false) throw new \RuntimeException('Tidak bisa membuka file.');
        $headers = null;
        while (($row = fgetcsv($fh, 0, ',')) !== false) {
            if ($headers === null) { $headers = array_map('trim', $row); continue; }
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, array_map('trim', $row));
            }
        }
        fclose($fh);
        return $rows;
    }

    private function parseXlsx(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) throw new \RuntimeException('File XLSX tidak valid.');

        $sharedStrings = [];
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml) {
            $ss = new \SimpleXMLElement($xml);
            $ss->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            foreach ($ss->xpath('//x:si') as $si) {
                $sharedStrings[] = strip_tags($si->asXML());
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if (!$sheetXml) throw new \RuntimeException('Sheet tidak ditemukan dalam file XLSX.');

        $sheet = new \SimpleXMLElement($sheetXml);
        $sheet->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $rawRows = [];
        foreach ($sheet->xpath('//x:row') as $row) {
            $cells = [];
            foreach ($row->xpath('x:c') as $c) {
                $attr = $c->attributes();
                $ref  = (string)($attr['r'] ?? '');
                $col  = preg_replace('/[0-9]/', '', $ref);
                $type = (string)($attr['t'] ?? '');
                $val  = (string)($c->v ?? '');
                if ($type === 's') $val = $sharedStrings[(int)$val] ?? '';
                $cells[$col] = $val;
            }
            $rawRows[] = $cells;
        }

        if (empty($rawRows)) return [];
        $headerRow = array_shift($rawRows);
        $colKeys   = array_keys($headerRow);
        $headers   = array_values($headerRow);

        $rows = [];
        foreach ($rawRows as $raw) {
            $mapped = [];
            foreach ($colKeys as $i => $col) {
                $mapped[$headers[$i]] = $raw[$col] ?? '';
            }
            $rows[] = $mapped;
        }
        return $rows;
    }

    // ── Processors ──────────────────────────────────────────────────────────

    private function processRows(array $rows, string $type, string $platform, int $storeId): int
    {
        return match ($type) {
            'orders'     => $this->processOrders($rows, $storeId, $platform),
            'financials' => $this->processFinancials($rows, $storeId),
            'ads'        => $this->processAds($rows, $storeId, $platform),
            'metrics'    => $this->processMetrics($rows, $storeId),
            default      => 0,
        };
    }

    private function processOrders(array $rows, int $storeId, string $platform): int
    {
        $store = Store::find($storeId);
        $count = 0;
        foreach ($rows as $row) {
            $row = array_change_key_case($row, CASE_LOWER);

            $orderId = $this->col($row, ['no. pesanan','order id','order_id','nomor pesanan','no pesanan']) ?? 'AUTO-'.uniqid();
            $dateRaw = $this->col($row, ['waktu pesanan dibuat','order time','tanggal','date','order date','create time']);
            $gmv     = $this->toNumber($this->col($row, ['total harga produk','gmv','total pesanan','total price','price','harga']));
            $qty     = (int)($this->toNumber($this->col($row, ['jumlah','qty','quantity'])) ?: 1);
            $sku     = $this->col($row, ['sku induk','sku','product sku','sku referensi']) ?? '-';
            $name    = $this->col($row, ['nama produk','product name','nama barang','item name']) ?? '-';
            $buyer   = $this->col($row, ['username (pembeli)','buyer','username','buyer username','nama pembeli']) ?? 'unknown';
            $status  = $this->mapStatus($this->col($row, ['status pesanan','status','order status']) ?? 'complete');

            if (!$dateRaw) continue;
            try { $date = Carbon::parse($dateRaw)->toDateString(); }
            catch (\Exception $e) { continue; }

            if (Order::where('order_id', $orderId)->exists()) continue;

            $isNew = !Customer::where('buyer_identifier', $buyer)->where('platform', $platform)->exists();
            $customer = Customer::firstOrCreate(
                ['buyer_identifier' => $buyer, 'platform' => $platform],
                ['first_order_date' => $date, 'first_store_id' => $storeId, 'total_orders' => 0, 'last_order_date' => $date]
            );
            $customer->increment('total_orders');
            $customer->update(['last_order_date' => $date]);

            Order::create([
                'order_id'        => $orderId,
                'store_id'        => $storeId,
                'date'            => $date,
                'gmv'             => $gmv,
                'status'          => $status,
                'product_sku'     => $sku,
                'product_name'    => $name,
                'qty'             => $qty,
                'buyer_identifier'=> $buyer,
                'is_new_customer' => $isNew,
                'customer_id'     => $customer->id,
                'channel_type'    => $store->channel_type,
                'source_platform' => $platform,
            ]);
            $count++;
        }
        return $count;
    }

    private function processFinancials(array $rows, int $storeId): int
    {
        $count = 0;
        foreach ($rows as $row) {
            $row    = array_change_key_case($row, CASE_LOWER);
            $period = $this->col($row, ['periode','period','bulan','month']) ?? '';
            if (!$period) continue;
            try { $period = Carbon::parse($period)->format('Y-m'); }
            catch (\Exception $e) { continue; }

            $grossGmv = $this->toNumber($this->col($row, ['gross gmv','total gmv','gmv']));
            $netGmv   = $this->toNumber($this->col($row, ['net gmv','pendapatan bersih']));
            $adminFee = $this->toNumber($this->col($row, ['biaya admin','admin fee','biaya layanan']));
            $ads      = $this->toNumber($this->col($row, ['biaya iklan','ads spend','iklan']));
            $hpp      = $this->toNumber($this->col($row, ['hpp','cogs','harga pokok']));
            $ops      = $this->toNumber($this->col($row, ['biaya operasional','operational']));

            Financial::updateOrCreate(
                ['store_id' => $storeId, 'period' => $period],
                [
                    'gross_gmv'        => $grossGmv,
                    'net_gmv'          => $netGmv ?: ($grossGmv - $adminFee),
                    'admin_fee'        => $adminFee,
                    'promo_xtra'       => $this->toNumber($this->col($row, ['promo','promo xtra','voucher'])),
                    'ongkir_fee'       => $this->toNumber($this->col($row, ['ongkir','shipping fee','biaya kirim'])),
                    'settlement'       => $this->toNumber($this->col($row, ['settlement','pencairan'])) ?: $netGmv,
                    'hpp_total'        => $hpp,
                    'ads_spend'        => $ads,
                    'operational_cost' => $ops,
                    'gross_profit'     => ($netGmv ?: $grossGmv) - $hpp,
                    'net_profit'       => ($netGmv ?: $grossGmv) - $hpp - $ads - $ops,
                ]
            );
            $count++;
        }
        return $count;
    }

    private function processAds(array $rows, int $storeId, string $platform): int
    {
        $count = 0;
        foreach ($rows as $row) {
            $row     = array_change_key_case($row, CASE_LOWER);
            $dateRaw = $this->col($row, ['tanggal','date','waktu','time','report date']);
            if (!$dateRaw) continue;
            try { $date = Carbon::parse($dateRaw)->toDateString(); }
            catch (\Exception $e) { continue; }

            $spend = $this->toNumber($this->col($row, ['pengeluaran iklan','spend','biaya','cost','amount spent']));
            $gmv   = $this->toNumber($this->col($row, ['gmv dari iklan','gmv','revenue','purchase value','conversion value']));
            $impr  = (int)$this->toNumber($this->col($row, ['tayangan','impressions','impresi']));
            $klik  = (int)$this->toNumber($this->col($row, ['klik','clicks','click']));
            $roas  = $spend > 0 ? round($gmv / $spend, 2) : 0;

            AdsPerformance::updateOrCreate(
                ['store_id' => $storeId, 'date' => $date, 'platform' => $platform],
                [
                    'spend'        => $spend,
                    'impressi'     => $impr,
                    'klik'         => $klik,
                    'gmv_from_ads' => $gmv,
                    'roas'         => $roas,
                    'reach'        => (int)$this->toNumber($this->col($row, ['jangkauan','reach'])),
                    'konversi'     => (int)$this->toNumber($this->col($row, ['konversi','conversions','purchase'])),
                    'campaign_name'=> $this->col($row, ['nama kampanye','campaign name','campaign']) ?? '-',
                ]
            );
            $count++;
        }
        return $count;
    }

    private function processMetrics(array $rows, int $storeId): int
    {
        $count = 0;
        foreach ($rows as $row) {
            $row     = array_change_key_case($row, CASE_LOWER);
            $dateRaw = $this->col($row, ['tanggal','date','waktu']);
            if (!$dateRaw) continue;
            try { $date = Carbon::parse($dateRaw)->toDateString(); }
            catch (\Exception $e) { continue; }

            $views    = (int)$this->toNumber($this->col($row, ['tayangan produk','views','page views','kunjungan']));
            $visitors = (int)$this->toNumber($this->col($row, ['pengunjung','visitors','unique visitors']));
            $atc      = (int)$this->toNumber($this->col($row, ['tambah ke keranjang','add to cart','atc']));
            $checkout = (int)$this->toNumber($this->col($row, ['checkout','bayar']));
            $buyers   = (int)$this->toNumber($this->col($row, ['pembeli','buyers','orders']));
            $cvr      = $visitors > 0 ? round($buyers / $visitors * 100, 4) : 0;
            $atcRate  = $visitors > 0 ? round($atc / $visitors * 100, 4) : 0;

            StoreMetric::updateOrCreate(
                ['store_id' => $storeId, 'date' => $date],
                compact('views', 'visitors', 'atc', 'checkout', 'buyers', 'cvr', 'atcRate') + ['atc_rate' => $atcRate]
            );
            $count++;
        }
        return $count;
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function col(array $row, array $keys): ?string
    {
        foreach ($keys as $k) {
            if (isset($row[$k]) && $row[$k] !== '') return $row[$k];
        }
        return null;
    }

    private function toNumber(?string $val): float
    {
        if ($val === null) return 0;
        return (float)preg_replace('/[^0-9.\-]/', '', str_replace(',', '.', $val));
    }

    private function mapStatus(string $raw): string
    {
        $raw = strtolower($raw);
        if (str_contains($raw, 'selesai') || str_contains($raw, 'complete') || str_contains($raw, 'delivered')) return 'complete';
        if (str_contains($raw, 'batal') || str_contains($raw, 'cancel')) return 'cancel';
        if (str_contains($raw, 'retur') || str_contains($raw, 'return')) return 'returned';
        if (str_contains($raw, 'refund')) return 'refunded';
        return 'pending';
    }
}
