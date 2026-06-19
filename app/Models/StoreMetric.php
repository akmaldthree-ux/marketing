<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StoreMetric extends Model
{
    protected $fillable = ['store_id', 'date', 'views', 'visitors', 'add_to_cart', 'checkout', 'buyers', 'cvr', 'atc_rate'];
    protected $casts = ['date' => 'date'];

    public function store() { return $this->belongsTo(Store::class); }
}
