@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/attendance_detail.css')}}">
@endsection

@section('content')
<div class="attendance-detail">
    <div class="attendance-detail__header">
        <span class="attendance-detail__title-accent" aria-hidden="true"></span>
        <h1 class="attendance-detail__heading">勤怠詳細</h1>
    </div>

    @if($attendance->correctionRequests->where('status', 0)->first())
        @php
            //承認待ちの修正申請を取得
            $pendingRequest = $attendance->correctionRequests->where('status', 0)->first();
            //修正申請の詳細を取得
            $pendingDetail = $pendingRequest->detail;
        @endphp
        <div class="attendance-detail__body attendance-detail--readonly">
            <div class="attendance-detail__card">
                @if (session('success'))
                    <p class="attendance-detail__message attendance-detail__message--success" role="status">{{ session('success') }}</p>
                @endif
                <table class="attendance-detail__table">
                    <tbody>
                        <tr>
                            <th>名前</th>
                            <td>{{ $pendingRequest->user->name }}</td>
                        </tr>
                        <tr>
                            <th>日付</th>
                            <td>
                                <span class="attendance-detail__date">
                                    <span class="attendance-detail__date-year">{{ $attendance->date->format('Y') }}年</span>
                                    <span class="attendance-detail__date-mdmj">{{ $attendance->date->format('n') }}月{{ $attendance->date->format('j') }}日</span>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>出勤・退勤</th>
                            <td>
                                <span class="attendance-detail__time-text">
                                    <span class="attendance-detail__time-hhmm">{{$pendingDetail->clock_in->format('H:i')}}</span>
                                    <span class="attendance-detail__time-between attendance-detail__time-between--static" aria-hidden="true">
                                        <span class="attendance-detail__tilde">〜</span>
                                    </span>
                                    <span class="attendance-detail__time-hhmm">{{$pendingDetail->clock_out->format('H:i')}}</span>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>休憩</th>
                            <td>
                                <div class="attendance-detail__rest-readonly-list">
                                    @foreach($pendingRequest->rests as $rest)
                                    <div class="attendance-detail__time-text attendance-detail__rest-readonly-row">
                                        <span class="attendance-detail__time-hhmm">{{ $rest->rest_start->format('H:i') }}</span>
                                        <span class="attendance-detail__time-between attendance-detail__time-between--static" aria-hidden="true">
                                            <span class="attendance-detail__tilde">〜</span>
                                        </span>
                                        <span class="attendance-detail__time-hhmm">{{ $rest->rest_end->format('H:i') }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        <tr class="attendance-detail__table-tr--remark">
                            <th>備考</th>
                            <td>{{$pendingDetail->remark}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="attendance-detail__pending-notice">*承認待ちのため修正はできません。</p>
        </div>

    @else
    <form action="{{ route('attendance.correction.store', $attendance->id) }}" method="post" class="attendance-detail__form">
        @csrf
    <div class="attendance-detail__body">
        <div class="attendance-detail__card">
        @if ($errors->any())
        <p class="attendance-detail__error-summary" role="alert">
            <strong>入力内容に誤りがあります。</strong> 各項目の下のメッセージをご確認ください。
        </p>
        @endif

        <table class="attendance-detail__table">
            <tbody>
                <tr>
                    <th>名前</th>
                    <td><span class="attendance-detail__user-name">{{ $attendance->user->name }}</span></td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        <input type="hidden" name="date" value="{{ $attendance->date->format('Y-m-d') }}">
                        @error('date')
                        <p class="attendance-detail__error-message attendance-detail__error-message--field">{{ $message }}</p>
                        @enderror
                        <span class="attendance-detail__date">
                            <span class="attendance-detail__date-year">{{ $attendance->date->format('Y') }}年</span>
                            <span class="attendance-detail__date-mdmj">{{ $attendance->date->format('n') }}月{{ $attendance->date->format('j') }}日</span>
                    </span>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="attendance-detail__time-input">
                        <input type="text" name="clock_in" class="input-time" value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                        <div class="attendance-detail__time-between" aria-hidden="true">
                            <span class="attendance-detail__tilde">〜</span>
                        </div>
                        <input type="text" name="clock_out" class="input-time" value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                        </div>
                        @error('clock_in')
                        <p class="attendance-detail__error-message">{{ $message }}</p>
                        @enderror
                        @error('clock_out')
                        <p class="attendance-detail__error-message">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
                @foreach ($attendance->rests as $index => $rest)
                <tr>
                    <th>休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                    <td>
                        <div class="attendance-detail__time-input">
                        <input type="text" name="rest_start[]" class="input-time" value="{{ old("rest_start.$index", \Carbon\Carbon::parse($rest->rest_start)->format('H:i'))  }}">
                        <div class="attendance-detail__time-between" aria-hidden="true">
                            <span class="attendance-detail__tilde">〜</span>
                        </div>
                        <input type="text" name="rest_end[]" class="input-time" value="{{ old("rest_end.$index", \Carbon\Carbon::parse($rest->rest_end)->format('H:i')) }}">
                        </div>
                        @error("rest_start.$index")
                        <p class="attendance-detail__error-message">{{ $message }}</p>
                        @enderror
                        @error("rest_end.$index")
                        <p class="attendance-detail__error-message">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
                @endforeach
                @php
                    $restExtraIndex = $attendance->rests->count();
                @endphp
                <tr>
                    <th>休憩{{ $attendance->rests->count() > 0 ? $attendance->rests->count() + 1 : '2' }}</th>
                    <td>
                        <div class="attendance-detail__time-input">
                        <input type="text" name="rest_start[]" class="input-time" value="{{ old('rest_start.' . $restExtraIndex) }}">
                        <div class="attendance-detail__time-between" aria-hidden="true">
                            <span class="attendance-detail__tilde">〜</span>
                        </div>
                        <input type="text" name="rest_end[]" class="input-time" value="{{ old('rest_end.' . $restExtraIndex) }}">
                        </div>
                        @error("rest_start.$restExtraIndex")
                        <p class="attendance-detail__error-message">{{ $message }}</p>
                        @enderror
                        @error("rest_end.$restExtraIndex")
                        <p class="attendance-detail__error-message">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
                <tr class="attendance-detail__table-tr--remark">
                    <th>備考</th>
                    <td>
                        <textarea name="remark" rows="3">{{ old('remark', $attendance->remark) }}</textarea>
                        @error('remark')
                        <p class="attendance-detail__error-message">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
        <div class="attendance-detail__button">
            <button type="submit" class="attendance-detail__button-submit">修正</button>
        </div>
    </div>
    </form>
    @endif  
</div>
@endsection