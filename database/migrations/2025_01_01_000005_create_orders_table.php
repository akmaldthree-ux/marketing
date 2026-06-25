<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->date('order_date');
            $table->decimal('gmv', 15, 2)->default(0);
            $table->enum('status', ['complete','shipped','processing','pending','cancelled','returned','refunded'])->default('pending');
            $table->string('product_sku')->nullable();
            $table->string('product_name')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->boolean('is_new_customer')->default(false);
            $table->string('source_platform')->nullable();
            $table->index(['store_id','order_date']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('orders'); }
};
