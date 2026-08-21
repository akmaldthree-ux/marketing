<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearReportData extends Command {
    protected $signature   = 'reports:clear {--force : Skip confirmation}';
    protected $description = 'Hapus semua data transaksi: orders, ads_performance, store_metrics, financials, customers, upload_logs';

    public function handle() {
        if (!$this->option('force')) {
            if (!$this->confirm('Ini akan menghapus SEMUA data transaksi. Lanjutkan?')) {
                $this->info('Dibatalkan.');
                return 0;
            }
        }

        $tables = [
            'upload_logs'      => 'Riwayat Upload',
            'orders'           => 'Orders',
            'ads_performance'  => 'Ads Performance',
            'store_metrics'    => 'Store Metrics',
            'financials'       => 'Financials',
            'customers'        => 'Customers',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table => $label) {
            $count = DB::table($table)->count();
            DB::table($table)->truncate();
            $this->line("  ✓ {$label} ({$count} baris dihapus)");
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Selesai. Semua data transaksi berhasil dihapus.');
        return 0;
    }
}
