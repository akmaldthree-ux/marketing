<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('store_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->date('date');
            $table->integer('views')->default(0);
            $table->integer('visitors')->default(0);
            $table->integer('add_to_cart')->default(0);
            $table->integer('checkout')->default(0);
            $table->integer('buyers')->default(0);
            $table->decimal('cvr', 8, 4)->default(0);
            $table->decimal('atc_rate', 8, 4)->default(0);
            $table->timestamps();
            $table->index(['store_id', 'date']);
        });
    }
    public function down(): void { Schema::dropIfExists('store_metrics'); }
};
