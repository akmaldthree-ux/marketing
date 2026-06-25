<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnRateLog extends Model
{
    protected $fillable = ['store_id', 'period', 'total_orders', 'returned_orders', 'return_rate', 'status'];

    public function store() { return $this->belongsTo(Store::class); }
}
