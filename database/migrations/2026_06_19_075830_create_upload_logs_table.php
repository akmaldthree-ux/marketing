<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('upload_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pic_id')->nullable();
            $table->unsignedBigInteger('store_id');
            $table->enum('report_type', ['orders', 'financials', 'ads', 'metrics']);
            $table->enum('source_platform', ['Shopee', 'TikTok Shop', 'Meta Ads']);
            $table->string('filename');
            $table->integer('rows_parsed')->default(0);
            $table->enum('status', ['success', 'failed', 'processing'])->default('processing');
            $table->text('error_message')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('upload_logs'); }
};
