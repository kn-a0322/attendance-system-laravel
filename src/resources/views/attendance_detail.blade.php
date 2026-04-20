@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance_detail.css')}}">
@endsection

@section('content')
<div class="attendance-detail">
    <div class="attendance-detail__header">
        <h1 class="attendance-detail__heading">勤怠詳細</h1>
    </div>
    <form action="{{ route('attendance.update', $attendance->id) }}" method="post" class="attendance-detail__form">
        @csrf
    <div class="attendance-detail__body">
        <table class="attendance-detail__table">
            <tbody>
                <tr>
                    <th>名前</th>
                    <td><span class="attendance-detail__user-name">{{ $attendance->user->name }}</span></td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td><span class="attendance-detail__date">
                        {{ $attendance->date->format('Y') }}年
                        {{ $attendance->date->format('n') }}月{{ $attendance->date->format('j') }}日
                    </span></td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="attendance-detail__time-input">
                        <input type="text" name="clock_in" class="input-time" value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                        <span>~</span>
                        <input type="text" name="clock_out" class="input-time" value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                        </div>
                    </td>
                </tr>
                @foreach ($attendance->rests as $index => $rest)
                <tr>
                    <th>休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                    <td>
                        <input type="text" name="rest_start[]" value="{{ old("rest_start.$index", \Carbon\Carbon::parse($rest->rest_start)->format('H:i'))  }}">
                        <span>~</span>
                        <input type="text" name="rest_end[]" value="{{ old("rest_end.$index", \Carbon\Carbon::parse($rest->rest_end)->format('H:i')) }}">
                    </td>
                </tr>
                @endforeach
                <tr>
                    <th>休憩{{ $attendance->rests->count() > 0 ? $attendance->rests->count() + 1 : '2' }}</th>
                    <td>
                        <input type="text" name="rest_start[]" value="{{ old('rest_start.' . $attendance->rests->count()) }}">
                        <span>~</span>
                        <input type="text" name="rest_end[]" value="{{ old('rest_end.' . $attendance->rests->count()) }}">
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="note" rows="3">{{ old('note', $attendance->note) }}</textarea>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="attendance-detail__button">
            <button type="submit" class="attendance-detail__button-submit">修正</button>
        </div>
    </div>
    </form>
</div>
@endsection