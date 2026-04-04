<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequestRest extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_request_id',
        'rest_start',
        'rest_end',
    ];

    protected $casts = [
        'rest_start' => 'datetime',
        'rest_end' => 'datetime',
    ];

    public function correctionRequest()
    {
        return $this->belongsTo(CorrectionRequest::class);
    }
}
