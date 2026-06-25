<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('funnel_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->enum('stage', ['views_to_visitor','atc_rate','cvr']);
            $table->decimal('target_pct', 8, 2)->default(0);
            $table->date('effective_from');
            $table->unique(['store_id','stage']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('funnel_targets'); }
};
