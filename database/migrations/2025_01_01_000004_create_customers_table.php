<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->enum('platform', ['Shopee','TikTok Shop','Meta Ads']);
            $table->date('first_order_date')->nullable();
            $table->date('last_order_date')->nullable();
            $table->unsignedInteger('total_orders')->default(0);
            $table->foreignId('first_store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->unique(['username','platform']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('customers'); }
};
