<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private const PLATFORMS = [
        'Shopee',
        'TikTok Shop',
        'Meta Ads',
        'Lazada',
        'Blibli',
        'Tokopedia',
    ];

    public function up(): void
    {
        $this->changePlatformEnum('stores', self::PLATFORMS);
        $this->changePlatformEnum('customers', self::PLATFORMS);
    }

    public function down(): void
    {
        $originalPlatforms = ['Shopee', 'TikTok Shop', 'Meta Ads'];

        $this->changePlatformEnum('customers', $originalPlatforms);
        $this->changePlatformEnum('stores', $originalPlatforms);
    }

    private function changePlatformEnum(string $table, array $platforms): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $values = collect($platforms)
            ->map(fn (string $platform) => "'".str_replace("'", "''", $platform)."'")
            ->implode(',');

        DB::statement("ALTER TABLE {$table} MODIFY platform ENUM({$values}) NOT NULL");
    }
};
