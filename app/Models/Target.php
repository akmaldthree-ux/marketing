<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    protected $fillable = ['store_id', 'month', 'year', 'gmv_target'];

    public function store() { return $this->belongsTo(Store::class); }
}
