<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model {
    protected $fillable = ['product_sku','canonical_name','brand','category','is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    // Nama tampilan: canonical_name dari master, fallback ke parameter
    public static function resolvedName(string $sku, string $fallback = ''): string
    {
        static $cache = [];
        if (!isset($cache[$sku])) {
            $cache[$sku] = static::where('product_sku', $sku)->value('canonical_name');
        }
        return $cache[$sku] ?? $fallback;
    }
}
