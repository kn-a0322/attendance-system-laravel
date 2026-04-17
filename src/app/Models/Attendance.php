<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
        'clock_in' => 'string',
        'clock_out' => 'string',
    ];

    /*自分のレコードを取得するスコープ*/
    public function scopeToday($query, $userId)
    {
        return $query->where('user_id', $userId)
                     ->whereDate('date', Carbon::today());
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
    
    /*休憩時間の合計を取得*/
    public function getTotalRestTimeAttribute()
    {
       $totalMinutes = 0;
       foreach ($this->rests as $rest) {
        if($rest->rest_start && $rest->rest_end) {
           $start = Carbon::parse($rest->rest_start);
           $end = Carbon::parse($rest->rest_end);
           $totalMinutes += $start->diffInMinutes($end);
        }
       }
       $hours = floor($totalMinutes / 60);
       $minutes = $totalMinutes % 60;
       return sprintf('%02d:%02d', $hours, $minutes);
    }
    
    /*実動時間を取得(勤務時間合計ー休憩時間合計)*/
    public function getWorkTimeAttribute()
    {
        if(!$this->clock_in || !$this->clock_out) {
            return null;
      } 
      
      $start = Carbon::parse($this->clock_in);
      $end = Carbon::parse($this->clock_out);

      $totalWorkMinutes = $start->diffInMinutes($end);
      $totalMinutes = 0;
       foreach ($this->rests as $rest) {
        if($rest->rest_start && $rest->rest_end) {
           $totalRestMinutes += Carbon::parse($rest->rest_start)->diffInMinutes(Carbon::parse($rest->rest_end));
        }
       }
       $totalWorkMinutes -= $totalRestMinutes;
       $hours = floor($totalWorkMinutes / 60);
       $minutes = $totalWorkMinutes % 60;
       return sprintf('%02d:%02d', $hours, $minutes);
      
    }
}
