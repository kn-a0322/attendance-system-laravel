<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequestDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_request_id',
        'clock_in',
        'clock_out',
        'remark',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function correctionRequest()
    {
        return $this->belongsTo(CorrectionRequest::class);
    }


}
