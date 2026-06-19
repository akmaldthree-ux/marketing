<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('financials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('period'); // YYYY-MM
            $table->decimal('gross_gmv', 15, 2)->default(0);
            $table->decimal('net_gmv', 15, 2)->default(0);
            $table->decimal('admin_fee', 15, 2)->default(0);
            $table->decimal('promo_xtra', 15, 2)->default(0);
            $table->decimal('ongkir_fee', 15, 2)->default(0);
            $table->decimal('settlement', 15, 2)->default(0);
            $table->decimal('hpp_total', 15, 2)->default(0);
            $table->decimal('ads_spend', 15, 2)->default(0);
            $table->decimal('operational_cost', 15, 2)->default(0);
            $table->decimal('gross_profit', 15, 2)->default(0);
            $table->decimal('net_profit', 15, 2)->default(0);
            $table->timestamps();
            $table->unique(['store_id', 'period']);
        });
    }
    public function down(): void { Schema::dropIfExists('financials'); }
};
