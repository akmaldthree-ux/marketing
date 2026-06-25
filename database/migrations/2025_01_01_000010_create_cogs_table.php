<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('product_sku');
            $table->string('product_name');
            $table->decimal('hpp_per_unit', 15, 2)->default(0);
            $table->date('effective_from');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('cogs'); }
};
