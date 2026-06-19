<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('return_rate_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('pic_id')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('total_orders')->default(0);
            $table->integer('return_count')->default(0);
            $table->decimal('return_rate', 8, 4)->default(0);
            $table->enum('status', ['normal', 'warning', 'breach'])->default('normal');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('return_rate_logs'); }
};
