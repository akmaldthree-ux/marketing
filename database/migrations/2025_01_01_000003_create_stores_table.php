<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('brand', ['DTHREE','HURIM','ASFARA']);
            $table->enum('platform', ['Shopee','TikTok Shop','Meta Ads']);
            $table->enum('channel_type', ['marketplace','non_marketplace'])->default('marketplace');
            $table->foreignId('pic_id')->nullable()->constrained('pics')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('stores'); }
};
