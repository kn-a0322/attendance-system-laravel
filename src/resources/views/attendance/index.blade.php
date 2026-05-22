@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
<div class="attendance-page">
    <h1 class="visually-hidden">勤怠登録画面</h1>

    <div class="attendance-container">
        <span class="attendance-status">
            @if($status === \App\Models\Attendance::STATUS_OUT_OF_WORK) 勤務外
            @elseif($status === \App\Models\Attendance::STATUS_WORKING) 出勤中
            @elseif($status === \App\Models\Attendance::STATUS_RESTING) 休憩中
            @elseif($status === \App\Models\Attendance::STATUS_FINISHED) 退勤済
            @endif
        </span>

        <p class="attendance-date">
            {{ now()->format('Y年m月d日') }}({{ ['日', '月', '火', '水', '木', '金', '土'][now()->format('w')] }})
        </p>

        <p class="attendance-time">
            {{ now()->format('H:i') }}
        </p>

        <div class="attendance-buttons">
            @if(session('success'))
                <p class="text-message success">{{ session('success') }}</p>
            @endif
            @if(session('error'))
                <p class="text-message error">{{ session('error') }}</p>
            @endif

            @if($status === \App\Models\Attendance::STATUS_OUT_OF_WORK)
                <form action="{{ route('attendance.start') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-start">出勤</button>
                </form>

            @elseif($status === \App\Models\Attendance::STATUS_WORKING)
                <div class="button-row">
                    <form action="{{ route('attendance.end') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-end">退勤</button>
                    </form>
                    <form action="{{ route('attendance.rest-start') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-rest">休憩入</button>
                    </form>
                </div>

            @elseif($status === \App\Models\Attendance::STATUS_RESTING)
                <form action="{{ route('attendance.rest-end') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-rest">休憩戻</button>
                </form>

            @elseif($status === \App\Models\Attendance::STATUS_FINISHED)
                <p class="text-message text-message--done">お疲れ様でした。</p>
            @endif
        </div>
    </div>
</div>
@endsection
