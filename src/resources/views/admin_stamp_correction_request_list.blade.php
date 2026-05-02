@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_stamp_correction_request_list.css')}}">
@endsection

@section('content')
<div class="admin-stamp-correction-request-list">
    <div class="admin-stamp-correction-request-list__header">
        <span class="admin-stamp-correction-request-list__title-accent" aria-hidden="true"></span>
        <h1 class="admin-stamp-correction-request-list__heading">申請一覧</h1>
    </div>
<nav class="stamp-correction-list__tabs" aria-label="申請の表示切替">
    <ul class="stamp-correction-list__tab-list">
        <li class="stamp-correction-list__tab-item">
            <a href="{{ route('admin.stamp_correction_request.list', ['status' => 0]) }}" class="stamp-correction-list__tab-link {{ request('status', 0) == 0 ? 'is-active' : '' }}">承認待ち</a>
        </li>
        <li class="stamp-correction-list__tab-item">
            <a href="{{ route('admin.stamp_correction_request.list', ['status' => 1]) }}" class="stamp-correction-list__tab-link {{ request('status', 1) == 1 ? 'is-active' : '' }}">承認済み</a>
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
            @forelse($requests as $request)
                <tr class="stamp-correction-list__table-row">
                    <td class="stamp-correction-list__table-cell">{{ $request->status == 0 ? '承認待ち' : '承認済み' }}</td>
                    <td class="stamp-correction-list__table-cell">{{ $request->user->name }}</td>
                    <td class="stamp-correction-list__table-cell">{{ $request->detail->date ? \Carbon\Carbon::parse($request->detail->date)->format('Y/m/d') : '' }}</td>
                    <td class="stamp-correction-list__table-cell">{{ $request->detail->remark }}</td>
                    <td class="stamp-correction-list__table-cell">{{ $request->created_at->format('Y/m/d H:i') }}</td>
                    <td class="stamp-correction-list__table-cell"><a href="{{ route('admin.stamp_correction_request.show', ['id' => $request->id]) }}" class="stamp-correction-list__table-link">詳細</a></td>
                </tr>
            @empty
                <tr class="stamp-correction-list__table-row">
                    <td class="stamp-correction-list__table-cell" colspan="6">申請がありません</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
@endsection