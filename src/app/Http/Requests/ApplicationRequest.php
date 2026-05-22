<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationRequest extends FormRequest
{
    private const NIGHT_SHIFT_CLOCK_IN_HOUR = 18;
    private const NIGHT_SHIFT_CLOCK_OUT_HOUR = 13;

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
        $validator->after(function (Validator $validator) {
            if (! $this->canValidateWorkTimes($validator)) {
                return;
            }

            $clockIn = Carbon::parse("{$this->input('date')} {$this->input('clock_in')}");
            $clockOut = $this->resolveClockOutAt($clockIn);

            if ($clockOut === null) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');

                return;
            }

            $this->validateRestTimes($validator, $clockIn, $clockOut);
        });
    }

    private function canValidateWorkTimes(Validator $validator): bool
    {
        foreach (['date', 'clock_in', 'clock_out'] as $field) {
            if ($validator->errors()->has($field)) {
                return false;
            }
        }

        return true;
    }

    private function resolveClockOutAt(Carbon $clockInAt): ?Carbon
    {
        $clockOutAt = Carbon::parse("{$this->input('date')} {$this->input('clock_out')}");
        $clockIn = $clockInAt->copy();
        $clockOut = $clockOutAt->copy();
        $workInvalid = false;

        if ($clockOutAt->gt($clockInAt)) {
            // 例: 09:00 〜 18:00
        } elseif ($clockOutAt->eq($clockInAt)) {
            $workInvalid = true;
        } else {
            $isNightShift = $clockInAt->hour >= self::NIGHT_SHIFT_CLOCK_IN_HOUR
                && $clockOutAt->hour < self::NIGHT_SHIFT_CLOCK_OUT_HOUR;
            if (! $isNightShift) {
                $workInvalid = true;
            } else {
                $clockOut->addDay();
            }
        }

        if ($workInvalid || ! $clockIn->lt($clockOut)) {
            return null;
        }

        return $clockOut;
    }

    private function validateRestTimes(Validator $validator, Carbon $clockIn, Carbon $clockOut): void
    {
        $date = $this->input('date');
        $startTimes = $this->input('rest_start', []) ?: [];
        $endTimes = $this->input('rest_end', []) ?: [];

        foreach ($startTimes as $index => $startTime) {
            $endTime = $endTimes[$index] ?? null;

            if ($validator->errors()->has("rest_start.{$index}") || $validator->errors()->has("rest_end.{$index}")) {
                continue;
            }

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

            $restStartAt = Carbon::parse("{$date} {$startTime}");
            $restEndAt = Carbon::parse("{$date} {$endTime}");

            for ($i = 0; $i < 2 && $restStartAt->lt($clockIn); $i++) {
                $restStartAt->addDay();
            }
            for ($i = 0; $i < 2 && $restEndAt->lte($restStartAt); $i++) {
                $restEndAt->addDay();
            }

            if ($restStartAt->lt($clockIn) || $restStartAt->gt($clockOut)) {
                $validator->errors()->add("rest_start.{$index}", '休憩時間が不適切な値です');
                continue;
            }

            if ($restEndAt->gt($clockOut)) {
                $validator->errors()->add("rest_end.{$index}", '休憩時間もしくは退勤時間が不適切な値です');
                continue;
            }

            if ($restEndAt->lte($restStartAt)) {
                $validator->errors()->add("rest_end.{$index}", '休憩時間が不適切な値です');
            }
        }
    }
}
