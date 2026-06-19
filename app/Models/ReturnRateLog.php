<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ReturnRateLog extends Model
{
    protected $fillable = ['store_id', 'pic_id', 'period_start', 'period_end', 'total_orders', 'return_count', 'return_rate', 'status'];
}
