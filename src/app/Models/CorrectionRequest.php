<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'approved_by',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function detail()
    {
        return $this->hasOne(CorrectionRequestDetail::class);
    }

    public function rests()
    {
        return $this->hasMany(CorrectionRequestRest::class);
    }

    
    
}
