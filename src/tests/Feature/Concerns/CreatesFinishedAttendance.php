<?php

namespace Tests\Feature\Concerns;

use App\Models\Attendance;
use App\Models\User;

trait CreatesFinishedAttendance
{
    private function makeFinishedAttendance(User $user, string $date): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => Attendance::STATUS_FINISHED,
        ]);
    }
}
