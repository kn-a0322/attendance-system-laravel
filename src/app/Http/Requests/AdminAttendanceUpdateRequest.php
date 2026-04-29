<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AdminAttendanceUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i',
            'remark' => 'required|string',
            'rest_start.*' => 'nullable|date_format:H:i',
            'rest_end.*' => 'nullable|date_format:H:i',

        ];
    }

    public function messages()
    {
        return [
            'remark.required' => '備考を記入してください',
            'date_format' => '時間を正しい形式（00:00）で入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockInStr = $this->input('clock_in');
            $clockOutStr = $this->input('clock_out');  
            //出勤・退勤の時間が入力されているか
            if(!$clockInStr || !$clockOutStr) {
                return;
            }

            $in = Carbon::parse($clockInStr);
            $out = Carbon::parse($clockOutStr);

            //出勤・退勤の前後関係
            if($out->lt($in)) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            //休憩時間のチェック
            $restStarts = $this->input('rest_start', []);
            $restEnds = $this->input('rest_end', []);

            foreach($restStarts as $index => $restStartStr) {
                $restEndStr = $restEnds[$index] ?? null;

                if($restStartStr && $restEndStr ) {
                    $start = Carbon::parse($restStartStr);
                    $end = Carbon::parse($restEndStr);
                    
                    //休憩開始時間が出勤より前、退勤より後
                    if($start->lt($in) || $start->gt($out)) {
                        $validator->errors()->add('rest_start.' . $index, '休憩時間が不適切な値です');           
                    }
                    //休憩終了時間が退勤より後
                    if($end->gt($out)) {
                        $validator->errors()->add('rest_end.' . $index, '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }
}
