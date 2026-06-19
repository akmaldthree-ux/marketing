<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->unsignedBigInteger('store_id');
            $table->date('date');
            $table->decimal('gmv', 15, 2)->default(0);
            $table->enum('status', ['complete', 'cancel', 'returned', 'refunded', 'pending'])->default('complete');
            $table->string('product_sku')->nullable();
            $table->string('product_name')->nullable();
            $table->integer('qty')->default(1);
            $table->string('buyer_identifier')->nullable();
            $table->boolean('is_new_customer')->default(true);
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->enum('channel_type', ['mp', 'non_mp'])->default('mp');
            $table->string('source_platform')->nullable();
            $table->timestamps();
            $table->index(['store_id', 'date']);
            $table->index(['buyer_identifier', 'store_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('orders'); }
};
