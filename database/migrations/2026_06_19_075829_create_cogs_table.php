<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cogs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->string('product_sku');
            $table->string('product_name')->nullable();
            $table->decimal('hpp_per_unit', 15, 2)->default(0);
            $table->string('period'); // YYYY-MM
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('cogs'); }
};
