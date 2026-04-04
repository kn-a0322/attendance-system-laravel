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
        'admin_id',
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
        return $this->belongsTo(User::class);
    }
    
    public function admin()
    {
        return $this->belongsTo(Admin::class);
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
