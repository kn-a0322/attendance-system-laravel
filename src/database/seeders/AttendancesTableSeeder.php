<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Attendance;
use App\Models\Rest;
use Database\Factories\AttendanceFactory;
use Database\Factories\RestFactory;

class AttendancesTableSeeder extends Seeder
{
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        $userIds = [1, 2];
        $months = ['2026-04', '2026-05', '2026-06'];

        foreach($userIds as $user_id) {
            foreach($months as $month) {

                //月の初日を取得
                $startOfMonth = \Carbon\Carbon::parse($month)->startOfMonth();
                //月の日数を取得
                $daysInMonth = $startOfMonth->daysInMonth;

                for($day = 0; $day < $daysInMonth; $day++) {
                    $currentDate = $startOfMonth->copy()->addDays($day);

                    if($currentDate->isWeekend()) {
                        continue;
                    }

                    Attendance::factory()
                    ->has(Rest::factory()->count(rand(1, 2)))
                    ->create([
                        'user_id' => $user_id,
                        'date' => $currentDate->format('Y-m-d'),
                    ]);
                }
            }
        }
    }
}
