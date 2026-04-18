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

        foreach([1, 2] as $user_id) {
            foreach(range(1, 30) as $date) {
                $dateString = "2026-04-{$date}";
                $dayOfWeek = date('w', strtotime($dateString));/*曜日を取得*/
                if($dayOfWeek == 0 || $dayOfWeek == 6) {/*土日はスキップ*/
                    continue;
                }
                Attendance::factory()
                ->has(Rest::factory()->count(rand(1, 2)))
                ->create([ 
                    'user_id' => $user_id, 
                    'date' => $dateString
                ]);
            }
        }
    }
}
