<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ads_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('platform');
            $table->decimal('spend', 15, 2)->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('gmv_from_ads', 15, 2)->default(0);
            $table->decimal('roas', 8, 2)->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->unsignedBigInteger('conversions')->default(0);
            $table->string('campaign_name')->nullable();
            $table->unique(['store_id','date','platform']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('ads_performance'); }
};
