@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <div class="attendance-list__header">
        <span class="attendance-list__title-accent" aria-hidden="true"></span>
        <h1 class="attendance-list__heading">勤怠一覧</h1>
    </div>
    <nav class="month-nav" aria-label="月の切り替え">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="month-nav__link month-nav__link--prev">
            <img src="{{ asset('images/logo/arrow.png') }}" alt="" class="month-nav__arrow month-nav__arrow--prev" width="20" height="20">
            <span>前月</span>
        </a>
        <div class="month-nav__current">
            <img src="{{ asset('images/logo/calender.png') }}" alt="" class="month-nav__calendar-icon" width="22" height="22">
            <span class="month-nav__current-text">{{ $currentMonth->format('Y/m') }}</span>
        </div>
        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="month-nav__link month-nav__link--next">
            <span>翌月</span>
            <img src="{{ asset('images/logo/arrow.png') }}" alt="" class="month-nav__arrow month-nav__arrow--next" width="20" height="20">
        </a>
    </nav>
    <div class="attendance-list__table">
        <table class="attendance-list__table-inner">
            <thead class="attendance-list__table-header">
                <tr class="attendance-list__table-row">
                    <th class="attendance-list__table-cell">日付</th>
                    <th class="attendance-list__table-cell">出勤</th>
                    <th class="attendance-list__table-cell">退勤</th>
                    <th class="attendance-list__table-cell">休憩</th>
                    <th class="attendance-list__table-cell">合計</th>
                    <th class="attendance-list__table-cell">詳細</th>
                </tr>
            </thead>
            <tbody class="attendance-list__table-body">
                @foreach($calendarDays as $day)
                {{-- $attendancesの中から、'date'カラムが$dayの日付と一致するものを1件探す --}}
                @php
                    $attendance = $attendances->first(function($attendance) use ($day) {
                        $dbDate = $attendance->date instanceof \Carbon\Carbon ? $attendance->date->format('Y-m-d') : $attendance->date;
                        return $dbDate === $day->format('Y-m-d');
                    });
                @endphp

                <tr class="attendance-list__table-row">
                    <td class="attendance-list__table-cell">
                        {{ $day->format('m/d') }}({{ ['日', '月', '火', '水', '木', '金', '土'][$day->format('w')] }})
                    </td>

                    @if($attendance)
                        <td class="attendance-list__table-cell">
                            {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                        </td>
                        <td class="attendance-list__table-cell">
                        {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                        </td>
                        <td class="attendance-list__table-cell">
                            {{ $attendance->total_rest_time }}
                        </td>
                        <td class="attendance-list__table-cell">
                            {{ $attendance->work_time ?? '' }}
                        </td>
                        <td class="attendance-list__table-cell">
                            <a href="{{ route('attendance.detail', $attendance->id) }}" class="detail-link">詳細</a>
                        </td>
    
                    @else
                        <td class="attendance-list__table-cell"></td>
                        <td class="attendance-list__table-cell"></td>
                        <td class="attendance-list__table-cell"></td>
                        <td class="attendance-list__table-cell"></td>
                        <td class="attendance-list__table-cell"></td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection