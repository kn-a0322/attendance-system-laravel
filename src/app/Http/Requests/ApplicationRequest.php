<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'rest_start.*' => ['nullable', 'date_format:H:i'],
            'rest_end.*' => ['nullable', 'date_format:H:i'],
            'remark' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => '日付が不正です。',
            'clock_in.required' => '出勤時間を入力してください。',
            'clock_out.required' => '退勤時間を入力してください。',
            'remark.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $date = $this->input('date');
            $clockInStr = $this->input('clock_in');
            $clockOutStr = $this->input('clock_out');

            if (! $date || ! $clockInStr || ! $clockOutStr) {
                return;
            }

            $in0 = Carbon::parse("{$date} {$clockInStr}");
            $out0 = Carbon::parse("{$date} {$clockOutStr}");

            /* 1. 出退勤
             * ・同日の「時刻」だけ見たとき 退勤 <= 出勤 なら、夜勤(翌日退勤)か、単なる入力不整合かを分ける
             * ・夜勤: 出勤が18時以降 かつ 退勤が13時未満（=翌早朝〜昼前）のパターンを許容
             * ・それ以外で 退勤 <= 出勤 なら「出勤が退勤より後／退勤が出勤より前」＝不適切
             * ・同日で同一時刻も不適切
             */
            $in = $in0->copy();
            $out = $out0->copy();
            $workInvalid = false;

            if ($out0->gt($in0)) {
                // 例: 09:00 〜 18:00
            } elseif ($out0->eq($in0)) {
                $workInvalid = true;
            } else {
                // 同日かつ 退勤の時刻 < 出勤の時刻（例: 10:00 / 9:00 や 22:00 / 6:00）
                // 退勤が翌朝扱い（12:30 など hour=12 も含む）
                $isNightShift = $in0->hour >= 18 && $out0->hour < 13;
                if (! $isNightShift) {
                    $workInvalid = true;
                } else {
                    $out->addDay();
                }
            }

            if ($workInvalid) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');

                return;
            }

            if (! $in->lt($out)) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');

                return;
            }

            // 休憩
            $startTimes = $this->input('rest_start', []) ?: [];
            $endTimes = $this->input('rest_end', []) ?: [];

            foreach ($startTimes as $index => $startTime) {
                $endTime = $endTimes[$index] ?? null;

                if (empty($startTime) && empty($endTime)) {
                    continue;
                }
                if (empty($startTime) || empty($endTime)) {
                    $validator->errors()->add(
                        empty($endTime) ? "rest_end.{$index}" : "rest_start.{$index}",
                        '休憩時間が不適切な値です'
                    );
                    continue;
                }

                $start = Carbon::parse("{$date} {$startTime}");
                $end = Carbon::parse("{$date} {$endTime}");

                for ($i = 0; $i < 2 && $start->lt($in); $i++) {
                    $start->addDay();
                }
                for ($i = 0; $i < 2 && $end->lte($start); $i++) {
                    $end->addDay();
                }

                if ($start->lt($in) || $start->gt($out)) {
                    $validator->errors()->add("rest_start.{$index}", '休憩時間が不適切な値です');
                    continue;
                }

                if ($end->gt($out)) {
                    $validator->errors()->add("rest_end.{$index}", '休憩時間もしくは退勤時間が不適切な値です');
                    continue;
                }

                if ($end->lte($start)) {
                    $validator->errors()->add("rest_end.{$index}", '休憩時間が不適切な値です');
                }
            }
        });
    }
}
