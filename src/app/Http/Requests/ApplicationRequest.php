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
            // 空行の休憩はスキップするため nullable（中身は withValidator で整合チェック）
            'rest_start.*' => ['nullable', 'date_format:H:i'],
            'rest_end.*' => ['nullable', 'date_format:H:i'],
            'note' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => '日付が不正です。',
            'clock_in.required' => '出勤時間を入力してください。',
            'clock_out.required' => '退勤時間を入力してください。',
            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
        $date = $this->input('date');
        $clockInStr = $this->input('clock_in');
        $clockOutStr = $this->input('clock_out');

        // 出勤・退勤の比較
        $in = Carbon::parse($date . ' ' . $clockInStr);
        $out = Carbon::parse($date . ' ' . $clockOutStr);

        if ($out->lt($in)) {
            $out->addDay(); // 退勤が前なら翌日とみなす
        }

        if ($in->greaterThanOrEqualTo($out)) {
            $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
        }

        // 休憩の比較
        $startTimes = $this->input('rest_start', []);
        $endTimes = $this->input('rest_end', []);

        foreach ($startTimes as $index => $startTime) {
            $endTime = $endTimes[$index] ?? null;

            if (!empty($startTime) && !empty($endTime)) {
                $start = Carbon::parse($date . ' ' . $startTime);
                $end = Carbon::parse($date . ' ' . $endTime);

                // 補正：出勤より前なら1日足す、開始より前なら1日足す
                if ($start->lt($in)) { $start->addDay(); }
                if ($end->lt($start)) { $end->addDay(); }

                // チェック：出退勤の枠に収まっているか
                if ($start->lt($in) || $end->gt($out)) {
                    $validator->errors()->add("rest_start.{$index}", '休憩時間が不適切な値です');
                }
                
                // チェック：開始と終了が逆転していないか
                if ($start->greaterThanOrEqualTo($end)) {
                    $validator->errors()->add("rest_end.{$index}", '休憩時間が不適切な値です');
                }
            }
        }
    });
 }
}


    