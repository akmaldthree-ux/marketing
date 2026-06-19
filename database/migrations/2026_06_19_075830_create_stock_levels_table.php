<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('product_sku');
            $table->string('product_name')->nullable();
            $table->integer('stock_qty')->default(0);
            $table->date('as_of_date');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('stock_levels'); }
};
