<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Financial extends Model
{
    protected $fillable = ['store_id', 'period', 'gross_gmv', 'net_gmv', 'admin_fee', 'promo_xtra', 'ongkir_fee', 'settlement', 'hpp_total', 'ads_spend', 'operational_cost', 'gross_profit', 'net_profit'];

    public function store() { return $this->belongsTo(Store::class); }
}
