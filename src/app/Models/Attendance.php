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
    
    /*休憩時間の合計を取得（アクセサ名 → $attendance->total_rest_time）*/
    public function getTotalRestTimeAttribute(): string
    {
        if($this->rests->isEmpty()) {
            return '';
        }
        
        $totalMinutes = 0;
        $hasCompletedRest = false;//休憩が終了しているかどうかをチェック

        foreach ($this->rests as $rest) {
            if (!empty($rest->rest_start) && !empty($rest->rest_end)) {
                try {
                    $start = Carbon::parse($rest->rest_start);
                    $end = Carbon::parse($rest->rest_end);
                    if ($end->lessThan($start)) {
                        $end->addDay();
                    }
                    $totalMinutes += $start->diffInMinutes($end);
                    $hasCompletedRest = true;
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        //休憩が終了していない場合は空文字を返す
        if(!$hasCompletedRest) {
            return '';
        }

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }
    
    /*実動時間を取得(勤務時間合計ー休憩時間合計)*/
    public function getWorkTimeAttribute(): string
    {
        if (! $this->clock_in || ! $this->clock_out) {
            return '';
        }

        // time 型のみのとき、勤怠日と組み合わせて同日の差分にする
        $dateStr = $this->date->format('Y-m-d');
        $start = Carbon::parse($dateStr . ' ' . $this->toTimeStr($this->clock_in));
        $end = Carbon::parse($dateStr . ' ' . $this->toTimeStr($this->clock_out));

        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $totalWorkMinutes = $start->diffInMinutes($end);

        $totalRestMinutes = 0;
        foreach ($this->rests as $rest) {
            if ($rest->rest_start && $rest->rest_end) {
                $restStart = Carbon::parse($dateStr . ' ' . $this->toTimeStr($rest->rest_start));
                $restEnd = Carbon::parse($dateStr . ' ' . $this->toTimeStr($rest->rest_end));
                if ($restEnd->lessThan($restStart)) {
                    $restEnd->addDay();
                }
                $totalRestMinutes += $restStart->diffInMinutes($restEnd);
            }
        }

        $actualWorkMinutes = max(0, $totalWorkMinutes - $totalRestMinutes);
        $hours = intdiv($actualWorkMinutes, 60);
        $minutes = $actualWorkMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Carbon / 「時刻のみ」「日付+時刻」文字列のいずれでも、勤怠日と結合できるよう H:i:s にそろえる。
     * （datetime キャストの Carbon を文字列連結すると __toString で日付付きになり二重日付になるのを防ぐ）
     */
    private function toTimeStr($value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('H:i:s');
        }

        return Carbon::parse((string) $value)->format('H:i:s');
    }
}
