@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance_list.css')}}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="attendance-list__heading">勤怠一覧</h1>
    <div class="month-nav">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="month-nav__prev">&larr; 前月</a>
        <span class="month-nav__current">{{ $currentMonth->format('Y/m') }}</span>
        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="month-nav__next">&rarr; 翌月</a>
    </div>
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
                @foreach($attendances as $attendance)
                <tr class="attendance-list__table-row">
                    <td class="attendance-list__table-cell">
                        {{ $attendance->date->format('m/d') }}({{ ['日', '月', '火', '水', '木', '金', '土'][$attendance->date->format('w')] }})</td>
                    <td class="attendance-list__table-cell">
                        {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                    <td class="attendance-list__table-cell">
                        {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                    <td class="attendance-list__table-cell">
                        {{ $attendance->total_rest_time }}</td>
                    <td class="attendance-list__table-cell">
                        {{ $attendance->total_work_time }}</td>
                    <td class="attendance-list__table-cell">
                        <a href="{{ route('attendance.detail', $attendance->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection