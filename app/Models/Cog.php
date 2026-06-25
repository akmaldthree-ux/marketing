<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Cog extends Model {
    protected $table = 'cogs';
    protected $fillable = ['product_sku','product_name','hpp_per_unit','effective_from'];
    protected $casts = ['effective_from'=>'date'];
}
