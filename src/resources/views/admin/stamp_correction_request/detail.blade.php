@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <div class="attendance-detail__header">
        <span class="attendance-detail__title-accent" aria-hidden="true"></span>
        <h1 class="attendance-detail__heading">勤怠詳細</h1>
    </div>
    
    @if (session('success'))
        <p class="attendance-detail__message attendance-detail__message--success" role="status">{{ session('success') }}</p>
    @endif
    
    <form action="{{ route('admin.stamp_correction_request.approve', $request->id) }}" method="post" class="admin-detail__form">
        @csrf
        @method('PATCH')
        
    <div class="attendance-detail__body attendance-detail--readonly">
        <div class="attendance-detail__card">
            <table class="attendance-detail__table">
                <tbody>
                    <tr>
                        <th >名前</th>
                        <td>{{ $request->user->name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                            <td>
                                <span class="admin-detail__date">
                                    <span class="admin-detail__date-year">{{ \Carbon\Carbon::parse($request->detail->date)->format('Y') }}年</span>
                                    <span class="admin-detail__date-mdmj">{{ \Carbon\Carbon::parse($request->detail->date)->format('n') }}月{{ \Carbon\Carbon::parse($request->detail->date)->format('j') }}日</span>
                                </span>
                            </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>        
                            <span class="admin-detail__text">
                                <span class="admin-detail__time-hhmm">{{ \Carbon\Carbon::parse($request->detail->clock_in)->format('H:i') }}</span>
                                <span class="admin-detail__time-between admin-detail__time-between--static" aria-hidden="true">
                                    <span class="admin-detail__tilde">〜</span>
                                </span>
                                <span class="admin-detail__time-hhmm">{{ \Carbon\Carbon::parse($request->detail->clock_out)->format('H:i') }}</span>
                            </span>
                        </td>
                    </tr> 
                    @php
                        $rests = $request->rests->values();
                        $firstRest = $rests->get(0);
                        $secondRest = $rests->get(1);
                    @endphp
                    <tr>
                        <th>休憩</th>
                        <td>
                            @if($firstRest)
                                <span class="admin-detail__text">
                                    <span class="admin-detail__time-hhmm">{{ \Carbon\Carbon::parse($firstRest->rest_start)->format('H:i') }}</span>
                                    <span class="admin-detail__time-between admin-detail__time-between--static" aria-hidden="true">
                                        <span class="admin-detail__tilde">〜</span>
                                    </span>
                                    <span class="admin-detail__time-hhmm">{{ \Carbon\Carbon::parse($firstRest->rest_end)->format('H:i') }}</span>
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>休憩2</th>
                        <td>
                            @if($secondRest)
                                <span class="admin-detail__text">
                                    <span class="admin-detail__time-hhmm">{{ \Carbon\Carbon::parse($secondRest->rest_start)->format('H:i') }}</span>
                                    <span class="admin-detail__time-between admin-detail__time-between--static" aria-hidden="true">
                                        <span class="admin-detail__tilde">〜</span>
                                    </span>
                                    <span class="admin-detail__time-hhmm">{{ \Carbon\Carbon::parse($secondRest->rest_end)->format('H:i') }}</span>
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr class="attendance-detail__table-tr--remark">
                        <th>備考</th>
                        <td>
                            <textarea class="admin-detail__textarea" readonly>{{ $request->detail->remark }}</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="admin-detail__actions">
        @if ($request->status === 0)
            <button type="submit" class="admin-detail__approve-button">承認</button>
        @else
            <button type="submit" class="admin-detail__approved-label" disabled>承認済み</button>
        @endif
    </div>
    </form>
</div>
@endsection
