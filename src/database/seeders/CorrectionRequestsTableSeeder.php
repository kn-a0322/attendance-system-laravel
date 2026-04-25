<?php

namespace Database\Seeders;

use App\Models\CorrectionRequest;
use App\Models\CorrectionRequestDetail;
use App\Models\CorrectionRequestRest;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CorrectionRequestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //承認待ちのデータ
        $pending = CorrectionRequest::create([
            'user_id' => 1,
            'attendance_id' => 1,
            'status' => 0,
        ]);

        $pending->detail()->create([
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'remark' => '電車遅延のため',
        ]);

        //承認済みのデータ
        $approved = CorrectionRequest::create([
            'user_id' => 1,
            'attendance_id' => 2,
            'status' => 1,
            'approved_at' => Carbon::now(),
            'approved_by' => 3,//管理者
        ]);

        $approved->detail()->create([
            'clock_in' => '08:45:00',
            'clock_out' => '17:45:00',
            'remark' => '未打刻のため',
        ]);

        $approved->rests()->create([
            'rest_start' => '12:00:00',
            'rest_end' => '13:00:00',
        ]);
    }
}
