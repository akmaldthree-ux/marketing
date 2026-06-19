<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Cog extends Model
{
    protected $fillable = ['store_id', 'product_sku', 'product_name', 'hpp_per_unit', 'period'];
}
