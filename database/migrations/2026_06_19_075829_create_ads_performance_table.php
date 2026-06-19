<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ads_performance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->date('date');
            $table->enum('platform', ['Shopee', 'TikTok Shop', 'Meta Ads']);
            $table->decimal('spend', 15, 2)->default(0);
            $table->integer('impressi')->default(0);
            $table->integer('klik')->default(0);
            $table->decimal('gmv_from_ads', 15, 2)->default(0);
            $table->decimal('roas', 8, 2)->default(0);
            $table->integer('reach')->default(0);
            $table->integer('konversi')->default(0);
            $table->string('campaign_name')->nullable();
            $table->timestamps();
            $table->index(['store_id', 'date']);
        });
    }
    public function down(): void { Schema::dropIfExists('ads_performance'); }
};
