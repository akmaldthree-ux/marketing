<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('store_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('visitors')->default(0);
            $table->unsignedBigInteger('add_to_cart')->default(0);
            $table->unsignedBigInteger('checkout')->default(0);
            $table->unsignedBigInteger('buyers')->default(0);
            $table->decimal('cvr', 8, 4)->default(0);
            $table->decimal('atc_rate', 8, 4)->default(0);
            $table->unique(['store_id','date']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('store_metrics'); }
};
