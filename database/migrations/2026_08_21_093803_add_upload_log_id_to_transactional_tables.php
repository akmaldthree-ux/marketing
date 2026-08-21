<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        foreach (['orders','ads_performance','store_metrics','financials'] as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'upload_log_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('upload_log_id')->nullable()->after('id')->index();
                });
            }
        }
    }
    public function down(): void {
        foreach (['orders','ads_performance','store_metrics','financials'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'upload_log_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('upload_log_id');
                });
            }
        }
    }
};
