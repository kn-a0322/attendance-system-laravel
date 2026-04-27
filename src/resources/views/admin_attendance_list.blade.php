@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

@section('content')
<div class="admin-attendance-list">
    <div class="admin-attendance-list__header">
        <span class="admin-attendance-list__title-accent" aria-hidden="true"></span>
        <h1 class="admin-attendance-list__heading">{{ $currentDate->format('Y年m月d日') }}の勤怠</h1>
    </div>
    <nav class="date-nav" aria-label="日の切り替え">
        <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="date-nav__link date-nav__link--prev">
            <img src="{{ asset('images/logo/arrow.png') }}" alt="" class="date-nav__arrow date-nav__arrow--prev" width="20" height="20">
            <span>前日</span>
        </a>
        <div class="date-nav__current">
            <img src="{{ asset('images/logo/calender.png') }}" alt="" class="date-nav__calendar-icon" width="22" height="22">
            <span class="date-nav__current-text">{{ $currentDate->format('Y/m/d') }}</span>
        </div>
        <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="date-nav__link date-nav__link--next">
            <span>翌日</span>
            <img src="{{ asset('images/logo/arrow.png') }}" alt="" class="date-nav__arrow date-nav__arrow--next" width="20" height="20">
        </a>
    </nav>
    <div class="admin-attendance-list__table">
        <table class="attendance-list__table-inner">
            <thead class="attendance-list__table-header">
                <tr class="attendance-list__table-row">
                    <th class="attendance-list__table-cell" scope="col">名前</th>
                    <th class="attendance-list__table-cell" scope="col">出勤</th>
                    <th class="attendance-list__table-cell" scope="col">退勤</th>
                    <th class="attendance-list__table-cell" scope="col">休憩</th>
                    <th class="attendance-list__table-cell" scope="col">合計</th>
                    <th class="attendance-list__table-cell" scope="col">詳細</th>
                </tr>
            </thead>
            <tbody class="attendance-list__table-body">
                @foreach($attendances as $attendance)
                <tr class="attendance-list__table-row">
                    <td class="attendance-list__table-cell">{{ $attendance->user->name }}</td>
                    <td class="attendance-list__table-cell">
                    {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                    </td>
                    <td class="attendance-list__table-cell">
                    {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                    </td>
                    <td class="attendance-list__table-cell">{{ $attendance->total_rest_time }}</td>
                    <td class="attendance-list__table-cell">{{ $attendance->work_time }}</td>
                    <td class="attendance-list__table-cell">
                        <a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
