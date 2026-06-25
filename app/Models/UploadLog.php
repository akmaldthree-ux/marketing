<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadLog extends Model
{
    protected $fillable = [
        'store_id', 'pic_id', 'filename', 'source_platform', 'report_type',
        'rows_parsed', 'status', 'error_message', 'uploaded_at',
    ];

    public function store() { return $this->belongsTo(Store::class); }
    public function pic() { return $this->belongsTo(Pic::class); }
}
