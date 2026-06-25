<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunnelTarget extends Model
{
    protected $fillable = ['store_id', 'stage', 'target_pct', 'effective_from'];

    public function store() { return $this->belongsTo(Store::class); }
}
