<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    const STATUS_OUT_OF_WORK = 0;
    const STATUS_WORKING = 1;
    const STATUS_RESTING = 2;
    const STATUS_FINISHED = 3;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    /*自分のレコードを取得するスコープ*/
    public function scopeToday($query)
    {
        return $query->where('user_id', auth()->user()->id)
                     ->where('date', now()->format('Y-m-d'));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rests()
    {
        return $this->hasMany(Rest::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }
}
