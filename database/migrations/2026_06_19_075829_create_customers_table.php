<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('buyer_identifier');
            $table->enum('platform', ['Shopee', 'TikTok Shop', 'Meta Ads']);
            $table->date('first_order_date');
            $table->unsignedBigInteger('first_store_id');
            $table->integer('total_orders')->default(0);
            $table->date('last_order_date')->nullable();
            $table->timestamps();
            $table->index(['buyer_identifier', 'platform']);
        });
    }
    public function down(): void { Schema::dropIfExists('customers'); }
};
