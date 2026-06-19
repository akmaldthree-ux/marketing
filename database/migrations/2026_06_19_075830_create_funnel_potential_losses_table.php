<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('funnel_potential_losses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->date('date');
            $table->string('stage');
            $table->decimal('actual_rate', 8, 4)->default(0);
            $table->decimal('target_rate', 8, 4)->default(0);
            $table->integer('traffic_volume')->default(0);
            $table->decimal('aov', 15, 2)->default(0);
            $table->decimal('potential_loss_rp', 15, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('funnel_potential_losses'); }
};
