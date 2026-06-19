<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('demand_forecasts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('product_sku');
            $table->string('product_name')->nullable();
            $table->date('forecast_week_start');
            $table->decimal('avg_daily_sales_4w', 10, 2)->default(0);
            $table->integer('forecast_qty')->default(0);
            $table->integer('safety_stock_qty')->default(0);
            $table->integer('recommended_order_qty')->default(0);
            $table->enum('trend_direction', ['up', 'down', 'stable'])->default('stable');
            $table->decimal('trend_change_pct', 8, 2)->default(0);
            $table->boolean('is_limited_data')->default(false);
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('demand_forecasts'); }
};
