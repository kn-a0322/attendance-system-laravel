@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/application.css')}}">
@endsection

@section('content')
<div class="application">
    <span class="application__title-accent" aria-hidden="true"></span>
    <h1 class="application__heading">申請一覧</h1>
</div>
<div class="application-tabs">
    <ul class="application-tabs__list">
        <li class="application-tabs__item">
            <a href="{{ route('stamp_correction_request.list', ['tab' => 'pending']) }}" class="application-tabs__link {{ request('tab', 'pending') == 'pending' ? 'is-active' : '' }}">承認待ち</a>
        </li>
        <li class="application-tabs__item">
            <a href="{{ route('stamp_correction_request.list', ['tab' => 'approved']) }}" class="application-tabs__link {{ request('tab') == 'approved' ? 'is-active' : '' }}">承認済み</a>
        </li>
    </ul>
</div>
<div class="application-list__table">
    <table class="application-list__table-inner">
        <thead class="application-list__table-header">
            <tr class="application-list__table-row">
                <th class="application-list__table-cell">状態</th>
                <th class="application-list__table-cell">名前</th>
                <th class="application-list__table-cell">対象日時</th>
                <th class="application-list__table-cell">申請理由</th>
                <th class="application-list__table-cell">申請日時</th>
                <th class="application-list__table-cell">詳細</th>
            </tr>
        </thead>
        <tbody class="application-list__table-body">
            @if(request('tab', 'pending') == 'pending')
               @foreach($pendingRequests as $pendingRequest)
               <tr class="application-list__table-row">
                <td class="application-list__table-cell">承認待ち</td>
                <td class="application-list__table-cell">{{ $pendingRequest->user->name }}</td>
                <td class="application-list__table-cell">{{ $pendingRequest->attendance->date->format('Y/m/d') }}</td>
                <td class="application-list__table-cell">{{ $pendingRequest->detail->remark ?? '備考なし' }}</td>
                <td class="application-list__table-cell">{{ $pendingRequest->created_at->format('Y/m/d') }}</td>
                <td class="application-list__table-cell"><a href="{{ route('attendance.detail', $pendingRequest->attendance_id) }}" class="application-list__table-link">詳細</a></td>
               </tr>
               @endforeach
            @else
               @foreach($approvedRequests as $approvedRequest)
               <tr class="application-list__table-row">
                <td class="application-list__table-cell">承認済み</td>
                <td class="application-list__table-cell">{{ $approvedRequest->user->name }}</td>
                <td class="application-list__table-cell">{{ $approvedRequest->attendance->date->format('Y/m/d') }}</td>
                <td class="application-list__table-cell">{{ $approvedRequest->detail->remark ?? '備考なし' }}</td>
                <td class="application-list__table-cell">{{ $approvedRequest->created_at->format('Y/m/d') }}</td>
                <td class="application-list__table-cell"><a href="{{ route('attendance.detail', $approvedRequest->attendance_id) }}" class="application-list__table-link">詳細</a></td>
               </tr>
               @endforeach
            @endif
        </tbody>
    </table>
</div>
@endsection