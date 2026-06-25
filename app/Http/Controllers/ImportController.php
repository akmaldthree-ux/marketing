<?php
namespace App\Http\Controllers;

use App\Models\{Store, Target, Cog, FunnelTarget, Financial};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportController extends Controller
{
    // ── Template Downloads ────────────────────────────────────────────────────

    public function templateTargetGmv()
    {
        $stores = Store::where('is_active', true)->orderBy('brand')->orderBy('name')->get();
        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        $header = array_merge(['Nama Toko', 'Brand'], $months);
        $rows   = [$header];
        foreach ($stores as $s) {
            $rows[] = array_merge([$s->name, $s->brand], array_fill(0, 12, 0));
        }
        // Petunjuk di bawah
        $rows[] = [];
        $rows[] = ['# PETUNJUK:'];
        $rows[] = ['# - Isi angka Target GMV (Rupiah) di kolom bulan yang sesuai'];
        $rows[] = ['# - Nama Toko harus persis sama seperti di sistem'];
        $rows[] = ['# - Baris yang diawali # akan diabaikan'];
        $rows[] = ['# - Contoh: 50000000 untuk Rp 50 juta'];
        return $this->csvDownload('template_target_gmv.csv', $rows);
    }

    public function templateHpp()
    {
        $rows = [
            ['SKU', 'Nama Produk', 'HPP per Unit (Rp)', 'Berlaku Dari (YYYY-MM-DD)'],
            ['DTH-001', 'Contoh: Gamis Syari Premium', '185000', '2026-01-01'],
            ['DTH-002', 'Contoh: Mukena Travel', '95000', '2026-01-01'],
        ];
        $rows[] = [];
        $rows[] = ['# PETUNJUK:'];
        $rows[] = ['# - SKU harus unik per produk'];
        $rows[] = ['# - Jika SKU sudah ada di sistem, HPP akan diupdate'];
        $rows[] = ['# - Berlaku Dari format: YYYY-MM-DD (contoh: 2026-01-01)'];
        return $this->csvDownload('template_hpp.csv', $rows);
    }

    public function templateBiayaOps()
    {
        $stores = Store::where('is_active', true)->orderBy('brand')->orderBy('name')->get();
        $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        $rows   = [array_merge(['Nama Toko', 'Brand', 'Tahun'], $months)];
        foreach ($stores as $s) {
            $rows[] = array_merge([$s->name, $s->brand, now()->year], array_fill(0, 12, 0));
        }
        $rows[] = [];
        $rows[] = ['# PETUNJUK:'];
        $rows[] = ['# - Isi biaya operasional (Rp) per bulan per toko'];
        $rows[] = ['# - Bisa alokasikan semua biaya ke satu toko sebagai cost center brand'];
        $rows[] = ['# - Biaya ops: gaji, sewa gudang, utilitas, dll'];
        return $this->csvDownload('template_biaya_ops.csv', $rows);
    }

    public function templateFunnelTarget()
    {
        $stores = Store::where('is_active', true)->orderBy('brand')->orderBy('name')->get();
        $rows   = [['Nama Toko', 'Brand', 'Views to Visitor (%)', 'ATC Rate (%)', 'CVR (%)', 'Berlaku Dari (YYYY-MM-DD)']];
        foreach ($stores as $s) {
            $rows[] = [$s->name, $s->brand, '10', '15', '2', now()->startOfYear()->toDateString()];
        }
        $rows[] = [];
        $rows[] = ['# PETUNJUK:'];
        $rows[] = ['# - Views to Visitor: % pengunjung dari total tayangan'];
        $rows[] = ['# - ATC Rate: % yang menambah ke keranjang'];
        $rows[] = ['# - CVR: % yang jadi pembeli dari total pengunjung'];
        $rows[] = ['# - Masukkan angka desimal, contoh: 2.5 untuk 2.5%'];
        return $this->csvDownload('template_funnel_target.csv', $rows);
    }

    // ── Import Processors ─────────────────────────────────────────────────────

    public function importTargetGmv(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls', 'year' => 'required|integer']);
        $year = (int)$request->year;

        try {
            $rows   = $this->parseFile($request->file('file'));
            $months = ['jan'=>1,'feb'=>2,'mar'=>3,'apr'=>4,'mei'=>5,'jun'=>6,
                       'jul'=>7,'ags'=>8,'sep'=>9,'okt'=>10,'nov'=>11,'des'=>12];

            $errors  = [];
            $count   = 0;
            $storeMap = Store::where('is_active', true)->pluck('id', 'name');

            foreach ($rows as $i => $row) {
                $row = array_change_key_case($row, CASE_LOWER);
                // Skip baris petunjuk
                $firstVal = trim(reset($row));
                if (str_starts_with($firstVal, '#') || $firstVal === '') continue;

                $storeName = trim($row['nama toko'] ?? $row['toko'] ?? '');
                if (!$storeName) continue;

                $storeId = $storeMap[$storeName] ?? null;
                if (!$storeId) {
                    $errors[] = "Baris " . ($i + 2) . ": Toko '{$storeName}' tidak ditemukan.";
                    continue;
                }

                foreach ($months as $key => $monthNum) {
                    $val = trim($row[$key] ?? '0');
                    if ($val === '' || $val === '0' || str_starts_with($val, '#')) continue;
                    $gmv = (int)str_replace(['.', ',', ' '], ['', '', ''], $val);
                    if ($gmv <= 0) continue;

                    Target::updateOrCreate(
                        ['store_id' => $storeId, 'month' => $monthNum, 'year' => $year],
                        ['gmv_target' => $gmv]
                    );
                    $count++;
                }
            }

            $msg = "Berhasil import {$count} target GMV tahun {$year}.";
            if ($errors) $msg .= ' ' . count($errors) . ' baris dilewati.';
            return back()->with('success', $msg)->with('import_errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function importHpp(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls']);

        try {
            $rows   = $this->parseFile($request->file('file'));
            $errors = [];
            $count  = 0;

            foreach ($rows as $i => $row) {
                $row = array_change_key_case($row, CASE_LOWER);
                $firstVal = trim(reset($row));
                if (str_starts_with($firstVal, '#') || $firstVal === '') continue;

                $sku  = trim($row['sku'] ?? '');
                $name = trim($row['nama produk'] ?? $row['nama'] ?? '');
                $hpp  = (float)str_replace(['.', ','], ['', '.'], trim($row['hpp per unit (rp)'] ?? $row['hpp'] ?? $row['hpp per unit'] ?? '0'));
                $date = trim($row['berlaku dari (yyyy-mm-dd)'] ?? $row['berlaku dari'] ?? $row['effective_from'] ?? now()->toDateString());

                if (!$sku) continue;
                if ($hpp <= 0) {
                    $errors[] = "Baris " . ($i + 2) . ": HPP untuk SKU '{$sku}' tidak valid.";
                    continue;
                }
                try { $date = Carbon::parse($date)->toDateString(); } catch (\Exception $e) { $date = now()->toDateString(); }

                Cog::updateOrCreate(
                    ['product_sku' => $sku],
                    ['product_name' => $name ?: $sku, 'hpp_per_unit' => $hpp, 'effective_from' => $date]
                );
                $count++;
            }

            $msg = "Berhasil import {$count} data HPP.";
            if ($errors) $msg .= ' ' . count($errors) . ' baris dilewati.';
            return back()->with('success', $msg)->with('import_errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function importBiayaOps(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls']);

        try {
            $rows     = $this->parseFile($request->file('file'));
            $months   = ['jan'=>'01','feb'=>'02','mar'=>'03','apr'=>'04','mei'=>'05','jun'=>'06',
                         'jul'=>'07','ags'=>'08','sep'=>'09','okt'=>'10','nov'=>'11','des'=>'12'];
            $storeMap = Store::where('is_active', true)->pluck('id', 'name');
            $errors   = [];
            $count    = 0;

            foreach ($rows as $i => $row) {
                $row = array_change_key_case($row, CASE_LOWER);
                $firstVal = trim(reset($row));
                if (str_starts_with($firstVal, '#') || $firstVal === '') continue;

                $storeName = trim($row['nama toko'] ?? $row['toko'] ?? '');
                if (!$storeName) continue;

                $storeId = $storeMap[$storeName] ?? null;
                if (!$storeId) {
                    $errors[] = "Baris " . ($i + 2) . ": Toko '{$storeName}' tidak ditemukan.";
                    continue;
                }

                $year = (int)trim($row['tahun'] ?? now()->year);

                foreach ($months as $key => $monthNum) {
                    $val = trim($row[$key] ?? '0');
                    if ($val === '' || $val === '0' || str_starts_with($val, '#')) continue;
                    $ops = (int)str_replace(['.', ',', ' '], ['', '', ''], $val);
                    if ($ops <= 0) continue;

                    Financial::updateOrCreate(
                        ['store_id' => $storeId, 'period' => $year . '-' . $monthNum],
                        ['operational_cost' => $ops]
                    );
                    $count++;
                }
            }

            $msg = "Berhasil import {$count} data biaya operasional.";
            if ($errors) $msg .= ' ' . count($errors) . ' baris dilewati.';
            return back()->with('success', $msg)->with('import_errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function importFunnelTarget(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls']);

        try {
            $rows     = $this->parseFile($request->file('file'));
            $storeMap = Store::where('is_active', true)->pluck('id', 'name');
            $errors   = [];
            $count    = 0;

            foreach ($rows as $i => $row) {
                $row = array_change_key_case($row, CASE_LOWER);
                $firstVal = trim(reset($row));
                if (str_starts_with($firstVal, '#') || $firstVal === '') continue;

                $storeName = trim($row['nama toko'] ?? $row['toko'] ?? '');
                if (!$storeName) continue;

                $storeId = $storeMap[$storeName] ?? null;
                if (!$storeId) {
                    $errors[] = "Baris " . ($i + 2) . ": Toko '{$storeName}' tidak ditemukan.";
                    continue;
                }

                $date = trim($row['berlaku dari (yyyy-mm-dd)'] ?? $row['berlaku dari'] ?? now()->startOfYear()->toDateString());
                try { $date = Carbon::parse($date)->toDateString(); } catch (\Exception $e) { $date = now()->toDateString(); }

                $stages = [
                    'views_to_visitor' => (float)($row['views to visitor (%)'] ?? $row['views to visitor'] ?? 0),
                    'atc_rate'         => (float)($row['atc rate (%)'] ?? $row['atc rate'] ?? 0),
                    'cvr'              => (float)($row['cvr (%)'] ?? $row['cvr'] ?? 0),
                ];

                foreach ($stages as $stage => $pct) {
                    if ($pct <= 0) continue;
                    FunnelTarget::updateOrCreate(
                        ['store_id' => $storeId, 'stage' => $stage],
                        ['target_pct' => $pct, 'effective_from' => $date]
                    );
                    $count++;
                }
            }

            $msg = "Berhasil import {$count} target funnel.";
            if ($errors) $msg .= ' ' . count($errors) . ' baris dilewati.';
            return back()->with('success', $msg)->with('import_errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    // ── File Parser (CSV + XLSX) ──────────────────────────────────────────────

    public function parseFilePublic($file): array { return $this->parseFile($file); }

    private function parseFile($file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        return $ext === 'csv' ? $this->parseCsv($file->getRealPath()) : $this->parseXlsx($file->getRealPath());
    }

    private function parseCsv(string $path): array
    {
        $rows = [];
        if (!($fh = fopen($path, 'r'))) throw new \RuntimeException('Tidak bisa membuka file.');
        // Detect separator
        $firstLine = fgets($fh);
        rewind($fh);
        $sep = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        $headers = null;
        while (($row = fgetcsv($fh, 0, $sep)) !== false) {
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
        $shared = [];
        if ($ss = $zip->getFromName('xl/sharedStrings.xml')) {
            preg_match_all('/<si>(.*?)<\/si>/s', $ss, $siM);
            foreach ($siM[1] as $si) {
                preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $si, $tM);
                $shared[] = html_entity_decode(implode('', $tM[1]), ENT_XML1, 'UTF-8');
            }
        }
        $sheetXml = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#xl/worksheets/sheet\d+\.xml#', $name)) {
                $sheetXml = $zip->getFromIndex($i);
                break;
            }
        }
        $zip->close();
        if (!$sheetXml) throw new \RuntimeException('Sheet tidak ditemukan dalam file XLSX.');

        $rawRows = [];
        preg_match_all('/<row[^>]*>(.*?)<\/row>/s', $sheetXml, $rowM);
        foreach ($rowM[1] as $rowContent) {
            $cells = [];
            preg_match_all('/<c\s([^>]*)>(.*?)<\/c>/s', $rowContent, $cellM, PREG_SET_ORDER);
            foreach ($cellM as $cell) {
                preg_match('/r="([^"]+)"/', $cell[1], $rM);
                preg_match('/t="([^"]+)"/', $cell[1], $tM);
                preg_match('/<v>(.*?)<\/v>/s', $cell[2], $vM);
                $col  = preg_replace('/[0-9]/', '', $rM[1] ?? '');
                $type = $tM[1] ?? '';
                $val  = $vM[1] ?? '';
                if ($type === 's') $val = $shared[(int)$val] ?? '';
                if ($col !== '') $cells[$col] = trim($val);
            }
            if (!empty($cells)) $rawRows[] = $cells;
        }
        if (empty($rawRows)) return [];
        $headerRow = array_shift($rawRows);
        $colKeys   = array_keys($headerRow);
        $headers   = array_values($headerRow);
        $rows      = [];
        foreach ($rawRows as $raw) {
            $mapped = [];
            foreach ($colKeys as $idx => $col) $mapped[$headers[$idx]] = $raw[$col] ?? '';
            $rows[] = $mapped;
        }
        return $rows;
    }

    private function csvDownload(string $filename, array $rows): \Illuminate\Http\Response
    {
        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM agar Excel terbaca benar
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\r\n";
        }
        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
