@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request_list.css') }}">
@endsection

@section('content')
<div class="stamp-correction-list">
    <div class="stamp-correction-list__header">
        <span class="stamp-correction-list__title-accent" aria-hidden="true"></span>
        <h1 class="stamp-correction-list__heading">申請一覧</h1>
    </div>
    <nav class="stamp-correction-list__tabs" aria-label="申請の表示切替">
        <ul class="stamp-correction-list__tab-list">
            <li class="stamp-correction-list__tab-item">
                <a href="{{ route('stamp_correction_request.list', ['tab' => 'pending']) }}" class="stamp-correction-list__tab-link {{ request('tab', 'pending') == 'pending' ? 'is-active' : '' }}">承認待ち</a>
            </li>
            <li class="stamp-correction-list__tab-item">
                <a href="{{ route('stamp_correction_request.list', ['tab' => 'approved']) }}" class="stamp-correction-list__tab-link {{ request('tab') == 'approved' ? 'is-active' : '' }}">承認済み</a>
            </li>
        </ul>
    </nav>
    <div class="stamp-correction-list__tab-rule" role="presentation" aria-hidden="true"></div>
    <div class="stamp-correction-list__table">
        <table class="stamp-correction-list__table-inner">
            <thead class="stamp-correction-list__table-header">
                <tr class="stamp-correction-list__table-row">
                    <th class="stamp-correction-list__table-cell" scope="col">状態</th>
                    <th class="stamp-correction-list__table-cell" scope="col">名前</th>
                    <th class="stamp-correction-list__table-cell" scope="col">対象日時</th>
                    <th class="stamp-correction-list__table-cell" scope="col">申請理由</th>
                    <th class="stamp-correction-list__table-cell" scope="col">申請日時</th>
                    <th class="stamp-correction-list__table-cell" scope="col">詳細</th>
                </tr>
            </thead>
            <tbody class="stamp-correction-list__table-body">
                @if(request('tab', 'pending') == 'pending')
                    @foreach($pendingRequests as $pendingRequest)
                    <tr class="stamp-correction-list__table-row">
                        <td class="stamp-correction-list__table-cell">承認待ち</td>
                        <td class="stamp-correction-list__table-cell">{{ $pendingRequest->user->name }}</td>
                        <td class="stamp-correction-list__table-cell">{{ $pendingRequest->attendance->date->format('Y/m/d') }}</td>
                        <td class="stamp-correction-list__table-cell">{{ $pendingRequest->detail->remark ?? '備考なし' }}</td>
                        <td class="stamp-correction-list__table-cell">{{ $pendingRequest->created_at->format('Y/m/d') }}</td>
                        <td class="stamp-correction-list__table-cell"><a href="{{ route('attendance.detail', $pendingRequest->attendance_id) }}" class="stamp-correction-list__table-link">詳細</a></td>
                    </tr>
                    @endforeach
                @else
                    @foreach($approvedRequests as $approvedRequest)
                    <tr class="stamp-correction-list__table-row">
                        <td class="stamp-correction-list__table-cell">承認済み</td>
                        <td class="stamp-correction-list__table-cell">{{ $approvedRequest->user->name }}</td>
                        <td class="stamp-correction-list__table-cell">{{ $approvedRequest->attendance->date->format('Y/m/d') }}</td>
                        <td class="stamp-correction-list__table-cell">{{ $approvedRequest->detail->remark ?? '備考なし' }}</td>
                        <td class="stamp-correction-list__table-cell">{{ $approvedRequest->created_at->format('Y/m/d') }}</td>
                        <td class="stamp-correction-list__table-cell"><a href="{{ route('attendance.detail', $approvedRequest->attendance_id) }}" class="stamp-correction-list__table-link">詳細</a></td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
