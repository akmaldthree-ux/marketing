<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('gmv_target', 15, 2)->default(0);
            $table->timestamps();
            $table->unique(['store_id', 'month', 'year']);
        });
    }
    public function down(): void { Schema::dropIfExists('targets'); }
};
