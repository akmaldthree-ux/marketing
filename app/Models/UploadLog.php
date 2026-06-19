<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UploadLog extends Model
{
    protected $fillable = ['pic_id', 'store_id', 'report_type', 'source_platform', 'filename', 'rows_parsed', 'status', 'error_message', 'uploaded_at'];

    public function store() { return $this->belongsTo(Store::class); }
    public function pic() { return $this->belongsTo(Pic::class); }
}
